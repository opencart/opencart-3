<?php
namespace Session;

final class DB {
	public function __construct(Registry $registry) {
		$this->db = $registry->get('db');
		
		$this->config = $registry->get('config');
	}

	public function read($session_id) {
		$query = $this->db->query("SELECT `data` FROM `" . DB_PREFIX . "session` WHERE `session_id` = '" . $this->db->escape($session_id) . "' AND `expire` > '" . $this->db->escape(date('Y-m-d H:i:s'))  . "'");

		if ($query->num_rows) {
			return json_decode($query->row['data'], true);
		} else {
			return array();
		}
	}

	public function write($session_id, $data) {
		if ($session_id) {
			$this->db->query("REPLACE INTO `" . DB_PREFIX . "session` SET `session_id` = '" . $this->db->escape($session_id) . "', `data` = '" . $this->db->escape($data ? json_encode($data) : '') . "', `expire` = '" . $this->db->escape(date('Y-m-d H:i:s', time() + $this->config->get('session_expire'))) . "'");
		}

		return true;
	}

	public function destroy($session_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "session` WHERE `session_id` = '" . $this->db->escape($session_id) . "'");

		return true;
	}

	public function gc() {
		if (round(rand(1, $this->config->get('session_divisor') / $this->config->get('session_probability'))) == 1) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "session` WHERE `expire` < '" . $this->db->escape(date('Y-m-d H:i:s', time())) . "'");
		}

		return true;
	}
}
