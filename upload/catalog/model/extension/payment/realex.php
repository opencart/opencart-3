<?php
/**
 * Class Realex
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentRealex extends Model {
	/**
	 * Get Methods
	 *
	 * @param array<string, mixed> $address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/realex');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_realex_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_realex_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'realex',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_realex_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * Add Order
	 *
	 * @param array<string, mixed> $order_info
	 * @param string               $pas_ref
	 * @param string               $auth_code
	 * @param string               $account
	 * @param string               $order_ref
	 *
	 * @return int
	 */
	public function addOrder(array $order_info, string $pas_ref, string $auth_code, string $account, string $order_ref): int {
		if ($this->config->get('payment_realex_auto_settle') == 1) {
			$settle_status = 1;
		} else {
			$settle_status = 0;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "realex_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `settle_type` = '" . (int)$this->config->get('payment_realex_auto_settle') . "', `order_ref` = '" . $this->db->escape($order_ref) . "', `order_ref_previous` = '" . $this->db->escape($order_ref) . "', `date_added` = NOW(), `date_modified` = NOW(), `capture_status` = '" . (int)$settle_status . "', `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `pasref` = '" . $this->db->escape($pas_ref) . "', `pasref_previous` = '" . $this->db->escape($pas_ref) . "', `authcode` = '" . $this->db->escape($auth_code) . "', `account` = '" . $this->db->escape($account) . "', `total` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) . "'");

		return $this->db->getLastId();
	}

	/**
	 * Add Transaction
	 *
	 * @param int                  $realex_order_id
	 * @param string               $type
	 * @param array<string, mixed> $order_info
	 *
	 * @return void
	 */
	public function addTransaction(int $realex_order_id, string $type, array $order_info): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "realex_order_transaction` SET `realex_order_id` = '" . (int)$realex_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) . "'");
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_realex_debug') == 1) {
			// Log
			$log = new \Log('realex.log');
			$log->write($message);
		}
	}
}
