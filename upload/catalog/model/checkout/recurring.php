<?php
class ModelCheckoutRecurring extends Model {
	public function editReference($order_recurring_id, $reference) {
		$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `reference` = '" . $this->db->escape($reference) . "' WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");

		if ($this->db->countAffected() > 0) {
			return true;
		} else {
			return false;
		}
	}
}