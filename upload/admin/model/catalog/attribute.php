<?php
/**
 * Class Attribute
 *
 * Can be called using $this->load->model('catalog/attribute');
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogAttribute extends Model {
	/**
	 * Add Attribute
	 *
	 * Create a new attribute record in the database.
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new attribute record
	 *
	 * @example
	 *
	 * $attribute_data = [
	 *     'attribute_group_id' => 1,
	 *     'sort_order'         => 0
	 * ];
	 *
	 * $this->load->model('catalog/attribute');
	 *
	 * $attribute_id = $this->model_catalog_attribute->addAttribute($attribute_data);
	 */
	public function addAttribute(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute` SET `attribute_group_id` = '" . (int)$data['attribute_group_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "'");

		$attribute_id = $this->db->getLastId();

		foreach ($data['attribute_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute_description` SET `attribute_id` = '" . (int)$attribute_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		return $attribute_id;
	}

	/**
	 * Edit Attribute
	 *
	 * Edit attribute record in the database.
	 *
	 * @param int                  $attribute_id primary key of the attribute record
	 * @param array<string, mixed> $data         array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $attribute_data['attribute_description'] = [
	 *     'attribute_group_id' => 1,
	 *     'sort_order'         => 0
	 * ];
	 *
	 * $this->load->model('catalog/attribute');
	 *
	 * $this->model_catalog_attribute->editAttribute($attribute_id, $attribute_data);
	 */
	public function editAttribute(int $attribute_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "attribute` SET `attribute_group_id` = '" . (int)$data['attribute_group_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "' WHERE `attribute_id` = '" . (int)$attribute_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute_description` WHERE `attribute_id` = '" . (int)$attribute_id . "'");

		foreach ($data['attribute_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute_description` SET `attribute_id` = '" . (int)$attribute_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}
	}

	/**
	 * Delete Attribute
	 *
	 * Delete attribute record in the database.
	 *
	 * @param int $attribute_id primary key of the attribute record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute');
	 *
	 * $this->model_catalog_attribute->deleteAttribute($attribute_id);
	 */
	public function deleteAttribute(int $attribute_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute` WHERE `attribute_id` = '" . (int)$attribute_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute_description` WHERE `attribute_id` = '" . (int)$attribute_id . "'");
	}

	/**
	 * Get Attribute
	 *
	 * Get the record of the attribute record in the database.
	 *
	 * @param int $attribute_id primary key of the attribute record
	 *
	 * @return array<string, mixed> attribute record that has attribute ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute');
	 *
	 * $attribute_info = $this->model_catalog_attribute->getAttribute($attribute_id);
	 */
	public function getAttribute(int $attribute_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attribute` `a` LEFT JOIN `" . DB_PREFIX . "attribute_description` `ad` ON (`a`.`attribute_id` = `ad`.`attribute_id`) WHERE `a`.`attribute_id` = '" . (int)$attribute_id . "' AND `ad`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Attributes
	 *
	 * Get the record of the attribute record in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> attribute records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort'  => 'ad.name',
	 *     'order' => 'DESC',
	 *     'start' => 0,
	 *     'limit' => 10
	 * ];
	 *
	 * $this->load->model('catalog/attribute');
	 *
	 * $results = $this->model_catalog_attribute->getAttributes($filter_data);
	 */
	public function getAttributes(array $data = []): array {
		$sql = "SELECT *, (SELECT `agd`.`name` FROM `" . DB_PREFIX . "attribute_group_description` `agd` WHERE `agd`.`attribute_group_id` = `a`.`attribute_group_id` AND `agd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `attribute_group` FROM `" . DB_PREFIX . "attribute` `a` LEFT JOIN `" . DB_PREFIX . "attribute_description` `ad` ON (`a`.`attribute_id` = `ad`.`attribute_id`) WHERE `ad`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `ad`.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_attribute_group_id'])) {
			$sql .= " AND `a`.`attribute_group_id` = '" . $this->db->escape($data['filter_attribute_group_id']) . "'";
		}

		$sort_data = [
			'ad.name',
			'attribute_group',
			'a.sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `attribute_group`, `ad`.`name`";
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
	 * Get the record of the attribute record in the database.
	 *
	 * @param int $attribute_id primary key of the attribute record
	 *
	 * @return array<int, array<string, string>> description records that have attribute ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute');
	 *
	 * $attribute_description = $this->model_catalog_attribute->getDescriptions($attribute_id);
	 */
	public function getDescriptions(int $attribute_id): array {
		$attribute_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attribute_description` WHERE `attribute_id` = '" . (int)$attribute_id . "'");

		foreach ($query->rows as $result) {
			$attribute_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $attribute_data;
	}

	/**
	 * Get Total Attributes
	 *
	 * Get the total number of attribute records in the database.
	 *
	 * @return int total number of attribute records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute');
	 *
	 * $attribute_total = $this->model_catalog_attribute->getTotalAttributes();
	 */
	public function getTotalAttributes(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "attribute`");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Attributes By Attribute Group ID
	 *
	 * Get the total number of attribute records with group ID in the database.
	 *
	 * @param int $attribute_group_id foreign key of the attribute group record
	 *
	 * @return int total number of attribute records that have attribute group ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/attribute');
	 *
	 * $attribute_total = $this->model_catalog_attribute->getTotalAttributesByAttributeGroupId($attribute_group_id);
	 */
	public function getTotalAttributesByAttributeGroupId(int $attribute_group_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "attribute` WHERE `attribute_group_id` = '" . (int)$attribute_group_id . "'");

		return (int)$query->row['total'];
	}
}
