<?php
/**
 * Class Upload
 *
 * @package Catalog\Model\Tool
 */
class ModelToolUpload extends Model {
	/**
	 * addUpload
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
	 * getUploadByCode
	 *
	 * @param string $code
	 *
	 * @return array
	 */
	public function getUploadByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "upload` WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
}
