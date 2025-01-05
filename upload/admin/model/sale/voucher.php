<?php
/**
 * Class Voucher
 * 
 * Can be called from $this->load->model('sale/voucher');
 *
 * @package Admin\Model\Sale
 */
class ModelSaleVoucher extends Model {
	/**
	 * Add Voucher
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new voucher record
	 * 
	 * @example 
	 * 
	 * $voucher_id = $this->model_sale_voucher->addVoucher($data);
	 */
	public function addVoucher(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "voucher` SET `code` = '" . $this->db->escape($data['code']) . "', `from_name` = '" . $this->db->escape($data['from_name']) . "', `from_email` = '" . $this->db->escape($data['from_email']) . "', `to_name` = '" . $this->db->escape($data['to_name']) . "', `to_email` = '" . $this->db->escape($data['to_email']) . "', `voucher_theme_id` = '" . (int)$data['voucher_theme_id'] . "', `message` = '" . $this->db->escape($data['message']) . "', `amount` = '" . (float)$data['amount'] . "', `status` = '" . (int)$data['status'] . "', `date_added` = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * Edit Voucher
	 *
	 * @param int                  $voucher_id primary key of the voucher record
	 * @param array<string, mixed> $data       array of data
	 *
	 * @return void
	 * 
	 * @example 
	 * 
	 * $this->model_sale_voucher->editVoucher($voucher_id, $data);
	 */
	public function editVoucher(int $voucher_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "voucher` SET `code` = '" . $this->db->escape($data['code']) . "', `from_name` = '" . $this->db->escape($data['from_name']) . "', `from_email` = '" . $this->db->escape($data['from_email']) . "', `to_name` = '" . $this->db->escape($data['to_name']) . "', `to_email` = '" . $this->db->escape($data['to_email']) . "', `voucher_theme_id` = '" . (int)$data['voucher_theme_id'] . "', `message` = '" . $this->db->escape($data['message']) . "', `amount` = '" . (float)$data['amount'] . "', `status` = '" . (int)$data['status'] . "' WHERE `voucher_id` = '" . (int)$voucher_id . "'");
	}

	/**
	 * Delete Voucher
	 *
	 * @param int $voucher_id primary key of the voucher record
	 *
	 * @return void
	 * 
	 * @example 
	 * 
	 * $this->model_sale_voucher->deleteVoucher($voucher_id);
	 */
	public function deleteVoucher(int $voucher_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "voucher` WHERE `voucher_id` = '" . (int)$voucher_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "voucher_history` WHERE `voucher_id` = '" . (int)$voucher_id . "'");
	}

	/**
	 * Get Voucher
	 *
	 * @param int $voucher_id primary key of the voucher record
	 *
	 * @return array<string, mixed> voucher record that has voucher ID
	 * 
	 * @example 
	 * 
	 * $voucher_info = $this->model_sale_voucher->getVoucher($voucher_id);
	 */
	public function getVoucher(int $voucher_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "voucher` WHERE `voucher_id` = '" . (int)$voucher_id . "'");

		return $query->row;
	}

	/**
	 * Get Voucher By Code
	 *
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 * 
	 * @example 
	 * 
	 * $voucher_info = $this->model_sale_voucher->getVoucherByCode($code);
	 */
	public function getVoucherByCode(string $code): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "voucher` WHERE `code` = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	/**
	 * Get Vouchers
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> voucher records
	 * 
	 * @example 
	 * 
	 * $results = $this->model_sale_order->getVouchers();
	 */
	public function getVouchers(array $data = []): array {
		$sql = "SELECT `v`.`voucher_id`, `v`.`order_id`, `v`.`code`, `v`.`from_name`, `v`.`from_email`, `v`.`to_name`, `v`.`to_email`, (SELECT `vtd`.`name` FROM `" . DB_PREFIX . "voucher_theme_description` `vtd` WHERE `vtd`.`voucher_theme_id` = `v`.`voucher_theme_id` AND `vtd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `theme`, `v`.`amount`, `v`.`status`, `v`.`date_added` FROM `" . DB_PREFIX . "voucher` `v`";

		$sort_data = [
			'v.code',
			'v.from_name',
			'v.to_name',
			'theme',
			'v.amount',
			'v.status',
			'v.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `v`.`date_added`";
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
	 * Get Total Vouchers
	 *
	 * @return int total number of voucher records
	 * 
	 * @example 
	 * 
	 * $voucher_total = $this->model_sale_voucher->getTotalVouchers();
	 */
	public function getTotalVouchers(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "voucher`");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Vouchers By Voucher Theme ID
	 *
	 * @param int $voucher_theme_id primary key of the voucher record
	 *
	 * @return int total number of voucher records that have voucher theme ID
	 * 
	 * @example 
	 * 
	 * $voucher_total = $this->model_sale_voucher->getTotalVouchersByVoucherThemeId($voucher_theme_id);
	 */
	public function getTotalVouchersByVoucherThemeId(int $voucher_theme_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "voucher` WHERE `voucher_theme_id` = '" . (int)$voucher_theme_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Histories
	 *
	 * @param int $voucher_id primary key of the voucher record
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>> history records that have voucher ID
	 * 
	 * @example 
	 * 
	 * $results = $this->model_sale_voucher->getHistories($voucher_id, $start, $limit);
	 */
	public function getHistories(int $voucher_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT `vh`.`order_id`, CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) AS `customer`, `vh`.`amount`, `vh`.`date_added` FROM `" . DB_PREFIX . "voucher_history` `vh` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`vh`.`order_id` = `o`.`order_id`) WHERE `vh`.`voucher_id` = '" . (int)$voucher_id . "' ORDER BY `vh`.`date_added` ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * Get Total Histories
	 *
	 * @param int $voucher_id primary key of the voucher record
	 *
	 * @return int total number of history records that have voucher ID
	 * 
	 * @example 
	 * 
	 * $history_total = $this->model_sale_voucher->getTotalHistories($voucher_id);
	 */
	public function getTotalHistories(int $voucher_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "voucher_history` WHERE `voucher_id` = '" . (int)$voucher_id . "'");

		return (int)$query->row['total'];
	}
}
