<?php
/**
 * Class Authorize.net Sim
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentAuthorizeNetSim extends Model {
	/**
	 * getMethod
	 *
	 * @param array $address
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/authorizenet_sim');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_authorizenet_sim_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_authorizenet_sim_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'authorizenet_sim',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_authorizenet_sim_sort_order')
			];
		}

		return $method_data;
	}
}
