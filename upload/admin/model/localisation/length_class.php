<?php
/**
 * Class Length Class
 *
 * Can be called from $this->load->model('localisation/length_class');
 *
 * @package Admin\Model\Localisation
 */
class ModelLocalisationLengthClass extends Model {
	/**
	 * Add Length Class
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new length class record
	 *
	 * @example
	 *
	 * $length_class_id = $this->model_localisation_length_class->addLengthClass($data);
	 */
	public function addLengthClass(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "length_class` SET `value` = '" . (float)$data['value'] . "'");

		$length_class_id = $this->db->getLastId();

		foreach ($data['length_class_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "length_class_description` SET `length_class_id` = '" . (int)$length_class_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "', `unit` = '" . $this->db->escape($value['unit']) . "'");
		}

		$this->cache->delete('length_class');

		return $length_class_id;
	}

	/**
	 * Edit Length Class
	 *
	 * @param int                  $length_class_id primary key of the length class record
	 * @param array<string, mixed> $data            array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_length_class->editLengthClass($length_class_id, $data);
	 */
	public function editLengthClass(int $length_class_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "length_class` SET `value` = '" . (float)$data['value'] . "' WHERE `length_class_id` = '" . (int)$length_class_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "length_class_description` WHERE `length_class_id` = '" . (int)$length_class_id . "'");

		foreach ($data['length_class_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "length_class_description` SET `length_class_id` = '" . (int)$length_class_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "', `unit` = '" . $this->db->escape($value['unit']) . "'");
		}

		$this->cache->delete('length_class');
	}

	/**
	 * Delete Length Class
	 *
	 * @param int $length_class_id primary key of the length class record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_length_class->deleteLengthClass($length_class_id);
	 */
	public function deleteLengthClass(int $length_class_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "length_class` WHERE `length_class_id` = '" . (int)$length_class_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "length_class_description` WHERE `length_class_id` = '" . (int)$length_class_id . "'");

		$this->cache->delete('length_class');
	}

	/**
	 * Get Length Classes
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> length class records
	 *
	 * @example
	 *
	 * $length_classes = $this->model_localisation_length_class->getLengthClasses();
	 */
	public function getLengthClasses(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "length_class` `lc` LEFT JOIN `" . DB_PREFIX . "length_class_description` `lcd` ON (`lc`.`length_class_id` = `lcd`.`length_class_id`) WHERE `lcd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = [
				'title',
				'unit',
				'value'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY `title`";
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
			$length_class_data = $this->cache->get('length_class.' . (int)$this->config->get('config_language_id'));

			if (!$length_class_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "length_class` `lc` LEFT JOIN `" . DB_PREFIX . "length_class_description` `lcd` ON (`lc`.`length_class_id` = `lcd`.`length_class_id`) WHERE `lcd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

				$length_class_data = $query->rows;

				$this->cache->set('length_class.' . (int)$this->config->get('config_language_id'), $length_class_data);
			}

			return $length_class_data;
		}
	}

	/**
	 * Get Length Class
	 *
	 * @param int $length_class_id primary key of the length class record
	 *
	 * @return array<string, mixed> length class record that has length class ID
	 *
	 * @example
	 *
	 * $length_class_info = $this->model_localisation_length_class->getLengthClass($length_class_id);
	 */
	public function getLengthClass(int $length_class_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "length_class` `lc` LEFT JOIN `" . DB_PREFIX . "length_class_description` `lcd` ON (`lc`.`length_class_id` = `lcd`.`length_class_id`) WHERE `lc`.`length_class_id` = '" . (int)$length_class_id . "' AND `lcd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Description By Unit
	 *
	 * @param string $unit
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $length_class_info = $this->model_localisation_length_class->getDescriptionByUnit($unit);
	 */
	public function getDescriptionByUnit(string $unit): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "length_class_description` WHERE `unit` = '" . $this->db->escape($unit) . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Descriptions
	 *
	 * @param int $length_class_id primary key of the length class record
	 *
	 * @return array<int, array<string, mixed>> description records that have length class ID
	 *
	 * @example
	 *
	 * $length_class_description = $this->model_localisation_length_class->getDescriptions($length_class_id);
	 */
	public function getDescriptions(int $length_class_id): array {
		$length_class_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "length_class_description` WHERE `length_class_id` = '" . (int)$length_class_id . "'");

		foreach ($query->rows as $result) {
			$length_class_data[$result['language_id']] = [
				'title' => $result['title'],
				'unit'  => $result['unit']
			];
		}

		return $length_class_data;
	}

	/**
	 * Get Total Length Classes
	 *
	 * @return int total number of length class records
	 *
	 * @example
	 *
	 * $length_class_total = $this->model_localisation_length_class->getTotalLengthClasses();
	 */
	public function getTotalLengthClasses(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "length_class`");

		return (int)$query->row['total'];
	}
}
