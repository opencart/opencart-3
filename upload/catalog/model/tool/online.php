<?php
/**
 * Class Online
 *
 * Can be called using $this->load->model('tool/online');
 *
 * @package Catalog\Model\Tool
 */
class ModelToolOnline extends Model {
	/**
	 * Add Online
	 *
	 * @param string $ip
	 * @param int    $customer_id primary key of the customer record
	 * @param string $url
	 * @param string $referer
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('tool/online');
	 *
	 * $this->model_tool_online->addOnline($ip, $customer_id, $url, $referer);
	 */
	public function addOnline(string $ip, int $customer_id, string $url, string $referer): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_online` WHERE `date_added` < '" . date('Y-m-d H:i:s', strtotime('-1 hour')) . "'");

		$this->db->query("REPLACE INTO `" . DB_PREFIX . "customer_online` SET `ip` = '" . $this->db->escape($ip) . "', `customer_id` = '" . (int)$customer_id . "', `url` = '" . $this->db->escape($url) . "', `referer` = '" . $this->db->escape($referer) . "', `date_added` = '" . $this->db->escape(date('Y-m-d H:i:s')) . "'");
	}
}
