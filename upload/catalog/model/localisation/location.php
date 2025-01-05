<?php
/**
 * Class Location
 *
 * Can be called from $this->load->model('localisation/location');
 *
 * @package Catalog\Model\Localisation
 */
class ModelLocalisationLocation extends Model {
	/**
	 * Get Location
	 *
	 * @param int $location_id primary key of the location record
	 *
	 * @return array<string, mixed> location record
	 *
	 * @example
	 *
	 * $location_info = $this->model_localisation_location->getLocation($location_id);
	 */
	public function getLocation(int $location_id): array {
		$query = $this->db->query("SELECT `location_id`, `name`, `address`, `geocode`, `telephone`, `fax`, `image`, `open`, `comment` FROM `" . DB_PREFIX . "location` WHERE `location_id` = '" . (int)$location_id . "'");

		return $query->row;
	}
}
