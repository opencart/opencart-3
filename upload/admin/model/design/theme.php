<?php
/**
 * Class Theme
 *
 * @example $theme_model = $this->model_design_theme;
 *
 * Can be called from $this->load->model('design/theme');
 *
 * @package Admin\Model\Design
 */
class ModelDesignTheme extends Model {
	/**
	 * Edit Theme
	 *
	 * @param int    $store_id
	 * @param string $theme
	 * @param string $route
	 * @param string $code
	 *
	 * @return void
	 */
	public function editTheme(int $store_id, string $theme, string $route, string $code): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "theme` WHERE `store_id` = '" . (int)$store_id . "' AND `theme` = '" . $this->db->escape($theme) . "' AND `route` = '" . $this->db->escape($route) . "'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "theme` SET `store_id` = '" . (int)$store_id . "', `theme` = '" . $this->db->escape($theme) . "', `route` = '" . $this->db->escape($route) . "', `code` = '" . $this->db->escape($code) . "', `date_added` = NOW()");
	}

	/**
	 * Delete Theme
	 *
	 * @param int $theme_id primary key of the theme record
	 *
	 * @return void
	 */
	public function deleteTheme(int $theme_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "theme` WHERE `theme_id` = '" . (int)$theme_id . "'");
	}

	/**
	 * Get Theme
	 *
	 * @param int    $store_id
	 * @param string $theme
	 * @param string $route
	 *
	 * @return array<string, string>
	 */
	public function getTheme(int $store_id, string $theme, string $route): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "theme` WHERE `store_id` = '" . (int)$store_id . "' AND `theme` = '" . $this->db->escape($theme) . "' AND `route` = '" . $this->db->escape($route) . "'");

		return $query->row;
	}

	/**
	 * Get Themes
	 *
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getThemes(int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT *, (SELECT `name` FROM `" . DB_PREFIX . "store` `s` WHERE `s`.`store_id` = `t`.`store_id`) AS `store` FROM `" . DB_PREFIX . "theme` `t` ORDER BY `t`.`date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * Get Total Themes
	 *
	 * @return int total number of theme records
	 */
	public function getTotalThemes(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "theme`");

		return (int)$query->row['total'];
	}
}
