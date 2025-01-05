<?php
/**
 * Class Shipping Method
 *
 * Can be called from $this->load->model('checkout/shipping_method');
 *
 * @package Catalog\Model\Checkout
 */
class ModelCheckoutShippingMethod extends Model {
	/**
	 * Get Methods
	 *
	 * @param array<string, mixed> $shipping_address
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function getMethods(array $shipping_address): array {
		$method_data = [];

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensionsByType('shipping');

		foreach ($results as $result) {
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);

				$callable = [$this->{'model_extension_shipping_' . $result['code']}, 'getQuote'];

				if (is_callable($callable)) {
					$quote = $callable($shipping_address);

					if ($quote) {
						$method_data[$result['code']] = $quote;
					}
				}
			}
		}

		$sort_order = [];

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		return $method_data;
	}
}
