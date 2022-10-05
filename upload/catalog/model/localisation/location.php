<?php
class ModelLocalisationLocation extends Model {
    public function getLocation(int $location_id): array {
        $query = $this->db->query("SELECT `location_id`, `name`, `address`, `geocode`, `telephone`, `fax`, `image`, `open`, `comment` FROM `" . DB_PREFIX . "location` WHERE `location_id` = '" . (int)$location_id . "'");

        return $query->row;
    }
}