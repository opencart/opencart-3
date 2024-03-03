<?php
/**
 * Class Free Checkout
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentFreeCheckout extends Model {
	/**
	 * Get Methods
	 *
	 * @param array<string, mixed> $address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/free_checkout');

		$total = $this->cart->getTotal();

		if (!empty($this->session->data['vouchers'])) {
			$amounts = array_column($this->session->data['vouchers'], 'amount');
		} else {
			$amounts = [];
		}

		$total += array_sum($amounts);

		if ((float)$total <= 0.00) {
			$status = true;
		} elseif ($this->cart->hasSubscription()) {
			$status = false;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'free_checkout',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_free_checkout_sort_order')
			];
		}

		return $method_data;
	}
}
