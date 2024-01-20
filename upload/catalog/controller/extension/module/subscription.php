<?php
/**
 * Class Subscription
 *
 * @package Catalog\Controller\Extension\Module
 */
class ControllerExtensionModuleSubscription extends Controller {
	/**
	 * dateNext
	 *
	 * @return void
	 */
	public function dateNext(): void {
		$this->load->language('extension/module/subscription');

		$json = [];

		if (!isset($this->session->data['api_id'])) {
			$json['error']['warning'] = $this->language->get('error_permission');
		} else {
			$filter_data = [
				'filter_subscription_id'        => $this->request->get['subscription_id'],
				'filter_subscription_status_id' => $this->config->get('config_subscription_active_status_id'),
				'filter_date_next'              => date('Y-m-d H:i:s')
			];

			// Subscriptions
			$this->load->model('account/subscription');

			$results = $this->model_account_subscription->getSubscriptions($filter_data);

			if ($results) {
				foreach ($results as $result) {
					$amount = 0;

					if ($result['trial_status'] && $result['trial_duration'] > 0) {
						$amount = $result['trial_price'];
					} elseif ($result['duration'] > 0) {
						$amount = $result['price'];
					}

					$subscription_status_id = $this->config->get('config_subscription_status_id');

					// Get the payment method used by the subscription
					$payment_info = $this->model_customer_customer->getPaymentMethod($result['customer_id'], $result['customer_payment_id']);

					if ($payment_info) {
						// Check payment status
						if ($this->config->get('payment_' . $payment_info['code'] . '_status')) {
							$this->load->model('extension/payment/' . $payment_info['code']);

							$callable = [$this->{'model_extension_payment_' . $payment_info['code']}, 'charge'];

							if (is_callable($callable) && $amount) {
								$subscription_status_id = $this->{'model_extension_payment_' . $payment_info['code']}->charge($result['customer_id'], $result['customer_payment_id'], $amount);

								// Transaction
								if ($this->config->get('config_subscription_active_status_id') == $subscription_status_id) {
									$date_next = '';

									if ($result['trial_status'] && $result['trial_duration'] > 0) {
										$date_next = date('Y-m-d', strtotime('+' . $result['trial_cycle'] . ' ' . $result['trial_frequency']));
									} elseif ($result['duration'] > 0) {
										$date_next = date('Y-m-d', strtotime('+' . $result['cycle'] . ' ' . $result['frequency']));
									}

									$filter_data = [
										'filter_date_next'              => $date_next,
										'filter_subscription_status_id' => $subscription_status_id,
										'start'                         => 0,
										'limit'                         => 1
									];

									$subscriptions = $this->model_account_subscription->getSubscriptions($filter_data);

									if ($subscriptions) {
										
									}
								}
							} else {
								// Failed if payment method does not have recurring payment method
								$subscription_status_id = $this->config->get('config_subscription_failed_status_id');

								$this->model_checkout_subscription->addHistory($result['subscription_id'], $subscription_status_id, $this->language->get('error_recurring'), true);
							}
						} else {
							$subscription_status_id = $this->config->get('config_subscription_failed_status_id');

							$this->model_checkout_subscription->addHistory($result['subscription_id'], $subscription_status_id, $this->language->get('error_extension'), true);
						}
					} else {
						$subscription_status_id = $this->config->get('config_subscription_failed_status_id');

						$this->model_checkout_subscription->addHistory($result['subscription_id'], $subscription_status_id, sprintf($this->language->get('error_payment'), ''), true);
					}

					// History
					if ($result['subscription_status_id'] != $subscription_status_id) {
						$this->model_checkout_subscription->addHistory($result['subscription_id'], $subscription_status_id, 'payment extension ' . $result['payment_code'] . ' could not be loaded', true);
					}
				}
			}
		}

		$json['status'] = $this->language->get('text_status');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Account
	 *
	 * @param string $route
	 * @param array  $args
	 * @param mixed  $output
	 *
	 * @return void
	 *
	 * catalog/view/account/recurring_list/after
	 */
	public function account(string &$route, array &$args, mixed &$output): void {
		if ($this->config->get('config_information_subscription_id')) {
			$this->load->language('extension/module/subscription');

			// Information
			$this->load->model('catalog/information');

			// Subscriptions
			$this->load->model('account/subscription');

			$args['total_subscriptions'] = $this->model_account_subscription->getTotalSubscriptions();

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_information_subscription_id'));

			if ($information_info) {
				$args['text_subscription_marketing'] = sprintf($this->language->get('text_subscription_marketing'), $this->url->link('information/information', 'information_id=' . $this->config->get('config_information_subscription_id'), true), $information_info['title']);

				$search = '<h1>';
				$replace = $this->load->view('extension/module/account_subscription', $args);

				$output = str_replace($search, $replace . $search, $output);
			} else {
				$args['text_subscription_marketing'] = '';
			}
		}
	}
}
