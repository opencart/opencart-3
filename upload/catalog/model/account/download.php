<?php
/**
 * Class Download
 *
 * Can be called using $this->load->model('account/download');
 *
 * @package Catalog\Model\Account
 */
class ModelAccountDownload extends Model {
	/**
	 * Get Download
	 *
	 * @param int $download_id primary key of the download record
	 *
	 * @return array<string, mixed> download record that has download ID
	 *
	 * @example
	 *
	 * $this->load->model('account/download');
	 *
	 * $download_info = $this->model_account_download->getDownload($download_id);
	 */
	public function getDownload(int $download_id): array {
		$implode = [];

		$order_statuses = (array)$this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "`o`.`order_status_id` = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT `d`.`filename`, `d`.`mask` FROM `" . DB_PREFIX . "order` `o` LEFT JOIN `" . DB_PREFIX . "order_product` `op` ON (`o`.`order_id` = `op`.`order_id`) LEFT JOIN `" . DB_PREFIX . "product_to_download` p2d ON (`op`.`product_id` = `p2d`.`product_id`) LEFT JOIN `" . DB_PREFIX . "download` `d` ON (`p2d`.`download_id` = `d`.`download_id`) WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $implode) . ") AND `d`.`download_id` = '" . (int)$download_id . "'");

			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * Get Downloads
	 *
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>> download records
	 *
	 * @example
	 *
	 * $this->load->model('account/download');
	 *
	 * $results = $this->model_account_download->getDownloads();
	 */
	public function getDownloads(int $start = 0, int $limit = 20): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$implode = [];

		$order_statuses = (array)$this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "`o`.`order_status_id` = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT DISTINCT `op`.`order_product_id`, `d`.`download_id`, `o`.`order_id`, `o`.`date_added`, `dd`.`name`, `d`.`filename` FROM `" . DB_PREFIX . "order` `o` LEFT JOIN `" . DB_PREFIX . "order_product` `op` ON (`o`.`order_id` = `op`.`order_id`) LEFT JOIN `" . DB_PREFIX . "product_to_download` `p2d` ON (`op`.`product_id` = `p2d`.`product_id`) LEFT JOIN `" . DB_PREFIX . "download` `d` ON (`p2d`.`download_id` = `d`.`download_id`) LEFT JOIN `" . DB_PREFIX . "download_description` `dd` ON (`d`.`download_id` = `dd`.`download_id`) WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "' AND `dd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND (" . implode(" OR ", $implode) . ") ORDER BY `o`.`date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * Get Total Downloads
	 *
	 * @return int total number of download records
	 *
	 * @example
	 *
	 * $this->load->model('account/download');
	 *
	 * $download_total = $this->model_account_download->getTotalDownloads();
	 */
	public function getTotalDownloads(): int {
		$implode = [];

		$order_statuses = (array)$this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "`o`.`order_status_id` = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` `o` LEFT JOIN `" . DB_PREFIX . "order_product` `op` ON (`o`.`order_id` = `op`.`order_id`) LEFT JOIN `" . DB_PREFIX . "product_to_download` `p2d` ON (`op`.`product_id` = `p2d`.`product_id`) WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $implode) . ")");

			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}
}
