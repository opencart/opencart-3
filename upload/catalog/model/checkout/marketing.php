<?php
class ModelCheckoutMarketing extends Model {
	public function getMarketingByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "marketing` WHERE `code` = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
	
	public function addReport(int $marketing_id, $ip, string $country = ''): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "marketing_report` SET `marketing_id` = '" . (int)$marketing_id . "', `store_id` = '" . (int)$this->config->get('config_store_id') . "', `ip` = '" . $this->db->escape($ip) . "', `country` = '" . $this->db->escape($country) . "', `date_added` = NOW()");
	}
}