<?php
class ControllerExtensionModuleAccount extends Controller {
	public function index(): string {
		$this->load->language('extension/module/account');

		$data['logged'] = $this->customer->isLogged();
		$data['register'] = $this->url->link('account/register', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['login'] = $this->url->link('account/login', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['logout'] = $this->url->link('account/logout', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['forgotten'] = $this->url->link('account/forgotten', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['account'] = $this->url->link('account/account', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['edit'] = $this->url->link('account/edit', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['password'] = $this->url->link('account/password', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['address'] = $this->url->link('account/address', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['wishlist'] = $this->url->link('account/wishlist', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['order'] = $this->url->link('account/order', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['download'] = $this->url->link('account/download', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['reward'] = $this->url->link('account/reward', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['returns'] = $this->url->link('account/returns', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['transaction'] = $this->url->link('account/transaction', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['newsletter'] = $this->url->link('account/newsletter', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);		
		$data['subscription'] = $this->url->link('account/subscription', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		
		// Recurring
		$this->load->model('account/recurring');

		$recurring_total = $this->model_account_recurring->getTotalOrderRecurrings();
		
		if ($recurring_total) {
			$data['recurring'] = $this->url->link('account/recurring', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		} else {
			$data['recurring'] = '';
		}

		return $this->load->view('extension/module/account', $data);
	}
}