<?php
/**
 * Class Upload
 *
 * Can be called from $this->load->model('tool/upload');
 *
 * @package Catalog\Model\Tool
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
	 * $upload_id = $this->model_tool_upload->addUpload($name, $filename);
	 */
	public function addUpload(string $name, string $filename): string {
		$code = sha1(uniqid(mt_rand(), true));

		$this->db->query("INSERT INTO `" . DB_PREFIX . "upload` SET `name` = '" . $this->db->escape($name) . "', `filename` = '" . $this->db->escape($filename) . "', `code` = '" . $this->db->escape($code) . "', `date_added` = NOW()");

		return $code;
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
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "upload` WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
}
