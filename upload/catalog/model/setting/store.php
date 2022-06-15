<?php
class ModelSettingStore extends Model {
	public function getStores() {
		$store_data = $this->cache->get('store');

		if (!$store_data) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` ORDER BY `url`");

			$store_data = $query->rows;

			$this->cache->set('store', $store_data);
		}

		return $store_data;
	}
	
	public function getStoreByHostname(string $url) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` WHERE REPLACE(`url`, 'www.', '') = '" . $this->db->escape($url) . "'");

		return $query->row;
	}
}