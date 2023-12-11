<?php
/**
 * Class SagePay Us
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentSagePayUS extends Model {
	/**
	 * getMethod
	 *
	 * @param array $address
	 *
	 * @return array
	 */
    public function getMethod(array $address): array {
        $this->load->language('extension/payment/sagepay_us');

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_sagepay_us_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

        if (!$this->config->get('payment_sagepay_us_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = [];

        if ($status) {
            $method_data = [
                'code'       => 'sagepay_us',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_sagepay_us_sort_order')
            ];
        }

        return $method_data;
    }
}
