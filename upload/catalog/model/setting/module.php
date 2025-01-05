<?php
/**
 * Class Module
 *
 * Can be called from $this->load->model('setting/module');
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingModule extends Model {
	/**
	 * Get Module
	 *
	 * @param int $module_id primary key of the module record
	 *
	 * @return array<string, mixed> module record that has module ID
	 *
	 * @example
	 *
	 * $module_info = $this->model_setting_module->getModule($module_id);
	 */
	public function getModule(int $module_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `module_id` = '" . (int)$module_id . "'");

		if ($query->row) {
			return json_decode($query->row['setting'], true);
		} else {
			return [];
		}
	}
}
