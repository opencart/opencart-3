<?php
/**
 * Class Payment
 *
 * @package Catalog\Controller\Api
 */
class ControllerApiPayment extends Controller {
	/**
	 * @return void
	 */
    public function address(): void {
        $this->load->language('api/payment');

        // Delete old payment address, payment methods and method so not to cause any issues if there is an error
        unset($this->session->data['payment_address']);
        unset($this->session->data['payment_methods']);
        unset($this->session->data['payment_method']);

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            // Add keys for missing post vars
            $keys = [
                'firstname',
                'lastname',
                'company',
                'address_1',
                'address_2',
                'postcode',
                'city',
                'zone_id',
                'country_id'
            ];

            foreach ($keys as $key) {
                if (!isset($this->request->post[$key])) {
                    $this->request->post[$key] = '';
                }
            }

            if ((oc_strlen($this->request->post['firstname']) < 1) || (oc_strlen($this->request->post['firstname']) > 32)) {
                $json['error']['firstname'] = $this->language->get('error_firstname');
            }

            if ((oc_strlen($this->request->post['lastname']) < 1) || (oc_strlen($this->request->post['lastname']) > 32)) {
                $json['error']['lastname'] = $this->language->get('error_lastname');
            }

            if ((oc_strlen($this->request->post['address_1']) < 3) || (oc_strlen($this->request->post['address_1']) > 128)) {
                $json['error']['address_1'] = $this->language->get('error_address_1');
            }

            if ((oc_strlen($this->request->post['city']) < 2) || (oc_strlen($this->request->post['city']) > 32)) {
                $json['error']['city'] = $this->language->get('error_city');
            }

            // Countries
            $this->load->model('localisation/country');

            $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

            if ($country_info && $country_info['postcode_required'] && (oc_strlen($this->request->post['postcode']) < 2 || oc_strlen($this->request->post['postcode']) > 10)) {
                $json['error']['postcode'] = $this->language->get('error_postcode');
            }

            if ($this->request->post['country_id'] == '') {
                $json['error']['country'] = $this->language->get('error_country');
            }

            if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
                $json['error']['zone'] = $this->language->get('error_zone');
            }

            // Custom field validation
            $this->load->model('account/custom_field');

            $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

            foreach ($custom_fields as $custom_field) {
                if ($custom_field['location'] == 'address') {
                    if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                        $json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                    } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !preg_match(html_entity_decode($custom_field['validation'], ENT_QUOTES, 'UTF-8'), $this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                        $json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
                    }
                }
            }

            if (!$json) {
                // Countries
                $this->load->model('localisation/country');

                $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

                if ($country_info) {
                    $country = $country_info['name'];
                    $iso_code_2 = $country_info['iso_code_2'];
                    $iso_code_3 = $country_info['iso_code_3'];
                    $address_format = $country_info['address_format'];
                } else {
                    $country = '';
                    $iso_code_2 = '';
                    $iso_code_3 = '';
                    $address_format = '';
                }

                // Zones
                $this->load->model('localisation/zone');

                $zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

                if ($zone_info) {
                    $zone = $zone_info['name'];
                    $zone_code = $zone_info['code'];
                } else {
                    $zone = '';
                    $zone_code = '';
                }

                $this->session->data['payment_address'] = [
                    'firstname'      => $this->request->post['firstname'],
                    'lastname'       => $this->request->post['lastname'],
                    'company'        => $this->request->post['company'],
                    'address_1'      => $this->request->post['address_1'],
                    'address_2'      => $this->request->post['address_2'],
                    'postcode'       => $this->request->post['postcode'],
                    'city'           => $this->request->post['city'],
                    'zone_id'        => $this->request->post['zone_id'],
                    'zone'           => $zone,
                    'zone_code'      => $zone_code,
                    'country_id'     => $this->request->post['country_id'],
                    'country'        => $country,
                    'iso_code_2'     => $iso_code_2,
                    'iso_code_3'     => $iso_code_3,
                    'address_format' => $address_format,
                    'custom_field'   => isset($this->request->post['custom_field']) ? $this->request->post['custom_field'] : []
                ];

                $json['success'] = $this->language->get('text_address');

                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	/**
	 * @return void
	 */
    public function methods(): void {
        $this->load->language('api/payment');

        // Delete past shipping methods and method just in case there is an error
        unset($this->session->data['payment_methods']);
        unset($this->session->data['payment_method']);

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            // Payment Address
            if (!isset($this->session->data['payment_address'])) {
                $json['error'] = $this->language->get('error_address');
            }

            if (!$json) {
                // Totals
                $total = 0;
                $totals = [];
                $taxes = $this->cart->getTaxes();

                // Because __call cannot keep var references, so we put them into an array.
                $total_data = [
                    'totals' => &$totals,
                    'taxes'  => &$taxes,
                    'total'  => &$total
                ];

                // Extensions
                $this->load->model('setting/extension');

                $sort_order = [];

                $results = $this->model_setting_extension->getExtensionsByType('total');

                foreach ($results as $key => $value) {
                    $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
                }

                array_multisort($sort_order, SORT_ASC, $results);

                foreach ($results as $result) {
                    if ($this->config->get('total_' . $result['code'] . '_status')) {
                        $this->load->model('extension/total/' . $result['code']);

                        // We have to put the totals in an array so that they pass by reference.
                        $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                    }
                }

                $json['payment_methods'] = [];

                // Payment Methods
                $this->load->model('checkout/payment_method');

                $payment_methods = $this->model_checkout_payment_method->getMethods($this->session->data['payment_address']);

                if ($payment_methods) {
                    // Store payment methods in session
                    $json['payment_methods'] = $payment_methods;
                }

                if ($json['payment_methods']) {
                    $this->session->data['payment_methods'] = $json['payment_methods'];
                } else {
                    $json['error'] = $this->language->get('error_no_payment');
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	/**
	 * @return void
	 */
    public function method(): void {
        $this->load->language('api/payment');

        // Delete old payment method so not to cause any issues if there is an error
        unset($this->session->data['payment_method']);

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            // Payment Address
            if (!isset($this->session->data['payment_address'])) {
                $json['error'] = $this->language->get('error_address');
            }

            // Payment Method
            if (empty($this->session->data['payment_methods'])) {
                $json['error'] = $this->language->get('error_no_payment');
            } elseif (!isset($this->request->post['payment_method'])) {
                $json['error'] = $this->language->get('error_method');
            } elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
                $json['error'] = $this->language->get('error_method');
            }

            if (!$json) {
                $this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];

                $json['success'] = $this->language->get('text_method');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
