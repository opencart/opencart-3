<?php
/**
 * Class Download
 *
 * Can be called from $this->load->model('catalog/download');
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogDownload extends Model {
	/**
	 * Add Download
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new download record
	 *
	 * @example
	 *
	 * $download_id = $this->model_catalog_download->addDownload($data);
	 */
	public function addDownload(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "download` SET `filename` = '" . $this->db->escape($data['filename']) . "', `mask` = '" . $this->db->escape($data['mask']) . "', `date_added` = NOW()");

		$download_id = $this->db->getLastId();

		foreach ($data['download_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "download_description` SET `download_id` = '" . (int)$download_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		return $download_id;
	}

	/**
	 * Edit Download
	 *
	 * @param int                  $download_id primary key of the download record
	 * @param array<string, mixed> $data        array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_catalog_download->editDownload($download_id, $data);
	 */
	public function editDownload(int $download_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "download` SET `filename` = '" . $this->db->escape($data['filename']) . "', `mask` = '" . $this->db->escape($data['mask']) . "' WHERE `download_id` = '" . (int)$download_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "download_description` WHERE `download_id` = '" . (int)$download_id . "'");

		foreach ($data['download_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "download_description` SET `download_id` = '" . (int)$download_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}
	}

	/**
	 * Delete Download
	 *
	 * @param int $download_id primary key of the download record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_catalog_download->deleteDownload($download_id);
	 */
	public function deleteDownload(int $download_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "download` WHERE `download_id` = '" . (int)$download_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "download_description` WHERE `download_id` = '" . (int)$download_id . "'");
	}

	/**
	 * Get Download
	 *
	 * @param int $download_id primary key of the download record
	 *
	 * @return array<string, mixed> download record that has download ID
	 *
	 * @example
	 *
	 * $download_info = $this->model_catalog_download->getDownload($download_id);
	 */
	public function getDownload(int $download_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "download` `d` LEFT JOIN `" . DB_PREFIX . "download_description` `dd` ON (`d`.`download_id` = `dd`.`download_id`) WHERE `d`.`download_id` = '" . (int)$download_id . "' AND `dd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Downloads
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> download records
	 *
	 * @example
	 *
	 * $results = $this->model_catalog_download->getDownloads();
	 */
	public function getDownloads(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "download` `d` LEFT JOIN `" . DB_PREFIX . "download_description` `dd` ON (`d`.`download_id` = `dd`.`download_id`) WHERE `dd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `dd`.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = [
			'dd.name',
			'd.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `dd`.`name`";
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
	 * Get Descriptions
	 *
	 * @param int $download_id primary key of the download record
	 *
	 * @return array<int, array<string, string>> description records that have download ID
	 *
	 * @example
	 *
	 * $download_description = $this->model_catalog_download->getDescriptions($download_id);
	 */
	public function getDescriptions(int $download_id): array {
		$download_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "download_description` WHERE `download_id` = '" . (int)$download_id . "'");

		foreach ($query->rows as $result) {
			$download_description_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $download_description_data;
	}

	/**
	 * Get Total Downloads
	 *
	 * @return int total number of download records
	 *
	 * @example
	 *
	 * $download_total = $this->model_catalog_download->getTotalDownloads();
	 */
	public function getTotalDownloads(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "download`");

		return (int)$query->row['total'];
	}
}
