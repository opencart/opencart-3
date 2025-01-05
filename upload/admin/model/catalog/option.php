<?php
/**
 * Class Option
 *
 * Can be called from $this->load->model('catalog/option');
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogOption extends Model {
	/**
	 * Add Option
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new option record
	 *
	 * @example
	 *
	 * $option_id = $this->model_catalog_option->addOption($data);
	 */
	public function addOption(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET `type` = '" . $this->db->escape($data['type']) . "', `sort_order` = '" . (int)$data['sort_order'] . "'");

		$option_id = $this->db->getLastId();

		foreach ($data['option_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "option_description` SET `option_id` = '" . (int)$option_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['option_value'])) {
			foreach ($data['option_value'] as $option_value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "option_value` SET `option_id` = '" . (int)$option_id . "', `image` = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', `sort_order` = '" . (int)$option_value['sort_order'] . "'");

				$option_value_id = $this->db->getLastId();

				foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "option_value_description` SET `option_value_id` = '" . (int)$option_value_id . "', `language_id` = '" . (int)$language_id . "', `option_id` = '" . (int)$option_id . "', `name` = '" . $this->db->escape($option_value_description['name']) . "'");
				}
			}
		}

		return $option_id;
	}

	/**
	 * Edit Option
	 *
	 * @param int                  $option_id primary key of the option record
	 * @param array<string, mixed> $data      array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_catalog_option->editOption($option_id, $data);
	 */
	public function editOption(int $option_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "option` SET `type` = '" . $this->db->escape($data['type']) . "', `sort_order` = '" . (int)$data['sort_order'] . "' WHERE `option_id` = '" . (int)$option_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "option_description` WHERE `option_id` = '" . (int)$option_id . "'");

		foreach ($data['option_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "option_description` SET `option_id` = '" . (int)$option_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "option_value` WHERE `option_id` = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option_value_description` WHERE `option_id` = '" . (int)$option_id . "'");

		if (isset($data['option_value'])) {
			foreach ($data['option_value'] as $option_value) {
				if ($option_value['option_value_id']) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "option_value` SET `option_value_id` = '" . (int)$option_value['option_value_id'] . "', `option_id` = '" . (int)$option_id . "', `image` = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', `sort_order` = '" . (int)$option_value['sort_order'] . "'");
				} else {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "option_value` SET `option_id` = '" . (int)$option_id . "', `image` = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "', `sort_order` = '" . (int)$option_value['sort_order'] . "'");
				}

				$option_value_id = $this->db->getLastId();

				foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "option_value_description` SET `option_value_id` = '" . (int)$option_value_id . "', `language_id` = '" . (int)$language_id . "', `option_id` = '" . (int)$option_id . "', `name` = '" . $this->db->escape($option_value_description['name']) . "'");
				}
			}
		}
	}

	/**
	 * Delete Option
	 *
	 * @param int $option_id primary key of the option record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_catalog_option->deleteOption($option_id);
	 */
	public function deleteOption(int $option_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option` WHERE `option_id` = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option_description` WHERE `option_id` = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option_value` WHERE `option_id` = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option_value_description` WHERE `option_id` = '" . (int)$option_id . "'");
	}

	/**
	 * Get Option
	 *
	 * @param int $option_id primary key of the option record
	 *
	 * @return array<string, mixed> option record that has option ID
	 *
	 * @example
	 *
	 * $option_info = $this->model_catalog_option->getOption($option_id);
	 */
	public function getOption(int $option_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option` `o` LEFT JOIN `" . DB_PREFIX . "option_description` `od` ON (`o`.`option_id` = `od`.`option_id`) WHERE `o`.`option_id` = '" . (int)$option_id . "' AND `od`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Options
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> option records
	 *
	 * @example
	 *
	 * $results = $this->model_catalog_option->getOptions();
	 */
	public function getOptions(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "option` `o` LEFT JOIN `" . DB_PREFIX . "option_description` `od` ON (`o`.`option_id` = `od`.`option_id`) WHERE `od`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `od`.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = [
			'od.name',
			'o.type',
			'o.sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `od`.`name`";
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
	 * Get Descriptions
	 *
	 * @param int $option_id primary key of the option record
	 *
	 * @return array<int, array<string, string>> description records that have option ID
	 *
	 * @example
	 *
	 * $option_description = $this->model_catalog_option->getDescriptions($option_id);
	 */
	public function getDescriptions(int $option_id): array {
		$option_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_description` WHERE `option_id` = '" . (int)$option_id . "'");

		foreach ($query->rows as $result) {
			$option_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $option_data;
	}

	/**
	 * Get Value
	 *
	 * @param int $option_value_id primary key of the option value record
	 *
	 * @return array<string, mixed> value record that has option value ID
	 *
	 * @example
	 *
	 * $option_value_info = $this->model_catalog_option->getValue($option_value_id);
	 */
	public function getValue(int $option_value_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_value` `ov` LEFT JOIN `" . DB_PREFIX . "option_value_description` `ovd` ON (`ov`.`option_value_id` = `ovd`.`option_value_id`) WHERE `ov`.`option_value_id` = '" . (int)$option_value_id . "' AND `ovd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Values
	 *
	 * @param int $option_id primary key of the option record
	 *
	 * @return array<int, array<string, mixed>> value records that have option ID
	 *
	 * @example
	 *
	 * $option_values = $this->model_catalog_option->getValues($option_id);
	 */
	public function getValues(int $option_id): array {
		$option_value_data = [];

		$option_value_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_value` `ov` LEFT JOIN `" . DB_PREFIX . "option_value_description` `ovd` ON (`ov`.`option_value_id` = `ovd`.`option_value_id`) WHERE `ov`.`option_id` = '" . (int)$option_id . "' AND `ovd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `ov`.`sort_order`, `ovd`.`name`");

		foreach ($option_value_query->rows as $option_value) {
			$option_value_data[] = [
				'option_value_id' => $option_value['option_value_id'],
				'name'            => $option_value['name'],
				'image'           => $option_value['image'],
				'sort_order'      => $option_value['sort_order']
			];
		}

		return $option_value_data;
	}

	/**
	 * Get Value Descriptions
	 *
	 * @param int $option_id primary key of the option record
	 *
	 * @return array<int, array<string, mixed>> value description records that have option ID
	 *
	 * @example
	 *
	 * $option_value_description = $this->model_catalog_option->getValueDescriptions($option_id);
	 */
	public function getValueDescriptions(int $option_id): array {
		$option_value_data = [];

		$option_value_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_value` WHERE `option_id` = '" . (int)$option_id . "' ORDER BY `sort_order`");

		foreach ($option_value_query->rows as $option_value) {
			$option_value_description_data = [];

			$option_value_description_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_value_description` WHERE `option_value_id` = '" . (int)$option_value['option_value_id'] . "'");

			foreach ($option_value_description_query->rows as $option_value_description) {
				$option_value_description_data[$option_value_description['language_id']] = ['name' => $option_value_description['name']];
			}

			$option_value_data[] = [
				'option_value_id'          => $option_value['option_value_id'],
				'option_value_description' => $option_value_description_data,
				'image'                    => $option_value['image'],
				'sort_order'               => $option_value['sort_order']
			];
		}

		return $option_value_data;
	}

	/**
	 * Get Total Options
	 *
	 * @return int total number of option records
	 *
	 * @example
	 *
	 * $option_total = $this->model_catalog_option->getTotalOptions();
	 */
	public function getTotalOptions(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "option`");

		return (int)$query->row['total'];
	}
}
