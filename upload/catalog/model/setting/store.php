<?php
/**
 * Class Store
 * 
 * @example $store_model = $this->model_setting_store;
 * 
 * Can be called from $this->load->model('setting/store');
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingStore extends Model {
	/**
	 * Get Store
	 *
	 * @param int $store_id primary key of the store record
	 *
	 * @return array<string, mixed> store record that has store ID
	 */
	public function getStore(int $store_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "store` WHERE `store_id` = '" . (int)$store_id . "'");

		return $query->row;
	}

	/**
	 * Get Stores
	 *
	 * @return array<int, array<string, mixed>> store records
	 */
	public function getStores(): array {
		$store_data = $this->cache->get('store');

		if (!$store_data) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` ORDER BY `url`");

			$store_data = $query->rows;

			$this->cache->set('store', $store_data);
		}

		return $store_data;
	}

	/**
	 * Get Store By Hostname
	 *
	 * @param string $url
	 *
	 * @return array<string, mixed>
	 */
	public function getStoreByHostname(string $url): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` WHERE REPLACE(`url`, 'www.', '') = '" . $this->db->escape($url) . "'");

		return $query->row;
	}
}
