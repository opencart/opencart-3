<?php
class ControllerCommonFooter extends Controller {
	public function index(): string {
		$this->load->language('common/footer');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}
		
		if ($this->config->get('config_gdpr_id')) {
			$data['gdpr'] = $this->url->link('information/gdpr');
		} else {
			$data['gdpr'] = '';
		}

		$data['contact'] = $this->url->link('information/contact');
		$data['returns'] = $this->url->link('account/returns/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['special'] = $this->url->link('product/special');
		$data['voucher'] = $this->url->link('account/voucher', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);				
		$data['account'] = $this->url->link('account/account', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['order'] = $this->url->link('account/order', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['wishlist'] = $this->url->link('account/wishlist', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		$data['newsletter'] = $this->url->link('account/newsletter', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		
		// Affiliate
		if ($this->config->get('config_affiliate_status')) {
			$data['affiliate'] = $this->url->link('account/affiliate', (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
		} else {
			$data['affiliate'] = '';
		}

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['HTTP_X_REAL_IP'])) {
				$ip = $this->request->server['HTTP_X_REAL_IP'];
			} elseif (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$data['scripts'] = $this->document->getScripts('footer');
		$data['styles'] = $this->document->getStyles('footer');
		
		$data['cookie'] = $this->load->controller('common/cookie');
		
		return $this->load->view('common/footer', $data);
	}
}
