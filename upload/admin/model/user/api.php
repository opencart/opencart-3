<?php
/**
 * Class Api
 *
 * Can be called from $this->load->model('user/api');
 *
 * @package Admin\Model\User
 */
class ModelUserApi extends Model {
	/**
	 * Add Api
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new api record
	 * 
	 * @example 
	 * 
	 * $api_id = $this->model_user_api->addApi($data);
	 */
	public function addApi(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "api` SET `username` = '" . $this->db->escape($data['username']) . "', `key` = '" . $this->db->escape($data['key']) . "', `status` = '" . (int)$data['status'] . "', `date_added` = NOW(), `date_modified` = NOW()");

		$api_id = $this->db->getLastId();

		if (isset($data['api_ip'])) {
			foreach ($data['api_ip'] as $ip) {
				if ($ip) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "api_ip` SET `api_id` = '" . (int)$api_id . "', `ip` = '" . $this->db->escape($ip) . "'");
				}
			}
		}

		return $api_id;
	}

	/**
	 * Edit Api
	 *
	 * @param int                  $api_id primary key of the api record
	 * @param array<string, mixed> $data   array of data
	 *
	 * @return void
	 * 
	 * @example 
	 * 
	 * $this->model_user_api->editApi($api_id, $data);
	 */
	public function editApi(int $api_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "api` SET `username` = '" . $this->db->escape($data['username']) . "', `key` = '" . $this->db->escape($data['key']) . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW() WHERE `api_id` = '" . (int)$api_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "api_ip` WHERE `api_id` = '" . (int)$api_id . "'");

		if (isset($data['api_ip'])) {
			foreach ($data['api_ip'] as $ip) {
				if ($ip) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "api_ip` SET `api_id` = '" . (int)$api_id . "', `ip` = '" . $this->db->escape($ip) . "'");
				}
			}
		}
	}

	/**
	 * Delete Api
	 *
	 * @param int $api_id primary key of the api record
	 *
	 * @return void
	 * 
	 * @example 
	 * 
	 * $this->model_user_spi->deleteApi($api_id);
	 */
	public function deleteApi(int $api_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "api` WHERE `api_id` = '" . (int)$api_id . "'");
	}

	/**
	 * Get Api
	 *
	 * @param int $api_id primary key of the api record
	 *
	 * @return array<string, mixed> api record that has api ID
	 * 
	 * @example 
	 * 
	 * $api_info = $this->model_user_api->getApi($api_id);
	 */
	public function getApi(int $api_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api` WHERE `api_id` = '" . (int)$api_id . "'");

		return $query->row;
	}

	/**
	 * Get Apis
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> api records
	 * 
	 * @example 
	 * 
	 * $results = $this->model_user_api->getApis();
	 */
	public function getApis(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "api`";

		$sort_data = [
			'username',
			'status',
			'date_added',
			'date_modified'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `username`";
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
	 * Get Total Apis
	 *
	 * @return int total number of api records
	 * 
	 * @example 
	 * 
	 * $api_total = $this->model_user_api->getTotalApis();
	 */
	public function getTotalApis(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "api`");

		return (int)$query->row['total'];
	}

	/**
	 * Add Ip
	 *
	 * @param int    $api_id primary key of the api record
	 * @param string $ip
	 *
	 * @return void
	 * 
	 * @example 
	 * 
	 * $this->model_user_api->addIp($api_id, $ip);
	 */
	public function addIp(int $api_id, string $ip): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "api_ip` SET `api_id` = '" . (int)$api_id . "', `ip` = '" . $this->db->escape($ip) . "'");
	}

	/**
	 * Get Ips
	 *
	 * @param int $api_id primary key of the api record
	 *
	 * @return array<int, string> ip records that have api ID
	 * 
	 * @example 
	 * 
	 * $results = $this->model_user_api->getIps($api_id);
	 */
	public function getIps(int $api_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api_ip` WHERE `api_id` = '" . (int)$api_id . "'");

		return $query->rows;
	}

	/**
	 * Add Session
	 *
	 * @param int    $api_id     primary key of the api record
	 * @param string $session_id
	 * @param string $ip
	 *
	 * @return int returns the primary key of the new api session record
	 * 
	 * @example 
	 * 
	 * $api_session_id = $this->model_user_api->addSession($api_id, $session_id, $ip);
	 */
	public function addSession(int $api_id, string $session_id, string $ip): int {
		$api_ip_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api_ip` WHERE `ip` = '" . $this->db->escape($ip) . "'");

		if (!$api_ip_query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "api_ip` SET `api_id` = '" . (int)$api_id . "', `ip` = '" . $this->db->escape($ip) . "'");
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "api_session` SET `api_id` = '" . (int)$api_id . "', `session_id` = '" . $this->db->escape($session_id) . "', `ip` = '" . $this->db->escape($ip) . "', `date_added` = NOW(), `date_modified` = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * Get Sessions
	 *
	 * @param int $api_id primary key of the api record
	 *
	 * @return array<int, array<string, mixed>> session records that have api ID
	 * 
	 * @example 
	 * 
	 * $results = $this->model_user_api->getSessions($api_id);
	 */
	public function getSessions(int $api_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api_session` WHERE `api_id` = '" . (int)$api_id . "'");

		return $query->rows;
	}

	/**
	 * Delete Session
	 *
	 * @param int $api_session_id primary key of the api session record
	 *
	 * @return void
	 * 
	 * @example 
	 * 
	 * $this->model_user_api->deleteSession($api_session_id);
	 */
	public function deleteSession(int $api_session_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE `api_session_id` = '" . (int)$api_session_id . "'");
	}

	/**
	 * Delete Session By Session ID
	 *
	 * @param string $session_id
	 *
	 * @return void
	 * 
	 * @example 
	 * 
	 * $this->model_user_api->deleteSessionBySessionId($session_id);
	 */
	public function deleteSessionBySessionId(string $session_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE `session_id` = '" . $this->db->escape($session_id) . "'");
	}
}
