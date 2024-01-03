<?php
/**
 * Class Theme
 *
 * @package Catalog\Model\Design
 */
class ModelDesignTheme extends Model {
	/**
	 * getTheme
	 *
	 * @param string $route
	 * @param string $theme
	 *
	 * @return array
	 */
	public function getTheme(string $route, string $theme): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "theme` WHERE `store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `theme` = '" . $this->db->escape($theme) . "' AND `route` = '" . $this->db->escape($route) . "'");

		return $query->row;
	}
}
