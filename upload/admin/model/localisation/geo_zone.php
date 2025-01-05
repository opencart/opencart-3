<?php
/**
 * Class Geo Zone
 *
 * Can be called from $this->load->model('localisation/geo_zone');
 *
 * @package Admin\Model\Localisation
 */
class ModelLocalisationGeoZone extends Model {
	/**
	 * Add Geo Zone
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new geo zone record
	 *
	 * @example
	 *
	 * $geo_zone_id = $this->model_localisation_geo_zone->addGeoZone($data);
	 */
	public function addGeoZone(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "geo_zone` SET `name` = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', `date_added` = NOW()");

		$geo_zone_id = $this->db->getLastId();

		if (isset($data['zone_to_geo_zone'])) {
			foreach ($data['zone_to_geo_zone'] as $value) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "' AND `country_id` = '" . (int)$value['country_id'] . "' AND `zone_id` = '" . (int)$value['zone_id'] . "'");

				$this->db->query("INSERT INTO `" . DB_PREFIX . "zone_to_geo_zone` SET `country_id` = '" . (int)$value['country_id'] . "', `zone_id` = '" . (int)$value['zone_id'] . "', `geo_zone_id` = '" . (int)$geo_zone_id . "', `date_added` = NOW()");
			}
		}

		$this->cache->delete('geo_zone');

		return $geo_zone_id;
	}

	/**
	 * Edit Geo Zone
	 *
	 * @param int                  $geo_zone_id primary key of the geo zone record
	 * @param array<string, mixed> $data        array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_geo_zone->editGeoZone($geo_zone_id, $data);
	 */
	public function editGeoZone(int $geo_zone_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "geo_zone` SET `name` = '" . $this->db->escape($data['name']) . "', `description` = '" . $this->db->escape($data['description']) . "', `date_modified` = NOW() WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "'");

		if (isset($data['zone_to_geo_zone'])) {
			foreach ($data['zone_to_geo_zone'] as $value) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "' AND `country_id` = '" . (int)$value['country_id'] . "' AND `zone_id` = '" . (int)$value['zone_id'] . "'");

				$this->db->query("INSERT INTO `" . DB_PREFIX . "zone_to_geo_zone` SET `country_id` = '" . (int)$value['country_id'] . "', `zone_id` = '" . (int)$value['zone_id'] . "', `geo_zone_id` = '" . (int)$geo_zone_id . "', `date_added` = NOW()");
			}
		}

		$this->cache->delete('geo_zone');
	}

	/**
	 * Delete Geo Zone
	 *
	 * @param int $geo_zone_id primary key of the geo zone record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_geo_zone->deleteGeoZone($geo_zone_id);
	 */
	public function deleteGeoZone(int $geo_zone_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "geo_zone` WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "'");

		$this->cache->delete('geo_zone');
	}

	/**
	 * Get Geo Zone
	 *
	 * @param int $geo_zone_id primary key of the geo zone record
	 *
	 * @return array<string, mixed> geo zone record that has geo zone ID
	 *
	 * @example
	 *
	 * $geo_zone_info = $this->model_localisation_geo_zone->getGeoZone($geo_zone_id);
	 */
	public function getGeoZone(int $geo_zone_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "geo_zone` WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "'");

		return $query->row;
	}

	/**
	 * Get Geo Zones
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> geo zone records
	 *
	 * @example
	 *
	 * $results = $this->model_localisation_geo_zone->getGeoZones();
	 */
	public function getGeoZones(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "geo_zone`";

			$sort_data = [
				'name',
				'description'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY `name`";
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
			$geo_zone_data = $this->cache->get('geo_zone');

			if (!$geo_zone_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "geo_zone` ORDER BY `name` ASC");

				$geo_zone_data = $query->rows;

				$this->cache->set('geo_zone', $geo_zone_data);
			}

			return $geo_zone_data;
		}
	}

	/**
	 * Get Total Geo Zones
	 *
	 * @return int total number of geo zone records
	 *
	 * @example
	 *
	 * $geo_zone_total = $this->model_localisation_geo_zone->getTotalGeoZones();
	 */
	public function getTotalGeoZones(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "geo_zone`");

		return (int)$query->row['total'];
	}

	/**
	 * Get Zone To Geo Zones
	 *
	 * @param int $geo_zone_id primary key of the geo zone record
	 *
	 * @return array<int, array<string, mixed>> geo zone records that have geo zone ID
	 *
	 * @example
	 *
	 * $zone_to_geo_zones = $this->model_localisation_geo_zone->geoZoneToGeoZones($geo_zone_id);
	 */
	public function getZoneToGeoZones(int $geo_zone_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "'");

		return $query->rows;
	}

	/**
	 * Get Total Zone To Geo Zone By Geo Zone Id
	 *
	 * @param int $geo_zone_id primary key of the geo zone record
	 *
	 * @return int total number of geo zone records that have geo zone ID
	 *
	 * @example
	 *
	 * $zone_to_geo_zones = $this->model_localisation_geo_zone->getTotalZoneToGeoZoneByGeoZoneId($geo_zone_id);
	 */
	public function getTotalZoneToGeoZoneByGeoZoneId(int $geo_zone_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Zone To Geo Zone By Country Id
	 *
	 * @param int $country_id primary key of the country record
	 *
	 * @return int total number of geo zone records that have country ID
	 *
	 * @example
	 *
	 * $geo_zone_total = $this->model_localisation_geo_zone->getTotalZoneToGeoZoneByCountryId($country_id);
	 */
	public function getTotalZoneToGeoZoneByCountryId(int $country_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `country_id` = '" . (int)$country_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Zone To Geo Zone By Zone Id
	 *
	 * @param int $zone_id primary key of the zone record
	 *
	 * @return int total number of geo zone records that have zone ID
	 *
	 * @example
	 *
	 * $geo_zone_total = $this->model_localisation_geo_zone->getTotalZoneToGeoZoneByZoneId($zone_id);
	 */
	public function getTotalZoneToGeoZoneByZoneId(int $zone_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `zone_id` = '" . (int)$zone_id . "'");

		return (int)$query->row['total'];
	}
}
