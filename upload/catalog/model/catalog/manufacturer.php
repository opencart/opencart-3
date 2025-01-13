<?php
/**
 * Class Manufacturer
 *
 * Can be called using $this->load->model('catalog/manufacturer');
 *
 * @package Catalog\Model\Catalog
 */
class ModelCatalogManufacturer extends Model {
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
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer` `m` LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` `m2s` ON (`m`.`manufacturer_id` = `m2s`.`manufacturer_id`) WHERE `m`.`manufacturer_id` = '" . (int)$manufacturer_id . "' AND `m2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "'");

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
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $manufacturers = $this->model_catalog_manufacturer->getManufacturers();
	 */
	public function getManufacturers(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "manufacturer` `m` LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` `m2s` ON (`m`.`manufacturer_id` = `m2s`.`manufacturer_id`) WHERE `m2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "'";

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
		} else {
			$manufacturer_data = $this->cache->get('manufacturer.' . (int)$this->config->get('config_store_id'));

			if (!$manufacturer_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer` `m` LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` `m2s` ON (`m`.`manufacturer_id` = `m2s`.`manufacturer_id`) WHERE `m2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "' ORDER BY `m`.`name`");

				$manufacturer_data = $query->rows;

				$this->cache->set('manufacturer.' . (int)$this->config->get('config_store_id'), $manufacturer_data);
			}

			return $manufacturer_data;
		}
	}
}
