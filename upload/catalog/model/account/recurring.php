<?php
/**
 * Class Recurring
 *
 * @package Catalog\Model\Account
 */
class ModelAccountRecurring extends Model {
	/**
	 * Edit Status
	 *
	 * @param int $order_recurring_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function editStatus(int $order_recurring_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = '" . (int)$status . "' WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");
	}

	/**
	 * Get Recurring
	 *
	 * @param int $order_recurring_id
	 *
	 * @return array<string, mixed>
	 */
	public function getRecurring(int $order_recurring_id): array {
		$query = $this->db->query("SELECT `or`.*, `o`.`payment_method`, `o`.`currency_code` FROM `" . DB_PREFIX . "order_recurring` `or` LEFT JOIN `" . DB_PREFIX . "order` `o` ON `or`.`order_id` = `o`.`order_id` WHERE `or`.`order_recurring_id` = '" . (int)$order_recurring_id . "' AND `o`.`customer_id` = '" . (int)$this->customer->getId() . "'");

		if ($query->num_rows) {
			$query->row['payment_method']['name'] = json_decode($query->row['payment_method']['name'], true);
			$query->row['payment_method']['code'] = json_decode($query->row['payment_method']['code'], true);

			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * Get Recurrings
	 *
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getRecurrings(int $start = 0, int $limit = 20): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$query = $this->db->query("SELECT `or`.*, `o`.`payment_method`, `o`.`currency_id`, `o`.`currency_value` FROM `" . DB_PREFIX . "order_recurring` `or` LEFT JOIN `" . DB_PREFIX . "order` `o` ON `or`.`order_id` = `o`.`order_id` WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "' ORDER BY `o`.`order_id` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * Get Recurring By Reference
	 *
	 * @param string $reference
	 *
	 * @return array<string, mixed>
	 */
	public function getRecurringByReference(string $reference): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE `reference` = '" . $this->db->escape($reference) . "'");

		return $query->row;
	}

	/**
	 * Get Recurring Transactions
	 *
	 * @param int $order_recurring_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getRecurringTransactions(int $order_recurring_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring_transaction` WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");

		return $query->rows;
	}

	/**
	 * Get Total Recurrings
	 *
	 * @return int
	 */
	public function getTotalRecurrings(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_recurring` `or` LEFT JOIN `" . DB_PREFIX . "order` `o` ON `or`.`order_id` = `o`.`order_id` WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "'");

		return (int)$query->row['total'];
	}
}
