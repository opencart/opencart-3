<?php
/**
 * Class Extension
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingExtension extends Model {
    public function getExtensionsByType(string $type): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "'");

        return $query->rows;
    }

    public function getExtensionByCode(string $type, string $code): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");

        return $query->row;
    }
}
