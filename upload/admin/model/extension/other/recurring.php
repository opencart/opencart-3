<?php
/**
 * Class Recurring
 *
 * @package Admin\Model\Extension\Other
 */
class ModelExtensionOtherRecurring extends Model {
	/**
	 * Get Recurring
	 *
	 * @param int $order_recurring_id
	 *
	 * @return array<string, string>
	 */
	public function getRecurring(int $order_recurring_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");

		return $query->row;
	}

	/**
	 * Get Recurrings
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getRecurrings(array $data): array {
		$sql = "SELECT `or`.`order_recurring_id`, `or`.`order_id`, `or`.`reference`, `or`.`status`, `or`.`date_added`, CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) AS customer FROM `" . DB_PREFIX . "order_recurring` `or` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`or`.`order_id` = `o`.`order_id`)";

		$implode = [];

		if (!empty($data['filter_order_recurring_id'])) {
			$implode[] = "`or`.`order_recurring_id` = '" . (int)$data['filter_order_recurring_id'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$implode[] = "`or`.`order_id` = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_reference'])) {
			$implode[] = "`or`.`reference` LIKE '" . $this->db->escape($data['filter_reference']) . "%'";
		}

		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_status'])) {
			$implode[] = "`or`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(`or`.`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = [
			'or.order_recurring_id',
			'or.order_id',
			'or.reference',
			'customer',
			'or.status',
			'or.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `or`.`order_recurring_id`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Get Recurring Transactions
	 *
	 * @param int $order_recurring_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getRecurringTransactions(int $order_recurring_id): array {
		$transactions = [];

		$query = $this->db->query("SELECT `amount`, `type`, `date_added` FROM `" . DB_PREFIX . "order_recurring_transaction` WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "' ORDER BY `date_added` DESC");

		foreach ($query->rows as $result) {
			switch ($result['type']) {
				case 0:
					$type = $this->language->get('text_transaction_date_added');
					break;
				case 1:
					$type = $this->language->get('text_transaction_payment');
					break;
				case 2:
					$type = $this->language->get('text_transaction_outstanding_payment');
					break;
				case 3:
					$type = $this->language->get('text_transaction_skipped');
					break;
				case 4:
					$type = $this->language->get('text_transaction_failed');
					break;
				case 5:
					$type = $this->language->get('text_transaction_cancelled');
					break;
				case 6:
					$type = $this->language->get('text_transaction_suspended');
					break;
				case 7:
					$type = $this->language->get('text_transaction_suspended_failed');
					break;
				case 8:
					$type = $this->language->get('text_transaction_outstanding_failed');
					break;
				case 9:
					$type = $this->language->get('text_transaction_expired');
					break;
				default:
					$type = '';
					break;
			}

			$transactions[] = [
				'date_added' => $result['date_added'],
				'amount'     => $result['amount'],
				'type'       => $type
			];
		}

		return $transactions;
	}

	/**
	 * Get Recurring Report
	 *
	 * @param int $order_recurring_id
	 *
	 * @return array<string, string>
	 */
	public function getRecurringReport(int $order_recurring_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring_report` WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");

		return $query->row;
	}

	/**
	 * Get Total Recurrings
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function getTotalRecurrings(array $data): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_recurring` `or` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`or`.`order_id` = `o`.`order_id`)";

		$implode = [];

		if (!empty($data['filter_order_recurring_id'])) {
			$implode[] = "`or`.`order_recurring_id` = '" . (int)$data['filter_order_recurring_id'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$implode[] = "`or`.`order_id` = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_payment_reference'])) {
			$implode[] = " `or`.`reference` LIKE '" . $this->db->escape($data['filter_reference']) . "%'";
		}

		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_status'])) {
			$implode[] = "`or`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(`or`.`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get Status
	 *
	 * @param int $status
	 *
	 * @return string
	 */
	private function getStatus(int $status): string {
		switch ($status) {
			case 1:
				$result = $this->language->get('text_status_inactive');
				break;
			case 2:
				$result = $this->language->get('text_status_active');
				break;
			case 3:
				$result = $this->language->get('text_status_suspended');
				break;
			case 4:
				$result = $this->language->get('text_status_cancelled');
				break;
			case 5:
				$result = $this->language->get('text_status_expired');
				break;
			case 6:
				$result = $this->language->get('text_status_pending');
				break;
			default:
				$result = '';
				break;
		}

		return $result;
	}

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

	/**
	 * Get Histories
	 *
	 * @param int $order_recurring_id
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getHistories(int $order_recurring_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT `comment`, `date_added` FROM `" . DB_PREFIX . "order_recurring_history` WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * Get Total Histories
	 *
	 * @param int $order_recurring_id
	 *
	 * @return int
	 */
	public function getTotalHistories(int $order_recurring_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_recurring_history` WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");

		return (int)$query->row['total'];
	}
}
