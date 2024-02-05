<?php
/**
 * Class Shipping
 *
 * @package Catalog\Controller\Extension\Total
 */
class ControllerExtensionTotalShipping extends Controller {
	/**
	 * @return string
	 */
	public function index(): string {
		if ($this->config->get('total_shipping_status') && $this->config->get('total_shipping_estimator') && $this->cart->hasShipping()) {
			$this->load->language('extension/total/shipping');

			if (isset($this->session->data['shipping_address']['country_id'])) {
				$data['country_id'] = (int)$this->session->data['shipping_address']['country_id'];
			} else {
				$data['country_id'] = $this->config->get('config_country_id');
			}

			// Countries
			$this->load->model('localisation/country');

			$data['countries'] = $this->model_localisation_country->getCountries();

			if (isset($this->session->data['shipping_address']['zone_id'])) {
				$data['zone_id'] = (int)$this->session->data['shipping_address']['zone_id'];
			} else {
				$data['zone_id'] = '';
			}

			if (isset($this->session->data['shipping_address']['postcode'])) {
				$data['postcode'] = $this->session->data['shipping_address']['postcode'];
			} else {
				$data['postcode'] = '';
			}

			if (isset($this->session->data['shipping_method'])) {
				$data['shipping_method'] = $this->session->data['shipping_method']['code'];
			} else {
				$data['shipping_method'] = '';
			}

			return $this->load->view('extension/total/shipping', $data);
		} else {
			return '';
		}
	}

	/**
	 * Quote
	 *
	 * @return void
	 */
	public function quote(): void {
		$this->load->language('extension/total/shipping');

		$json = [];

		if (!$this->cart->hasProducts()) {
			$json['error']['warning'] = $this->language->get('error_product');
		}

		if (!$this->cart->hasShipping()) {
			$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		}

		if ($this->request->post['country_id'] == '') {
			$json['error']['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$json['error']['zone'] = $this->language->get('error_zone');
		}

		// Countries
		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (oc_strlen($this->request->post['postcode']) < 2 || oc_strlen($this->request->post['postcode']) > 10)) {
			$json['error']['postcode'] = $this->language->get('error_postcode');
		}

		if (!$json) {
			$this->tax->clear();
			$this->tax->setShippingAddress($this->request->post['country_id'], $this->request->post['zone_id']);

			if (isset($this->session->data['payment_address']['country_id']) && isset($this->session->data['payment_address']['zone_id'])) {
				$this->tax->setPaymentAddress($this->session->data['payment_address']['country_id'], $this->session->data['payment_address']['zone_id']);
			} elseif ($this->config->get('config_tax_default') == 'payment') {
				$this->tax->setPaymentAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
			}

			$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

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

			$this->session->data['shipping_address'] = [
				'firstname'      => '',
				'lastname'       => '',
				'company'        => '',
				'address_1'      => '',
				'address_2'      => '',
				'postcode'       => $this->request->post['postcode'],
				'city'           => '',
				'zone_id'        => $this->request->post['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $this->request->post['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			];

			$quote_data = [];

			// Shipping methods
			$this->load->model('checkout/shipping_method');

			$quote_data = $this->model_checkout_shipping_method->getMethods($this->session->data['shipping_address']);

			if ($quote_data) {
				$json['shipping_methods'] = $this->session->data['shipping_methods'] = $quote_data;			

				if ($this->session->data['shipping_methods']) {
					$json['shipping_method'] = $this->session->data['shipping_methods'];
				} else {
					$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
				}
			} else {
				$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Shipping
	 *
	 * @return void
	 */
	public function shipping(): void {
		$this->load->language('extension/total/shipping');

		$json = [];

		if (!empty($this->request->post['shipping_method'])) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
				$json['warning'] = $this->language->get('error_shipping');
			}
		} else {
			$json['warning'] = $this->language->get('error_shipping');
		}

		if (!$json) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
			$this->session->data['success'] = $this->language->get('text_success');

			$json['redirect'] = $this->url->link('checkout/cart');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Country
	 *
	 * @return void
	 */
	public function country(): void {
		$json = [];

		// Countries
		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			// Zones
			$this->load->model('localisation/zone');

			$json = [
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
