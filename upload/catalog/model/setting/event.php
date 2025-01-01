<?php
/**
 * Class Event
 * 
 * @example $event_model = $this->model_setting_event;
 * 
 * Can be called from $this->load->model('setting/event');
 *
 * @package Catalog\Model\Setting
 */
class ModelSettingEvent extends Model {
	/**
	 * Get Events
	 *
	 * @return array<int, array<string, mixed>> event records
	 */
	public function getEvents(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `trigger` LIKE 'catalog/%' AND `status` = '1' ORDER BY `sort_order` ASC");

		return $query->rows;
	}
}
