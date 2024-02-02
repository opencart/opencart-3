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
	 * @param string $comment
	 *
	 * @return void
	 */
	public function addHistory(int $order_recurring_id, string $comment): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_history` SET `order_recurring_id` = '" . (int)$order_recurring_id . "', `comment` = '" . $this->db->escape(strip_tags($comment)) . "', `date_added` = NOW()");
	}
}
