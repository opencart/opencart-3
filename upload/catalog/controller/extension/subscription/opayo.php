<?php
class ControllerExtensionSubscriptionOpayo extends Controller {
	/**
	 * @var array<string, string>
	 */
	private array $error = [];

	/**
	 * @return string
	 */
	public function index(): string {
		$content = '';

		if ($this->config->get('payment_opayo_status')) {
			$this->load->language('extension/subscription/opayo');

			if (isset($this->request->post['subscription_id'])) {
				$subscription_id = (int)$this->request->get['subscription_id'];
			} else {
				$subscription_id = 0;
			}

			$order_subscription_info = $this->model_extension_subscription_opayo->getSubscriptionOrder($subscription_id);

			if ($order_subscription_info) {
				$data['subscription_id'] = $subscription_id;

				$data['subscription_status'] = $order_subscription_info['status'];

				$data['info_url'] =  str_replace('&amp;', '&', $this->url->link('extension/subscription/opayo/getSubscriptionInfo', 'subscription_id=' . $subscription_id, true));
				$data['enable_url'] =  str_replace('&amp;', '&', $this->url->link('extension/subscription/opayo/enableSubscription', '', true));
				$data['disable_url'] =  str_replace('&amp;', '&', $this->url->link('extension/subscription/opayo/disableSubscription', '', true));

				$content = $this->load->view('extension/subscription/opayo', $data);
			}
		}

		return $content;
	}

	/**
	 * getSubscriptionInfo
	 * 
	 * @return void
	 */
	public function getSubscriptionInfo(): void {
		$this->response->setOutput($this->index());
	}

	/**
	 * enableSubscription
	 * 
	 * @return void
	 */
	public function enableSubscription(): void {
		$json = [];

		if ($this->config->get('payment_opayo_status')) {
			$this->load->language('extension/subscription/opayo');

			$this->load->model('extension/payment/opayo');

			if (isset($this->request->post['subscription_id'])) {
				$subscription_id = (int)$this->request->get['subscription_id'];
			} else {
				$subscription_id = 0;
			}

			$this->model_extension_payment_opayo->editOrderSubscriptionStatus($subscription_id, 1);

			$json['success'] = $this->language->get('text_success_enable_subscription');
		}

		$json['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * disableSubscription
	 * 
	 * @return void
	 */
	public function disableSubscription(): void {
		$json = [];

		if ($this->config->get('payment_opayo_status')) {
			$this->load->language('extension/subscription/opayo');

			$this->load->model('extension/payment/opayo');

			if (isset($this->request->post['subscription_id'])) {
				$subscription_id = (int)$this->request->get['subscription_id'];
			} else {
				$subscription_id = 0;
			}

			$this->model_extension_payment_opayo->editOrderSubscriptionStatus($subscription_id, 2);

			$json['success'] = $this->language->get('text_success_disable_subscription');
		}

		$json['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
