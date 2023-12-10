<?php
/**
 * Class Paymate
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentPayMate extends Model {
	/**
	 * getMethod
	 */
    public function getMethod(array $address): array {
        $this->load->language('extension/payment/paymate');

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_paymate_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

        if (!$this->config->get('payment_paymate_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $currencies = [
            'AUD',
            'NZD',
            'USD',
            'EUR',
            'GBP'
        ];

        if (!in_array(strtoupper($this->session->data['currency']), $currencies)) {
            $status = false;
        }

        $method_data = [];

        if ($status) {
            $method_data = [
                'code'       => 'paymate',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_paymate_sort_order')
            ];
        }

        return $method_data;
    }
}
