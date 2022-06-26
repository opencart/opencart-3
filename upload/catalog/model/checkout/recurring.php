<?php
class ModelCheckoutRecurring extends Model {
	public function editReference($subscription_id, $reference) {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `reference` = '" . $this->db->escape($reference) . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");

		if ($this->db->countAffected() > 0) {
			return true;
		} else {
			return false;
		}
	}
}