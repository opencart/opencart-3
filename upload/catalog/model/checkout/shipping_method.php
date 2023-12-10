<?php
/**
 * Class Shipping Method
 *
 * @package Catalog\Model\Checkout
 */
class ModelCheckoutShippingMethod extends Model {
	/**
	 * getMethods
	 *
	 * @param array $shipping_address
	 *
	 * @return array
	 */
    public function getMethods(array $shipping_address): array {
        $method_data = [];

        // Extensions
        $this->load->model('setting/extension');

        $results = $this->model_setting_extension->getExtensionsByType('shipping');

        foreach ($results as $result) {
            if ($this->config->get('shipping_' . $result['code'] . '_status')) {
                $this->load->model('extension/shipping/' . $result['code']);

                $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($shipping_address);

                if ($quote) {
                    $method_data[$result['code']] = [
                        'title'      => $quote['title'],
                        'quote'      => $quote['quote'],
                        'sort_order' => $quote['sort_order'],
                        'error'      => $quote['error']
                    ];
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
