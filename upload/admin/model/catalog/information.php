<?php
/**
 * Class Information
 *
 * Can be called from $this->load->model('catalog/information');
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogInformation extends Model {
	/**
	 * Add Information
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new information record
	 *
	 * @example
	 *
	 * $information_id = $this->model_catalog_information->addInformation($data);
	 */
	public function addInformation(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "information` SET `sort_order` = '" . (int)$data['sort_order'] . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', `status` = '" . (int)$data['status'] . "'");

		$information_id = $this->db->getLastId();

		foreach ($data['information_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "information_description` SET `information_id` = '" . (int)$information_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_store` SET `information_id` = '" . (int)$information_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		if (isset($data['information_seo_url'])) {
			foreach ($data['information_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `query` = 'information_id=" . (int)$information_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_layout` SET `information_id` = '" . (int)$information_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('information');

		return $information_id;
	}

	/**
	 * Edit Information
	 *
	 * @param int                  $information_id primary key of the information record
	 * @param array<string, mixed> $data           array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_catalog_information->editInformation($information_id, $data);
	 */
	public function editInformation(int $information_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "information` SET `sort_order` = '" . (int)$data['sort_order'] . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', `status` = '" . (int)$data['status'] . "' WHERE `information_id` = '" . (int)$information_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_description` WHERE `information_id` = '" . (int)$information_id . "'");

		foreach ($data['information_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "information_description` SET `information_id` = '" . (int)$information_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_store` WHERE `information_id` = '" . (int)$information_id . "'");

		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_store` SET `information_id` = '" . (int)$information_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'information_id=" . (int)$information_id . "'");

		if (isset($data['information_seo_url'])) {
			foreach ($data['information_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `query` = 'information_id=" . (int)$information_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE `information_id` = '" . (int)$information_id . "'");

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_layout` SET `information_id` = '" . (int)$information_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('information');
	}

	/**
	 * Delete Information
	 *
	 * @param int $information_id primary key of the information record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_catalog_information->deleteInformation($information_id);
	 */
	public function deleteInformation(int $information_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information` WHERE `information_id` = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_description` WHERE `information_id` = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_store` WHERE `information_id` = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE `information_id` = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'information_id=" . (int)$information_id . "'");

		$this->cache->delete('information');
	}

	/**
	 * Get Information
	 *
	 * @param int $information_id primary key of the information record
	 *
	 * @return array<string, mixed> information record that has information ID
	 *
	 * @example
	 *
	 * $information_info = $this->model_catalog_information->getInformation($information_id);
	 */
	public function getInformation(int $information_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "information` WHERE `information_id` = '" . (int)$information_id . "'");

		return $query->row;
	}

	/**
	 * Get Information(s)
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> information records
	 *
	 * @example
	 *
	 * $results = $this->model_catalog_information->getInformations();
	 */
	public function getInformations(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "information` `i` LEFT JOIN `" . DB_PREFIX . "information_description` `id` ON (`i`.`information_id` = `id`.`information_id`) WHERE `id`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = [
				'id.title',
				'i.sort_order'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY `id`.`title`";
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
			$information_data = $this->cache->get('information.' . (int)$this->config->get('config_language_id'));

			if (!$information_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information` `i` LEFT JOIN `" . DB_PREFIX . "information_description` `id` ON (`i`.`information_id` = `id`.`information_id`) WHERE `id`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `id`.`title`");

				$information_data = $query->rows;

				$this->cache->set('information.' . (int)$this->config->get('config_language_id'), $information_data);
			}

			return $information_data;
		}
	}

	/**
	 * Get Descriptions
	 *
	 * @param int $information_id primary key of the information record
	 *
	 * @return array<int, array<string, string>> information description records that have information ID
	 * 
	 * @example 
	 * 
	 * $results = $this->model_catalog_information->getDescriptions($information_id);
	 */
	public function getDescriptions(int $information_id): array {
		$information_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_description` WHERE `information_id` = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_description_data[$result['language_id']] = [
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			];
		}

		return $information_description_data;
	}

	/**
	 * Get Stores
	 *
	 * @param int $information_id primary key of the information record
	 *
	 * @return array<int, int> store records that have information ID
	 *
	 * @example
	 *
	 * $information_store = $this->model_catalog_information->getStores($information_id);
	 */
	public function getStores(int $information_id): array {
		$information_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_to_store` WHERE `information_id` = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_store_data[] = $result['store_id'];
		}

		return $information_store_data;
	}

	/**
	 * Get Information Seo Urls
	 *
	 * @param int $information_id primary key of the information record
	 *
	 * @return array<int, array<string, string>> SEO URL records that have information ID
	 * 
	 * @example 
	 * 
	 * $results = $this->model_catalog_information->getInformationSeoUrls($information_id);
	 */
	public function getInformationSeoUrls(int $information_id): array {
		$information_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'information_id=" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $information_seo_url_data;
	}

	/**
	 * Get Information Layouts
	 *
	 * @param int $information_id primary key of the information record
	 *
	 * @return array<int, array<string, string>> layout records that have information ID
	 * 
	 * @example 
	 * 
	 * $results = $this->model_catalog_information->getInformationLayouts($information_id);
	 */
	public function getInformationLayouts(int $information_id): array {
		$information_layout_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_to_layout` WHERE `information_id` = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $information_layout_data;
	}

	/**
	 * Get Total Information(s)
	 *
	 * @return int total number of information records
	 * 
	 * @example 
	 * 
	 * $information_total = $this->model_catalog_information->getTotalInformations();
	 */
	public function getTotalInformations(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "information`");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Information(s) By LayoutId
	 *
	 * @param int $layout_id primary key of the information record
	 *
	 * @return int total number of layout records that have layout ID
	 * 
	 * @example 
	 * 
	 * $information_total = $this->model_catalog_information->getTotalInformationsByLayoutId($layout_id);
	 */
	public function getTotalInformationsByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "information_to_layout` WHERE `layout_id` = '" . (int)$layout_id . "'");

		return (int)$query->row['total'];
	}
}
