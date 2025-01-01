<?php
/**
 * Class Tax Class
 *
 * @example $tax_class_model = $this->model_localisation_tax_class;
 *
 * Can be called from $this->load->model('localisation/tax_class');
 *
 * @package Admin\Model\Localisation
 */
class ModelLocalisationTaxClass extends Model {
	/**
	 * Add Tax Class
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new tax class record
	 */
	public function addTaxClass(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "tax_class` SET `title` = '" . $this->db->escape($data['title']) . "', `description` = '" . $this->db->escape($data['description']) . "', `date_added` = NOW()");

		$tax_class_id = $this->db->getLastId();

		if (isset($data['tax_rule'])) {
			foreach ($data['tax_rule'] as $tax_rule) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "tax_rule` SET `tax_class_id` = '" . (int)$tax_class_id . "', `tax_rate_id` = '" . (int)$tax_rule['tax_rate_id'] . "', `based` = '" . $this->db->escape($tax_rule['based']) . "', `priority` = '" . (int)$tax_rule['priority'] . "'");
			}
		}

		$this->cache->delete('tax_class');

		return $tax_class_id;
	}

	/**
	 * Edit Tax Class
	 *
	 * @param int                  $tax_class_id primary key of the tax class record
	 * @param array<string, mixed> $data         array of data
	 *
	 * @return void
	 */
	public function editTaxClass(int $tax_class_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "tax_class` SET `title` = '" . $this->db->escape($data['title']) . "', `description` = '" . $this->db->escape($data['description']) . "', `date_modified` = NOW() WHERE `tax_class_id` = '" . (int)$tax_class_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "tax_rule` WHERE `tax_class_id` = '" . (int)$tax_class_id . "'");

		if (isset($data['tax_rule'])) {
			foreach ($data['tax_rule'] as $tax_rule) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "tax_rule` SET `tax_class_id` = '" . (int)$tax_class_id . "', `tax_rate_id` = '" . (int)$tax_rule['tax_rate_id'] . "', `based` = '" . $this->db->escape($tax_rule['based']) . "', `priority` = '" . (int)$tax_rule['priority'] . "'");
			}
		}

		$this->cache->delete('tax_class');
	}

	/**
	 * Delete Tax Class
	 *
	 * @param int $tax_class_id primary key of the tax class record
	 *
	 * @return void
	 */
	public function deleteTaxClass(int $tax_class_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "tax_class` WHERE `tax_class_id` = '" . (int)$tax_class_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "tax_rule` WHERE `tax_class_id` = '" . (int)$tax_class_id . "'");

		$this->cache->delete('tax_class');
	}

	/**
	 * Get Tax Class
	 *
	 * @param int $tax_class_id primary key of the tax class record
	 *
	 * @return array<string, mixed> tax class record that has tax class ID
	 */
	public function getTaxClass(int $tax_class_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "tax_class` WHERE `tax_class_id` = '" . (int)$tax_class_id . "'");

		return $query->row;
	}

	/**
	 * Get Tax Classes
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> tax class records
	 */
	public function getTaxClasses(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "tax_class`";

			$sql .= " ORDER BY `title`";

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
			$tax_class_data = $this->cache->get('tax_class');

			if (!$tax_class_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "tax_class`");

				$tax_class_data = $query->rows;

				$this->cache->set('tax_class', $tax_class_data);
			}

			return $tax_class_data;
		}
	}

	/**
	 * Get Total Tax Classes
	 *
	 * @return int total number of tax class records
	 */
	public function getTotalTaxClasses(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "tax_class`");

		return (int)$query->row['total'];
	}

	/**
	 * Get Tax Rules
	 *
	 * @param int $tax_class_id primary key of the tax class record
	 *
	 * @return array<int, array<string, mixed>> tax rule records that have tax class ID
	 */
	public function getTaxRules(int $tax_class_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "tax_rule` WHERE `tax_class_id` = '" . (int)$tax_class_id . "' ORDER BY `priority` ASC");

		return $query->rows;
	}

	/**
	 * Get Total Tax Rules By Tax Rate ID
	 *
	 * @param int $tax_rate_id primary key of the tax rate record
	 *
	 * @return int total number of tax rule records that have tax rate ID
	 */
	public function getTotalTaxRulesByTaxRateId(int $tax_rate_id): int {
		$query = $this->db->query("SELECT COUNT(DISTINCT `tax_class_id`) AS `total` FROM `" . DB_PREFIX . "tax_rule` WHERE `tax_rate_id` = '" . (int)$tax_rate_id . "'");

		return (int)$query->row['total'];
	}
}
