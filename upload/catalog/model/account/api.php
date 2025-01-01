<?php
/**
 * Class Api
 * 
 * @example $api_model = $this->model_account_api;
 * 
 * Can be called from $this->load->model('account/api');
 *
 * @package Catalog\Model\Account
 */
class ModelAccountApi extends Model {
	/**
	 * Login
	 *
	 * @param string $username
	 * @param string $key
	 *
	 * @return array<string, mixed>
	 */
	public function login(string $username, string $key): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api` WHERE `username` = '" . $this->db->escape($username) . "' AND `key` = '" . $this->db->escape($key) . "' AND `status` = '1'");

		return $query->row;
	}

	/**
	 * Add Session
	 *
	 * @param int    $api_id primary key of the api record
	 * @param string $session_id
	 * @param string $ip
	 *
	 * @return int returns the primary key of the new api session record
	 */
	public function addSession(int $api_id, string $session_id, string $ip): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "api_session` SET `api_id` = '" . (int)$api_id . "', `session_id` = '" . $this->db->escape($session_id) . "', `ip` = '" . $this->db->escape($ip) . "', `date_added` = NOW(), `date_modified` = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * Get Ips
	 *
	 * @param int $api_id primary key of the address record
	 *
	 * @return array<int, array<string, mixed>> ip records that have api ID
	 */
	public function getIps(int $api_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api_ip` WHERE `api_id` = '" . (int)$api_id . "'");

		return $query->rows;
	}
}
