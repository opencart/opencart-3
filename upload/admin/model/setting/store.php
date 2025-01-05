<?php
/**
 * Class Store
 *
 * Can be called from $this->load->model('setting/store');
 *
 * @package Admin\Model\Setting
 */
class ModelSettingStore extends Model {
	/**
	 * Add Store
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new store record
	 *
	 * @example
	 *
	 * $store_id = $this->model_setting_store->addStore($data);
	 */
	public function addStore(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "store` SET `name` = '" . $this->db->escape($data['config_name']) . "', `url` = '" . $this->db->escape($data['config_url']) . "', `ssl` = '" . $this->db->escape($data['config_ssl']) . "'");

		$store_id = $this->db->getLastId();

		// Layout Route
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "layout_route` WHERE `store_id` = '0'");

		foreach ($query->rows as $layout_route) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "layout_route` SET `layout_id` = '" . (int)$layout_route['layout_id'] . "', `route` = '" . $this->db->escape($layout_route['route']) . "', `store_id` = '" . (int)$store_id . "'");
		}

		$this->cache->delete('store');

		return $store_id;
	}

	/**
	 * Edit Store
	 *
	 * @param int                  $store_id
	 * @param array<string, mixed> $data     array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_setting_store->editStore($store_id, $data);
	 */
	public function editStore(int $store_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "store` SET `name` = '" . $this->db->escape($data['config_name']) . "', `url` = '" . $this->db->escape($data['config_url']) . "', `ssl` = '" . $this->db->escape($data['config_ssl']) . "' WHERE `store_id` = '" . (int)$store_id . "'");

		$this->cache->delete('store');
	}

	/**
	 * Delete Store
	 *
	 * @param int $store_id
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_setting_store->deleteStore($store_id);
	 */
	public function deleteStore(int $store_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_layout` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_affiliate_report` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_ip` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_search` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "gdpr` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_store` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout_route` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_layout` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_layout` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "store` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "subscription` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "theme` WHERE `store_id` = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "translation` WHERE `store_id` = '" . (int)$store_id . "'");

		$this->cache->delete('store');
	}

	/**
	 * Get Store
	 *
	 * @param int $store_id
	 *
	 * @return array<string, mixed> store record that has store ID
	 *
	 * @example
	 *
	 * $store_info = $this->model_setting_store->getStore($store_id);
	 */
	public function getStore(int $store_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "store` WHERE `store_id` = '" . (int)$store_id . "'");

		return $query->row;
	}

	/**
	 * Get Stores
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> store records
	 *
	 * @example
	 *
	 * $stores = $this->model_setting_store->getStores();
	 */
	public function getStores(array $data = []): array {
		$store_data = $this->cache->get('store');

		if (!$store_data) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` ORDER BY `url`");

			$store_data = $query->rows;

			$this->cache->set('store', $store_data);
		}

		return $store_data;
	}

	/**
	 * Get Total Stores
	 *
	 * @return int total number of store records
	 *
	 * @example
	 *
	 * $store_total = $this->model_setting_store->getTotalStores();
	 */
	public function getTotalStores(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "store`");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Stores By Layout ID
	 *
	 * @param int $layout_id total number of store records that have layout ID
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $store_total = $this->model_setting_store->getTotalStoresByLayoutId($layout_id);
	 */
	public function getTotalStoresByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_layout_id' AND `value` = '" . (int)$layout_id . "' AND `store_id` != '0'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Stores By Language
	 *
	 * @param string $language
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $store_total = $this->model_setting_store->getTotalStoresByLanguage($language);
	 */
	public function getTotalStoresByLanguage(string $language): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language' AND `value` = '" . $this->db->escape($language) . "' AND `store_id` != '0'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Stores By Currency
	 *
	 * @param string $currency
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $store_total = $this->model_setting_store->getTotalStoresByCurrency($currency);
	 */
	public function getTotalStoresByCurrency(string $currency): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_currency' AND `value` = '" . $this->db->escape($currency) . "' AND `store_id` != '0'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Stores By Country ID
	 *
	 * @param int $country_id primary key of the country record
	 *
	 * @return int total number of store records that have country ID
	 *
	 * @example
	 *
	 * $store_total = $this->model_setting_store->getTotalStoresByCountryId($country_id);
	 */
	public function getTotalStoresByCountryId(int $country_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_country_id' AND `value` = '" . (int)$country_id . "' AND `store_id` != '0'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Stores By Zone ID
	 *
	 * @param int $zone_id primary key of the zone record
	 *
	 * @return int total number of store records that have zone ID
	 *
	 * @example
	 *
	 * $store_total = $this->model_setting_store->getTotalStoresByZoneId($zone_id);
	 */
	public function getTotalStoresByZoneId(int $zone_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_zone_id' AND `value` = '" . (int)$zone_id . "' AND `store_id` != '0'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Stores By Customer Group ID
	 *
	 * @param int $customer_group_id primary key of the customer group record
	 *
	 * @return int total number of store records that have customer group ID
	 *
	 * @example
	 *
	 * $store_total = $this->model_setting_store->getTotalStoresByCustomerGroupId($customer_group_id);
	 */
	public function getTotalStoresByCustomerGroupId(int $customer_group_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_customer_group_id' AND `value` = '" . (int)$customer_group_id . "' AND `store_id` != '0'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Stores By Information ID
	 *
	 * @param int $information_id primary key of the information record
	 *
	 * @return int total number of store records that have information ID
	 *
	 * @example
	 *
	 * $store_total = $this->model_setting_store->getTotalStoresByInformationId($information_id);
	 */
	public function getTotalStoresByInformationId(int $information_id): int {
		$account_query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_account_id' AND `value` = '" . (int)$information_id . "' AND `store_id` != '0'");
		$checkout_query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_checkout_id' AND `value` = '" . (int)$information_id . "' AND `store_id` != '0'");

		return (int)$account_query->row['total'] + (int)$checkout_query->row['total'];
	}

	/**
	 * Get Total Stores By Order Status ID
	 *
	 * @param int $order_status_id primary key of the order status record
	 *
	 * @return int total number of store records that have order status ID
	 *
	 * @example
	 *
	 * $store_total = $this->model_setting_store->getTotalStoresByOrderStatusId($order_status_id);
	 */
	public function getTotalStoresByOrderStatusId(int $order_status_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_order_status_id' AND `value` = '" . (int)$order_status_id . "' AND `store_id` != '0'");

		return (int)$query->row['total'];
	}
}
