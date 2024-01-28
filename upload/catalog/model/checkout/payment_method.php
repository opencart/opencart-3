<?php
/**
 * Class PaymentMethod
 *
 * @package Catalog\Model\Checkout
 */
class ModelCheckoutPaymentMethod extends Model {
	/**
	 * Get Methods
	 *
	 * @param array<string, mixed> $payment_address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $payment_address = []): array {
		$method_data = [];

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensionsByType('payment');

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);

				if (is_callable([$this->{'model_extension_payment_' . $result['code']}, 'getMethods'])) {
					$payment_methods = $this->{'model_extension_payment_' . $result['code']}->getMethods($payment_address);

					if ($payment_methods) {
						$method_data[$result['code']] = $payment_methods;
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
