<?php
/**
 * Class PayPal
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentPayPal extends Model {
	/**
	 * getMethod
	 *
	 * @param array $address
	 * @param float $total
	 *
	 * @return array
	 */
	public function getMethod(array $address, float $total): array {
		$method_data = [];

		$agree_status = $this->getAgreeStatus();

		if ($this->config->get('payment_paypal_status') && $this->config->get('payment_paypal_client_id') && $this->config->get('payment_paypal_secret') && $agree_status) {
			$this->load->language('extension/payment/paypal');

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_paypal_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

			if (($this->config->get('payment_paypal_total') > 0) && ($this->config->get('payment_paypal_total') > $total)) {
				$status = false;
			} elseif (!$this->config->get('payment_paypal_geo_zone_id')) {
				$status = true;
			} elseif ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}

			if ($status) {
				$method_data = [
					'code'       => 'paypal',
					'title'      => $this->language->get('text_paypal_title'),
					'terms'      => '',
					'sort_order' => $this->config->get('payment_paypal_sort_order')
				];
			}
		}

		return $method_data;
	}

	/**
	 * hasProductInCart
	 *
	 * @param int   $product_id
	 * @param array $option
	 * @param int   $subscription_id
	 *
	 * @return int
	 */
	public function hasProductInCart(int $product_id, array $option = [], int $subscription_plan_id = 0): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "cart` WHERE `api_id` = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND `customer_id` = '" . (int)$this->customer->getId() . "' AND `session_id` = '" . $this->db->escape($this->session->getId()) . "' AND `product_id` = '" . (int)$product_id . "' AND `subscription_plan_id` = '" . (int)$subscription_plan_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");

		return (int)$query->row['total'];
	}

	/**
	 * getCountryByCode
	 *
	 * @param string $code
	 *
	 * @return array
	 */
	public function getCountryByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE `iso_code_2` = '" . $this->db->escape($code) . "' AND `status` = '1'");

		return $query->row;
	}

	/**
	 * getZoneByCode
	 *
	 * @param int    $country_id
	 * @param string $code
	 *
	 * @return array
	 */
	public function getZoneByCode(int $country_id, string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '" . (int)$country_id . "' AND (`code` = '" . $this->db->escape($code) . "' OR `name` = '" . $this->db->escape($code) . "') AND `status` = '1'");

		return $query->row;
	}

	/**
	 * getAgreeStatus
	 *
	 * @return bool
	 */
	public function getAgreeStatus(): bool {
		$agree_status = true;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE `status` = '1' AND (`iso_code_2` = 'CU' OR `iso_code_2` = 'IR' OR `iso_code_2` = 'SY' OR `iso_code_2` = 'KP')");

		if ($query->rows) {
			$agree_status = false;
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '220' AND `status` = '1' AND (`code` = '43' OR `code` = '14' OR `code` = '09')");

		if ($query->rows) {
			$agree_status = false;
		}

		return $agree_status;
	}

	/**
	 * Log
	 *
	 * @param string $message
	 * @param string $title
	 *
	 * @return void
	 */
	public function log(string $message, ?string $title = null): void {
		// Setting
		$_config = new \Config();
		$_config->load('paypal');

		$config_setting = $_config->get('paypal_setting');

		$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

		if ($setting['general']['debug']) {
			$log = new \Log('paypal.log');
			$log->write('PayPal debug (' . $title . '): ' . json_encode($message));
		}
	}
}
