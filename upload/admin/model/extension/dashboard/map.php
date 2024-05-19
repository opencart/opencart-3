<?php
/**
 * Class Map
 *
 * @package Admin\Model\Extension\Dashboard
 */
class ModelExtensionDashboardMap extends Model {
	public function getTotalOrdersByCountry(): array {
		$implode = [];

		foreach ((array)$this->config->get('config_complete_status') as $order_status_id) {
			$implode[] = "`o`.`order_status_id` = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT COUNT(*) AS `total`, SUM(`o`.`total`) AS `amount`, LCASE(`c`.`iso_code_2`) AS `iso_code_2` FROM `" . DB_PREFIX . "order` `o` RIGHT JOIN `" . DB_PREFIX . "country` `c` ON (`o`.`payment_country_id` = `c`.`country_id`) WHERE (" . implode(" OR ", $implode) . ") GROUP BY `o`.`payment_country_id`");

			return $query->rows;
		} else {
			return [];
		}
	}
}
