<?php
/**
 * Class Recurring
 *
 * @package Opencart\Catalog\Model\Checkout
 */
class ModelCheckoutRecurring extends Model {
	/**
	 * Add History
	 *
	 * @param int    $order_recurring_id
	 * @param int    $status
	 * @param string $comment
	 * @param bool   $notify
	 *
	 * @return void
	 */
	public function addHistory(int $order_recurring_id, int $status, string $comment = '', bool $notify = false): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_history` SET `order_recurring_id` = '" . (int)$order_recurring_id . "', `status` = '" . (int)$status . "', `comment` = '" . $this->db->escape($comment) . "', `notify` = '" . (bool)$notify . "'");
	}
}
