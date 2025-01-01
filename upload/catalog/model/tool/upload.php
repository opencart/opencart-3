<?php
/**
 * Class Upload
 *
 * @package Catalog\Model\Tool
 */
class ModelToolUpload extends Model {
	/**
	 * Add Upload
	 * 
	 * @example $upload_model = $this->model_tool_upload;
	 * 
	 * Can be called from $this->load->model('tool/upload');
	 *
	 * @param string $name
	 * @param string $filename
	 *
	 * @return string
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
	 */
	public function getUploadByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "upload` WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
}
