<?php

class ModelExtensionPaymentPayPal extends Model {
	public function getMethods(array $address, float $total): array {
		$method_data = [];

		$agree_status = $this->getAgreeStatus();

		if ($this->config->get('payment_paypal_status') && $this->config->get('payment_paypal_client_id') && $this->config->get('payment_paypal_secret') && $agree_status) {
			$this->load->language('extension/payment/paypal');

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_paypal_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

			if (($this->config->get('payment_paypal_total') > 0) && ($this->config->get('payment_paypal_total') > $total)) {
				$status = false;
			} elseif (!$this->config->get('payment_paypal_geo_zone_id')) {
				$status = true;
			} elseif ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}

			if ($status) {
				$method_data = [
					'code'       => 'paypal',
					'title'      => $this->language->get('text_paypal_title'),
					'terms'      => '',
					'sort_order' => $this->config->get('payment_paypal_sort_order')
				];
			}
		}

		return $method_data;
	}

	/**
	 * Has Product In Cart
	 *
	 * @param int                  $product_id
	 * @param array<string, mixed> $option
	 * @param int                  $subscription_plan_id
	 *
	 * @return int
	 */
	public function hasProductInCart(int $product_id, array $option = [], int $subscription_plan_id = 0): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "cart` WHERE `api_id` = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND `customer_id` = '" . (int)$this->customer->getId() . "' AND `session_id` = '" . $this->db->escape($this->session->getId()) . "' AND `product_id` = '" . (int)$product_id . "' AND `subscription_plan_id` = '" . (int)$subscription_plan_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Country By Code
	 *
	 * @param string $code
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getCountryByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE `iso_code_2` = '" . $this->db->escape($code) . "' AND `status` = '1'");

		return $query->row;
	}

	/**
	 * Get Zone By Code
	 *
	 * @param int    $country_id
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 */
	public function getZoneByCode(int $country_id, string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '" . (int)$country_id . "' AND (`code` = '" . $this->db->escape($code) . "' OR `name` = '" . $this->db->escape($code) . "') AND `status` = '1'");

		return $query->row;
	}

	/**
	 * Add PayPal Order
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function addPayPalOrder(array $data): void {
		$sql = "INSERT INTO `" . DB_PREFIX . "paypal_checkout_integration_order` SET";

		$implode = [];

		if (!empty($data['order_id'])) {
			$implode[] = "`order_id` = '" . (int)$data['order_id'] . "'";
		}

		if (!empty($data['transaction_id'])) {
			$implode[] = "`transaction_id` = '" . $this->db->escape($data['transaction_id']) . "'";
		}

		if (!empty($data['transaction_status'])) {
			$implode[] = "`transaction_status` = '" . $this->db->escape($data['transaction_status']) . "'";
		}

		if (!empty($data['payment_method'])) {
			$implode[] = "`payment_method` = '" . $this->db->escape($data['payment_method']) . "'";
		}

		if (!empty($data['vault_id'])) {
			$implode[] = "`vault_id` = '" . $this->db->escape($data['vault_id']) . "'";
		}

		if (!empty($data['vault_customer_id'])) {
			$implode[] = "`vault_customer_id` = '" . $this->db->escape($data['vault_customer_id']) . "'";
		}

		if (!empty($data['environment'])) {
			$implode[] = "`environment` = '" . $this->db->escape($data['environment']) . "'";
		}

		if ($implode) {
			$sql .= implode(", ", $implode);
		}

		$this->db->query($sql);
	}

	/**
	 * Edit PayPal Order
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function editPayPalOrder(array $data): void {
		$sql = "UPDATE `" . DB_PREFIX . "paypal_checkout_integration_order` SET";

		$implode = [];

		if (!empty($data['transaction_id'])) {
			$implode[] = "`transaction_id` = '" . $this->db->escape($data['transaction_id']) . "'";
		}

		if (!empty($data['transaction_status'])) {
			$implode[] = "`transaction_status` = '" . $this->db->escape($data['transaction_status']) . "'";
		}

		if (!empty($data['payment_method'])) {
			$implode[] = "`payment_method` = '" . $this->db->escape($data['payment_method']) . "'";
		}

		if (!empty($data['vault_id'])) {
			$implode[] = "`vault_id` = '" . $this->db->escape($data['vault_id']) . "'";
		}

		if (!empty($data['vault_customer_id'])) {
			$implode[] = "`vault_customer_id` = '" . $this->db->escape($data['vault_customer_id']) . "'";
		}

		if (!empty($data['environment'])) {
			$implode[] = "`environment` = '" . $this->db->escape($data['environment']) . "'";
		}

		if ($implode) {
			$sql .= implode(", ", $implode);
		}

		$sql .= " WHERE `order_id` = '" . (int)$data['order_id'] . "'";

		$this->db->query($sql);
	}

	/**
	 * Delete PayPal Order
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deletePayPalOrder(int $order_id): void {
		$query = $this->db->query("DELETE FROM `" . DB_PREFIX . "paypal_checkout_integration_order` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Get PayPal Order
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getPayPalOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_checkout_integration_order` WHERE `order_id` = '" . (int)$order_id . "'");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * Add PayPal Order Subscription
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function addPayPalOrderSubscription(array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "paypal_checkout_integration_subscription` SET `subscription_id` = '" . (int)$data['subscription_id'] . "', `order_id` = '" . (int)$data['order_id'] . "', `next_payment` = NOW(), `trial_end` = '" . $data['trial_end'] . "', `subscription_end` = '" . $data['subscription_end'] . "', `currency_code` = '" . $this->db->escape($data['currency_code']) . "', `total` = '" . $this->currency->format($data['amount'], $data['currency_code'], false, false) . "', `date_added` = NOW(), `date_modified` = NOW()");
	}

	/**
	 * Edit PayPal Order Subscription Next Payment
	 *
	 * @param int    $order_id
	 * @param string $next_payment
	 *
	 * @return void
	 */
	public function editPayPalOrderSubscriptionNextPayment(int $order_id, string $next_payment): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "paypal_checkout_integration_subscription` SET `next_payment` = '" . $this->db->escape($next_payment) . "', `date_modified` = NOW() WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Delete PayPal Order Subscription
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deletePayPalOrderSubscription(int $order_id): void {
		$query = $this->db->query("DELETE FROM `" . DB_PREFIX . "paypal_checkout_integration_order` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Get PayPal Order Subscription
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getPayPalOrderSubscription(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_checkout_integration_subscription` WHERE `order_id` = '" . (int)$order_id . "'");

		return $query->row;
	}

	/**
	 * Add Order Subscription
	 *
	 * @param int                  $order_id
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function addOrderSubscription(int $order_id, array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_subscription` SET `order_id` = '" . (int)$order_id . "', `product_id` = '" . (int)$data['subscription']['product_id'] . "', `order_product_id` = '" . (int)$data['subscription']['order_product_id'] . "', `subscription_plan_id` = '" . (int)$data['subscription']['subscription_plan_id'] . "', `frequency` = '" . $this->db->escape($data['subscription']['frequency']) . "', `cycle` = '" . (int)$data['subscription']['cycle'] . "', `duration` = '" . (int)$data['subscription']['duration'] . "', `price` = '" . (float)$data['subscription']['price'] . "', `tax` = '" . (float)$data['subscription']['tax'] . "', `trial_frequency` = '" . $this->db->escape($data['subscription']['trial_frequency']) . "', `trial_cycle` = '" . (int)$data['subscription']['trial_cycle'] . "', `trial_duration` = '" . (int)$data['subscription']['trial_duration'] . "', `trial_price` = '" . (float)$data['subscription']['trial_price'] . "'");

		return $this->db->getLastId();
	}

	/**
	 * Edit Order Subscription Status
	 *
	 * @param int $order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function editOrderSubscriptionStatus(int $order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "paypal_checkout_integration_subscription` SET `status` = '" . (int)$status . "' WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Delete Order Subscription
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deleteOrderSubscription(int $order_id): void {
		$query = $this->db->query("SELECT `order_id` FROM `" . DB_PREFIX . "paypal_checkout_integration_subscription` WHERE `order_id` = '" . (int)$order_id . "'");

		foreach ($query->rows as $order_subscription) {
			$this->deleteSubscriptionTransaction($order_subscription['order_id']);
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "paypal_checkout_integration_subscription` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Get Order Subscriptions
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getOrderSubscriptions(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_checkout_integration_subscription` `os` INNER JOIN `" . DB_PREFIX . "order` `o` ON (`o`.`order_id` = `os`.`order_id`) WHERE `o`.`payment_code` = 'paypal' AND `o`.`customer_id` = '" . (int)$this->customer->getId() . "'");

		return $query->rows;
	}

	/**
	 * Get Order Subscription
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getOrderSubscription(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_checkout_integration_subscription` WHERE `order_id` = '" . (int)$order_id . "'");

		return $query->row;
	}

	/**
	 * Add Subscription Transaction
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function addSubscriptionTransaction(array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "paypal_checkout_integration_transaction` SET `order_id` = '" . (int)$data['order_id'] . "', `reference` = '" . $this->db->escape($data['reference']) . "', `type` = '" . (int)$data['type'] . "', `amount` = '" . (float)$data['amount'] . "', `date_added` = NOW()");
	}

	/**
	 * Delete Subscription Transaction
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deleteSubscriptionTransaction(int $order_id): void {
		$query = $this->db->query("DELETE FROM `" . DB_PREFIX . "paypal_checkout_integration_transaction` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Subscription Payment
	 *
	 * @param array                $item
	 * @param array<string, mixed> $order_info
	 * @param array                $paypal_order_data
	 *
	 * @return void
	 */
	public function subscriptionPayment(array $item, array $order_info, array $paypal_order_data): void {
		$this->load->model('checkout/subscription');

		$_config = new \Config();
		$_config->load('paypal');

		$config_setting = $_config->get('paypal_setting');

		$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

		$transaction_method = $setting['general']['transaction_method'];

		if ($item['subscription']['trial_status'] == 1) {
			$price = $item['subscription']['trial_price'];
			$trial_amt = $this->currency->format($this->tax->calculate($item['subscription']['trial_price'], $item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], false, false) * $item['quantity'] . ' ' . $this->session->data['currency'];
			$trial_text = sprintf($this->language->get('text_trial'), $trial_amt, $item['subscription']['trial_cycle'], $item['subscription']['trial_frequency'], $item['subscription']['trial_duration']);
		} else {
			$price = $item['subscription']['price'];
			$trial_text = '';
		}

		// Subscriptions
		$description = '';

		if ($item['subscription']) {
			if ($item['subscription']['trial_status']) {
				$trial_price = $this->currency->format($this->tax->calculate($item['subscription']['trial_price'], $item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$trial_cycle = $item['subscription']['trial_cycle'];
				$trial_frequency = $this->language->get('text_' . $item['subscription']['trial_frequency']);
				$trial_duration = $item['subscription']['trial_duration'];

				$description .= sprintf($this->language->get('text_subscription_trial'), $price ? $trial_price : '', $trial_cycle, $trial_frequency, $trial_duration);
			}

			$price = $this->currency->format($this->tax->calculate($item['subscription']['price'], $item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

			$cycle = $item['subscription']['cycle'];
			$frequency = $this->language->get('text_' . $item['subscription']['frequency']);
			$duration = $item['subscription']['duration'];

			if ($duration) {
				$description .= sprintf($this->language->get('text_subscription_duration'), $price ?: '', $cycle, $frequency, $duration);
			} else {
				$description .= sprintf($this->language->get('text_subscription_cancel'), $price ?: '', $cycle, $frequency);
			}
		}

		$next_payment = new \DateTime('now');
		$trial_end = new \DateTime('now');
		$subscription_end = new \DateTime('now');

		if (($item['subscription']['trial_status'] == 1) && ($item['subscription']['trial_duration'] != 0)) {
			$next_payment = $this->calculateSchedule($item['subscription']['trial_frequency'], $next_payment, $item['subscription']['trial_cycle']);
			$trial_end = $this->calculateSchedule($item['subscription']['trial_frequency'], $trial_end, $item['subscription']['trial_cycle'] * $item['subscription']['trial_duration']);
		} elseif ($item['subscription']['trial_status'] == 1) {
			$next_payment = $this->calculateSchedule($item['subscription']['trial_frequency'], $next_payment, $item['subscription']['trial_cycle']);
			$trial_end = new \DateTime('0000-00-00');
		}

		if (date_format($trial_end, 'Y-m-d H:i:s') > date_format($subscription_end, 'Y-m-d H:i:s') && $item['subscription']['duration'] != 0) {
			$subscription_end = new \DateTime(date_format($trial_end, 'Y-m-d H:i:s'));
			$subscription_end = $this->calculateSchedule($item['subscription']['frequency'], $subscription_end, $item['subscription']['cycle'] * $item['subscription']['duration']);
		} elseif (date_format($trial_end, 'Y-m-d H:i:s') == date_format($subscription_end, 'Y-m-d H:i:s') && $item['subscription']['duration'] != 0) {
			$next_payment = $this->calculateSchedule($item['subscription']['frequency'], $next_payment, $item['subscription']['cycle']);
			$subscription_end = $this->calculateSchedule($item['subscription']['frequency'], $subscription_end, $item['subscription']['cycle'] * $item['subscription']['duration']);
		} elseif (date_format($trial_end, 'Y-m-d H:i:s') > date_format($subscription_end, 'Y-m-d H:i:s') && $item['subscription']['duration'] == 0) {
			$subscription_end = new \DateTime('0000-00-00');
		} elseif (date_format($trial_end, 'Y-m-d H:i:s') == date_format($subscription_end, 'Y-m-d H:i:s') && $item['subscription']['duration'] == 0) {
			$next_payment = $this->calculateSchedule($item['subscription']['frequency'], $next_payment, $item['subscription']['cycle']);
			$subscription_end = new \DateTime('0000-00-00');
		}

		$this->addOrderSubscription($item['subscription']['order_id'], $item);

		$result = $this->createPayment($order_info, $paypal_order_data, $price, $item['subscription']['name']);

		$transaction_status = '';
		$transaction_id = '';
		$currency_code = '';
		$amount = '';

		if ($transaction_method == 'authorize') {
			if (isset($result['purchase_units'][0]['payments']['authorizations'][0]['status']) && isset($result['purchase_units'][0]['payments']['authorizations'][0]['seller_protection']['status'])) {
				$transaction_id = $result['purchase_units'][0]['payments']['authorizations'][0]['id'];
				$transaction_status = $result['purchase_units'][0]['payments']['authorizations'][0]['status'];
				$currency_code = $result['purchase_units'][0]['payments']['authorizations'][0]['amount']['currency_code'];
				$amount = $result['purchase_units'][0]['payments']['authorizations'][0]['amount']['value'];
			}
		} else {
			if (isset($result['purchase_units'][0]['payments']['captures'][0]['status']) && isset($result['purchase_units'][0]['payments']['captures'][0]['seller_protection']['status'])) {
				$transaction_id = $result['purchase_units'][0]['payments']['captures'][0]['id'];
				$transaction_status = $result['purchase_units'][0]['payments']['captures'][0]['status'];
				$currency_code = $result['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
				$amount = $result['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
			}
		}

		if ($transaction_id && $transaction_status && $currency_code && $amount) {
			$this->editOrderSubscriptionStatus($order_info['order_id'], 1);

			$paypal_subscription_data = [
				'subscription_id'  => $item['subscription']['subscription_id'],
				'order_id'         => $item['subscription']['order_id'],
				'trial_end'        => date_format($trial_end, 'Y-m-d H:i:s'),
				'subscription_end' => date_format($subscription_end, 'Y-m-d H:i:s'),
				'currency_code'    => $currency_code,
				'amount'           => $amount
			];

			$this->addPayPalOrderSubscription($paypal_subscription_data);

			if (($transaction_status == 'CREATED') || ($transaction_status == 'COMPLETED') || ($transaction_status == 'PENDING')) {
				$subscription_transaction_data = [
					'subscription_id' => $item['subscription']['subscription_id'],
					'order_id'        => $item['subscription']['order_id'],
					'reference'       => $transaction_id,
					'type'            => '1',
					'amount'          => $amount
				];

				$this->addSubscriptionTransaction($subscription_transaction_data);

				$this->editPayPalOrderSubscriptionNextPayment($item['subscription']['subscription_id'], date_format($next_payment, 'Y-m-d H:i:s'));
			} else {
				$subscription_transaction_data = [
					'subscription_id' => $item['subscription']['subscription_id'],
					'order_id'        => $item['subscription']['order_id'],
					'reference'       => $transaction_id,
					'type'            => '4',
					'amount'          => $amount
				];

				$this->addSubscriptionTransaction($subscription_transaction_data);
			}
		}
	}

	/**
	 * Cron Payment
	 *
	 * @return void
	 */
	public function cronPayment(): void {
		$this->load->model('account/subscription');
		$this->load->model('checkout/order');
		$this->load->model('account/order');

		$_config = new \Config();
		$_config->load('paypal');

		$config_setting = $_config->get('paypal_setting');

		$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

		$transaction_method = $setting['general']['transaction_method'];

		$limit = $this->config->get('config_pagination');

		$subscriptions = $this->model_account_subscription->getSubscriptions(0, $limit);

		foreach ($subscriptions as $subscription) {
			$order_subscription = $this->model_account_subscription->getSubscriptionByOrderProductId($subscription['order_id'], $subscription['order_product_id']);

			if ($order_subscription && $order_subscription['status'] == 1) {
				$paypal_order_subscription = $this->getPayPalOrderSubscription($order_subscription['order_id']);

				if ($paypal_order_subscription) {
					$today = new \DateTime('now');
					$unlimited = new \DateTime('0000-00-00');
					$next_payment = new \DateTime($paypal_order_subscription['next_payment']);
					$trial_end = new \DateTime($paypal_order_subscription['trial_end']);
					$subscription_end = new \DateTime($paypal_order_subscription['subscription_end']);

					$order_info = $this->model_checkout_order->getOrder($order_subscription['order_id']);

					$order_product = $this->model_account_order->getProduct($order_subscription['order_id'], $order_subscription['order_product_id']);

					$paypal_order_info = $this->getPayPalOrder($order_subscription['order_id']);

					if ((date_format($today, 'Y-m-d H:i:s') > date_format($next_payment, 'Y-m-d H:i:s')) && (date_format($trial_end, 'Y-m-d H:i:s') > date_format($today, 'Y-m-d H:i:s') || date_format($trial_end, 'Y-m-d H:i:s') == date_format($unlimited, 'Y-m-d H:i:s'))) {
						$price = $this->currency->format($order_subscription['trial_price'], $order_info['currency_code'], false, false);
						$frequency = $order_subscription['trial_frequency'];
						$cycle = $order_subscription['trial_cycle'];
						$next_payment = $this->calculateSchedule($frequency, $next_payment, $cycle);
					} elseif ((date_format($today, 'Y-m-d H:i:s') > date_format($next_payment, 'Y-m-d H:i:s')) && (date_format($subscription_end, 'Y-m-d H:i:s') > date_format($today, 'Y-m-d H:i:s') || date_format($subscription_end, 'Y-m-d H:i:s') == date_format($unlimited, 'Y-m-d H:i:s'))) {
						$price = $this->currency->format($order_subscription['price'], $order_info['currency_code'], false, false);
						$frequency = $order_subscription['frequency'];
						$cycle = $order_subscription['cycle'];
						$next_payment = $this->calculateSchedule($frequency, $next_payment, $cycle);
					} else {
						continue;
					}

					$result = $this->createPayment($order_info, $paypal_order_info, $price, $order_product['name']);

					$transaction_status = '';
					$transaction_id = '';
					$currency_code = '';
					$amount = '';

					if ($transaction_method == 'authorize') {
						if (isset($result['purchase_units'][0]['payments']['authorizations'][0]['status']) && isset($result['purchase_units'][0]['payments']['authorizations'][0]['seller_protection']['status'])) {
							$transaction_id = $result['purchase_units'][0]['payments']['authorizations'][0]['id'];
							$transaction_status = $result['purchase_units'][0]['payments']['authorizations'][0]['status'];
							$currency_code = $result['purchase_units'][0]['payments']['authorizations'][0]['amount']['currency_code'];
							$amount = $result['purchase_units'][0]['payments']['authorizations'][0]['amount']['value'];
						}
					} else {
						if (isset($result['purchase_units'][0]['payments']['captures'][0]['status']) && isset($result['purchase_units'][0]['payments']['captures'][0]['seller_protection']['status'])) {
							$transaction_id = $result['purchase_units'][0]['payments']['captures'][0]['id'];
							$transaction_status = $result['purchase_units'][0]['payments']['captures'][0]['status'];
							$currency_code = $result['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
							$amount = $result['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
						}
					}

					if ($transaction_id && $transaction_status && $currency_code && $amount) {
						if (($transaction_status == 'CREATED') || ($transaction_status == 'COMPLETED') || ($transaction_status == 'PENDING')) {
							$subscription_transaction_data = [
								'subscription_id' => $subscription['subscription_id'],
								'reference'       => $transaction_id,
								'type'            => '1',
								'amount'          => $amount
							];

							$this->addSubscriptionTransaction($subscription_transaction_data);

							$this->editPayPalOrderSubscriptionNextPayment($order_subscription['order_id'], date_format($next_payment, 'Y-m-d H:i:s'));
						} else {
							$subscription_transaction_data = [
								'subscription_id' => $subscription['subscription_id'],
								'reference'       => $transaction_id,
								'type'            => '4',
								'amount'          => $amount
							];

							$this->addSubscriptionTransaction($subscription_transaction_data);
						}
					}
				}
			}
		}
	}

	/**
	 * Create Payment
	 *
	 * @param array<string, mixed> $order_data
	 * @param array<string, mixed> $paypal_order_data
	 * @param float                $price
	 * @param string               $name
	 *
	 * @return array<string, mixed>
	 */
	public function createPayment(array $order_data, array $paypal_order_data, float $price, string $name): array {
		$this->load->language('extension/payment/paypal');

		$_config = new \Config();
		$_config->load('paypal');

		$config_setting = $_config->get('paypal_setting');

		$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

		$client_id = $this->config->get('payment_paypal_client_id');
		$secret = $this->config->get('payment_paypal_secret');
		$merchant_id = $this->config->get('payment_paypal_merchant_id');
		$environment = $this->config->get('payment_paypal_environment');
		$partner_id = $setting['partner'][$environment]['partner_id'];
		$partner_attribution_id = $setting['partner'][$environment]['partner_attribution_id'];
		$transaction_method = $setting['general']['transaction_method'];

		$currency_code = $order_data['currency_code'];
		$currency_value = $this->currency->getValue($currency_code);
		$decimal_place = $setting['currency'][$currency_code]['decimal_place'];

		require_once DIR_SYSTEM . 'library/paypal/paypal.php';

		$paypal_info = [
			'partner_id'             => $partner_id,
			'client_id'              => $client_id,
			'secret'                 => $secret,
			'environment'            => $environment,
			'partner_attribution_id' => $partner_attribution_id
		];

		$paypal = new PayPal($paypal_info);

		$token_info = [
			'grant_type' => 'client_credentials'
		];

		$paypal->setAccessToken($token_info);

		$item_info = [];

		$item_total = 0;

		$product_price = number_format($price * $currency_value, $decimal_place, '.', '');

		$item_info[] = [
			'name'        => $name,
			'quantity'    => 1,
			'unit_amount' => [
				'currency_code' => $currency_code,
				'value'         => $product_price
			]
		];

		$item_total += $product_price;

		$item_total = number_format($item_total, $decimal_place, '.', '');
		$order_total = number_format($item_total, $decimal_place, '.', '');

		$amount_info = [];

		$amount_info['currency_code'] = $currency_code;
		$amount_info['value'] = $order_total;

		$amount_info['breakdown']['item_total'] = [
			'currency_code' => $currency_code,
			'value'         => $item_total
		];

		$paypal_order_info = [];

		$paypal_order_info['intent'] = strtoupper($transaction_method);
		$paypal_order_info['purchase_units'][0]['reference_id'] = 'default';
		$paypal_order_info['purchase_units'][0]['items'] = $item_info;
		$paypal_order_info['purchase_units'][0]['amount'] = $amount_info;

		$paypal_order_info['purchase_units'][0]['description'] = 'Subscription to order ' . $order_data['order_id'];

		$shipping_preference = 'NO_SHIPPING';

		$paypal_order_info['application_context']['shipping_preference'] = $shipping_preference;

		$paypal_order_info['payment_source'][$paypal_order_data['payment_method']]['vault_id'] = $paypal_order_data['vault_id'];

		$result = $paypal->createOrder($paypal_order_info);

		$errors = [];

		if ($paypal->hasErrors()) {
			$errors = $paypal->getErrors();

			foreach ($errors as $error) {
				if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
					$error['message'] = $this->language->get('error_timeout');
				}

				$this->log($error, $error['message']);
			}
		}

		if (isset($result['id']) && isset($result['status']) && !$errors) {
			$this->log($result, 'Create Subscription Payment');

			return $result;
		}

		return [];
	}

	/**
	 * Calculate Schedule
	 *
	 * @param string    $frequency
	 * @param \Datetime $next_payment
	 * @param int       $cycle
	 *
	 * @return \Datetime
	 */
	private function calculateSchedule(string $frequency, \DateTime $next_payment, int $cycle) {
		$next_payment = clone $next_payment;

		if ($frequency == 'semi_month') {
			$day = $next_payment->format('d');
			$value = 15 - $day;
			$is_even = false;

			if ($cycle % 2 == 0) {
				$is_even = true;
			}

			$odd = ($cycle + 1) / 2;
			$plus_even = ($cycle / 2) + 1;
			$minus_even = $cycle / 2;

			if ($day == 1) {
				$odd--;
				$plus_even--;
				$day = 16;
			}

			if ($day <= 15 && $is_even) {
				$next_payment->modify('+' . $value . ' day');
				$next_payment->modify('+' . $minus_even . ' month');
			} elseif ($day <= 15) {
				$next_payment->modify('first day of this month');
				$next_payment->modify('+' . $odd . ' month');
			} elseif ($day > 15 && $is_even) {
				$next_payment->modify('first day of this month');
				$next_payment->modify('+' . $plus_even . ' month');
			} elseif ($day > 15) {
				$next_payment->modify('+' . $value . ' day');
				$next_payment->modify('+' . $odd . ' month');
			}
		} else {
			$next_payment->modify('+' . $cycle . ' ' . $frequency);
		}

		return $next_payment;
	}

	/**
	 * Get Agree Status
	 *
	 * @return bool
	 */
	public function getAgreeStatus(): bool {
		$agree_status = true;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE `status` = '1' AND (`iso_code_2` = 'CU' OR `iso_code_2` = 'IR' OR `iso_code_2` = 'SY' OR `iso_code_2` = 'KP')");

		if ($query->rows) {
			$agree_status = false;
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '220' AND `status` = '1' AND (`code` = '43' OR `code` = '14' OR `code` = '09')");

		if ($query->rows) {
			$agree_status = false;
		}

		return $agree_status;
	}

	/**
	 * Log
	 *
	 * @param array<string, mixed> $data
	 * @param ?string              $title
	 *
	 * @return void
	 */
	public function log(array $data, ?string $title = ''): void {
		// Setting
		$_config = new \Config();
		$_config->load('paypal');

		$config_setting = $_config->get('paypal_setting');

		$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

		if ($setting['general']['debug']) {
			$log = new \Log('paypal.log');
			$log->write('PayPal debug (' . $title . '): ' . json_encode($data));
		}
	}

	/**
	 * Charge
	 *
	 * @param int    $customer_id
	 * @param int    $order_id
	 * @param float  $total
	 * @param string $payment_code
	 *
	 * @return bool
	 */
	public function charge(int $customer_id, int $order_id, float $total, string $payment_code): bool {
		/*
		 * Used by the checkout to state the module
		 * supports recurring subscriptions.
		 */
		return true;
	}
}
