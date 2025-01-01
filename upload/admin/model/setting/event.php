<?php
/**
 * Class Event
 *
 * @example $event_model = $this->model_setting_event;
 *
 * Can be called from $this->load->model('setting/event');
 *
 * @package Admin\Model\Setting
 */
class ModelSettingEvent extends Model {
	/**
	 * Add Event
	 *
	 * @param string $code
	 * @param string $trigger
	 * @param string $action
	 * @param int    $status
	 * @param int    $sort_order
	 *
	 * @return int returns the primary key of the new event record
	 */
	public function addEvent(string $code, string $trigger, string $action, int $status = 1, int $sort_order = 0): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `code` = '" . $this->db->escape($code) . "', `trigger` = '" . $this->db->escape($trigger) . "', `action` = '" . $this->db->escape($action) . "', `sort_order` = '" . (int)$sort_order . "', `status` = '" . (int)$status . "'");

		return $this->db->getLastId();
	}

	/**
	 * Delete Event
	 *
	 * @param int $event_id primary key of the event record
	 *
	 * @return void
	 */
	public function deleteEvent(int $event_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `event_id` = '" . (int)$event_id . "'");
	}

	/**
	 * Delete Event By Code
	 *
	 * @param string $code
	 *
	 * @return void
	 */
	public function deleteEventByCode(string $code): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($code) . "'");
	}

	/**
	 * Enable Event
	 *
	 * @param int $event_id primary key of the event record
	 *
	 * @return void
	 */
	public function enableEvent(int $event_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `status` = '1' WHERE `event_id` = '" . (int)$event_id . "'");
	}

	/**
	 * Disable Event
	 *
	 * @param int $event_id primary key of the event record
	 *
	 * @return void
	 */
	public function disableEvent(int $event_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `status` = '0' WHERE `event_id` = '" . (int)$event_id . "'");
	}

	/**
	 * Uninstall
	 *
	 * @param string $type
	 * @param string $code
	 *
	 * @return void
	 */
	public function uninstall(string $type, string $code): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = '" . $this->db->escape($code) . "'");
	}

	/**
	 * Get Event
	 *
	 * @param int $event_id primary key of the event record
	 *
	 * @return array<string, mixed> event record that has event ID
	 */
	public function getEvent(int $event_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "event` WHERE `event_id` = '" . (int)$event_id . "' LIMIT 1");

		return $query->row;
	}

	/**
	 * Get Event By Code
	 *
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 */
	public function getEventByCode(string $code): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($code) . "' LIMIT 1");

		return $query->row;
	}

	/**
	 * Get Events
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> event records
	 */
	public function getEvents(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "event`";

		$sort_data = [
			'code',
			'trigger',
			'action',
			'sort_order',
			'status',
			'date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `sort_order`";
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
	 * Get Total Events
	 *
	 * @return int total number of event records
	 */
	public function getTotalEvents(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "event`");

		return (int)$query->row['total'];
	}
}
