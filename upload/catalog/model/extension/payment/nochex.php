<?php
/**
 * Class Nochex
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentNochex extends Model {
	/**
	 * getMethod
	 *
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/nochex');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_nochex_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_nochex_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'nochex',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_nochex_sort_order')
			];
		}

		return $method_data;
	}
}
