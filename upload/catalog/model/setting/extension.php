<?php
/**
 * Class Extension
 * 
 * @example $extension_model = $this->model_setting_extension;
 * 
 * Can be called from $this->load->model('setting/extension');
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingExtension extends Model {
	/**
	 * Get Extensions By Type
	 *
	 * @param string $type
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getExtensionsByType(string $type): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "'");

		return $query->rows;
	}

	/**
	 * Get Extension By Code
	 *
	 * @param string $type
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 */
	public function getExtensionByCode(string $type, string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
}
