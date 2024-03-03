<?php
/**
 * Class Cod
 *
 * @package Catalog\Controller\Extension\Payment
 */
class ControllerExtensionPaymentCod extends Controller {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		return $this->load->view('extension/payment/cod');
	}

	/**
	 * Confirm
	 *
	 * @return void
	 */
	public function confirm(): void {
		$json = [];

		if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'cod') {
			// Orders
			$this->load->model('checkout/order');

			$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_cod_order_status_id'));

			$json['redirect'] = $this->url->link('checkout/success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
