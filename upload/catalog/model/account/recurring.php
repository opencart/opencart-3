<?php
class ModelAccountRecurring extends Model {
	public function getOrderRecurring(int $order_recurring_id): array {
		$query = $this->db->query("SELECT `or`.*, o.`payment_method`, o.`payment_code`, o.`currency_code` FROM `" . DB_PREFIX . "order_recurring` `or` LEFT JOIN `" . DB_PREFIX . "order` o ON `or`.`order_id` = o.`order_id` WHERE `or`.`order_recurring_id` = '" . (int)$order_recurring_id . "' AND o.`customer_id` = '" . (int)$this->customer->getId() . "'");

		return $query->row;
	}

	public function getOrderRecurrings(int $start = 0, int $limit = 20): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$query = $this->db->query("SELECT `or`.*, o.`payment_method`, o.`currency_id`, o.`currency_value` FROM `" . DB_PREFIX . "order_recurring` `or` LEFT JOIN `" . DB_PREFIX . "order` o ON `or`.`order_id` = o.`order_id` WHERE o.`customer_id` = '" . (int)$this->customer->getId() . "' ORDER BY o.`order_id` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}
	
	public function getOrderRecurringByReference(string $reference): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE `reference` = '" . $this->db->escape($reference) . "'");

		return $query->row;
	}

	public function getOrderRecurringTransactions(int $order_recurring_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring_transaction` WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");

		return $query->rows;
	}

	public function getTotalOrderRecurrings(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_recurring` `or` LEFT JOIN `" . DB_PREFIX . "order` o ON `or`.`order_id` = o.`order_id` WHERE o.`customer_id` = '" . (int)$this->customer->getId() . "'");

		return (int)$query->row['total'];
	}
	
	public function editOrderRecurringStatus(int $order_recurring_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = '" . (int)$status . "' WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");
	}
}