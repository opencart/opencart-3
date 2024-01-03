<?php
/**
 * Class Bluepay Hosted
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentBluePayHosted extends Model {
	/**
	 * getMethod
	 *
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethod(array $address): array {
		$this->load->language('extension/payment/bluepay_hosted');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_bluepay_hosted_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_bluepay_hosted_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'bluepay_hosted',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_bluepay_hosted_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * addOrder
	 *
	 * @param array $order_info
	 * @param array $response_data
	 */
	public function addOrder(array $order_info, array $response_data): int {
		if ($this->config->get('payment_bluepay_hosted_transaction') == 'SALE') {
			$release_status = 1;
		} else {
			$release_status = null;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "bluepay_hosted_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `transaction_id` = '" . $this->db->escape($response_data['RRNO']) . "', `date_added` = NOW(), `date_modified` = NOW(), `release_status` = '" . (int)$release_status . "', `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `total` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "'");

		return $this->db->getLastId();
	}

	/**
	 * addTransaction
	 *
	 * @param int    $bluepay_hosted_order_id
	 * @param string $type
	 * @param array  $order_info
	 *
	 * @return void
	 */
	public function addTransaction(int $bluepay_hosted_order_id, string $type, array $order_info): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "bluepay_hosted_order_transaction` SET `bluepay_hosted_order_id` = '" . (int)$bluepay_hosted_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "'");
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_bluepay_hosted_debug') == 1) {
			// Log
			$log = new \Log('bluepay_hosted.log');
			$log->write($message);
		}
	}
}
