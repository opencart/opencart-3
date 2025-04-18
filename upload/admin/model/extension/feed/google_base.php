<?php
/**
 * Class Google Base
 *
 * @package Admin\Model\Extension\Feed
 */
class ModelExtensionFeedGoogleBase extends Model {
	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE `" . DB_PREFIX . "google_base_category` (
				`google_base_category_id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				PRIMARY KEY (`google_base_category_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

		$this->db->query("
			CREATE TABLE `" . DB_PREFIX . "google_base_category_to_category` (
				`google_base_category_id` int(11) NOT NULL,
				`category_id` int(11) NOT NULL,
				PRIMARY KEY (`google_base_category_id`, `category_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "google_base_category`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "google_base_category_to_category`");
	}

	/**
	 * Import
	 *
	 * @param string $string
	 *
	 * @return void
	 */
	public function import(string $string): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "google_base_category`");

		$lines = explode("\n", $string);

		foreach ($lines as $line) {
			if (substr($line, 0, 1) != '#') {
				$part = explode(' - ', $line, 2);

				if (isset($part[1])) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "google_base_category` SET `google_base_category_id` = '" . (int)$part[0] . "', `name` = '" . $this->db->escape($part[1]) . "'");
				}
			}
		}
	}

	/**
	 * getGoogleBaseCategories
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getGoogleBaseCategories(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "google_base_category` WHERE `name` LIKE '%" . $this->db->escape($data['filter_name']) . "%' ORDER BY `name` ASC";

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
	 * addCategory
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function addCategory(array $data): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "google_base_category_to_category` WHERE `category_id` = '" . (int)$data['category_id'] . "'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "google_base_category_to_category` SET `google_base_category_id` = '" . (int)$data['google_base_category_id'] . "', `category_id` = '" . (int)$data['category_id'] . "'");
	}

	/**
	 * deleteCategory
	 *
	 * @param int $category_id
	 *
	 * @return void
	 */
	public function deleteCategory(int $category_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "google_base_category_to_category` WHERE `category_id` = '" . (int)$category_id . "'");
	}

	/**
	 * getCategories
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getCategories(array $data = []): array {
		$sql = "SELECT `google_base_category_id`, (SELECT `name` FROM `" . DB_PREFIX . "google_base_category` `gbc` WHERE `gbc`.`google_base_category_id` = `gbc2c`.`google_base_category_id`) AS `google_base_category`, `category_id`, (SELECT `name` FROM `" . DB_PREFIX . "category_description` `cd` WHERE `cd`.`category_id` = `gbc2c`.`category_id` AND `cd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `category` FROM `" . DB_PREFIX . "google_base_category_to_category` `gbc2c` ORDER BY `google_base_category` ASC";

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
	 * getTotalCategories
	 *
	 * @return int
	 */
	public function getTotalCategories(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "google_base_category_to_category`");

		return (int)$query->row['total'];
	}
}
