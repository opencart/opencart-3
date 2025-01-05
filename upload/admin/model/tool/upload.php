<?php
/**
 * Class Upload
 *
 * Can be called from $this->load->model('tool/upload');
 *
 * @package Admin\Model\Tool
 */
class ModelToolUpload extends Model {
	/**
	 * Add Upload
	 *
	 * @param string $name
	 * @param string $filename
	 *
	 * @return string
	 * 
	 * @example 
	 * 
	 * $code = $this->model_tool_upload->addUpload($name, $filename);
	 */
	public function addUpload(string $name, string $filename): string {
		$code = sha1(uniqid(mt_rand(), true));

		$this->db->query("INSERT INTO `" . DB_PREFIX . "upload` SET `name` = '" . $this->db->escape($name) . "', `filename` = '" . $this->db->escape($filename) . "', `code` = '" . $this->db->escape($code) . "', `date_added` = NOW()");

		return $code;
	}

	/**
	 * Delete Upload
	 *
	 * @param int $upload_id primary key of the upload record
	 *
	 * @return void
	 * 
	 * @example 
	 * 
	 * $this->model_tool_upload->deleteUpload($upload_id);
	 */
	public function deleteUpload(int $upload_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "upload` WHERE `upload_id` = '" . (int)$upload_id . "'");
	}

	/**
	 * Get Upload
	 *
	 * @param int $upload_id primary key of the upload record
	 *
	 * @return array<string, mixed> upload record that has upload ID
	 * 
	 * @example 
	 * 
	 * $upload_info = $this->model_tool_upload->getUpload($upload_id);
	 */
	public function getUpload(int $upload_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "upload` WHERE `upload_id` = '" . (int)$upload_id . "'");

		return $query->row;
	}

	/**
	 * Get Upload By Code
	 *
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 * 
	 * @example 
	 * 
	 * $upload_info = $this->model_tool_upload->getUploadByCode($code);
	 */
	public function getUploadByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "upload` WHERE `code` = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	/**
	 * Get Uploads
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> upload records
	 * 
	 * @example 
	 * 
	 * $results = $this->model_tool_upload->getUploads();
	 */
	public function getUploads(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "upload`";

		$implode = [];

		if (!empty($data['filter_name'])) {
			$implode[] = "`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_filename'])) {
			$implode[] = "`filename` LIKE '" . $this->db->escape($data['filter_filename']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = [
			'name',
			'filename',
			'date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `date_added`";
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
	 * Get Total Uploads
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of upload records
	 * 
	 * @example 
	 * 
	 * $upload_total = $this->model_tool_upload->getTotalUploads();
	 */
	public function getTotalUploads($data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "upload`";

		$implode = [];

		if (!empty($data['filter_name'])) {
			$implode[] = "`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_filename'])) {
			$implode[] = "`filename` LIKE '" . $this->db->escape($data['filter_filename']) . "%'";
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
}
