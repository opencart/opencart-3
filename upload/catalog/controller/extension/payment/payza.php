<?php
/**
 * Class Payza
 *
 * @package Catalog\Controller\Extension\Payment
 */
class ControllerExtensionPaymentPayza extends Controller {
	/**
	 * @return string
	 */
	public function index(): string {
		if (!isset($this->session->data['order_id'])) {
			return '';
		}

		// Orders
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['action'] = 'https://secure.payza.com/checkout';
		$data['ap_merchant'] = $this->config->get('payment_payza_merchant');
		$data['ap_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$data['ap_currency'] = $order_info['currency_code'];
		$data['ap_purchasetype'] = 'Item';
		$data['ap_itemname'] = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];
		$data['ap_itemcode'] = (int)$this->session->data['order_id'];
		$data['ap_returnurl'] = $this->url->link('checkout/success');
		$data['ap_cancelurl'] = $this->url->link('checkout/checkout', '', true);

		return $this->load->view('extension/payment/payza', $data);
	}

	/**
	 * Callback
	 *
	 * @return void
	 */
	public function callback(): void {
		if (isset($this->request->post['ap_securitycode']) && ($this->request->post['ap_securitycode'] == $this->config->get('payment_payza_security'))) {
			// Orders
			$this->load->model('checkout/order');

			$this->model_checkout_order->addHistory($this->request->post['ap_itemcode'], $this->config->get('payment_payza_order_status_id'));
		}
	}
}
