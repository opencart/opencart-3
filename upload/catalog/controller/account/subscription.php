<?php
/**
 * Class Subscription
 *
 * @package Catalog\Controller\Account
 */
class ControllerAccountSubscription extends Controller {
	/**
	 * Index
	 * 
	 * @return void
	 */
	public function index(): void {
		$this->load->language('account/subscription');

		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('account/subscription', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

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
			'href' => $this->url->link('account/subscription', 'customer_token=' . $this->session->data['customer_token'] . $url, true)
		];

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['subscriptions'] = [];

		// Subscriptions
		$this->load->model('account/subscription');

		$filter_data = [
			'start' => ($page - 1) * 10,
			'limit' => 10
		];

		$subscription_total = $this->model_account_subscription->getTotalSubscriptions();

		$results = $this->model_account_subscription->getSubscriptions($filter_data);

		foreach ($results as $result) {
			if ($result['status']) {
				$status = $this->language->get('text_status_' . $result['status']);
			} else {
				$status = '';
			}

			if ($result['duration']) {
				if ($result['frequency'] == 'semi_month') {
					$period = strtotime("2 weeks");
				} else {
					$period = strtotime($result['cycle'] . ' ' . $result['frequency']);
				}
			}

			$date_next = strtotime($result['date_next']);

			$data['subscriptions'][] = [
				'subscription_id' => $result['subscription_id'],
				'product'         => $result['product'],
				'status'          => $status,
				'date_added'      => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'is_subscription' => isset($period) && $date_next <= $period ? true : false,
				'view'            => $this->url->link('account/subscription/info', 'customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $result['subscription_id'], true)
			];
		}

		$pagination = new \Pagination();
		$pagination->total = $subscription_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/subscription', 'customer_token=' . $this->session->data['customer_token'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['continue'] = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/subscription_list', $data));
	}

	/**
	 * Info
	 *
	 * @return void
	 */
	public function info(): void {
		$this->load->language('account/subscription');

		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('account/subscription', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}

		// Subscriptions
		$this->load->model('account/subscription');

		$subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

		if ($subscription_info) {
			$this->document->setTitle($this->language->get('text_subscription'));

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

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
				'href' => $this->url->link('account/subscription', 'customer_token=' . $this->session->data['customer_token'] . $url, true)
			];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_subscription'),
				'href' => $this->url->link('account/subscription/info', 'customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $this->request->get['subscription_id'] . $url, true)
			];

			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($subscription_info['date_added']));

			$data['subscription_id'] = (int)$this->request->get['subscription_id'];

			if ($subscription_info['status']) {
				$data['status'] = $this->language->get('text_status_' . $subscription_info['status']);
			} else {
				$data['status'] = '';
			}

			if ($subscription_info['duration']) {
				if ($subscription_info['frequency'] == 'semi_month') {
					$period = strtotime('2 weeks');
				} else {
					$period = strtotime($subscription_info['cycle'] . ' ' . $subscription_info['frequency']);
				}
			}

			$date_next = strtotime($subscription_info['date_next']);

			$data['is_subscription'] = isset($period) && $date_next <= $period ? true : false;

			// Orders
			$this->load->model('account/order');

			$order_product = $this->model_account_order->getProduct($subscription_info['order_id'], $subscription_info['order_product_id']);

			$data['order_id'] = $subscription_info['order_id'];
			$data['reference'] = $subscription_info['reference'];
			$data['product_name'] = $order_product['name'];
			$data['payment_method'] = $subscription_info['payment_method'];
			$data['product_quantity'] = $order_product['quantity'];
			$data['description'] = $subscription_info['description'];

			$data['order'] = $this->url->link('account/order/info', 'customer_token=' . $this->session->data['customer_token'] . '&order_id=' . $subscription_info['order_id'], true);
			$data['product'] = $this->url->link('product/product', 'customer_token=' . $this->session->data['customer_token'] . '&product_id=' . $subscription_info['product_id']);

			// Extensions
			$this->load->model('setting/extension');

			$extension_info = $this->model_setting_extension->getExtensionByCode('payment', $subscription_info['payment_code']);

			if ($extension_info) {
				$data['subscription'] = $this->load->controller('extension/subscription/' . $subscription_info['payment_code']);
			} else {
				$data['subscription'] = '';
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/subscription_info', $data));
		} else {
			$this->document->setTitle($this->language->get('text_subscription'));

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
				'href' => $this->url->link('account/subscription', '&customer_token=' . $this->session->data['customer_token'], true)
			];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_recurring'),
				'href' => $this->url->link('account/subscription/info', 'customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $subscription_id, true)
			];

			$data['continue'] = $this->url->link('account/subscription', 'customer_token=' . $this->session->data['customer_token'], true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	/**
	 * Charge
	 *
	 * @param string $route
	 * @param array  $args
	 *
	 * @return void
	 *
	 * catalog/model/checkout/order/addHistory/before
	 */
	public function charge(&$route, &$args): void {
		$this->load->language('account/subscription');

		if (isset($args[0])) {
			$order_id = $args[0];
		} else {
			$order_id = 0;
		}

		if (isset($args[1])) {
			$order_status_id = $args[1];
		} else {
			$order_status_id = 0;
		}

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}

		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info && $this->cart->hasSubscription()) {
			$callable = [$this->{'model_extension_payment_' . $order_info['payment_code']}, 'charge'];

			if (is_callable($callable)) {
				// Process payment
				$response_info = $this->{'model_extension_' . $order_info['payment_code']}->charge($this->customer->getId(), $this->session->data['order_id'], $order_info['total'], $order_info['payment_code']);

				if (isset($response_info['order_status_id'])) {
					$order_status_id = $response_info['order_status_id'];
				} else {
					$order_status_id = 0;
				}

				if ($response_info['message']) {
					$message = $response_info['message'];
				} else {
					$message = '';
				}

				$this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id, $message, false);
				$this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id);

				// If payment order status is active or processing
				if (!in_array($order_status_id, (array)$this->config->get('config_processing_status') + (array)$this->config->get('config_complete_status'))) {
					$this->load->model('account/subscription');

					$subscriptions = $this->model_account_subscription->getSubscriptions();

					if ($subscriptions) {
						$result = $subscriptions[0];

						$remaining = 0;
						$date_next = '';

						if ($result['trial_status'] && $result['trial_remaining'] > 1) {
							$remaining = $result['trial_remaining'] - 1;
							$date_next = date('Y-m-d', strtotime('+' . $result['trial_cycle'] . ' ' . $result['trial_frequency']));

							$this->model_account_subscription->editTrialRemaining($result['subscription_id'], $remaining);
						} elseif ($result['duration'] && $result['remaining']) {
							$remaining = $result['remaining'] - 1;
							$date_next = date('Y-m-d', strtotime('+' . $result['cycle'] . ' ' . $result['frequency']));

							// If duration make sure there is remaining
							$this->model_account_subscription->editRemaining($result['subscription_id'], $remaining);
						} elseif (!$result['duration']) {
							// If duration is unlimited
							$date_next = date('Y-m-d', strtotime('+' . $result['cycle'] . ' ' . $result['frequency']));
						}

						if ($date_next) {
							$this->load->model('checkout/subscription');

							$this->model_checkout_subscription->editDateNext($result['subscription_id'], $date_next);
						}

						$this->model_checkout_subscription->addHistory($result['subscription_id'], $this->config->get('config_subscription_active_status_id'), $this->language->get('text_success'), true);
					}
				}
			}
		}
	}
}
