<?php
/**
 * Class Zone
 *
 * @package Catalog\Model\Localisation
 */
class ModelLocalisationZone extends Model {
	/**
	 * Get Zone
	 *
	 * @param int $zone_id
	 *
	 * @return array<string, mixed>
	 */
	public function getZone(int $zone_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$zone_id . "' AND `status` = '1'");

		return $query->row;
	}

	/**
	 * Get Zones By Country Id
	 *
	 * @param int $country_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getZonesByCountryId(int $country_id): array {
		$zone_data = $this->cache->get('zone.' . (int)$country_id);

		if (!$zone_data) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '" . (int)$country_id . "' AND `status` = '1' ORDER BY `name`");

			$zone_data = $query->rows;

			$this->cache->set('zone.' . (int)$country_id, $zone_data);
		}

		return $zone_data;
	}
}
