<?php
class ModelSettingApi extends Model {
	public function login($username, $key) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api` a LEFT JOIN `" . DB_PREFIX . "api_ip` ai ON (a.`api_id` = ai.`api_id`) WHERE a.`username` = '" . $this->db->escape($username) . "' AND a.`key` = '" . $this->db->escape($key) . "'");

		return $query->row;
	}
	
	public function updateSession($api_session_id) {
		// keep the session alive
		$this->db->query("UPDATE `" . DB_PREFIX . "api_session` SET `date_modified` = NOW() WHERE `api_session_id` = '" . (int)$api_session_id . "'");
	}
	
	public function cleanSessions() {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE TIMESTAMPADD(HOUR, 1, `date_modified`) < NOW()");
	}
}