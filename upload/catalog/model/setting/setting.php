<?php
/**
 * Class Setting
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingSetting extends Model {
	/**
	 * Get Settings
	 *
	 * @param int $store_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettings(int $store_id = 0): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' OR `store_id` = 0 ORDER BY `store_id` ASC");

		return $query->rows;
	}

	/**
	 * Get Setting
	 *
	 * @param string $code
	 * @param int    $store_id
	 *
	 * @return array<string, mixed>
	 */
	public function getSetting(string $code, int $store_id = 0): array {
		$data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$data[$result['key']] = $result['value'];
			} else {
				$data[$result['key']] = json_decode($result['value'], true);
			}
		}

		return $data;
	}

	/**
	 * Get Value
	 *
	 * @param string $key
	 * @param int    $store_id
	 *
	 * @return string
	 */
	public function getValue(string $key, int $store_id = 0): string {
		$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return '';
		}
	}
}
