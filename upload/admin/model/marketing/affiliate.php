<?php
/**
 * Class Affiliate
 *
 * Can be called from $this->load->model('marketing/affiliate');
 *
 * @package Admin\Model\Marketing
 */
class ModelMarketingAffiliate extends Model {
	/**
	 * Add Affiliate
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_marketing_affiliate->addAffiliate($data);
	 */
	public function addAffiliate(array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_affiliate` SET `customer_id` = '" . (int)$data['customer_id'] . "', `company` = '" . $this->db->escape($data['company']) . "', `website` = '" . $this->db->escape($data['website']) . "', `tracking` = '" . $this->db->escape($data['tracking']) . "', commission = '" . (float)$data['commission'] . "', `tax` = '" . $this->db->escape($data['tax']) . "', `payment` = '" . $this->db->escape($data['payment']) . "', `cheque` = '" . $this->db->escape($data['cheque']) . "', `paypal` = '" . $this->db->escape($data['paypal']) . "', `bank_name` = '" . $this->db->escape($data['bank_name']) . "', `bank_branch_number` = '" . $this->db->escape($data['bank_branch_number']) . "', `bank_swift_code` = '" . $this->db->escape($data['bank_swift_code']) . "', `bank_account_name` = '" . $this->db->escape($data['bank_account_name']) . "', `bank_account_number` = '" . $this->db->escape($data['bank_account_number']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : json_encode([])) . "', status = '" . (int)$data['status'] . "', `date_added` = NOW()");
	}

	/**
	 * Edit Affiliate
	 *
	 * @param int                  $customer_id primary key of the customer record
	 * @param array<string, mixed> $data        array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_marketing_affiliate->editAffiliate($customer_id, $data);
	 */
	public function editAffiliate(int $customer_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer_affiliate` SET `company` = '" . $this->db->escape($data['company']) . "', `website` = '" . $this->db->escape($data['website']) . "', `tracking` = '" . $this->db->escape($data['tracking']) . "', `commission` = '" . (float)$data['commission'] . "', `tax` = '" . $this->db->escape($data['tax']) . "', `payment` = '" . $this->db->escape($data['payment']) . "', `cheque` = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', `bank_name` = '" . $this->db->escape($data['bank_name']) . "', `bank_branch_number` = '" . $this->db->escape($data['bank_branch_number']) . "', `bank_swift_code` = '" . $this->db->escape($data['bank_swift_code']) . "', `bank_account_name` = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : json_encode([])) . "', `status` = '" . (int)$data['status'] . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
	}

	/**
	 * Delete Affiliate
	 *
	 * @param int $customer_id primary key of the customer record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_marketing_affiliate->deleteAffiliate($customer_id);
	 */
	public function deleteAffiliate(int $customer_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_affiliate` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_affiliate_report` WHERE `customer_id` = '" . (int)$customer_id . "'");
	}

	/**
	 * Get Affiliate
	 *
	 * @param int $customer_id primary key of the customer record
	 *
	 * @return array<string, mixed> affiliate record that has customer ID
	 *
	 * @example
	 *
	 * $affiliate_info = $this->model_marketing_affiliate->getAffiliate($customer_id);
	 */
	public function getAffiliate(int $customer_id): array {
		$query = $this->db->query("SELECT DISTINCT *, CONCAT(`c`.`firstname`, ' ', `c`.`lastname`) AS `customer`, `ca`.`custom_field` FROM `" . DB_PREFIX . "customer_affiliate` `ca` LEFT JOIN `" . DB_PREFIX . "customer` `c` ON (`ca`.`customer_id` = `c`.`customer_id`) WHERE `ca`.`customer_id` = '" . (int)$customer_id . "'");

		if ($query->num_rows) {
			return $query->row + ['custom_field' => json_decode($query->row['custom_field'], true)];
		} else {
			return [];
		}
	}

	/**
	 * Get Affiliate By Tracking
	 *
	 * @param string $tracking
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $affiliate_info = $this->model_marketing_affiliate->getAffiliateByTracking($tracking);
	 */
	public function getAffiliateByTracking(string $tracking): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_affiliate` WHERE `tracking` = '" . $this->db->escape($tracking) . "'");

		if ($query->num_rows) {
			return $query->row + ['custom_field' => json_decode($query->row['custom_field'], true)];
		} else {
			return [];
		}
	}

	/**
	 * Get Affiliates
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> affiliate records
	 *
	 * @example
	 *
	 * $results = $this->model_marketing_affiliate->getAffiliates();
	 */
	public function getAffiliates(array $data = []): array {
		$sql = "SELECT *, CONCAT(`c`.`firstname`, ' ', `c`.`lastname`) AS `name`, `ca`.`status` FROM `" . DB_PREFIX . "customer_affiliate` `ca` LEFT JOIN `" . DB_PREFIX . "customer` `c` ON (`ca`.`customer_id` = `c`.`customer_id`)";

		$implode = [];

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(`c`.`firstname`, ' ', `c`.`lastname`) LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_tracking'])) {
			$implode[] = "`ca`.`tracking` = '" . $this->db->escape($data['filter_tracking']) . "'";
		}

		if (!empty($data['filter_commission'])) {
			$implode[] = "`ca`.`commission` = '" . (float)$data['filter_commission'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "`ca`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(`ca`.`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = [
			'name',
			'ca.tracking',
			'ca.commission',
			'ca.status',
			'ca.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
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
	 * Get Total Affiliates
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of affiliate records
	 *
	 * @example
	 *
	 * $affiliate_total = $this->model_marketing_affiliate->getTotalAffiliates();
	 */
	public function getTotalAffiliates(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_affiliate` `ca` LEFT JOIN `" . DB_PREFIX . "customer` `c` ON (`ca`.`customer_id` = `c`.`customer_id`)";

		$implode = [];

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(`c`.`firstname`, ' ', `c`.`lastname`) LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_tracking'])) {
			$implode[] = "`ca`.`tracking` = '" . $this->db->escape($data['filter_tracking']) . "'";
		}

		if (!empty($data['filter_commission'])) {
			$implode[] = "`ca`.`commission` = '" . (float)$data['filter_commission'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "`ca`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(`ca`.`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get Reports
	 *
	 * @param int $customer_id primary key of the customer record
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>> report records that have customer ID
	 *
	 * @example
	 *
	 * $results = $this->model_marketing_affiliate->getReports($customer_id, $start, $limit);
	 */
	public function getReports(int $customer_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT `ip`, `store_id`, `country`, `date_added` FROM `" . DB_PREFIX . "customer_affiliate_report` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `date_added` ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * Get Total Reports
	 *
	 * @param int $customer_id primary key of the customer record
	 *
	 * @return int total number of report records
	 *
	 * @example
	 *
	 * $report_total = $this->model_marketing_affiliate->getTotalReports($customer_id);
	 */
	public function getTotalReports(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_affiliate_report` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}
}
