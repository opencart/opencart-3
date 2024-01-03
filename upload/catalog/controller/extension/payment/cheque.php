<?php
/**
 * Class Cheque
 *
 * @package Catalog\Controller\Extension\Payment
 */
class ControllerExtensionPaymentCheque extends Controller {
	/**
	 * @return string
	 */
	public function index(): string {
		$this->load->language('extension/payment/cheque');

		$data['payable'] = $this->config->get('payment_cheque_payable');
		$data['address'] = nl2br($this->config->get('config_address'));

		return $this->load->view('extension/payment/cheque', $data);
	}

	/**
	 * Confirm
	 *
	 * @return void
	 */
	public function confirm(): void {
		$json = [];

		if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'cheque') {
			$this->load->language('extension/payment/cheque');

			// Orders
			$this->load->model('checkout/order');

			$comment = $this->language->get('text_payable') . "\n";
			$comment .= $this->config->get('payment_cheque_payable') . "\n\n";
			$comment .= $this->language->get('text_address') . "\n";
			$comment .= $this->config->get('config_address') . "\n\n";
			$comment .= $this->language->get('text_payment') . "\n";

			$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_cheque_order_status_id'), $comment, true);

			$json['redirect'] = $this->url->link('checkout/success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
