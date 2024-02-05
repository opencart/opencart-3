<?php
/**
 * Class Sagepay Server
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentSagePayServer extends Model {
	/**
	 * Get Method
	 *
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/sagepay_server');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_sagepay_server_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_sagepay_server_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'sagepay_server',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_sagepay_server_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * Get Cards
	 *
	 * @param int $customer_id
	 *
	 * @return array
	 */
	public function getCards(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_server_card` WHERE `customer_id` = '" . (int)$customer_id . "'");

		$card_data = [];

		// Addresses
		$this->load->model('account/address');

		foreach ($query->rows as $row) {
			$card_data[] = [
				'card_id'     => $row['card_id'],
				'customer_id' => $row['customer_id'],
				'token'       => $row['token'],
				'digits'      => '**** ' . $row['digits'],
				'expiry'      => $row['expiry'],
				'type'        => $row['type'],
			];
		}

		return $card_data;
	}

	/**
	 * Get Card
	 *
	 * @param string $card_id
	 * @param string $token
	 *
	 * @return array
	 */
	public function getCard(string $card_id, string $token): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_server_card` WHERE (`card_id` = '" . $this->db->escape($card_id) . "' OR `token` = '" . $this->db->escape($token) . "') AND `customer_id` = '" . (int)$this->customer->getId() . "'");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * Add Card
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function addCard(array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sagepay_server_card` SET `customer_id` = '" . (int)$data['customer_id'] . "', `token` = '" . $this->db->escape($data['Token']) . "', `digits` = '" . $this->db->escape($data['Last4Digits']) . "', `expiry` = '" . $this->db->escape($data['ExpiryDate']) . "', `type` = '" . $this->db->escape($data['CardType']) . "'");
	}

	/**
	 * Delete Card
	 *
	 * @param int $card_id
	 *
	 * @return void
	 */
	public function deleteCard(int $card_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "sagepay_server_card` WHERE `card_id` = '" . (int)$card_id . "'");
	}

	/**
	 * Add Order
	 *
	 * @param array $order_info
	 *
	 * @return void
	 */
	public function addOrder(array $order_info): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "sagepay_server_order` WHERE `order_id` = '" . (int)$order_info['order_id'] . "'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "sagepay_server_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `customer_id` = '" . (int)$this->customer->getId() . "', `vps_tx_id` = '" . $this->db->escape($order_info['VPSTxId']) . "', `vendor_tx_code` = '" . $this->db->escape($order_info['VendorTxCode']) . "', `security_key` = '" . $this->db->escape($order_info['SecurityKey']) . "', `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `total` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "', `date_added` = NOW(), `date_modified` = NOW()");
	}

	/**
	 * Get Order
	 *
	 * @param int    $order_id
	 * @param string $vpstx_id
	 *
	 * @return array
	 */
	public function getOrder(int $order_id, ?string $vpstx_id = null): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_server_order` WHERE `order_id` = '" . (int)$order_id . "' OR `vps_tx_id` = '" . $this->db->escape($vpstx_id) . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;
			$order['transactions'] = $this->getTransactions($order['sagepay_server_order_id']);

			return $order;
		} else {
			return [];
		}
	}

	/**
	 * Update Order
	 *
	 * @param array  $order_info
	 * @param string $vps_txn_id
	 * @param string $tx_auth_no
	 *
	 * @return void
	 */
	public function updateOrder(array $order_info, string $vps_txn_id, string $tx_auth_no): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_server_order` SET `vps_tx_id` = '" . $this->db->escape($vps_txn_id) . "', `tx_auth_no` = '" . $this->db->escape($tx_auth_no) . "' WHERE `order_id` = '" . (int)$order_info['order_id'] . "'");
	}

	/**
	 * Delete Order
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deleteOrder(int $order_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "sagepay_server_order` WHERE `order_id` = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "subscription` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Add Transaction
	 *
	 * @param int    $sagepay_server_order_id
	 * @param string $type
	 * @param array  $order_info
	 *
	 * @return void
	 */
	public function addTransaction(int $sagepay_server_order_id, string $type, array $order_info): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sagepay_server_order_transaction` SET `sagepay_server_order_id` = '" . (int)$sagepay_server_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "'");
	}

	/**
	 * Get Transactions
	 *
	 * @param int $sagepay_server_order_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getTransactions(int $sagepay_server_order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_server_order_transaction` WHERE `sagepay_server_order_id` = '" . (int)$sagepay_server_order_id . "'");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * Get Subscription Orders
	 *
	 * @param int $order_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSubscriptionOrders(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription` WHERE `order_id` = '" . (int)$order_id . "'");

		return $query->rows;
	}

	/**
	 * Get Reference
	 *
	 * @param string $vendor_tx_code
	 *
	 * @return array<string, mixed>
	 */
	public function getReference(string $vendor_tx_code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_server_order_subscription` WHERE `vendor_tx_code` = '" . $this->db->escape($vendor_tx_code) . "'");

		return $query->row;
	}

	/**
	 * Subscription Payment
	 *
	 * @param array  $item
	 * @param string $vendor_tx_code
	 *
	 * @return void
	 */
	public function subscriptionPayment(array $item, string $vendor_tx_code): void {
		// Orders
		$this->load->model('checkout/subscription');
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($item['subscription']['order_id']);

		if ($order_info) {
			// Trial information
			if ($item['subscription']['trial_status'] == 1) {
				$price = $this->currency->format($item['subscription']['trial_price'], $this->session->data['currency'], false, false);
			} else {
				$price = $this->currency->format($item['subscription']['price'], $this->session->data['currency'], false, false);
			}

			$sagepay_order_info = $this->getReference($vendor_tx_code);

			if ($sagepay_order_info) {
				$response_data = $this->setPaymentData($order_info, $sagepay_order_info, $price, $item['subscription']['subscription_id'], $item['subscription']['name']);

				$next_payment = new \DateTime('now');
				$trial_end = new \DateTime('now');
				$subscription_end = new \DateTime('now');

				if ($item['subscription']['trial_status'] == 1 && $item['subscription']['trial_duration'] != 0) {
					$next_payment = $this->calculateSchedule($item['subscription']['trial_frequency'], $next_payment, $item['subscription']['trial_cycle']);
					$trial_end = $this->calculateSchedule($item['subscription']['trial_frequency'], $trial_end, $item['subscription']['trial_cycle'] * $item['subscription']['trial_duration']);
				} elseif ($item['subscription']['trial_status'] == 1) {
					$next_payment = $this->calculateSchedule($item['subscription']['trial_frequency'], $next_payment, $item['subscription']['trial_cycle']);
					$trial_end = new \DateTime('0000-00-00');
				}

				if ($trial_end > $subscription_end && $item['subscription']['duration'] != 0) {
					$subscription_end = new \DateTime(date_format($trial_end, 'Y-m-d H:i:s'));
					$subscription_end = $this->calculateSchedule($item['subscription']['frequency'], $subscription_end, $item['subscription']['cycle'] * $item['subscription']['duration']);
				} elseif ($trial_end == $subscription_end && $item['subscription']['duration'] != 0) {
					$next_payment = $this->calculateSchedule($item['subscription']['frequency'], $next_payment, $item['subscription']['cycle']);
					$subscription_end = $this->calculateSchedule($item['subscription']['frequency'], $subscription_end, $item['subscription']['cycle'] * $item['subscription']['duration']);
				} elseif ($trial_end > $subscription_end && $item['subscription']['duration'] == 0) {
					$subscription_end = new \DateTime('0000-00-00');
				} elseif ($trial_end == $subscription_end && $item['subscription']['duration'] == 0) {
					$next_payment = $this->calculateSchedule($item['subscription']['frequency'], $next_payment, $item['subscription']['cycle']);
					$subscription_end = new \DateTime('0000-00-00');
				}

				$this->addSubscriptionOrder($item['subscription']['order_id'], $response_data, $item['subscription']['subscription_id'], date_format($trial_end, 'Y-m-d H:i:s'), date_format($subscription_end, 'Y-m-d H:i:s'));

				$transaction = [
					'order_id'       => $item['subscription']['order_id'],
					'description'    => $response_data['Status'],
					'amount'         => $price,
					'payment_method' => $order_info['payment_method'],
					'payment_code'   => $order_info['payment_code']
				];

				if ($response_data['Status'] == 'OK') {
					$this->updateSubscriptionOrder($item['subscription']['subscription_id'], date_format($next_payment, 'Y-m-d H:i:s'));

					$this->addSubscriptionTransaction($item['subscription']['subscription_id'], $response_data, $transaction, 1);

					$this->model_checkout_subscription->editSubscription($item['subscription']['subscription_id'], $item['subscription']);
				} else {
					$this->addSubscriptionTransaction($item['subscription']['subscription_id'], $response_data, $transaction, 4);
				}
			}
		}
	}

	/**
	 * updateSubscriptionPayment
	 *
	 * @param array                $item['subscription']
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function updateSubscriptionPayment(array $item, array $data): void {
		// Orders
		$this->load->model('checkout/subscription');
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($data['order_id']);

		if ($order_info) {
			// Trial information
			if ($item['subscription']['trial_status'] == 1) {
				$price = $this->currency->format($item['subscription']['trial_price'], $this->session->data['currency'], false, false);
			} else {
				$price = $this->currency->format($item['subscription']['price'], $this->session->data['currency'], false, false);
			}

			$subscription_info = $this->getReference($data['vendor_tx_code']);

			if ($subscription_info) {
				$response_data = $this->setPaymentData($order_info, $data, $price, $subscription_info['subscription_id'], $item['subscription']['name']);

				$next_payment = new \DateTime('now');
				$trial_end = new \DateTime('now');
				$subscription_end = new \DateTime('now');

				if ($item['subscription']['trial_status'] == 1 && $item['subscription']['trial_duration'] != 0) {
					$next_payment = $this->calculateSchedule($item['subscription']['trial_frequency'], $next_payment, $item['subscription']['trial_cycle']);
					$trial_end = $this->calculateSchedule($item['subscription']['trial_frequency'], $trial_end, $item['subscription']['trial_cycle'] * $item['subscription']['trial_duration']);
				} elseif ($item['subscription']['trial_status'] == 1) {
					$next_payment = $this->calculateSchedule($item['subscription']['trial_frequency'], $next_payment, $item['subscription']['trial_cycle']);
					$trial_end = new \DateTime('0000-00-00');
				}

				if ($trial_end > $subscription_end && $item['subscription']['duration'] != 0) {
					$subscription_end = new \DateTime(date_format($trial_end, 'Y-m-d H:i:s'));
					$subscription_end = $this->calculateSchedule($item['subscription']['frequency'], $subscription_end, $item['subscription']['cycle'] * $item['subscription']['duration']);
				} elseif ($trial_end == $subscription_end && $item['subscription']['duration'] != 0) {
					$next_payment = $this->calculateSchedule($item['subscription']['frequency'], $next_payment, $item['subscription']['cycle']);
					$subscription_end = $this->calculateSchedule($item['subscription']['frequency'], $subscription_end, $item['subscription']['cycle'] * $item['subscription']['duration']);
				} elseif ($trial_end > $subscription_end && $item['subscription']['duration'] == 0) {
					$subscription_end = new \DateTime('0000-00-00');
				} elseif ($trial_end == $subscription_end && $item['subscription']['duration'] == 0) {
					$next_payment = $this->calculateSchedule($item['subscription']['frequency'], $next_payment, $item['subscription']['cycle']);
					$subscription_end = new \DateTime('0000-00-00');
				}

				$this->addSubscriptionOrder($data['order_id'], $response_data, $item['subscription']['subscription_id'], date_format($trial_end, 'Y-m-d H:i:s'), date_format($subscription_end, 'Y-m-d H:i:s'));

				$transaction = [
					'order_id'       => $subscription_info['order_id'],
					'description'    => $response_data['Status'],
					'amount'         => $price,
					'payment_method' => $order_info['payment_method'],
					'payment_code'   => $order_info['payment_code']
				];

				if ($response_data['Status'] == 'OK') {
					$this->updateSubscriptionOrder($item['subscription']['subscription_id'], date_format($next_payment, 'Y-m-d H:i:s'));

					$this->addSubscriptionTransaction($item['subscription']['subscription_id'], $response_data, $transaction, 1);

					$this->model_checkout_subscription->editSubscription($item['subscription']['subscription_id'], $item['subscription']);
				} else {
					$this->addSubscriptionTransaction($item['subscription']['subscription_id'], $response_data, $transaction, 4);
				}
			}
		}
	}

	/**
	 * Set Payment Data
	 *
	 * @param array<string, mixed> $order_info
	 * @param array<string, mixed> $sagepay_order_info
	 * @param float                $price
	 * @param int                  $subscription_id
	 * @param string               $name
	 * @param int                  $i
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function setPaymentData(array $order_info, array $sagepay_order_info, float $price, int $subscription_id, string $name, ?int $i = null) {
		$payment_data = [];

		$url = '';

		// https://en.wikipedia.org/wiki/Opayo
		if ($this->config->get('payment_sagepay_server_test') == 'live') {
			$url = 'https://live.opayo.eu.elavon.com/gateway/service/repeat.vsp';
			$payment_data['VPSProtocol'] = '4.00';
		} elseif ($this->config->get('payment_sagepay_server_test') == 'test') {
			$url = 'https://sandbox.opayo.eu.elavon.com/gateway/service/repeat.vsp';
			$payment_data['VPSProtocol'] = '4.00';
		} elseif ($this->config->get('payment_sagepay_server_test') == 'sim') {
			$url = 'https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRepeatTx';
			$payment_data['VPSProtocol'] = '4.00';
		}

		$payment_data['TxType'] = 'REPEAT';
		$payment_data['Vendor'] = $this->config->get('payment_sagepay_server_vendor');
		$payment_data['VendorTxCode'] = $subscription_id . 'RSD' . gmdate('YmdHis', time()) . mt_rand(1, 999);
		$payment_data['Amount'] = $this->currency->format($price, $this->session->data['currency'], false, false);
		$payment_data['Currency'] = $this->session->data['currency'];
		$payment_data['Description'] = substr($name, 0, 100);
		$payment_data['RelatedVPSTxId'] = trim($sagepay_order_info['vps_tx_id'], '{}');
		$payment_data['RelatedVendorTxCode'] = $sagepay_order_info['vendor_tx_code'];
		$payment_data['RelatedSecurityKey'] = $sagepay_order_info['security_key'];
		$payment_data['RelatedTxAuthNo'] = $sagepay_order_info['tx_auth_no'];

		if (!empty($order_info['shipping_lastname'])) {
			$payment_data['DeliverySurname'] = substr($order_info['shipping_lastname'], 0, 20);
			$payment_data['DeliveryFirstnames'] = substr($order_info['shipping_firstname'], 0, 20);
			$payment_data['DeliveryAddress1'] = substr($order_info['shipping_address_1'], 0, 100);

			if ($order_info['shipping_address_2']) {
				$payment_data['DeliveryAddress2'] = $order_info['shipping_address_2'];
			}

			$payment_data['DeliveryCity'] = substr($order_info['shipping_city'], 0, 40);
			$payment_data['DeliveryPostCode'] = substr($order_info['shipping_postcode'], 0, 10);
			$payment_data['DeliveryCountry'] = $order_info['shipping_iso_code_2'];

			if ($order_info['shipping_iso_code_2'] == 'US') {
				$payment_data['DeliveryState'] = $order_info['shipping_zone_code'];
			}

			$payment_data['CustomerName'] = substr($order_info['firstname'] . ' ' . $order_info['lastname'], 0, 100);
			$payment_data['DeliveryPhone'] = substr($order_info['telephone'], 0, 20);
		} else {
			$payment_data['DeliveryFirstnames'] = $order_info['payment_firstname'];
			$payment_data['DeliverySurname'] = $order_info['payment_lastname'];
			$payment_data['DeliveryAddress1'] = $order_info['payment_address_1'];

			if ($order_info['payment_address_2']) {
				$payment_data['DeliveryAddress2'] = $order_info['payment_address_2'];
			}

			$payment_data['DeliveryCity'] = $order_info['payment_city'];
			$payment_data['DeliveryPostCode'] = $order_info['payment_postcode'];
			$payment_data['DeliveryCountry'] = $order_info['payment_iso_code_2'];

			if ($order_info['payment_iso_code_2'] == 'US') {
				$payment_data['DeliveryState'] = $order_info['payment_zone_code'];
			}

			$payment_data['DeliveryPhone'] = $order_info['telephone'];
		}

		$response_data = $this->sendCurl($url, $payment_data, $i);

		$response_data['VendorTxCode'] = $payment_data['VendorTxCode'];
		$response_data['Amount'] = $payment_data['Amount'];
		$response_data['Currency'] = $payment_data['Currency'];

		return $response_data;
	}

	/**
	 * cronPayment
	 */
	public function cronPayment() {
		// Orders
		$this->load->model('account/order');

		$cron_data = [];

		$subscriptions = $this->getProfiles();

		$i = 0;

		foreach ($subscriptions as $subscription) {
			$subscription_order = $this->getSubscriptionOrder($subscription['subscription_id']);

			$today = new \DateTime('now');
			$unlimited = new \DateTime('0000-00-00');
			$next_payment = new \DateTime($subscription_order['next_payment']);
			$trial_end = new \DateTime($subscription_order['trial_end']);
			$subscription_end = new \DateTime($subscription_order['subscription_end']);

			$order_info = $this->model_account_order->getOrder($subscription['order_id']);

			if (($today > $next_payment) && ($trial_end > $today || $trial_end == $unlimited)) {
				$price = $this->currency->format($subscription['trial_price'], $order_info['currency_code'], false, false);
				$frequency = $subscription['trial_frequency'];
				$cycle = $subscription['trial_cycle'];
			} elseif (($today > $next_payment) && ($subscription_end > $today || $subscription_end == $unlimited)) {
				$price = $this->currency->format($subscription['price'], $order_info['currency_code'], false, false);
				$frequency = $subscription['frequency'];
				$cycle = $subscription['cycle'];
			} else {
				continue;
			}

			$sagepay_order_info = $this->getOrder($subscription['order_id']);

			$response_data = $this->setPaymentData($order_info, $sagepay_order_info, $price, $subscription['subscription_id'], $subscription['name'], $i);

			$cron_data[] = $response_data;

			$transaction = [
				'order_id'       => $subscription['order_id'],
				'description'    => $response_data['RepeatResponseData_' . $i++]['Status'],
				'amount'         => $price,
				'payment_method' => $order_info['payment_method'],
				'payment_code'   => $order_info['payment_code']
			];

			if ($response_data['RepeatResponseData_' . $i++]['Status'] == 'OK') {
				$this->addSubscriptionTransaction($subscription['subscription_id'], $response_data, $transaction, 1);

				$next_payment = $this->calculateSchedule($frequency, $next_payment, $cycle);
				$next_payment = date_format($next_payment, 'Y-m-d H:i:s');

				$this->updateSubscriptionOrder($subscription['subscription_id'], $next_payment);
			} else {
				$this->addSubscriptionTransaction($subscription['subscription_id'], $response_data, $transaction, 4);
			}
		}

		// Log
		$log = new \Log('sagepay_server_subscription_orders.log');
		$log->write(print_r($cron_data, 1));

		return $cron_data;
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
	private function calculateSchedule($frequency, \DateTime $next_payment, $cycle) {
		$next_payment = clone $next_payment;

		if ($frequency == 'semi_month') {
			// https://stackoverflow.com/a/35473574
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
	 * Add Subscription Order
	 *
	 * @param int                  $order_id
	 * @param array<string, mixed> $response_data
	 * @param int                  $subscription_id
	 * @param string               $trial_end
	 * @param string               $subscription_end
	 *
	 * @return void
	 */
	private function addSubscriptionOrder(int $order_id, array $response_data, int $subscription_id, string $trial_end, string $subscription_end): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sagepay_server_order_subscription` SET `order_id` = '" . (int)$order_id . "', `subscription_id` = '" . (int)$subscription_id . "', `vps_tx_id` = '" . $this->db->escape($response_data['VPSTxId']) . "', `vendor_tx_code` = '" . $this->db->escape($response_data['VendorTxCode']) . "', `security_key` = '" . $this->db->escape($response_data['SecurityKey']) . "', `tx_auth_no` = '" . $this->db->escape($response_data['TxAuthNo']) . "', `next_payment` = NOW(), `trial_end` = '" . $this->db->escape($trial_end) . "', `subscription_end` = '" . $this->db->escape($subscription_end) . "', `currency_code` = '" . $this->db->escape($response_data['Currency']) . "', `total` = '" . $this->currency->format($response_data['Amount'], $response_data['Currency'], false, false) . "', `date_added` = NOW(), `date_modified` = NOW()");
	}

	/**
	 * Update Subscription Order
	 *
	 * @param int    $subscription_id
	 * @param string $next_payment
	 *
	 * @return void
	 */
	private function updateSubscriptionOrder(int $subscription_id, string $next_payment): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_server_order_subscription` SET `next_payment` = '" . $this->db->escape($next_payment) . "', `date_modified` = NOW() WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Get Subscription Order
	 *
	 * @param int $subscription_id
	 *
	 * @return array<string, mixed>
	 */
	private function getSubscriptionOrder(int $subscription_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_server_order_subscription` WHERE `subscription_id` = '" . (int)$subscription_id . "'");

		return $query->row;
	}

	/**
	 * Add Subscription Transaction
	 *
	 * @param int                  $subscription_id
	 * @param array<string, mixed> $response_data
	 * @param array<string, mixed> $transaction
	 * @param int                  $type
	 *
	 * @return void
	 */
	private function addSubscriptionTransaction(int $subscription_id, array $response_data, array $transaction, int $type): void {
		// Subscriptions
		$this->load->model('account/subscription');

		$subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

		if ($subscription_info) {
			// Subscriptions
			$this->load->model('checkout/subscription');

			$this->editReference($subscription_id, $response_data['VendorTxCode']);

			$this->editStatus($subscription_id, $type);
		}
	}

	/**
	 * Edit Status
	 *
	 * @param int $subscription_id
	 * @param int $status
	 *
	 * @return void
	 */
	protected function editStatus(int $subscription_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_server_order_subscription` SET `settle_type` = '" . (int)$status . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Edit Reference
	 *
	 * @param int    $subscription_id
	 * @param string $reference
	 *
	 * @return void
	 */
	protected function editReference(int $subscription_id, string $reference): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_server_order_subscription` SET `vendor_tx_code` = '" . $this->db->escape($reference) . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Get Profiles
	 *
	 * @return array<int, mixed>
	 */
	private function getProfiles(): array {
		$order_recurring_data = [];

		// Recurring
		$this->load->model('account/recurring');

		$sql = "SELECT `r`.`order_recurring_id` FROM `" . DB_PREFIX . "order_recurring` `r` JOIN `" . DB_PREFIX . "order` `o` USING(`order_id`) WHERE `o`.`payment_code` = 'sagepay_server'";

		$query = $this->db->query($sql);

		foreach ($query->rows as $recurring) {
			$order_recurring_data[] = $this->model_account_recurring->getRecurring($recurring['order_recurring_id']);
		}

		return $order_recurring_data;
	}

	/**
	 * Update Cron Job Run Time
	 */
	public function updateCronJobRunTime(): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'sagepay_server' AND `key` = 'payment_sagepay_server_last_cron_job_run'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'sagepay_server', `key` = 'payment_sagepay_server_last_cron_job_run', `value` = NOW(), `serialized` = '0'");
	}

	/**
	 * Send Curl
	 *
	 * @param string               $url
	 * @param array<string, mixed> $payment_data
	 * @param int                  $i
	 *
	 * @return array
	 */
	public function sendCurl(string $url, array $payment_data, ?int $i = null): array {
		$post_data = [];

		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payment_data));

		$response = curl_exec($curl);

		curl_close($curl);

		$response_info = explode(chr(10), $response);

		foreach ($response_info as $string) {
			if (strpos($string, '=') && $i !== null) {
				$parts = explode('=', $string, 2);
				$post_data['RepeatResponseData_' . $i][trim($parts[0])] = trim($parts[1]);
			} elseif (strpos($string, '=')) {
				$parts = explode('=', $string, 2);
				$post_data[trim($parts[0])] = trim($parts[1]);
			}
		}

		return $post_data;
	}

	/**
	 * Logger
	 *
	 * @param string $title
	 * @param mixed  $data
	 *
	 * @return void
	 */
	public function logger(string $title, mixed $data): void {
		if ($this->config->get('payment_sagepay_server_debug')) {
			// Log
			$log = new \Log('sagepay_server.log');
			$backtrace = debug_backtrace();
			$log->write($backtrace[6]['class'] . '::' . $backtrace[6]['function'] . ' - ' . $title . ': ' . print_r($data, 1));
		}
	}

	/**
	 * Charge
	 */
	public function charge() {
		/*
		 * Used by the checkout to state the module
		 * supports subscriptions.
		 */

		return true;
	}
}
