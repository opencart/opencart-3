<?php
/**
 * Class Gdpr
 *
 * Can be called from $this->load->model('customer/gdpr');
 *
 * @package Admin\Model\Customer
 */
class ModelCustomerGdpr extends Model {
	/**
	 * Delete Gdpr
	 *
	 * @param int $gdpr_id primary key of the gdpr record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_customer_gdpr->deleteGdpr($gdpr_id);
	 */
	public function deleteGdpr(int $gdpr_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "gdpr` WHERE `gdpr_id` = '" . (int)$gdpr_id . "'");
	}

	/**
	 * Get Gdpr(s)
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> gdpr records
	 *
	 * @example
	 *
	 * $results = $this->model_customer_gdpr->getGdprs();
	 */
	public function getGdprs(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "gdpr`";

		$implode = [];

		if (!empty($data['filter_email'])) {
			$implode[] = "`email` LIKE '" . $this->db->escape($data['filter_email']) . "'";
		}

		if (!empty($data['filter_action'])) {
			$implode[] = "`action` = '" . $this->db->escape($data['filter_action']) . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sql .= " ORDER BY `date_added` DESC";

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
	 * Get Gdpr
	 *
	 * @param int $gdpr_id primary key of the gdpr record
	 *
	 * @return array<string, mixed> gdpr record that has gdpr ID
	 *
	 * @example
	 *
	 * $gdpr_info = $this->model_customer_gdpr->getGdpr($gdpr_id);
	 */
	public function getGdpr(int $gdpr_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "gdpr` WHERE `gdpr_id` = '" . (int)$gdpr_id . "'");

		return $query->row;
	}

	/**
	 * Get Total Gdpr(s)
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of gdpr records
	 *
	 * @example
	 *
	 * $gdpr_total = $this->model_customer_gdpr->getTotalGdprs();
	 */
	public function getTotalGdprs(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "gdpr`";

		$implode = [];

		if (!empty($data['filter_email'])) {
			$implode[] = "`email` LIKE '" . $this->db->escape($data['filter_email']) . "'";
		}

		if (!empty($data['filter_action'])) {
			$implode[] = "`action` = '" . $this->db->escape($data['filter_action']) . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get Expires
	 *
	 * @return array<int, array<string, mixed>> expire records
	 *
	 * @example
	 *
	 * $results = $this->model_customer_gdpr->getExpires();
	 */
	public function getExpires(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "gdpr` WHERE `status` = '2' AND DATE(`date_added`) <= DATE('" . $this->db->escape(date('Y-m-d', strtotime('+' . (int)$this->config->get('config_gdpr_limit') . ' days'))) . "') ORDER BY `date_added` DESC");

		return $query->rows;
	}

	/**
	 * Edit Status
	 *
	 * @param int $gdpr_id primary key of the gdpr record
	 * @param int $status
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_customer_gdpr->editStatus($gdpr_id, $status);
	 */
	public function editStatus($gdpr_id, $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "gdpr` SET `status` = '" . (int)$status . "' WHERE `gdpr_id` = '" . (int)$gdpr_id . "'");
	}
}
