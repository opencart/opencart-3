<?php
/**
 * Class Custom Field
 *
 * Can be called from $this->load->model('customer/custom_field');
 *
 * @package Admin\Model\Customer
 */
class ModelCustomerCustomField extends Model {
	/**
	 * Add Custom Field
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new custom field record
	 *
	 * @example
	 *
	 * $custom_field_id = $this->model_customer_custom_field->addCustomField($data);
	 */
	public function addCustomField(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field` SET `type` = '" . $this->db->escape($data['type']) . "', `value` = '" . $this->db->escape($data['value']) . "', `validation` = '" . $this->db->escape($data['validation']) . "', `location` = '" . $this->db->escape($data['location']) . "', `status` = '" . (int)$data['status'] . "', `sort_order` = '" . (int)$data['sort_order'] . "'");

		$custom_field_id = $this->db->getLastId();

		foreach ($data['custom_field_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field_description` SET `custom_field_id` = '" . (int)$custom_field_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['custom_field_customer_group'])) {
			foreach ($data['custom_field_customer_group'] as $custom_field_customer_group) {
				if (isset($custom_field_customer_group['customer_group_id'])) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field_customer_group` SET `custom_field_id` = '" . (int)$custom_field_id . "', `customer_group_id` = '" . (int)$custom_field_customer_group['customer_group_id'] . "', `required` = '" . (isset($custom_field_customer_group['required']) ? '1' : '0') . "'");
				}
			}
		}

		if (isset($data['custom_field_value'])) {
			foreach ($data['custom_field_value'] as $custom_field_value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field_value` SET `custom_field_id` = '" . (int)$custom_field_id . "', `sort_order` = '" . (int)$custom_field_value['sort_order'] . "'");

				$custom_field_value_id = $this->db->getLastId();

				foreach ($custom_field_value['custom_field_value_description'] as $language_id => $custom_field_value_description) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field_value_description` SET `custom_field_value_id` = '" . (int)$custom_field_value_id . "', `language_id` = '" . (int)$language_id . "', `custom_field_id` = '" . (int)$custom_field_id . "', `name` = '" . $this->db->escape($custom_field_value_description['name']) . "'");
				}
			}
		}

		return $custom_field_id;
	}

	/**
	 * Edit Custom Field
	 *
	 * @param int                  $custom_field_id primary key of the custom field record
	 * @param array<string, mixed> $data            array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_customer_custom_field->editCustomField($custom_field_id, $data);
	 */
	public function editCustomField(int $custom_field_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "custom_field` SET `type` = '" . $this->db->escape($data['type']) . "', `value` = '" . $this->db->escape($data['value']) . "', `validation` = '" . $this->db->escape($data['validation']) . "', `location` = '" . $this->db->escape($data['location']) . "', `status` = '" . (int)$data['status'] . "', `sort_order` = '" . (int)$data['sort_order'] . "' WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_description` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");

		foreach ($data['custom_field_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field_description` SET `custom_field_id` = '" . (int)$custom_field_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_customer_group` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");

		if (isset($data['custom_field_customer_group'])) {
			foreach ($data['custom_field_customer_group'] as $custom_field_customer_group) {
				if (isset($custom_field_customer_group['customer_group_id'])) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field_customer_group` SET `custom_field_id` = '" . (int)$custom_field_id . "', `customer_group_id` = '" . (int)$custom_field_customer_group['customer_group_id'] . "', `required` = '" . (isset($custom_field_customer_group['required']) ? '1' : '0') . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_value` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_value_description` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");

		if (isset($data['custom_field_value'])) {
			foreach ($data['custom_field_value'] as $custom_field_value) {
				if ($custom_field_value['custom_field_value_id']) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field_value` SET `custom_field_value_id` = '" . (int)$custom_field_value['custom_field_value_id'] . "', `custom_field_id` = '" . (int)$custom_field_id . "', `sort_order` = '" . (int)$custom_field_value['sort_order'] . "'");
				} else {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field_value` SET `custom_field_id` = '" . (int)$custom_field_id . "', `sort_order` = '" . (int)$custom_field_value['sort_order'] . "'");
				}

				$custom_field_value_id = $this->db->getLastId();

				foreach ($custom_field_value['custom_field_value_description'] as $language_id => $custom_field_value_description) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field_value_description` SET `custom_field_value_id` = '" . (int)$custom_field_value_id . "', `language_id` = '" . (int)$language_id . "', `custom_field_id` = '" . (int)$custom_field_id . "', `name` = '" . $this->db->escape($custom_field_value_description['name']) . "'");
				}
			}
		}
	}

	/**
	 * Delete Custom Field
	 *
	 * @param int $custom_field_id primary key of the custom field record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_customer_custom_field->deleteCustomField($custom_field_id);
	 */
	public function deleteCustomField(int $custom_field_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_description` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_customer_group` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_value` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_value_description` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");
	}

	/**
	 * Get Custom Field
	 *
	 * @param int $custom_field_id primary key of the custom field record
	 *
	 * @return array<string, mixed> custom field record that has custom field ID
	 *
	 * @example
	 *
	 * $custom_field_info = $this->model_customer_custom_field->getCustomField($custom_field_id);
	 */
	public function getCustomField(int $custom_field_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field` `cf` LEFT JOIN `" . DB_PREFIX . "custom_field_description` `cfd` ON (`cf`.`custom_field_id` = `cfd`.`custom_field_id`) WHERE `cf`.`custom_field_id` = '" . (int)$custom_field_id . "' AND `cfd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Custom Fields
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> custom field records
	 *
	 * @example
	 *
	 * $custom_fields = $this->model_customer_custom_field->getCustomFields();
	 */
	public function getCustomFields(array $data = []): array {
		if (empty($data['filter_customer_group_id'])) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "custom_field` `cf` LEFT JOIN `" . DB_PREFIX . "custom_field_description` `cfd` ON (`cf`.`custom_field_id` = `cfd`.`custom_field_id`) WHERE `cfd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";
		} else {
			$sql = "SELECT * FROM `" . DB_PREFIX . "custom_field_customer_group` `cfcg` LEFT JOIN `" . DB_PREFIX . "custom_field` `cf` ON (`cfcg`.`custom_field_id` = `cf`.`custom_field_id`) LEFT JOIN `" . DB_PREFIX . "custom_field_description` `cfd` ON (`cf`.`custom_field_id` = `cfd`.`custom_field_id`) WHERE `cfd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND `cfd`.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$sql .= " AND `cfcg`.`customer_group_id` = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		$sort_data = [
			'cfd.name',
			'cf.type',
			'cf.location',
			'cf.status',
			'cf.sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `cfd`.`name`";
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
	 * @param int $custom_field_id primary key of the custom field record
	 *
	 * @return array<int, array<string, string>> description records that have custom field ID
	 *
	 * @example
	 *
	 * $custom_field_description = $this->model_customer_custom_field->getDescriptions($custom_field_id);
	 */
	public function getDescriptions(int $custom_field_id): array {
		$custom_field_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_description` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");

		foreach ($query->rows as $result) {
			$custom_field_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $custom_field_data;
	}

	/**
	 * Get Value
	 *
	 * @param int $custom_field_value_id primary key of the custom field value record
	 *
	 * @return array<string, mixed> value record that has custom field value ID
	 *
	 * @example
	 *
	 * $custom_field_value = $this->model_customer_custom_field->getValue($custom_field_value_id);
	 */
	public function getValue(int $custom_field_value_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_value` `cfv` LEFT JOIN `" . DB_PREFIX . "custom_field_value_description` `cfvd` ON (`cfv`.`custom_field_value_id` = `cfvd`.`custom_field_value_id`) WHERE `cfv`.`custom_field_value_id` = '" . (int)$custom_field_value_id . "' AND `cfvd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Values
	 *
	 * @param int $custom_field_id primary key of the custom field record
	 *
	 * @return array<int, array<string, mixed>> value records that have custom field ID
	 *
	 * @example
	 *
	 * $custom_field_value = $this->model_customer_custom_field->getValues($custom_field_id);
	 */
	public function getValues(int $custom_field_id): array {
		$custom_field_value_data = [];

		$custom_field_value_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_value` `cfv` LEFT JOIN `" . DB_PREFIX . "custom_field_value_description` `cfvd` ON (`cfv`.`custom_field_value_id` = `cfvd`.`custom_field_value_id`) WHERE `cfv`.`custom_field_id` = '" . (int)$custom_field_id . "' AND `cfvd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `cfv`.`sort_order` ASC");

		foreach ($custom_field_value_query->rows as $custom_field_value) {
			$custom_field_value_data[$custom_field_value['custom_field_value_id']] = [
				'custom_field_value_id' => $custom_field_value['custom_field_value_id'],
				'name'                  => $custom_field_value['name']
			];
		}

		return $custom_field_value_data;
	}

	/**
	 * Get Customer Groups
	 *
	 * @param int $custom_field_id primary key of the custom field record
	 *
	 * @return array<int, array<string, mixed>> customer group records that have custom field ID
	 *
	 * @example
	 *
	 * $custom_field_customer_groups = $this->model_customer_custom_field->getCustomerGroups($custom_field_id);
	 */
	public function getCustomerGroups(int $custom_field_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_customer_group` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");

		return $query->rows;
	}

	/**
	 * Get Value Descriptions
	 *
	 * @param int $custom_field_id primary key of the custom field record
	 *
	 * @return array<int, array<string, mixed>> value description records that have custom field ID
	 *
	 * @example
	 *
	 * $custom_field_values = $this->model_customer_custom_field->getValueDescriptions($custom_field_id);
	 */
	public function getValueDescriptions(int $custom_field_id): array {
		$custom_field_value_data = [];

		$custom_field_value_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_value` WHERE `custom_field_id` = '" . (int)$custom_field_id . "'");

		foreach ($custom_field_value_query->rows as $custom_field_value) {
			$custom_field_value_description_data = [];

			$custom_field_value_description_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_value_description` WHERE `custom_field_value_id` = '" . (int)$custom_field_value['custom_field_value_id'] . "'");

			foreach ($custom_field_value_description_query->rows as $custom_field_value_description) {
				$custom_field_value_description_data[$custom_field_value_description['language_id']] = ['name' => $custom_field_value_description['name']];
			}

			$custom_field_value_data[] = [
				'custom_field_value_id'          => $custom_field_value['custom_field_value_id'],
				'custom_field_value_description' => $custom_field_value_description_data,
				'sort_order'                     => $custom_field_value['sort_order']
			];
		}

		return $custom_field_value_data;
	}

	/**
	 * Get Total Custom Fields
	 *
	 * @return int total number of custom field records
	 *
	 * @example
	 *
	 * $custom_field_total = $this->model_customer_custom_field->getTotalCustomFields();
	 */
	public function getTotalCustomFields(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "custom_field`");

		return (int)$query->row['total'];
	}
}
