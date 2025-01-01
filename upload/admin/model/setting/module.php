<?php
/**
 * Class Module
 *
 * @example $modification_model = $this->model_setting_module;
 *
 * Can be called from $this->load->model('setting/module');
 *
 * @package Admin\Model\Setting
 */
class ModelSettingModule extends Model {
	/**
	 * Add Module
	 *
	 * @param string               $code
	 * @param array<string, mixed> $data array of data
	 *
	 * @return void
	 */
	public function addModule(string $code, array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "module` SET `name` = '" . $this->db->escape($data['name']) . "', `code` = '" . $this->db->escape($code) . "', `setting` = '" . $this->db->escape(json_encode($data)) . "'");
	}

	/**
	 * Edit Module
	 *
	 * @param int                  $module_id primary key of the module record
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function editModule(int $module_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "module` SET `name` = '" . $this->db->escape($data['name']) . "', `setting` = '" . $this->db->escape(json_encode($data)) . "' WHERE `module_id` = '" . (int)$module_id . "'");
	}

	/**
	 * Delete Module
	 *
	 * @param int $module_id primary key of the module record
	 *
	 * @return void
	 */
	public function deleteModule(int $module_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "module` WHERE `module_id` = '" . (int)$module_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout_module` WHERE `code` LIKE '%." . (int)$module_id . "'");
	}

	/**
	 * Get Module
	 *
	 * @param int $module_id primary key of the module record
	 *
	 * @return array<mixed> module record that has the module ID
	 */
	public function getModule(int $module_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `module_id` = '" . (int)$module_id . "'");

		if ($query->row) {
			return json_decode($query->row['setting'], true);
		} else {
			return [];
		}
	}

	/**
	 * Get Modules
	 *
	 * @return array<int, array<string, mixed>> module records
	 */
	public function getModules(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` ORDER BY `code`");

		return $query->rows;
	}

	/**
	 * Get Modules By Code
	 *
	 * @param string $code
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getModulesByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `name`");

		return $query->rows;
	}

	/**
	 * Delete Modules By Code
	 *
	 * @param string $code
	 *
	 * @return void
	 */
	public function deleteModulesByCode(string $code): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "module` WHERE `code` = '" . $this->db->escape($code) . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout_module` WHERE `code` LIKE '" . $this->db->escape($code) . "' OR `code` LIKE '" . $this->db->escape($code . '.%') . "'");
	}
}
