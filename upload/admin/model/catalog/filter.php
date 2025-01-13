<?php
/**
 * Class Filter
 *
 * Can be called using $this->load->model('catalog/filter');
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogFilter extends Model {
	/**
	 * Add Filter
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new filter record
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort_order' => 0
	 * ];
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $filter_id = $this->model_catalog_filter->addFilter($filter_data);
	 */
	public function addFilter(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group` SET `sort_order` = '" . (int)$data['sort_order'] . "'");

		$filter_group_id = $this->db->getLastId();

		foreach ($data['filter_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group_description` SET `filter_group_id` = '" . (int)$filter_group_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['filter'])) {
			foreach ($data['filter'] as $filter) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "filter` SET `filter_group_id` = '" . (int)$filter_group_id . "', `sort_order` = '" . (int)$filter['sort_order'] . "'");

				$filter_id = $this->db->getLastId();

				foreach ($filter['filter_description'] as $language_id => $filter_description) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "filter_description` SET `filter_id` = '" . (int)$filter_id . "', `language_id` = '" . (int)$language_id . "', `filter_group_id` = '" . (int)$filter_group_id . "', `name` = '" . $this->db->escape($filter_description['name']) . "'");
				}
			}
		}

		return $filter_group_id;
	}

	/**
	 * Edit Filter
	 *
	 * @param int                  $filter_group_id
	 * @param array<string, mixed> $data            array of data
	 * @param int                  $filter_id       primary key of the filter record
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort_order' => 0
	 * ];
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $this->model_catalog_filter->editFilter($filter_id, $filter_data);
	 */
	public function editFilter(int $filter_group_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "filter_group` SET `sort_order` = '" . (int)$data['sort_order'] . "' WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter_group_description` WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");

		foreach ($data['filter_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group_description` SET `filter_group_id` = '" . (int)$filter_group_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter` WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter_description` WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");

		if (isset($data['filter'])) {
			foreach ($data['filter'] as $filter) {
				if ($filter['filter_id']) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "filter` SET `filter_id` = '" . (int)$filter['filter_id'] . "', `filter_group_id` = '" . (int)$filter_group_id . "', `sort_order` = '" . (int)$filter['sort_order'] . "'");
				} else {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "filter` SET `filter_group_id` = '" . (int)$filter_group_id . "', `sort_order` = '" . (int)$filter['sort_order'] . "'");
				}

				$filter_id = $this->db->getLastId();

				foreach ($filter['filter_description'] as $language_id => $filter_description) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "filter_description` SET `filter_id` = '" . (int)$filter_id . "', `language_id` = '" . (int)$language_id . "', `filter_group_id` = '" . (int)$filter_group_id . "', `name` = '" . $this->db->escape($filter_description['name']) . "'");
				}
			}
		}
	}

	/**
	 * Delete Filter
	 *
	 * @param int $filter_group_id
	 * @param int $filter_id       primary key of the filter record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $this->model_catalog_filter->deleteFilter($filter_id);
	 */
	public function deleteFilter(int $filter_group_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter_group` WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter_group_description` WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter` WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter_description` WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");
	}

	/**
	 * Get Group
	 *
	 * @param int $filter_group_id primary key of the filter group record
	 *
	 * @return array<string, mixed> filter group record that has filter group ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $filter_group_info = $this->model_catalog_filter->getGroup($filter_group_id);
	 */
	public function getGroup(int $filter_group_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "filter_group` `fg` LEFT JOIN `" . DB_PREFIX . "filter_group_description` `fgd` ON (`fg`.`filter_group_id` = `fgd`.`filter_group_id`) WHERE `fg`.`filter_group_id` = '" . (int)$filter_group_id . "' AND `fgd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Groups
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> filter group records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort'  => 'fgd.name',
	 *     'order' => 'DESC',
	 *     'start' => 0,
	 *     'limit' => 10
	 * ];
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $results = $this->model_catalog_filter->getGroups($filter_data);
	 */
	public function getGroups(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "filter_group` `fg` LEFT JOIN `" . DB_PREFIX . "filter_group_description` `fgd` ON (`fg`.`filter_group_id` = `fgd`.`filter_group_id`) WHERE `fgd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = [
			'fgd.name',
			'fg.sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `fgd`.`name`";
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
	 * Get Group Descriptions
	 *
	 * @param int $filter_group_id primary key of the filter group record
	 *
	 * @return array<int, array<string, string>> description records that have filter group ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $filter_group_description = $this->model_catalog_filter->getGroupDescriptions($filter_group_id);
	 */
	public function getGroupDescriptions(int $filter_group_id): array {
		$filter_group_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "filter_group_description` WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");

		foreach ($query->rows as $result) {
			$filter_group_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $filter_group_data;
	}

	/**
	 * Get Filter
	 *
	 * @param int $filter_id primary key of the filter record
	 *
	 * @return array<string, mixed> filter record that has filter ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $filter_info = $this->model_catalog_filter->getFilter($filter_id);
	 */
	public function getFilter(int $filter_id): array {
		$query = $this->db->query("SELECT *, (SELECT `name` FROM `" . DB_PREFIX . "filter_group_description` `fgd` WHERE `f`.`filter_group_id` = `fgd`.`filter_group_id` AND `fgd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `group` FROM `" . DB_PREFIX . "filter` `f` LEFT JOIN `" . DB_PREFIX . "filter_description` `fd` ON (`f`.`filter_id` = `fd`.`filter_id`) WHERE `f`.`filter_id` = '" . (int)$filter_id . "' AND `fd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Filters
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> filter records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort'  => 'fgd.name',
	 *     'order' => 'DESC',
	 *     'start' => 0,
	 *     'limit' => 10
	 * ];
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $results = $this->model_catalog_filter->getFilters($filter_data);
	 */
	public function getFilters(array $data): array {
		$sql = "SELECT *, (SELECT `name` FROM `" . DB_PREFIX . "filter_group_description` `fgd` WHERE `f`.`filter_group_id` = `fgd`.`filter_group_id` AND `fgd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `group` FROM `" . DB_PREFIX . "filter` `f` LEFT JOIN `" . DB_PREFIX . "filter_description` `fd` ON (`f`.`filter_id` = `fd`.`filter_id`) WHERE `fd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `fd`.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " ORDER BY `f`.`sort_order` ASC";

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
	 * Get Descriptions
	 *
	 * @param int $filter_group_id
	 * @param int $filter_id       primary key of the filter record
	 *
	 * @return array<int, array<string, string>> description records that have filter ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $filter_description = $this->model_catalog_filter->getDescriptions($filter_group_id);
	 */
	public function getDescriptions(int $filter_group_id): array {
		$filter_data = [];

		$filter_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "filter` WHERE `filter_group_id` = '" . (int)$filter_group_id . "'");

		foreach ($filter_query->rows as $filter) {
			$filter_description_data = [];

			$filter_description_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "filter_description` WHERE `filter_id` = '" . (int)$filter['filter_id'] . "'");

			foreach ($filter_description_query->rows as $filter_description) {
				$filter_description_data[$filter_description['language_id']] = ['name' => $filter_description['name']];
			}

			$filter_data[] = [
				'filter_id'          => $filter['filter_id'],
				'filter_description' => $filter_description_data,
				'sort_order'         => $filter['sort_order']
			];
		}

		return $filter_data;
	}

	/**
	 * Get Total Groups
	 *
	 * @return int total number of filter group records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/filter');
	 *
	 * $filter_group_total = $this->model_catalog_filter->getTotalGroups();
	 */
	public function getTotalGroups(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "filter_group`");

		return (int)$query->row['total'];
	}
}
