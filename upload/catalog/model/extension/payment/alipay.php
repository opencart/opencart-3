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
	 * getMethod
	 *
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethod(array $address): array {
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

	private function setParams($alipay_config): void {
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
	 * pagePay
	 *
	 * @param mixed $builder
	 * @param mixed $config
	 */
	public function pagePay($builder, $config) {
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
	 * check
	 *
	 * @param mixed $arr
	 * @param mixed $config
	 */
	public function check($arr, $config) {
		$this->setParams($config);

		return $this->rsaCheckV1($arr, $this->signtype);
	}

	/**
	 * pageExecute
	 *
	 * @param mixed $request
	 * @param mixed $httpmethod
	 */
	public function pageExecute($request, $httpmethod = 'POST') {
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

		$total_params = [];

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

	private function checkEmpty($value) {
		if (!isset($value)) {
			return true;
		}
		if ($value === null) {
			return true;
		}

		return (bool)(trim($value) === '');
	}

	/**
	 * rsaCheckV1
	 *
	 * @param mixed $params
	 * @param mixed $signType
	 */
	public function rsaCheckV1($params, $signType = 'RSA') {
		$sign = $params['sign'];

		$params['sign_type'] = null;
		$params['sign'] = null;

		return $this->verify($this->getSignContent($params), $sign, $signType);
	}

	/**
	 * Verify
	 *
	 * @param mixed $data
	 * @param mixed $sign
	 * @param mixed $signType
	 */
	private function verify($data, $sign, $signType = 'RSA') {
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

	private function getSignContent($params) {
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

	private function generateSign($params, $signType = "RSA") {
		return $this->sign($this->getSignContent($params), $signType);
	}

	private function sign($data, $signType = "RSA") {
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
	 * getPostCharset
	 */
	public function getPostCharset() {
		return trim($this->post_charset);
	}
}
