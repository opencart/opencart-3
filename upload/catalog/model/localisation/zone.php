<?php
/**
 * Class Zone
 *
 * Can be called using $this->load->model('localisation/zone');
 *
 * @package Catalog\Model\Localisation
 */
class ModelLocalisationZone extends Model {
	/**
	 * Get Zone
	 *
	 * @param int $zone_id primary key of the zone record
	 *
	 * @return array<string, mixed> zone record that has zone ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/zone');
	 *
	 * $zone_info = $this->model_localisation_zone->getZone($zone_id);
	 */
	public function getZone(int $zone_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$zone_id . "' AND `status` = '1'");

		return $query->row;
	}

	/**
	 * Get Zones By Country ID
	 *
	 * @param int $country_id primary key of the country record
	 *
	 * @return array<int, array<string, mixed>> zone records that have country ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/zone');
	 *
	 * $results = $this->model_localisation_zone->getZonesByCountryId($country_id);
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
