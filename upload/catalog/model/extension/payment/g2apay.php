<?php
/**
 * Class G2apay
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentG2APay extends Model {
	/**
	 * Get Method
	 *
	 * @param array<string, mixed> $address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/g2apay');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_g2apay_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_g2apay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'g2apay',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_g2apay_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * Add G2 aOrder
	 *
	 * @param array<string, mixed> $order_info
	 *
	 * @return void
	 */
	public function addG2aOrder(array $order_info): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "g2apay_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `date_added` = NOW(), `modified` = NOW(), `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `total` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "'");
	}

	/**
	 * Update Order
	 *
	 * @param int    $g2apay_order_id
	 * @param string $g2apay_transaction_id
	 * @param string $type
	 * @param array  $order_info
	 *
	 * return void
	 */
	public function updateOrder(int $g2apay_order_id, string $g2apay_transaction_id, string $type, array $order_info): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "g2apay_order` SET `g2apay_transaction_id` = '" . $this->db->escape($g2apay_transaction_id) . "', `type` = '" . $this->db->escape($type) . "', `modified` = NOW() WHERE `order_id` = '" . (int)$order_info['order_id'] . "'");

		$this->addTransaction($g2apay_order_id, $type, $order_info);
	}

	/**
	 * Add Transaction
	 *
	 * @param int    $g2apay_order_id
	 * @param string $type
	 * @param array  $order_info
	 *
	 * @return void
	 */
	public function addTransaction(int $g2apay_order_id, string $type, array $order_info): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "g2apay_order_transaction` SET `g2apay_order_id` = '" . (int)$g2apay_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "'");
	}

	/**
	 * Get G2a Order
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getG2aOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "g2apay_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * Send Curl
	 *
	 * @param string $url
	 * @param array  $fields
	 *
	 * @return ?object
	 */
	public function sendCurl(string $url, array $fields): ?object {
		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);

		$response = curl_exec($curl);

		curl_close($curl);

		return json_decode($response, true);
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_g2apay_debug') == 1) {
			$backtrace = debug_backtrace();

			// Log
			$log = new \Log('g2apay.log');
			$log->write('Origin: ' . $backtrace[6]['class'] . '::' . $backtrace[6]['function']);
			$log->write(print_r($message, 1));
		}
	}
}
