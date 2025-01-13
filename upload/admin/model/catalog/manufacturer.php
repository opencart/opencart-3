<?php
/**
 * Class Manufacturer
 *
 * Can be called using $this->load->model('catalog/manufacturer');
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogManufacturer extends Model {
	/**
	 * Add Manufacturer
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new manufacturer record
	 *
	 * @example
	 *
	 * $manufacturer_data = [
	 *     'name'       => 'Manufacturer Name',
	 *     'image'      => 'manufacturer_image',
	 *     'sort_order' => 'DESC'
	 * ];
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $manufacturer_id = $this->model_catalog_manufacturer->addManufacturer($manufacturer_data);
	 */
	public function addManufacturer(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer` SET `name` = '" . $this->db->escape($data['name']) . "', `sort_order` = '" . (int)$data['sort_order'] . "'");

		$manufacturer_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		}

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_to_store` SET `manufacturer_id` = '" . (int)$manufacturer_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `query` = 'manufacturer_id=" . (int)$manufacturer_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('manufacturer');

		return $manufacturer_id;
	}

	/**
	 * Edit Manufacturer
	 *
	 * @param int                  $manufacturer_id primary key of the manufacturer record
	 * @param array<string, mixed> $data            array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $manufacturer_data = [
	 *     'name'       => 'Manufacturer Name',
	 *     'image'      => 'manufacturer_image',
	 *     'sort_order' => 'DESC'
	 * ];
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $this->model_catalog_manufacturer->editManufacturer($manufacturer_id, $manufacturer_data);
	 */
	public function editManufacturer(int $manufacturer_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer` SET `name` = '" . $this->db->escape($data['name']) . "', `sort_order` = '" . (int)$data['sort_order'] . "' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_to_store` SET `manufacturer_id` = '" . (int)$manufacturer_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `query` = 'manufacturer_id=" . (int)$manufacturer_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('manufacturer');
	}

	/**
	 * Delete Manufacturer
	 *
	 * @param int $manufacturer_id primary key of the manufacturer record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $this->model_catalog_manufacturer->deleteManufacturer($manufacturer_id);
	 */
	public function deleteManufacturer(int $manufacturer_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		$this->cache->delete('manufacturer');
	}

	/**
	 * Get Manufacturer
	 *
	 * @param int $manufacturer_id primary key of the manufacturer record
	 *
	 * @return array<string, mixed> manufacturer record that has manufacturer ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);
	 */
	public function getManufacturer(int $manufacturer_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "manufacturer` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		return $query->row;
	}

	/**
	 * Get Manufacturers
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> manufacturer records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort'  => 'name',
	 *     'order' => 'DESC',
	 *     'start' => 0,
	 *     'limit' => 10
	 * ];
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $results = $this->model_catalog_manufacturer->getManufacturers($filter_data);
	 */
	public function getManufacturers(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "manufacturer`";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE `name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = [
			'name',
			'sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Get Stores
	 *
	 * @param int $manufacturer_id primary key of the manufacturer record
	 *
	 * @return array<int, int> store records that have manufacturer ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $manufacturer_store = $this->model_catalog_manufacturer->getStores($manufacturer_id);
	 */
	public function getStores(int $manufacturer_id): array {
		$manufacturer_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_store_data[] = $result['store_id'];
		}

		return $manufacturer_store_data;
	}

	/**
	 * Get Seo Urls
	 *
	 * @param int $manufacturer_id primary key of the manufacturer record
	 *
	 * @return array<int, array<string, string>> SEO URL records that have manufacturer ID
	 *
	 * @example
	 *
	 * $results = $this->model_catalog_manufacturer->getSeoUrls($manufacturer_id);
	 */
	public function getSeoUrls(int $manufacturer_id): array {
		$manufacturer_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $manufacturer_seo_url_data;
	}

	/**
	 * Get Total Manufacturers
	 *
	 * @return int total number of manufacturer records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $manufacturer_total = $this->model_catalog_manufacturer->getTotalManufacturers();
	 */
	public function getTotalManufacturers(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "manufacturer`");

		return (int)$query->row['total'];
	}
}
