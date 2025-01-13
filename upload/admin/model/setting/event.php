<?php
/**
 * Class Event
 *
 * Can be called using $this->load->model('setting/event');
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
	 *
	 * @example
	 *
	 * $event_data = [
	 *     'code'        => 'Event Code',
	 *     'trigger'     => 'Event Trigger',
	 *     'action'      => 'Event Action',
	 *     'status'      => 0,
	 *     'sort_order'  => 0
	 * ];
	 *
	 * $this->load->model('setting/event');
	 *
	 * $event_id = $this->model_setting_event->addEvent($code, $trigger, $action, $status, $sort_order);
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
	 *
	 * @example
	 *
	 * $this->load->model('setting/event');
	 *
	 * $this->model_setting_event->deleteEvent($event_id);
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
	 *
	 * @example
	 *
	 * $this->load->model('setting/event');
	 *
	 * $this->model_setting_event->deleteEventByCode($code);
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
	 *
	 * @example
	 *
	 * $this->model_setting_event->enableEvent($event_id);
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
	 *
	 * @example
	 *
	 * $this->model_setting_event->disableEvent($event_id);
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
	 *
	 * @example
	 *
	 * $this->model_setting_event->uninstall($type, $code);
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
	 *
	 * @example
	 *
	 * $this->load->model('setting/event');
	 *
	 * $event_info = $this->model_setting_event->getEvent($event_id);
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
	 *
	 * @example
	 *
	 * $this->load->model('setting/event');
	 *
	 * $event_info = $this->model_setting_event->getEventByCode($code);
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
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort'  => 'code',
	 *     'order' => 'DESC',
	 *     'start' => 0,
	 *     'limit' => 10
	 * ];
	 *
	 * $this->load->model('setting/event');
	 *
	 * $results = $this->model_setting_event->getEvents($filter_data);
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
	 *
	 * @example
	 *
	 * $this->load->model('setting/event');
	 *
	 * $event_total = $this->model_setting_event->getTotalEvents();
	 */
	public function getTotalEvents(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "event`");

		return (int)$query->row['total'];
	}
}
