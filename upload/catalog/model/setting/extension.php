<?php
/**
 * Class Extension
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingExtension extends Model {
	/**
	 * getExtensionsByType
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getExtensionsByType(string $type): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "'");

		return $query->rows;
	}

	/**
	 * getExtensionByCode
	 *
	 * @param string $type
	 * @param string $code
	 *
	 * @return array
	 */
	public function getExtensionByCode(string $type, string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
}
