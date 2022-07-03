<?php
class ModelExtensionModulePayPalSmartButton extends Model {	
	public function hasProductInCart(int $product_id, array $option = array(), int $subscription_plan_id = 0): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "cart` WHERE `api_id` = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND `customer_id` = '" . (int)$this->customer->getId() . "' AND `session_id` = '" . $this->db->escape($this->session->getId()) . "' AND `product_id` = '" . (int)$product_id . "' AND `subscription_plan_id` = '" . (int)$subscription_plan_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");
		
		return (int)$query->row['total'];
	}
	
	public function getCountryByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE `iso_code_2` = '" . $this->db->escape($code) . "' AND `status` = '1'");

		return $query->row;
	}
	
	public function getZoneByCode(int $country_id, string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '" . (int)$country_id . "' AND (`code` = '" . $this->db->escape($code) . "' OR `name` = '" . $this->db->escape($code) . "') AND `status` = '1'");
		
		return $query->row;
	}
	
	public function log(array $data, string $title = null): void {
		if ($this->config->get('payment_paypal_debug')) {
			$log = new \Log('paypal.log');
			$log->write('PayPal debug (' . $title . '): ' . json_encode($data));
		}
	}
}