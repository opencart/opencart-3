<?php
/**
 * Class Recurring
 *
 * @example $recurring_model = $this->model_checkout_recurring;
 *
 * Can be called from $this->load->model('checkout/recurring');
 *
 * @package Catalog\Model\Checkout
 */
class ModelCheckoutRecurring extends Model {
	/**
	 * Add Recurring Report
	 *
	 * @param int    $order_recurring_id primary key of the order recurring record
	 * @param int    $store_id
	 * @param string $ip
	 * @param string $country
	 *
	 * @return void
	 */
	public function addReport(int $order_recurring_id, int $store_id, string $ip, string $country): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_report` SET `order_recurring_id` = '" . (int)$order_recurring_id . "', `store_id` = '" . (int)$store_id . "', `ip` = '" . $this->db->escape($ip) . "', `country` = '" . $this->db->escape($country) . "', `date_added` = NOW()");
	}

	/**
	 * Get Order Recurring
	 *
	 * @param int $order_recurring_id primary key of the order recurring record
	 *
	 * @return array<string, mixed> recurring record that has order recurring ID
	 */
	public function getOrderRecurring(int $order_recurring_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");

		return $query->row;
	}
}
