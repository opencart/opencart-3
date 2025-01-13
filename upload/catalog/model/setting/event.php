<?php
/**
 * Class Event
 *
 * Can be called using $this->load->model('setting/event');
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingEvent extends Model {
	/**
	 * Get Events
	 *
	 * @return array<int, array<string, mixed>> event records
	 *
	 * @example
	 *
	 * $this->load->model('setting/event');
	 *
	 * $events = $this->model_setting_event->getEvents();
	 */
	public function getEvents(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `trigger` LIKE 'catalog/%' AND `status` = '1' ORDER BY `sort_order` ASC");

		return $query->rows;
	}
}
