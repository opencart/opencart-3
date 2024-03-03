<?php
/**
 * Class Alipay Cross
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentAlipayCross extends Model {
	public $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	public $https_verify_url_test = 'https://openapi.alipaydev.com/gateway.do?service=notify_verify&';
	public $alipay_config;

	/**
	 * Get Method
	 *
	 * @param array<string, mixed> $address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/alipay_cross');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_alipay_cross_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_alipay_cross_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'alipay_cross',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_alipay_cross_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * Build Request Mysign
	 *
	 * @param array<string, mixed> $para_sort
	 *
	 * @return string
	 */
	private function buildRequestMysign(array $para_sort): string {
		$prestr = $this->createLinkstring($para_sort);

		$mysign = '';

		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case 'MD5':
				$mysign = $this->md5Sign($prestr, $this->alipay_config['key']);
				break;
			default:
				$mysign = '';
		}

		return $mysign;
	}

	/**
	 * Build Request Para
	 *
	 * @param mixed $alipay_config
	 * @param mixed $para_temp
	 */
	public function buildRequestPara($alipay_config, $para_temp) {
		$this->alipay_config = $alipay_config;

		$para_filter = $this->paraFilter($para_temp);

		$para_sort = $this->argSort($para_filter);

		$mysign = $this->buildRequestMysign($para_sort);

		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));

		return $para_sort;
	}

	/**
	 * Verify Notify
	 *
	 * @param mixed $alipay_config
	 *
	 * @return bool
	 */
	public function verifyNotify($alipay_config): bool {
		$this->alipay_config = $alipay_config;

		if (empty($_POST)) {
			return false;
		} else {
			$isSign = $this->getSignVerify($_POST, $_POST['sign']);

			$responseTxt = 'false';

			if (!empty($_POST['notify_id'])) {
				$responseTxt = $this->getResponse($_POST['notify_id']);
			}

			// Verify
			if (preg_match("/true$/i", $responseTxt) && $isSign) {
				return true;
			} else {
				$this->log->write($responseTxt);

				return false;
			}
		}
	}

	/**
	 * Get Sign Verify
	 *
	 * @param array<string, mixed> $para_temp
	 * @param string               $sign
	 *
	 * @return bool
	 */
	private function getSignVerify($para_temp, $sign): bool {
		$para_filter = $this->paraFilter($para_temp);

		$para_sort = $this->argSort($para_filter);

		$prestr = $this->createLinkstring($para_sort);

		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case 'MD5':
				$isSgin = $this->md5Verify($prestr, $sign, $this->alipay_config['key']);
				break;
			default:
				$isSgin = false;
		}

		return $isSgin;
	}

	/**
	 * Get Response
	 *
	 * @param string $notify_id
	 *
	 * @return mixed
	 */
	private function getResponse(string $notify_id): mixed {
		$partner = trim($this->alipay_config['partner']);

		$verify_url = $this->config->get('payment_alipay_cross_test') == 'sandbox' ? $this->https_verify_url_test : $this->https_verify_url;
		$verify_url .= 'partner=' . $partner . '&notify_id=' . $notify_id;

		return $this->getHttpResponseGET($verify_url, $this->alipay_config['cacert']);
	}

	/**
	 * Create Linkstring
	 *
	 * @param array<string, mixed> $para
	 *
	 * @return string
	 */
	private function createLinkstring(array $para): string {
		return http_build_query($para, '', '&');
	}

	/**
	 * Para Filter
	 *
	 * @param array<string, mixed> $para
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function paraFilter(array $para): array {
		$para_filter = [];

		foreach ($para as $key => $val) {
			if ($key == 'sign' || $key == 'sign_type' || $val == '') {
				continue;
			} else {
				$para_filter[$key] = $para[$key];
			}
		}

		return $para_filter;
	}

	/**
	 * Arg Sort
	 *
	 * @param array<string, mixed> $para
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function argSort(array $para): array {
		ksort($para);

		reset($para);

		return $para;
	}

	/**
	 * Get Http Response GET
	 *
	 * @param string $url
	 * @param string $cacert_url
	 *
	 * @return mixed
	 */
	private function getHttpResponseGET(string $url, string $cacert_url): mixed {
		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_CAINFO, $cacert_url);

		$responseText = curl_exec($curl);

		if (!$responseText) {
			$this->log->write('ALIPAY NOTIFY CURL_ERROR: ' . var_export(curl_error($curl), true));
		}

		curl_close($curl);

		return $responseText;
	}

	/**
	 * Md5 Sign
	 *
	 * @param string $prestr
	 * @param string $key
	 *
	 * @return string
	 */
	private function md5Sign(string $prestr, string $key): string {
		$prestr .= $key;

		return md5($prestr);
	}

	/**
	 * Md5 Verify
	 *
	 * @param string $prestr
	 * @param string $sign
	 * @param string $key
	 */
	private function md5Verify($prestr, $sign, $key): bool {
		$prestr .= $key;
		$mysgin = md5($prestr);

		if ($mysgin == $sign) {
			return true;
		} else {
			return false;
		}
	}
}
