<?php
/**
 * Class Pilibaba
 *
 * @package Catalog\Model\Extension\Shipping
 */
class ModelExtensionShippingPilibaba extends Model {
	/**
	 * getQuote
	 *
	 * @param array $address
	 */
	public function getQuote(array $address): array {
		$this->load->language('extension/shipping/pilibaba');

		$status = true;
		$method_data = [];

		if ($status) {
			$quote_data = [];

			$quote_data['pilibaba'] = [
				'code'         => 'pilibaba.pilibaba',
				'title'        => $this->language->get('text_description'),
				'cost'         => $this->config->get('payment_pilibaba_shipping_fee'),
				'tax_class_id' => 0,
				'text'         => $this->currency->format($this->tax->calculate($this->config->get('payment_pilibaba_shipping_fee'), 0, $this->config->get('config_tax')), $this->session->data['currency'])
			];

			$method_data = [
				'code'       => 'pilibaba',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => 1,
				'error'      => false
			];
		}

		return $method_data;
	}
}
