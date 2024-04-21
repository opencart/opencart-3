<?php
/**
 * Class Recurring
 *
 * @package Admin\Model\Extension\Report
 */
class ModelExtensionReportRecurring extends Model {
	/**
	 * Get Recurring Reports
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getRecurringReports(array $data = []): array {
		$implode = [];

		$sql = "SELECT MIN(`orr`.`date_added`) AS `date_start`, MAX(`orr`.`date_added`) AS `date_end`, COUNT(*) AS `recurrings`, SUM((SELECT SUM(`ot`.`value`) FROM `" . DB_PREFIX . "order_total` `ot` WHERE `ot`.`order_id` = `or`.`order_id` AND `ot`.`code` = 'tax' GROUP BY `ot`.`order_id`)) AS `tax`, SUM(`or`.`product_quantity`) AS `products`, SUM(`or`.`recurring_price`) AS `total` FROM `" . DB_PREFIX . "order_recurring_report` `orr` INNER JOIN `" . DB_PREFIX . "order_recurring` `or` ON (`orr`.`order_recurring_id` = `or`.`order_recurring_id`)";

		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(`orr`.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_start']) . "')";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(`orr`.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_end']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch ($group) {
			case 'day':
				$sql .= " GROUP BY YEAR(`orr`.`date_added`), MONTH(`orr`.`date_added`), DAY(`orr`.`date_added`)";
				break;
			default:
			case 'week':
				$sql .= " GROUP BY YEAR(`orr`.`date_added`), WEEK(`orr`.`date_added`)";
				break;
			case 'month':
				$sql .= " GROUP BY YEAR(`orr`.`date_added`), MONTH(`orr`.`date_added`)";
				break;
			case 'year':
				$sql .= " GROUP BY YEAR(`orr`.`date_added`)";
				break;
		}

		$sql .= ", `or`.`product_id`, `orr`.`store_id`";

		$sql .= " ORDER BY `orr`.`date_added` DESC";

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
	 * Get Total Recurring Reports
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function getTotalRecurringReports(array $data = []): int {
		$implode = [];

		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch ($group) {
			case 'day':
				$sql = "SELECT COUNT(DISTINCT YEAR(`orr`.`date_added`), MONTH(`orr`.`date_added`), DAY(`orr`.`date_added`)) AS `total` FROM `" . DB_PREFIX . "order_recurring_report` `orr`";
				break;
			default:
			case 'week':
				$sql = "SELECT COUNT(DISTINCT YEAR(`orr`.`date_added`), WEEK(`orr`.`date_added`)) AS `total` FROM `" . DB_PREFIX . "order_recurring_report` `orr`";
				break;
			case 'month':
				$sql = "SELECT COUNT(DISTINCT YEAR(`orr`.`date_added`), MONTH(`orr`.`date_added`)) AS `total` FROM `" . DB_PREFIX . "order_recurring_report` `orr`";
				break;
			case 'year':
				$sql = "SELECT COUNT(DISTINCT YEAR(`orr`.`date_added`)) AS `total` FROM `" . DB_PREFIX . "order_recurring_report` `orr`";
				break;
		}

		$sql .= " INNER JOIN `" . DB_PREFIX . "order_recurring` `or` ON (`orr`.`order_recurring_id`) = `or`.`order_recurring_id`)";

		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(`orr`.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_start']) . "')";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(`orr`.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_end']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sql .= " GROUP BY `or`.`product_id`, `orr`.`store_id`";

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}
}
