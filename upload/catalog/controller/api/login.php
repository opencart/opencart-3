<?php
/**
 * Class Login
 *
 * @package Catalog\Controller\Api
 */
class ControllerApiLogin extends Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('api/login');

		$json = $api_info = [];

		// API login
		$this->load->model('account/api');

		// Login with API Key
		if (isset($this->request->post['username']) && isset($this->request->post['key'])) {
			$api_info = $this->model_account_api->login($this->request->post['username'], $this->request->post['key']);
		} elseif (isset($this->request->post['key'])) {
			$api_info = $this->model_account_api->login('Default', $this->request->post['key']);
		}

		if ($api_info) {
			// Check if IP is allowed
			$ip_data = [];

			$results = $this->model_account_api->getIps($api_info['api_id']);

			foreach ($results as $result) {
				$ip_data[] = trim($result['ip']);
			}

			if (!in_array(oc_get_ip(), $ip_data)) {
				$json['error']['ip'] = sprintf($this->language->get('error_ip'), oc_get_ip());
			}

			if (!$json) {
				$json['success'] = $this->language->get('text_success');

				// Session
				$session = new \Session($this->config->get('session_engine'), $this->registry);
				$session->start();

				$this->model_account_api->addSession($api_info['api_id'], $session->getId(), oc_get_ip());

				$session->data['api_id'] = $api_info['api_id'];

				// Create Token
				$json['api_token'] = $session->getId();
			} else {
				$json['error']['key'] = $this->language->get('error_key');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
