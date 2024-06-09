<?php
class ControllerExtensionRecurringOpayo extends Controller {
	/**
	 * @var array<string, string> $error
	 */
	private array $error = [];

	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$content = '';

		if (!empty($this->request->get['subscription_id'])) {
			$this->load->language('extension/subscription/opayo');

			$this->load->model('account/subscription');

			$data['subscription_id'] = (int)$this->request->get['subscription_id'];

			$subscription_info = $this->model_account_subscription->getSubscription($data['subscription_id']);

			if ($subscription_info) {
				$data['subscription_status'] = $subscription_info['status'];

				$data['info_url'] =  str_replace('&amp;', '&', $this->url->link('extension/subscription/opayo/getSubscriptionInfo', 'subscription_id=' . $data['subscription_id'], true));
				$data['enable_url'] =  str_replace('&amp;', '&', $this->url->link('extension/subscription/opayo/enableSubscription', '', true));
				$data['disable_url'] =  str_replace('&amp;', '&', $this->url->link('extension/subscription/opayo/disableSubscription', '', true));

				$content = $this->load->view('extension/subscription/opayo', $data);
			}
		}

		return $content;
	}

	/**
	 * Get Subscription Info
	 *
	 * @return void
	 */
	public function getSubscriptionInfo(): void {
		$this->response->setOutput($this->index());
	}

	/**
	 * Enable Subscription
	 *
	 * @return void
	 */
	public function enableSubscription(): void {
		if (!empty($this->request->post['subscription_id'])) {
			$this->load->language('extension/subscription/opayo');

			$this->load->model('checkout/subscription');

			$subscription_id = (int)$this->request->post['subscription_id'];

			$this->model_checkout_subscription->editSubscriptionStatus($subscription_id, 1);

			$data['success'] = $this->language->get('success_enable_subscription');
		}

		$data['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	/**
	 * Disable Subscription
	 *
	 * @return void
	 */
	public function disableSubscription(): void {
		if (!empty($this->request->post['subscription_id'])) {
			$this->load->language('extension/subscription/opayo');

			$this->load->model('checkout/subscription');

			$subscription_id = (int)$this->request->post['subscription_id'];

			$this->model_checkout_subscription->editSubscriptionStatus($subscription_id, 2);

			$data['success'] = $this->language->get('success_disable_subscription');
		}

		$data['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}
