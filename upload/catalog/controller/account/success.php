<?php
class ControllerAccountSuccess extends Controller {
	public function index(): void {
		$this->load->language('account/success');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('account/success', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''))
		);

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_message'), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_approval'), $this->config->get('config_name'), $this->url->link('information/contact'));
		}

		if ($this->cart->hasProducts()) {
			$data['continue'] = $this->url->link('checkout/cart');
		} else {
			$data['continue'] = $this->url->link('account/account', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}