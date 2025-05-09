<?php
/**
 * Class Api
 *
 * Can be called using $this->load->model('setting/api');
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingApi extends Model {
	/**
	 * Login
	 *
	 * @param string $username
	 * @param string $key
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $api_info = $this->model_setting_api->login($username, $key);
	 */
	public function login(string $username, string $key): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api` `a` LEFT JOIN `" . DB_PREFIX . "api_ip` `ai` ON (`a`.`api_id` = `ai`.`api_id`) WHERE `a`.`username` = '" . $this->db->escape($username) . "' AND `a`.`key` = '" . $this->db->escape($key) . "'");

		return $query->row;
	}

	/**
	 * Get Api By Token
	 *
	 * @param string $token
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $api_info = $this->model_setting_api->getApiByToken($token);
	 */
	public function getApiByToken(string $token): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "api` `a` LEFT JOIN `" . DB_PREFIX . "api_session` `as` ON (`a`.`api_id` = `as`.`api_id`) LEFT JOIN `" . DB_PREFIX . "api_ip` `ai` ON (`a`.`api_id` = `ai`.`api_id`) WHERE `a`.`status` = '1' AND `as`.`session_id` = '" . $this->db->escape($token) . "' AND `ai`.`ip` = '" . oc_get_ip() . "'");

		return $query->row;
	}

	/**
	 * Update Session
	 *
	 * @param int $api_session_id primary key of the api session record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $this->model_setting_api->updateSession($api_session_id);
	 */
	public function updateSession(int $api_session_id): void {
		// Keep the session alive
		$this->db->query("UPDATE `" . DB_PREFIX . "api_session` SET `date_modified` = NOW() WHERE `api_session_id` = '" . (int)$api_session_id . "'");
	}

	/**
	 * Clean API Sessions
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $this->model_setting_api->cleanSessions();
	 */
	public function cleanSessions(): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE TIMESTAMPADD(HOUR, 1, `date_modified`) < NOW()");
	}
}
