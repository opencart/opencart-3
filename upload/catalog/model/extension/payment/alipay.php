<?php
/**
 * Class Alipay
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentAlipay extends Model {
	private string $api_method_name = 'alipay.trade.page.pay';
	private string $post_charset = 'UTF-8';
	private string $alipay_sdk_version = 'alipay-sdk-php-20161101';
	private string $api_version = '1.0';
	private string $log_file_name = 'alipay.log';
	private string $gateway_url = 'https://openapi.alipay.com/gateway.do';
	private string $format = 'json';
	private string $signtype = 'RSA2';
	private string $alipay_public_key;
	private string $private_key;
	private string $appid;
	private string $notify_url;
	private string $return_url;
	private array $api_params = [];

	/**
	 * Get Method
	 *
	 * @param array<string, mixed> $address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/alipay');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_alipay_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_alipay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'alipay',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_alipay_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * Set Params
	 *
	 * @param array<string, mixed> $alipay_config
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	private function setParams(array $alipay_config): void {
		$this->gateway_url = $alipay_config['gateway_url'];
		$this->appid = $alipay_config['app_id'];
		$this->private_key = $alipay_config['merchant_private_key'];
		$this->alipay_public_key = $alipay_config['alipay_public_key'];
		$this->post_charset = $alipay_config['charset'];
		$this->signtype = $alipay_config['sign_type'];
		$this->notify_url = $alipay_config['notify_url'];
		$this->return_url = $alipay_config['return_url'];

		if (empty($this->appid) || trim($this->appid) == '') {
			throw new \Exception('appid should not be NULL!');
		}

		if (empty($this->private_key) || trim($this->private_key) == '') {
			throw new \Exception('private_key should not be NULL!');
		}

		if (empty($this->alipay_public_key) || trim($this->alipay_public_key) == '') {
			throw new \Exception('alipay_public_key should not be NULL!');
		}

		if (empty($this->post_charset) || trim($this->post_charset) == '') {
			throw new \Exception('charset should not be NULL!');
		}

		if (empty($this->gateway_url) || trim($this->gateway_url) == '') {
			throw new \Exception('gateway_url should not be NULL!');
		}
	}

	/**
	 * Page Pay
	 *
	 * @param array<string, mixed> $builder
	 * @param array<string, mixed> $config
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function pagePay(array $builder, array $config): array {
		$this->setParams($config);

		$biz_content = null;

		if (!empty($builder)) {
			$biz_content = json_encode($builder, JSON_UNESCAPED_UNICODE);
		}

		// Log
		$log = new \Log($this->log_file_name);
		$log->write($biz_content);

		$this->api_params['biz_content'] = $biz_content;

		$response = $this->pageExecute($this, 'post');

		$log->write("response: " . var_export($response, true));

		return $response;
	}

	/**
	 * Check
	 *
	 * @param array<string, mixed> $arr
	 * @param array<string, mixed> $config
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function check(array $arr, array $config): array {
		$this->setParams($config);

		return $this->rsaCheckV1($arr, $this->signtype);
	}

	/**
	 * Page Execute
	 *
	 * @param mixed  $request
	 * @param string $httpmethod
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function pageExecute($request, string $httpmethod = 'POST') {
		$iv = $this->api_version;

		$sys_params = [];

		$sys_params['app_id'] = $this->appid;
		$sys_params['version'] = $iv;
		$sys_params['format'] = $this->format;
		$sys_params['sign_type'] = $this->signtype;
		$sys_params['method'] = $this->api_method_name;
		$sys_params['timestamp'] = date('Y-m-d H:i:s');
		$sys_params['alipay_sdk'] = $this->alipay_sdk_version;
		$sys_params['notify_url'] = $this->notify_url;
		$sys_params['return_url'] = $this->return_url;
		$sys_params['charset'] = $this->post_charset;
		$sys_params['gateway_url'] = $this->gateway_url;

		$api_params = $this->api_params;

		$total_params = array_merge($api_params, $sys_params);
		$total_params['sign'] = $this->generateSign($total_params, $this->signtype);

		if (strtoupper($httpmethod) == 'GET') {
			$pre_string = $this->getSignContent(urlencode($total_params));

			return $this->gateway_url . '?' . $pre_string;
		} else {
			foreach ($total_params as $key => $value) {
				if ($this->checkEmpty($value) === false) {
					$value = str_replace("\"", "&quot;", $value);

					$total_params[$key] = $value;
				} else {
					unset($total_params[$key]);
				}
			}

			return $total_params;
		}
	}

	/**
	 * Check Empty
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	private function checkEmpty(string $value): bool {
		if (!isset($value)) {
			return true;
		}

		if ($value === null) {
			return true;
		}

		return (bool)(trim($value) === '');
	}

	/**
	 * Rsa Check V1
	 *
	 * @param array<string, mixed> $params
	 * @param string               $signType
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function rsaCheckV1(array $params, string $signType = 'RSA'): array {
		$sign = $params['sign'];

		$params['sign_type'] = null;
		$params['sign'] = null;

		return $this->verify($this->getSignContent($params), $sign, $signType);
	}

	/**
	 * Verify
	 *
	 * @param mixed  $data
	 * @param mixed  $sign
	 * @param string $signType
	 *
	 * return bool
	 */
	private function verify($data, $sign, $signType = 'RSA'): bool {
		$pub_key = $this->alipay_public_key;

		$res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($pub_key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";

		(trim($pub_key)) || exit('Alipay public key error!');

		if ($signType == 'RSA2') {
			$result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
		} else {
			$result = (bool)openssl_verify($data, base64_decode($sign), $res);
		}

		return $result;
	}

	/**
	 * Get Sign Content
	 *
	 * @param array<string, mixed> $params
	 *
	 * @return string
	 */
	private function getSignContent(array $params): string {
		ksort($params);

		$string_to_be_signed = '';

		$i = 0;

		foreach ($params as $k => $v) {
			if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
				if ($i == 0) {
					$string_to_be_signed .= "{$k}" . "=" . "{$v}";
				} else {
					$string_to_be_signed .= "&" . "{$k}" . "=" . "{$v}";
				}

				$i++;
			}
		}

		unset($k, $v);

		return $string_to_be_signed;
	}

	/**
	 * Generate Sign
	 *
	 * @param array<string, mixed> $params
	 * @param string               $signType
	 *
	 * @return array<string, mixed>
	 */
	private function generateSign(array $params, string $signType = 'RSA'): array {
		return $this->sign($this->getSignContent($params), $signType);
	}

	/**
	 * Sign
	 *
	 * @param array<string, mixed> $data
	 * @param string               $signType
	 *
	 * @return string
	 */
	private function sign(array $data, string $signType = 'RSA'): string {
		$pri_key = $this->private_key;

		$res = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($pri_key, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";

		if ($signType == 'RSA2') {
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		} else {
			openssl_sign($data, $sign, $res);
		}

		return base64_encode($sign);
	}

	/**
	 * Get Post Charset
	 *
	 * @return string
	 */
	public function getPostCharset(): string {
		return trim($this->post_charset);
	}
}
