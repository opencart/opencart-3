<?php
/**
 * Class Subscription
 *
 * @package Admin\Controller\Sale
 */
class ControllerSaleSubscription extends Controller {
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
		$this->load->language('sale/subscription');

		$this->document->setTitle($this->language->get('heading_title'));

		// Subscriptions
		$this->load->model('sale/subscription');

		$this->getList();
	}

	/**
	 * Get List
	 *
	 * @return void
	 */
	protected function getList(): void {
		if (isset($this->request->get['filter_subscription_id'])) {
			$filter_subscription_id = (int)$this->request->get['filter_subscription_id'];
		} else {
			$filter_subscription_id = '';
		}

		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = '';
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}

		if (isset($this->request->get['filter_subscription_status_id'])) {
			$filter_subscription_status_id = (int)$this->request->get['filter_subscription_status_id'];
		} else {
			$filter_subscription_status_id = '';
		}

		if (isset($this->request->get['filter_date_from'])) {
			$filter_date_from = $this->request->get['filter_date_from'];
		} else {
			$filter_date_from = '';
		}

		if (isset($this->request->get['filter_date_to'])) {
			$filter_date_to = $this->request->get['filter_date_to'];
		} else {
			$filter_date_to = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = (string)$this->request->get['sort'];
		} else {
			$sort = 's.subscription_id';
		}

		if (isset($this->request->get['order'])) {
			$order = (string)$this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_subscription_id'])) {
			$url .= '&filter_subscription_id=' . $this->request->get['filter_subscription_id'];
		}

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_subscription_status_id'])) {
			$url .= '&filter_subscription_status_id=' . $this->request->get['filter_subscription_status_id'];
		}

		if (isset($this->request->get['filter_date_from'])) {
			$url .= '&filter_date_from=' . $this->request->get['filter_date_from'];
		}

		if (isset($this->request->get['filter_date_to'])) {
			$url .= '&filter_date_to=' . $this->request->get['filter_date_to'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token'] . $url, true)
		];

		$data['subscriptions'] = [];

		$filter_data = [
			'filter_subscription_id'        => $filter_subscription_id,
			'filter_order_id'               => $filter_order_id,
			'filter_customer'               => $filter_customer,
			'filter_subscription_status_id' => $filter_subscription_status_id,
			'filter_date_from'              => $filter_date_from,
			'filter_date_to'                => $filter_date_to,
			'order'                         => $order,
			'sort'                          => $sort,
			'start'                         => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit'                         => $this->config->get('config_pagination_admin')
		];

		$this->load->model('sale/subscription');

		$results = $this->model_sale_subscription->getSubscriptions($filter_data);

		foreach ($results as $result) {
			$data['subscriptions'][] = [
				'subscription_id' => $result['subscription_id'],
				'order_id'        => $result['order_id'],
				'customer'        => $result['customer'],
				'status'          => $result['subscription_status'],
				'date_added'      => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'view'            => $this->url->link('sale/subscription/info', 'user_token=' . $this->session->data['user_token'] . '&subscription_id=' . $result['subscription_id'] . $url),
				'order'           => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'])
			];
		}

		$url = '';

		if (isset($this->request->get['filter_subscription_id'])) {
			$url .= '&filter_subscription_id=' . $this->request->get['filter_subscription_id'];
		}

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_subscription_status_id'])) {
			$url .= '&filter_subscription_status_id=' . $this->request->get['filter_subscription_status_id'];
		}

		if (isset($this->request->get['filter_date_from'])) {
			$url .= '&filter_date_from=' . $this->request->get['filter_date_from'];
		}

		if (isset($this->request->get['filter_date_to'])) {
			$url .= '&filter_date_to=' . $this->request->get['filter_date_to'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		$data['sort_subscription'] = $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token'] . '&sort=s.subscription_id' . $url);
		$data['sort_order'] = $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token'] . '&sort=s.order_id' . $url);
		$data['sort_customer'] = $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url);
		$data['sort_status'] = $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token'] . '&sort=subscription_status' . $url);
		$data['sort_date_added'] = $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token'] . '&sort=s.date_added' . $url);

		$url = '';

		if (isset($this->request->get['filter_subscription_id'])) {
			$url .= '&filter_subscription_id=' . $this->request->get['filter_subscription_id'];
		}

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_from'])) {
			$url .= '&filter_date_from=' . $this->request->get['filter_date_from'];
		}

		if (isset($this->request->get['filter_date_to'])) {
			$url .= '&filter_date_to=' . $this->request->get['filter_date_to'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$subscription_total = $this->model_sale_subscription->getTotalSubscriptions($filter_data);

		$pagination = new \Pagination();
		$pagination->total = $subscription_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($subscription_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($subscription_total - $this->config->get('config_limit_admin'))) ? $subscription_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $subscription_total, ceil($subscription_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/subscription', $data));
	}

	/**
	 * Info
	 *
	 * @return void
	 */
	public function info(): void {
		$this->load->language('sale/subscription');

		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['text_form'] = !$subscription_id ? $this->language->get('text_add') : sprintf($this->language->get('text_edit'), $subscription_id);

		$url = '';

		if (isset($this->request->get['filter_subscription_id'])) {
			$url .= '&filter_subscription_id=' . $this->request->get['filter_subscription_id'];
		}

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_subscription_status_id'])) {
			$url .= '&filter_subscription_status_id=' . $this->request->get['filter_subscription_status_id'];
		}

		if (isset($this->request->get['filter_date_from'])) {
			$url .= '&filter_date_from=' . $this->request->get['filter_date_from'];
		}

		if (isset($this->request->get['filter_date_to'])) {
			$url .= '&filter_date_to=' . $this->request->get['filter_date_to'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['back'] = $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token'] . $url);

		$this->load->model('sale/subscription');

		$subscription_info = $this->model_sale_subscription->getSubscription($subscription_id);

		if (!empty($subscription_info)) {
			$data['subscription_id'] = $subscription_info['subscription_id'];
		} else {
			$data['subscription_id'] = '';
		}

		// Order
		if (!empty($subscription_info)) {
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($subscription_info['order_id']);
		}

		if (!empty($order_info)) {
			$data['order'] = $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $subscription_info['order_id']);
		} else {
			$data['order'] = '';
		}

		if (!empty($subscription_info)) {
			$data['order_id'] = $subscription_info['order_id'];
		} else {
			$data['order_id'] = 0;
		}

		// Customer
		if (!empty($subscription_info)) {
			$this->load->model('customer/customer');

			$customer_info = $this->model_customer_customer->getCustomer($subscription_info['customer_id']);
		}

		if (!empty($customer_info)) {
			$data['firstname'] = $customer_info['firstname'];
		} else {
			$data['firstname'] = '';
		}

		if (!empty($customer_info)) {
			$data['lastname'] = $customer_info['lastname'];
		} else {
			$data['lastname'] = '';
		}

		// Subscription
		$data['subscription_plans'] = [];

		$this->load->model('catalog/subscription_plan');

		$results = $this->model_catalog_subscription_plan->getSubscriptionPlans();

		foreach ($results as $result) {
			$description = '';

			if ($result['trial_status']) {
				$trial_price = $this->currency->format($subscription_info['trial_price'], $this->config->get('config_currency'));
				$trial_cycle = $result['trial_cycle'];
				$trial_frequency = $this->language->get('text_' . $result['trial_frequency']);
				$trial_duration = $result['trial_duration'];

				$description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
			}

			$price = $this->currency->format($subscription_info['price'], $this->config->get('config_currency'));
			$cycle = $result['cycle'];
			$frequency = $this->language->get('text_' . $result['frequency']);
			$duration = $result['duration'];

			if ($result['duration']) {
				$description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
			} else {
				$description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
			}

			$data['subscription_plans'][] = [
				'subscription_plan_id' => $result['subscription_plan_id'],
				'name'                 => $result['name'],
				'description'          => $description
			];
		}

		if (!empty($subscription_info)) {
			$data['subscription_plan_id'] = $subscription_info['subscription_plan_id'];
		} else {
			$data['subscription_plan_id'] = 0;
		}

		$subscription_plan_info = $this->model_catalog_subscription_plan->getSubscriptionPlan($data['subscription_plan_id']);

		if (!empty($subscription_plan_info)) {
			$data['subscription_plan'] = '';

			if ($subscription_plan_info['trial_status']) {
				$trial_price = $this->currency->format($subscription_info['trial_price'], $this->config->get('config_currency'));
				$trial_cycle = $subscription_info['trial_cycle'];
				$trial_frequency = $this->language->get('text_' . $subscription_plan_info['trial_frequency']);
				$trial_duration = $subscription_plan_info['trial_duration'];

				$data['subscription_plan'] .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
			}

			$price = $this->currency->format($subscription_info['price'], $this->config->get('config_currency'));
			$cycle = $subscription_info['cycle'];
			$frequency = $this->language->get('text_' . $subscription_plan_info['frequency']);
			$duration = $subscription_plan_info['duration'];

			if ($subscription_plan_info['duration']) {
				$data['subscription_plan'] .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
			} else {
				$data['subscription_plan'] .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
			}
		} else {
			$data['subscription_plan'] = '';
		}

		if (!empty($subscription_info)) {
			$data['trial_price'] = $subscription_info['trial_price'];
		} else {
			$data['trial_price'] = 0;
		}

		$data['frequencies'] = [];

		$data['frequencies'][] = [
			'text'  => $this->language->get('text_day'),
			'value' => 'day'
		];

		$data['frequencies'][] = [
			'text'  => $this->language->get('text_week'),
			'value' => 'week'
		];

		$data['frequencies'][] = [
			'text'  => $this->language->get('text_semi_month'),
			'value' => 'semi_month'
		];

		$data['frequencies'][] = [
			'text'  => $this->language->get('text_month'),
			'value' => 'month'
		];

		$data['frequencies'][] = [
			'text'  => $this->language->get('text_year'),
			'value' => 'year'
		];

		if (!empty($subscription_info)) {
			$data['trial_frequency'] = $subscription_info['trial_frequency'];
		} else {
			$data['trial_frequency'] = '';
		}

		if (!empty($subscription_info)) {
			$data['trial_cycle'] = $subscription_info['trial_cycle'];
		} else {
			$data['trial_cycle'] = 0;
		}

		if (!empty($subscription_info)) {
			$data['trial_duration'] = $subscription_info['trial_duration'];
		} else {
			$data['trial_duration'] = 0;
		}

		if (!empty($subscription_info)) {
			$data['trial_remaining'] = $subscription_info['trial_remaining'];
		} else {
			$data['trial_remaining'] = 0;
		}

		if (!empty($subscription_info)) {
			$data['trial_status'] = $subscription_info['trial_status'];
		} else {
			$data['trial_status'] = 0;
		}

		if (!empty($subscription_info)) {
			$data['price'] = $subscription_info['price'];
		} else {
			$data['price'] = 0;
		}

		if (!empty($subscription_info)) {
			$data['frequency'] = $subscription_info['frequency'];
		} else {
			$data['frequency'] = '';
		}

		if (!empty($subscription_info)) {
			$data['cycle'] = $subscription_info['cycle'];
		} else {
			$data['cycle'] = 0;
		}

		if (!empty($subscription_info)) {
			$data['duration'] = $subscription_info['duration'];
		} else {
			$data['duration'] = 0;
		}

		if (!empty($subscription_info)) {
			$data['remaining'] = $subscription_info['remaining'];
		} else {
			$data['remaining'] = 0;
		}

		// Date next
		if (!empty($subscription_info)) {
			$data['date_next'] = date($this->language->get('date_format_short'), strtotime($subscription_info['date_next']));
		} else {
			$data['date_next'] = '';
		}

		// Payment method
		if (!empty($subscription_info)) {
			$data['payment_method'] = $subscription_info['payment_method']['name'];
		} else {
			$data['payment_method'] = '';
		}

		if (!empty($order_info)) {
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
		} else {
			$data['date_added'] = '';
		}

		// Product data
		if (!empty($subscription_info)) {
			$this->load->model('account/order');
			$product_info = $this->model_sale_order->getProduct($subscription_info['order_id'], $subscription_info['order_product_id']);
		}

		if (!empty($product_info['name'])) {
			$data['product_name'] = $product_info['name'];
		} else {
			$data['product_name'] = '';
		}

		if (!empty($product_info)) {
			$data['product'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product_info['product_id']);
		} else {
			$data['product'] = '';
		}

		$data['options'] = [];

		$options = $this->model_sale_order->getOptions($subscription_info['order_id'], $subscription_info['order_product_id']);

		foreach ($options as $option) {
			if ($option['type'] != 'file') {
				$data['options'][] = [
					'name'  => $option['name'],
					'value' => $option['value'],
					'type'  => $option['type']
				];
			} else {
				$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

				if ($upload_info) {
					$data['options'][] = [
						'name'  => $option['name'],
						'value' => $upload_info['name'],
						'type'  => $option['type'],
						'href'  => $this->url->link('tool/upload/download', 'user_token=' . $this->session->data['user_token'] . '&code=' . $upload_info['code'])
					];
				}
			}
		}

		if (!empty($product_info)) {
			$data['quantity'] = $product_info['quantity'];
		} else {
			$data['quantity'] = '';
		}

		$this->load->model('tool/image');

		$data['image_subscription_details'] = $this->model_tool_image->resize('subscription/subscription_details.png', 45, 45);
		$data['image_payment_address'] = $this->model_tool_image->resize('subscription/payment_address.png', 45, 45);
		$data['image_shipping_address'] = $this->model_tool_image->resize('subscription/shipping_address.png', 45, 45);
		$data['image_shipping_method'] = $this->model_tool_image->resize('subscription/shipping_method.png', 45, 45);
		$data['image_payment_method'] = $this->model_tool_image->resize('subscription/payment_method.png', 45, 45);
		$data['image_product'] = $this->model_tool_image->resize('subscription/product.png', 45, 45);

		$this->load->model('localisation/subscription_status');

		$data['subscription_statuses'] = $this->model_localisation_subscription_status->getSubscriptionStatuses();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/subscription_info', $data));
	}

	/**
	 * Order
	 *
	 * @return void
	 */
	public function order(): void {
		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['orders'] = [];

		$this->load->model('sale/order');
		$this->load->model('sale/subscription');

		$filter_data = [
			'filter_order_id' => $order_id
		];

		$results = $this->model_sale_subscription->getSubscriptions($filter_data);

		foreach ($results as $result) {
			$order_subscription = $this->model_sale_order->getSubscription($order_id, $result['order_product_id']);

			if ($order_subscription) {
				$data['orders'][] = [
					'order_id'   => $result['order_id'],
					'status'     => $result['status'],
					'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'view'       => $this->url->link('sale/subscription/order', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . '&page={page}')
				];
			}
		}

		$subscription_total = $this->model_sale_subscription->getTotalSubscriptions($filter_data);

		$pagination = new \Pagination();
		$pagination->total = $subscription_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/subscription/order', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($subscription_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($subscription_total - $this->config->get('config_limit_admin'))) ? $subscription_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $subscription_total, ceil($subscription_total / $this->config->get('config_limit_admin')));

		$this->response->setOutput($this->load->view('sale/subscription_order', $data));
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('sale/subscription');

		$json = [];

		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'sale/subscription')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif ($this->request->post['subscription_plan_id'] == '') {
			$json['error'] = $this->language->get('error_subscription_plan');
		}

		$this->load->model('catalog/subscription_plan');

		$subscription_plan_info = $this->model_catalog_subscription_plan->getSubscriptionPlan($this->request->post['subscription_plan_id']);

		if (!$subscription_plan_info) {
			$json['error'] = $this->language->get('error_subscription_plan');
		}

		$this->load->model('sale/subscription');

		$subscription_info = $this->model_sale_subscription->getSubscription($subscription_id);

		if ($subscription_info) {
			$this->load->model('sale/subscription');

			$filter_data = [
				'filter_customer_id'         => $subscription_info['customer_id'],
				'filter_customer_payment_id' => $this->request->post['customer_payment_id']
			];

			$payment_method_info = $this->model_sale_subscription->getSubscriptions($filter_data);

			if (!$payment_method_info) {
				$json['error'] = $this->language->get('error_payment_method');
			}
		} else {
			$json['error'] = $this->language->get('error_subscription');
		}

		if (!$json) {
			$this->model_sale_subscription->editSubscriptionPlan($subscription_id, $this->request->post['subscription_plan_id']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * History
	 *
	 * @return void
	 */
	public function history(): void {
		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;

		$data['histories'] = [];

		$this->load->model('sale/subscription');

		$results = $this->model_sale_subscription->getHistories($subscription_id, ($page - 1) * $limit, $limit);

		foreach ($results as $result) {
			$data['histories'][] = [
				'status'     => $result['status'],
				'comment'    => nl2br($result['comment']),
				'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			];
		}

		$subscription_total = $this->model_sale_subscription->getTotalHistories($subscription_id);

		$pagination = new \Pagination();
		$pagination->total = $subscription_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/subscription/history', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($subscription_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($subscription_total - $this->config->get('config_limit_admin'))) ? $subscription_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $subscription_total, ceil($subscription_total / $this->config->get('config_limit_admin')));

		$this->response->setOutput($this->load->view('sale/subscription_history', $data));
	}

	/**
	 * Add History
	 *
	 * @return void
	 */
	public function addHistory(): void {
		$this->load->language('sale/subscription');

		$json = [];

		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'sale/subscription')) {
			$json['error'] = $this->language->get('error_permission');
		}

		// Subscription
		$this->load->model('sale/subscription');

		$subscription_info = $this->model_sale_subscription->getSubscription($subscription_id);

		if (!$subscription_info) {
			$json['error'] = $this->language->get('error_subscription');
		}

		// Subscription Plan
		$this->load->model('localisation/subscription_status');

		$subscription_status_info = $this->model_localisation_subscription_status->getSubscriptionStatus($this->request->post['subscription_status_id']);

		if (!$subscription_status_info) {
			$json['error'] = $this->language->get('error_subscription_status');
		}

		if (!$json) {
			$this->model_sale_subscription->addHistory($subscription_id, $this->request->post['subscription_status_id'], $this->request->post['comment'], $this->request->post['notify']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
