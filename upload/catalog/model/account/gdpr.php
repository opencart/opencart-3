<?php
/**
 * Class Gdpr
 *
 * @package Catalog\Model\Account
 */
class ModelAccountGdpr extends Model {
	/**
	 * @param string $code
	 * @param string $email
	 * @param string $action
	 *
	 * @return void
	 */
    public function addGdpr(string $code, string $email, string $action): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "gdpr` SET `store_id` = '" . $this->db->escape($this->config->get('config_store_id')) . "', `language_id` = '" . $this->db->escape($this->config->get('config_language_id')) . "', `code` = '" . $this->db->escape($code) . "', `email` = '" . $this->db->escape($email) . "', `action` = '" . $this->db->escape($action) . "', `date_added` = NOW()");
    }

	/**
	 * @param int $gdpr_id
	 * @param int $status
	 *
	 * @return void
	 */
    public function editStatus(int $gdpr_id, int $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "gdpr` SET `status` = '" . (int)$status . "' WHERE `gdpr_id` = '" . (int)$gdpr_id . "'");
    }

	/**
	 * @param string $code
	 *
	 * @return array
	 */
    public function getGdprByCode(string $code): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "gdpr` WHERE `code` = '" . $this->db->escape($code) . "'");

        return $query->row;
    }

	/**
	 * @param string $email
	 *
	 * @return array
	 */
    public function getGdprsByEmail(string $email): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "gdpr` WHERE `email` = '" . $this->db->escape($email) . "'");

        return $query->rows;
    }
}
