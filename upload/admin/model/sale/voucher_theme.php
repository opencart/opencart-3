<?php
/**
 * Class Voucher Theme
 *
 * Can be called from $this->load->model('sale/voucher_theme');
 *
 * @package Admin\Model\Sale
 */
class ModelSaleVoucherTheme extends Model {
	/**
	 * Add Voucher Theme
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new voucher theme record
	 *
	 * @example
	 *
	 * $voucher_theme_id = $this->model_sale_voucher_theme->addVoucherTheme($data);
	 */
	public function addVoucherTheme(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "voucher_theme` SET `image` = '" . $this->db->escape($data['image']) . "'");

		$voucher_theme_id = $this->db->getLastId();

		foreach ($data['voucher_theme_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "voucher_theme_description` SET `voucher_theme_id` = '" . (int)$voucher_theme_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		$this->cache->delete('voucher_theme');

		return $voucher_theme_id;
	}

	/**
	 * Edit Voucher Theme
	 *
	 * @param int                  $voucher_theme_id primary key of the voucher theme record
	 * @param array<string, mixed> $data             array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_voucher_theme->editVoucherTheme($voucher_theme_id, $data);
	 */
	public function editVoucherTheme(int $voucher_theme_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "voucher_theme` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `voucher_theme_id` = '" . (int)$voucher_theme_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "voucher_theme_description` WHERE `voucher_theme_id` = '" . (int)$voucher_theme_id . "'");

		foreach ($data['voucher_theme_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "voucher_theme_description` SET `voucher_theme_id` = '" . (int)$voucher_theme_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		$this->cache->delete('voucher_theme');
	}

	/**
	 * Delete Voucher Theme
	 *
	 * @param int $voucher_theme_id primary key of the voucher theme record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_voucher_theme->deleteVoucherTheme($voucher_theme_id);
	 */
	public function deleteVoucherTheme(int $voucher_theme_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "voucher_theme` WHERE `voucher_theme_id` = '" . (int)$voucher_theme_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "voucher_theme_description` WHERE `voucher_theme_id` = '" . (int)$voucher_theme_id . "'");

		$this->cache->delete('voucher_theme');
	}

	/**
	 * Get Voucher Theme
	 *
	 * @param int $voucher_theme_id primary key of the voucher theme record
	 *
	 * @return array<string, mixed> voucher theme record that has voucher theme ID
	 *
	 * @example
	 *
	 * $voucher_theme_info = $this->model_sale_voucher_theme->getVoucherTheme($voucher_theme_id);
	 */
	public function getVoucherTheme(int $voucher_theme_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "voucher_theme` `vt` LEFT JOIN `" . DB_PREFIX . "voucher_theme_description` `vtd` ON (`vt`.`voucher_theme_id` = `vtd`.`voucher_theme_id`) WHERE `vt`.`voucher_theme_id` = '" . (int)$voucher_theme_id . "' AND `vtd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Voucher Themes
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> voucher theme records
	 *
	 * @example
	 *
	 * $results = $this->model_sale_voucher_themes = $this->model_sale_voucher_theme->getVoucherThemes();
	 */
	public function getVoucherThemes(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "voucher_theme` `vt` LEFT JOIN `" . DB_PREFIX . "voucher_theme_description` `vtd` ON (`vt`.`voucher_theme_id` = `vtd`.`voucher_theme_id`) WHERE `vtd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `vtd`.`name`";

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
			$voucher_theme_data = $this->cache->get('voucher_theme.' . (int)$this->config->get('config_language_id'));

			if (!$voucher_theme_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "voucher_theme` `vt` LEFT JOIN `" . DB_PREFIX . "voucher_theme_description` `vtd` ON (`vt`.`voucher_theme_id` = `vtd`.`voucher_theme_id`) WHERE `vtd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `vtd`.`name`");

				$voucher_theme_data = $query->rows;

				$this->cache->set('voucher_theme.' . (int)$this->config->get('config_language_id'), $voucher_theme_data);
			}

			return $voucher_theme_data;
		}
	}

	/**
	 * Get Descriptions
	 *
	 * @param int $voucher_theme_id primary key of the voucher theme record
	 *
	 * @return array<int, array<string, string>> description records that have voucher theme ID
	 *
	 * @example
	 *
	 * $voucher_theme_description = $this->model_sale_voucher_theme->getDescriptions($voucher_theme_id);
	 */
	public function getDescriptions(int $voucher_theme_id): array {
		$voucher_theme_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "voucher_theme_description` WHERE `voucher_theme_id` = '" . (int)$voucher_theme_id . "'");

		foreach ($query->rows as $result) {
			$voucher_theme_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $voucher_theme_data;
	}

	/**
	 * Get Total Voucher Themes
	 *
	 * @return int total number of voucher theme records
	 *
	 * @example
	 *
	 * $voucher_total = $this->model_sale_voucher_theme->getTotalVoucherThemes();
	 */
	public function getTotalVoucherThemes(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "voucher_theme`");

		return (int)$query->row['total'];
	}
}
