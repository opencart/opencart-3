<?php
/**
 * Class Translation
 *
 * Can be called using $this->load->model('design/translation');
 *
 * @package Admin\Model\Design
 */
class ModelDesignTranslation extends Model {
	/**
	 * Add Translation
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $translation_data = [
	 *     'route' => '',
	 *     'key'   => '',
	 *     'value' => ''
	 * ];
	 *
	 * $this->load->model('design/translation');
	 *
	 * $this->model_design_translation->addTranslation($translation_data);
	 */
	public function addTranslation(array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "translation` SET `store_id` = '" . (int)$data['store_id'] . "', `language_id` = '" . (int)$data['language_id'] . "', `route` = '" . $this->db->escape($data['route']) . "', `key` = '" . $this->db->escape($data['key']) . "', `value` = '" . $this->db->escape($data['value']) . "', `date_added` = NOW()");
	}

	/**
	 * Edit Translation
	 *
	 * @param int                  $translation_id primary key of the translation record
	 * @param array<string, mixed> $data           array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $translation_data = [
	 *     'route' => '',
	 *     'key'   => '',
	 *     'value' => ''
	 * ];
	 *
	 * $this->load->model('design/translation');
	 *
	 * $this->model_design_translation->editTranslation($translation_id, $translation_data);
	 */
	public function editTranslation(int $translation_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "translation` SET `store_id` = '" . (int)$data['store_id'] . "', `language_id` = '" . (int)$data['language_id'] . "', `route` = '" . $this->db->escape($data['route']) . "', `key` = '" . $this->db->escape($data['key']) . "', `value` = '" . $this->db->escape($data['value']) . "' WHERE `translation_id` = '" . (int)$translation_id . "'");
	}

	/**
	 * Delete Translation
	 *
	 * @param int $translation_id primary key of the translation record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('design/translation');
	 *
	 * $this->model_design_translation->deleteTranslation($translation_id);
	 */
	public function deleteTranslation(int $translation_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "translation` WHERE `translation_id` = '" . (int)$translation_id . "'");
	}

	/**
	 * Get Translation
	 *
	 * @param int $translation_id primary key of the translation record
	 *
	 * @return array<string, mixed> translation record that has translation ID
	 *
	 * @example
	 *
	 * $this->load->model('design/translation');
	 *
	 * $translation_info = $this->model_design_translation->getTranslation($translation_id);
	 */
	public function getTranslation(int $translation_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "translation` WHERE `translation_id` = '" . (int)$translation_id . "'");

		return $query->row;
	}

	/**
	 * Get Translations
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> translation records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort'  => 'store',
	 *     'order' => 'DESC',
	 *     'start' => 0,
	 *     'limit' => 10
	 * ];
	 *
	 * $this->load->model('design/translation');
	 *
	 * $results = $this->model_design_translation->getTranslations($filter_data);
	 */
	public function getTranslations(array $data = []): array {
		$sql = "SELECT *, (SELECT `s`.`name` FROM `" . DB_PREFIX . "store` `s` WHERE `s`.`store_id` = `t`.`store_id`) AS `store`, (SELECT `l`.`name` FROM `" . DB_PREFIX . "language` `l` WHERE `l`.`language_id` = `t`.`language_id`) AS `language` FROM `" . DB_PREFIX . "translation` `t`";

		$sort_data = [
			'store',
			'language',
			'route',
			'key',
			'value'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `store`";
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
	 * Get Total Translations
	 *
	 * @return int total number of translation records
	 *
	 * @example
	 *
	 * $this->load->model('design/translation');
	 *
	 * $translation_total = $this->model_design_translation->getTotalTranslations();
	 */
	public function getTotalTranslations(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "translation`");

		return (int)$query->row['total'];
	}
}
