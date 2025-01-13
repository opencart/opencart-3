<?php
/**
 * Class Attribute Group
 *
 * Can be called using $this->load->model('catalog/attribute_group');
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogAttributeGroup extends Model {
	/**
	 * Add Attribute Group
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new attribute group record
	 *
	 * @example
	 *
	 * $attribute_group_data = [
	 *     'sort_order' => 0,
	 * ];
	 *
	 * $this->load->model('catalog/attribute_group');
	 *
	 * $attribute_group_id = $this->model_catalog_attribute_group->addAttributeGroup($attribute_group_data);
	 */
	public function addAttributeGroup(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute_group` SET `sort_order` = '" . (int)$data['sort_order'] . "'");

		$attribute_group_id = $this->db->getLastId();

		foreach ($data['attribute_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute_group_description` SET `attribute_group_id` = '" . (int)$attribute_group_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		return $attribute_group_id;
	}

	/**
	 * Edit Attribute Group
	 *
	 * @param int                  $attribute_group_id primary key of the attribute group record
	 * @param array<string, mixed> $data               array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $attribute_group_data = [
	 *     'sort_order' => 0
	 * ];
	 *
	 * $this->load->model('catalog/attribute_group');
	 *
	 * $this->model_catalog_attribute_group->editAttributeGroup($attribute_group_id, $attribute_group_data);
	 */
	public function editAttributeGroup(int $attribute_group_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "attribute_group` SET `sort_order` = '" . (int)$data['sort_order'] . "' WHERE `attribute_group_id` = '" . (int)$attribute_group_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute_group_description` WHERE `attribute_group_id` = '" . (int)$attribute_group_id . "'");

		foreach ($data['attribute_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute_group_description` SET `attribute_group_id` = '" . (int)$attribute_group_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}
	}

	/**
	 * Delete Attribute Group
	 *
	 * @param int $attribute_group_id primary key of the attribute group record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute_group');
	 *
	 * $this->model_catalog_attribute_group->deleteAttributeGroup($attribute_group_id);
	 */
	public function deleteAttributeGroup(int $attribute_group_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute_group` WHERE `attribute_group_id` = '" . (int)$attribute_group_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute_group_description` WHERE `attribute_group_id` = '" . (int)$attribute_group_id . "'");
	}

	/**
	 * Get Attribute Group
	 *
	 * @param int $attribute_group_id primary key of the attribute group record
	 *
	 * @return array<string, mixed> attribute group record that has attribute group ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute_group');
	 *
	 * $attribute_group_info = $this->model_catalog_attribute_group->getAttributeGroup($attribute_group_id);
	 */
	public function getAttributeGroup(int $attribute_group_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attribute_group` WHERE `attribute_group_id` = '" . (int)$attribute_group_id . "'");

		return $query->row;
	}

	/**
	 * Get Attribute Groups
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> attribute group records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute_group');
	 *
	 * $filter_data = [
	 *	  'sort'  => 'agd.name',
	 *	  'order' => 'DESC',
	 *	  'start' => 0,
	 *	  'limit' => 10
	 * ];
	 *
	 * $attribute_groups = $this->model_catalog_attribute_group->getAttributeGroups($filter_data);
	 */
	public function getAttributeGroups(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "attribute_group` `ag` LEFT JOIN `" . DB_PREFIX . "attribute_group_description` `agd` ON (`ag`.`attribute_group_id` = `agd`.`attribute_group_id`) WHERE `agd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = [
			'agd.name',
			'ag.sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `agd`.`name`";
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
	 * @param int $attribute_group_id primary key of the attribute group record
	 *
	 * @return array<int, array<string, string>> description records that have attribute group ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute_group');
	 *
	 * $attribute_group_description = $this->model_catalog_attribute_group->getDescriptions($attribute_group_id);
	 */
	public function getDescriptions(int $attribute_group_id): array {
		$attribute_group_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attribute_group_description` WHERE `attribute_group_id` = '" . (int)$attribute_group_id . "'");

		foreach ($query->rows as $result) {
			$attribute_group_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $attribute_group_data;
	}

	/**
	 * Get Total Attribute Groups
	 *
	 * @return int total number of attribute group records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute_group');
	 *
	 * $attribute_group_total = $this->model_catalog_attribute_group->getTotalAttributeGroups();
	 */
	public function getTotalAttributeGroups(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "attribute_group`");

		return (int)$query->row['total'];
	}
}
