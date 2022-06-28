<?php
class ControllerExtensionModuleAccount extends Controller {
	public function index() {
		$this->load->language('extension/module/account');

		$data['logged'] = $this->customer->isLogged();
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);
		$data['account'] = $this->url->link('account/account', '', true);
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['order'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['reward'] = $this->url->link('account/reward', '', true);
		$data['returns'] = $this->url->link('account/returns', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);		
		$data['subscription'] = $this->url->link('account/subscription', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);

		return $this->load->view('extension/module/account', $data);
	}
	
	public function subscription(&$route, &$args, &$output) {
		if ($this->config->get('config_information_subscription_id')) {
			$this->load->language('extension/module/account');
		
			$args'scripts'] = $this->document->getScripts();
			
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_information_subscription_id'));

			if ($information_info) {
				$args['text_subscription_marketing'] = sprintf($this->language->get('text_subscription_marketing'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_information_subscription_id'), true), $information_info['title']);
				
				$search = '<div class="row">';
				
				$replace = $this->load->view('extension/module/account_subscription', $args);
				
				$output = str_replace($search, $replace . $search, $output);
			} else {
				$args['text_subscription_marketing'] = '';
			}
		} else {
			$args['text_subscription_marketing'] = '';
		}
	}
}