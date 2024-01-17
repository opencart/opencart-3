<?php
/**
 * Class Module
 *
 * @package Admin\Model\Setting
 */
class ModelSettingModule extends Model {
	/**
	 * addModule
	 *
	 * @param string $code
	 * @param array  $data
	 *
	 * @return void
	 */
	public function addModule(string $code, array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "module` SET `name` = '" . $this->db->escape($data['name']) . "', `code` = '" . $this->db->escape($code) . "', `setting` = '" . $this->db->escape(json_encode($data)) . "'");
	}

	/**
	 * editModule
	 *
	 * @param int   $module_id
	 * @param array $data
	 *
	 * @return void
	 */
	public function editModule(int $module_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "module` SET `name` = '" . $this->db->escape($data['name']) . "', `setting` = '" . $this->db->escape(json_encode($data)) . "' WHERE `module_id` = '" . (int)$module_id . "'");
	}

	/**
	 * deleteModule
	 *
	 * @param int $module_id
	 *
	 * @return void
	 */
	public function deleteModule(int $module_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "module` WHERE `module_id` = '" . (int)$module_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout_module` WHERE `code` LIKE '%." . (int)$module_id . "'");
	}

	/**
	 * getModule
	 *
	 * @param int $module_id
	 *
	 * @return array
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
	 * getModules
	 *
	 * @return array
	 */
	public function getModules(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` ORDER BY `code`");

		return $query->rows;
	}

	/**
	 * getModulesByCode
	 *
	 * @param string $code
	 *
	 * @return array
	 */
	public function getModulesByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `name`");

		return $query->rows;
	}

	/**
	 * deleteModulesByCode
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
