<?php
/**
 * Class Worldpay
 *
 * @package Catalog\Controller\Extension\Payment
 */
class ControllerExtensionPaymentWorldpay extends Controller {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$this->load->language('extension/payment/worldpay');

		$data['worldpay_script'] = 'https://cdn.worldpay.com/v1/worldpay.js';
		$data['worldpay_client_key'] = $this->config->get('payment_worldpay_client_key');
		$data['form_submit'] = $this->url->link('extension/payment/worldpay/send', '', true);

		if ($this->config->get('payment_worldpay_card') == '1' && $this->customer->isLogged()) {
			$data['payment_worldpay_card'] = true;
		} else {
			$data['payment_worldpay_card'] = false;
		}

		$data['existing_cards'] = [];

		if ($this->customer->isLogged() && $data['payment_worldpay_card']) {
			// Worldpay
			$this->load->model('extension/payment/worldpay');

			$data['existing_cards'] = $this->model_extension_payment_worldpay->getCards($this->customer->getId());
		}

		$subscription_products = $this->cart->getSubscriptions();

		if (!empty($subscription_products)) {
			$data['subscription_products'] = true;
		}

		return $this->load->view('extension/payment/worldpay', $data);
	}

	/**
	 * Send
	 *
	 * @return void
	 */
	public function send(): void {
		if (!isset($this->session->data['order_id'])) {
			return;
		}

		$this->load->language('extension/payment/worldpay');

		// Orders
		$this->load->model('checkout/order');

		// Countries
		$this->load->model('localisation/country');

		// Worldpay
		$this->load->model('extension/payment/worldpay');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$subscription_products = $this->cart->getSubscriptions();

		if (empty($subscription_products)) {
			$order_type = 'ECOM';
		} else {
			$order_type = 'RECURRING';
		}

		$country_info = $this->model_localisation_country->getCountry($order_info['payment_country_id']);

		$billing_address = [
			'address1'    => $order_info['payment_address_1'],
			'address2'    => $order_info['payment_address_2'],
			'address3'    => '',
			'postalCode'  => $order_info['payment_postcode'],
			'city'        => $order_info['payment_city'],
			'state'       => $order_info['payment_zone'],
			'countryCode' => $country_info['iso_code_2'],
		];

		$price = round($this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false));

		$order = [
			'token'             => $this->request->post['token'],
			'orderType'         => $order_type,
			'amount'            => (float)$amount * 100,
			'currencyCode'      => $order_info['currency_code'],
			'name'              => $order_info['firstname'] . ' ' . $order_info['lastname'],
			'orderDescription'  => $order_info['store_name'] . ' - ' . date('Y-m-d H:i:s'),
			'customerOrderCode' => $order_info['order_id'],
			'billingAddress'    => $billing_address
		];

		$this->model_extension_payment_worldpay->logger($order);

		$response_data = $this->model_extension_payment_worldpay->sendCurl('orders', $order);

		$this->model_extension_payment_worldpay->logger($response_data);

		if (isset($response_data->paymentStatus) && $response_data->paymentStatus == 'SUCCESS') {
			$this->model_checkout_order->addHistory($order_info['order_id'], $this->config->get('config_order_status_id'));

			$worldpay_order_id = $this->model_extension_payment_worldpay->addOrder($order_info, $response_data->orderCode);

			$this->model_extension_payment_worldpay->addTransaction($worldpay_order_id, 'payment', $order_info);

			if (isset($this->request->post['save-card'])) {
				$response = $this->model_extension_payment_worldpay->sendCurl('tokens/' . $this->request->post['token'], []);

				$this->model_extension_payment_worldpay->logger($response);

				$expiry_date = mktime(0, 0, 0, 0, (string)$response['paymentMethod']['expiryMonth'], (string)$response['paymentMethod']['expiryYear']);

				if (isset($response['paymentMethod'])) {
					$card_data = [];

					$card_data['customer_id'] = $this->customer->getId();
					$card_data['Token'] = $response['token'];
					$card_data['Last4Digits'] = (string)$response['paymentMethod']['maskedCardNumber'];
					$card_data['ExpiryDate'] = date('m/y', $expiry_date);
					$card_data['CardType'] = (string)$response['paymentMethod']['cardType'];

					$this->model_extension_payment_worldpay->addCard($this->session->data['order_id'], $card_data);
				}
			}

			$this->load->model('checkout/subscription');

			// Loop through any products that are subscription items
			$order_products = $this->model_checkout_order->getProducts($this->session->data['order_id']);

			if (isset($this->request->server['HTTP_X_REAL_IP'])) {
				$ip = $this->request->server['HTTP_X_REAL_IP'];
			} elseif (oc_get_ip()) {
				$ip = oc_get_ip();
			} else {
				$ip = '';
			}

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$forwarded_ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$forwarded_ip = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$forwarded_ip = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$user_agent = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$user_agent = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$accept_language = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$accept_language = '';
			}

			foreach ($subscription_products as $item) {
				foreach ($order_products as $order_product) {
					$subscription_info = $this->model_checkout_subscription->getSubscriptionByOrderProductId($this->session->data['order_id'], $order_product['order_product_id']);

					if ($subscription_info && $order_product['product_id'] == $item['product_id'] && $item['product_id'] == $subscription_info['product_id']) {
						$item['subscription']['subscription_id'] = $subscription_info['subscription_id'];
						$item['subscription']['order_product_id'] = $order_product['order_product_id'];
						$item['subscription']['name'] = $item['name'];
						$item['subscription']['product_id'] = $item['product_id'];
						$item['subscription']['tax'] = $this->tax->getTax($item['price'], $item['tax_class_id']);
						$item['subscription']['quantity'] = $item['quantity'];
						$item['subscription']['store_id'] = (int)$this->config->get('config_store_id');
						$item['subscription']['customer_id'] = $this->customer->getId();
						$item['subscription']['payment_address_id'] = $subscription_info['payment_address_id'];
						$item['subscription']['payment_method'] = $subscription_info['payment_method'];
						$item['subscription']['shipping_address_id'] = $subscription_info['shipping_address_id'];
						$item['subscription']['shipping_method'] = $subscription_info['shipping_method'];
						$item['subscription']['comment'] = $subscription_info['comment'];
						$item['subscription']['affiliate_id'] = $subscription_info['affiliate_id'];
						$item['subscription']['marketing_id'] = $subscription_info['marketing_id'];
						$item['subscription']['tracking'] = $subscription_info['tracking'];
						$item['subscription']['language_id'] = $this->config->get('config_language_id');
						$item['subscription']['currency_id'] = $subscription_info['currency_id'];
						$item['subscription']['ip'] = $ip;
						$item['subscription']['forwarded_ip'] = $forwarded_ip;
						$item['subscription']['user_agent'] = $user_agent;
						$item['subscription']['accept_language'] = $accept_language;

						$this->model_extension_payment_worldpay->subscriptionPayment($item, $this->session->data['order_id'] . mt_rand(), $this->request->post['token']);
					}
				}
			}

			$this->response->redirect($this->url->link('checkout/success', '', true));
		} else {
			$this->session->data['error'] = $this->language->get('error_process_order');

			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}
	}

	/**
	 * deleteCard
	 *
	 * @return void
	 */
	public function deleteCard(): void {
		$this->load->language('extension/payment/worldpay');

		$json = [];

		// Worldpay
		$this->load->model('extension/payment/worldpay');

		if (isset($this->request->post['token'])) {
			if ($this->model_extension_payment_worldpay->deleteCard($this->request->post['token'])) {
				$json['success'] = $this->language->get('text_card_success');
			} else {
				$json['error'] = $this->language->get('text_card_error');
			}

			if (count($this->model_extension_payment_worldpay->getCards($this->customer->getId()))) {
				$json['existing_cards'] = true;
			}
		} else {
			$json['error'] = $this->language->get('text_error');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Webhook
	 *
	 * @return void
	 */
	public function webhook(): void {
		if (isset($this->request->get['token']) && hash_equals($this->config->get('payment_worldpay_secret_token'), $this->request->get['token'])) {
			// Worldpay
			$this->load->model('extension/payment/worldpay');

			$message = json_decode(file_get_contents('php://input'), true);

			if (isset($message['orderCode'])) {
				$order = $this->model_extension_payment_worldpay->getWorldpayOrder($message['orderCode']);

				$this->model_extension_payment_worldpay->logger($order);

				$order_status_id = 0;

				switch ($message['paymentStatus']) {
					case 'SUCCESS':
						$order_status_id = (int)$this->config->get('payment_worldpay_success_status_id');
						break;
					case 'FAILED':
						$order_status_id = (int)$this->config->get('payment_worldpay_failed_status_id');
						break;
					case 'SETTLED':
						$order_status_id = (int)$this->config->get('payment_worldpay_settled_status_id');
						break;
					case 'REFUNDED':
						$order_status_id = (int)$this->config->get('payment_worldpay_refunded_status_id');
						break;
					case 'PARTIALLY_REFUNDED':
						$order_status_id = (int)$this->config->get('payment_worldpay_partially_refunded_status_id');
						break;
					case 'CHARGED_BACK':
						$order_status_id = (int)$this->config->get('payment_worldpay_charged_back_status_id');
						break;
					case 'INFORMATION_REQUESTED':
						$order_status_id = (int)$this->config->get('payment_worldpay_information_requested_status_id');
						break;
					case 'INFORMATION_SUPPLIED':
						$order_status_id = (int)$this->config->get('payment_worldpay_information_supplied_status_id');
						break;
					case 'CHARGEBACK_REVERSED':
						$order_status_id = (int)$this->config->get('payment_worldpay_chargeback_reversed_status_id');
						break;
				}

				$this->model_extension_payment_worldpay->logger($order_status_id);

				if (isset($order['order_id'])) {
					// Orders
					$this->load->model('checkout/order');

					$this->model_checkout_order->addHistory($order['order_id'], $order_status_id);
				}
			}
		}

		$this->response->addHeader('HTTP/1.1 200 OK');
		$this->response->addHeader('Content-Type: application/json');
	}

	/**
	 * Cron
	 *
	 * @return void
	 */
	public function cron(): void {
		if ($this->request->get['token'] == $this->config->get('payment_worldpay_secret_token')) {
			// Worldpay
			$this->load->model('extension/payment/worldpay');

			$orders = $this->model_extension_payment_worldpay->cronPayment();

			$this->model_extension_payment_worldpay->updateCronJobRunTime();

			$this->model_extension_payment_worldpay->logger($orders);
		}
	}
}
