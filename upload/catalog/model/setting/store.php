<?php
/**
 * Class Store
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingStore extends Model {
	/**
	 * @param int $store_id
	 *
	 * @return array
	 */
    public function getStore(int $store_id): array {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "store` WHERE `store_id` = '" . (int)$store_id . "'");

        return $query->row;
    }

	/**
	 * @return array
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
	 * @param string $url
	 *
	 * @return array
	 */
    public function getStoreByHostname(string $url): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` WHERE REPLACE(`url`, 'www.', '') = '" . $this->db->escape($url) . "'");

        return $query->row;
    }
}
