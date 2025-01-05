<?php
/**
 * Class Zone
 *
 * Can be called from $this->load->model('localisation/zone');
 *
 * @package Admin\Model\Localisation
 */
class ModelLocalisationZone extends Model {
	/**
	 * Add Zone
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $zone_id = $this->model_localisation_zone->addZone($data);
	 */
	public function addZone(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "zone` SET `status` = '" . (int)$data['status'] . "', `name` = '" . $this->db->escape($data['name']) . "', `code` = '" . $this->db->escape($data['code']) . "', `country_id` = '" . (int)$data['country_id'] . "'");

		$this->cache->delete('zone');

		return $this->db->getLastId();
	}

	/**
	 * Edit Zone
	 *
	 * @param int                  $zone_id primary key of the zone record
	 * @param array<string, mixed> $data    array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_zone->editZone($zone_id, $data);
	 */
	public function editZone(int $zone_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `status` = '" . (int)$data['status'] . "', `name` = '" . $this->db->escape($data['name']) . "', `code` = '" . $this->db->escape($data['code']) . "', `country_id` = '" . (int)$data['country_id'] . "' WHERE `zone_id` = '" . (int)$zone_id . "'");

		$this->cache->delete('zone');
	}

	/**
	 * Delete Zone
	 *
	 * @param int $zone_id primary key of the zone record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_zone->deleteZone($zone_id);
	 */
	public function deleteZone(int $zone_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$zone_id . "'");

		$this->cache->delete('zone');
	}

	/**
	 * Get Zone
	 *
	 * @param int $zone_id primary key of the zone record
	 *
	 * @return array<string, mixed> zone record that has zone ID
	 *
	 * @example
	 *
	 * $zone_info = $this->model_localisation_zone->getZone($zone_id);
	 */
	public function getZone(int $zone_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$zone_id . "'");

		return $query->row;
	}

	/**
	 * Get Zones
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> zone records
	 *
	 * @example
	 *
	 * $results = $this->model_localisation_zone->getZones();
	 */
	public function getZones(array $data = []): array {
		$sql = "SELECT *, `z`.`name`, `c`.`name` AS `country` FROM `" . DB_PREFIX . "zone` `z` LEFT JOIN `" . DB_PREFIX . "country` `c` ON (`z`.`country_id` = `c`.`country_id`)";

		$sort_data = [
			'c.name',
			'z.name',
			'z.code'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `c`.`name`";
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
	 * Get Zones By Country ID
	 *
	 * @param int $country_id primary key of the country record
	 *
	 * @return array<int, array<string, mixed>> zone records that have country ID
	 *
	 * @example
	 *
	 * $zones = $this->model_localisation_zone->getZonesByCountryId($country_id);
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

	/**
	 * Get Total Zones
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of zone records
	 *
	 * @example
	 *
	 * $zone_total = $this->model_localisation_zone->getTotalZones();
	 */
	public function getTotalZones(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "zone`");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Zones By Country ID
	 *
	 * @param int $country_id primary key of the country record
	 *
	 * @return int total number of zone records that have country ID
	 *
	 * @example
	 *
	 * $zone_total = $this->model_localisation_zone->getTotalZonesByCountryId($country_id);
	 */
	public function getTotalZonesByCountryId(int $country_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '" . (int)$country_id . "'");

		return (int)$query->row['total'];
	}
}
