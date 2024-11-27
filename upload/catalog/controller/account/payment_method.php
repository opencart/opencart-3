<?php
/**
 * Class Payment Method
 *
 * @package Catalog\Controller\Account
 */
class ControllerAccountPaymentMethod extends Controller {
	/**
	 * @var array<string, string>
	 */
	private array $error = [];

	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('account/payment_method', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/payment_method');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->getList();
	}

	/**
	 * Delete
	 *
	 * @return void
	 */
	public function delete(): void {
		$this->load->language('account/payment_method');

		$json = [];

		if (isset($this->request->get['code'])) {
			$code = (string)$this->request->get['code'];
		} else {
			$code = '';
		}

		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('account/payment_method');

			$json['redirect'] = $this->url->link('account/login', '', true);
		}

		if (!$json) {
			$this->load->model('setting/extension');

			$payment_method_info = $this->model_setting_extension->getExtensionByCode('payment', $code);

			if (!$payment_method_info) {
				$json['error'] = $this->language->get('error_payment_method');
			}
		}

		if (!$json && isset($payment_method_info)) {
			$this->load->model('extension/payment/' . $payment_method_info['code']);

			$callable = [$this->{'model_extension_payment_' . $payment_method_info['code']}, 'delete'];

			if (is_callable($callable)) {
				$callable();
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Get List
	 *
	 * @return void
	 */
	protected function getList(): void {
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/payment_method', 'customer_token=' . $this->session->data['customer_token'], true)
		];

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['payment_methods'] = [];

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensionsByType('payment');

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);

				$callable = [$this->{'model_extension_payment_' . $result['code']}, 'getStored'];

				if (is_callable($callable)) {
					$payment_method_info = $callable();

					if ($payment_method_info) {
						$data['payment_methods'][] = [
							'code'        => $payment_method_info['code'],
							'name'        => $payment_method_info['name'],
							'description' => $payment_method_info['description'],
							'image'       => $payment_method_info['image'],
							'delete'      => $this->url->link('account/payment_method/delete', 'customer_token=' . $this->session->data['customer_token'] . '&code=' . $payment_method_info['code'])
						];
					}
				}
			}
		}

		$data['customer_token'] = $this->session->data['customer_token'];

		$data['back'] = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/payment_method_list', $data));
	}
}
