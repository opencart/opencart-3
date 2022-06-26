<?php
class ModelAccountSubscription extends Model {
	public function editStatus($subscription_id, $status) {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `status` = '" . (bool)$status . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	public function getSubscription($subscription_id) {
		$query = $this->db->query("SELECT `s`.*, `o`.`payment_method`, `o`.`payment_code`, `o`.`currency_code` FROM `" . DB_PREFIX . "subscription` `s` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`s`.`order_id` = `o`.`order_id`) WHERE `s`.`subscription_id` = '" . (int)$subscription_id . "' AND `o`.`customer_id` = '" . (int)$this->customer->getId() . "'");

		return $query->row;
	}

	public function getSubscriptions($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$query = $this->db->query("SELECT `o`.*, `o`.`payment_method`, `o`.`currency_id`, `o`.`currency_value` FROM `" . DB_PREFIX . "subscription` `s` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`s`.`order_id` = o.`order_id`) WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "' ORDER BY `o`.`order_id` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}
	
	public function getSubscriptionByReference($reference) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription` WHERE `reference` = '" . $this->db->escape($reference) . "'");

		return $query->row;
	}

	public function getTotalSubscriptions() {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription` `s` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`s`.`order_id` = `o`.`order_id`) WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}

	public function getTransactions($subscription_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription_transaction` WHERE `subscription_id` = '" . (int)$subscription_id . "'");

		return $query->rows;
	}

	public function addTransaction($subscription_id, $order_id, $transaction_id, $description, $amount, $type, $payment_method, $payment_code) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_transaction` SET `subscription_id` = '" . (int)$subscription_id . "', `order_id` = '" . (int)$order_id . "', `transaction_id` = '" . (int)$transaction_id . "', `description` = '" . $this->db->escape($description) . "', `amount` = '" . (float)$amount . "', `type` = '" . (int)$type . "', `payment_method` = '" . $this->db->escape($payment_method) . "', `payment_code` = '" . $this->db->escape($payment_code) . "', `date_added` = NOW()");
	}
}
