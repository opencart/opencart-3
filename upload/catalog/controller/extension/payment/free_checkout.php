<?php
/**
 * Class Free Checkout
 *
 * @package Catalog\Controller\Extension\Payment
 */
class ControllerExtensionPaymentFreeCheckout extends Controller {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$data['continue'] = $this->url->link('checkout/success');

		return $this->load->view('extension/payment/free_checkout', $data);
	}

	/**
	 * Confirm
	 *
	 * @return void
	 */
	public function confirm(): void {
		if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'free_checkout') {
			// Orders
			$this->load->model('checkout/order');

			$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_free_checkout_order_status_id'));
		}
	}
}
