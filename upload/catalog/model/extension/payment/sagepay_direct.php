<?php
/**
 * Class Sagepay Direct
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentSagePayDirect extends Model {
	/**
	 * @param array $address
	 *
	 * getMethod
	 *
	 * @return array
	 */
	public function getMethod(array $address): array {
		$this->load->language('extension/payment/sagepay_direct');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_sagepay_direct_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_sagepay_direct_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'sagepay_direct',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_sagepay_direct_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * getCards
	 *
	 * @param int $customer_id
	 *
	 * @return array
	 */
	public function getCards(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_direct_card` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `card_id`");

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
	 * addCard
	 *
	 * @param array $card_data
	 *
	 * @return int
	 */
	public function addCard(array $card_data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sagepay_direct_card` SET `customer_id` = '" . $this->db->escape($card_data['customer_id']) . "', `digits` = '" . $this->db->escape($card_data['Last4Digits']) . "', `expiry` = '" . $this->db->escape($card_data['ExpiryDate']) . "', `type` = '" . $this->db->escape($card_data['CardType']) . "', `token` = '" . $this->db->escape($card_data['Token']) . "'");

		return $this->db->getLastId();
	}

	/**
	 * updateCard
	 *
	 * @param int    $card_id
	 * @param string $token
	 *
	 * @return void
	 */
	public function updateCard(int $card_id, string $token): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_direct_card` SET `token` = '" . $this->db->escape($token) . "' WHERE `card_id` = '" . (int)$card_id . "'");
	}

	/**
	 * getCard
	 *
	 * @param int    $card_id
	 * @param string $token
	 *
	 * @return array
	 */
	public function getCard(int $card_id, string $token): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_direct_card` WHERE (`card_id` = '" . $this->db->escape($card_id) . "' OR `token` = '" . $this->db->escape($token) . "') AND `customer_id` = '" . (int)$this->customer->getId() . "'");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * deleteCard
	 *
	 * @param int $card_id
	 *
	 * @return void
	 */
	public function deleteCard(int $card_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "sagepay_direct_card` WHERE `card_id` = '" . (int)$card_id . "'");
	}

	/**
	 * addOrder
	 *
	 * @param int   $order_id
	 * @param array $response_data
	 * @param array $payment_data
	 * @param int   $card_id
	 *
	 * @return int
	 */
	public function addOrder(int $order_id, array $response_data, array $payment_data, int $card_id): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sagepay_direct_order` SET `order_id` = '" . (int)$order_id . "', `vps_tx_id` = '" . $this->db->escape($response_data['VPSTxId']) . "', `vendor_tx_code` = '" . $this->db->escape($payment_data['VendorTxCode']) . "', `security_key` = '" . $this->db->escape($response_data['SecurityKey']) . "', `tx_auth_no` = '" . $this->db->escape($response_data['TxAuthNo']) . "', `date_added` = NOW(), `date_modified` = NOW(), `currency_code` = '" . $this->db->escape($payment_data['Currency']) . "', `total` = '" . $this->currency->format($payment_data['Amount'], $payment_data['Currency'], false, false) . "', `card_id` = '" . $this->db->escape($card_id) . "'");

		return $this->db->getLastId();
	}

	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_direct_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;
			$order['transactions'] = $this->getTransactions($order['sagepay_direct_order_id']);

			return $order;
		} else {
			return [];
		}
	}

	/**
	 * updateOrder
	 *
	 * @param array $order_info
	 * @param array $data
	 *
	 * @return int
	 */
	public function updateOrder(array $order_info, array $data): int {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_direct_order` SET `security_key` = '" . $this->db->escape($data['SecurityKey']) . "', `vps_tx_id` = '" . $this->db->escape($data['VPSTxId']) . "', `tx_auth_no` = '" . $this->db->escape($data['TxAuthNo']) . "' WHERE `order_id` = '" . (int)$order_info['order_id'] . "'");

		return $this->db->getLastId();
	}

	/**
	 * deleteOrder
	 *
	 * @param int $vendor_tx_code
	 *
	 * @return void
	 */
	public function deleteOrder(int $vendor_tx_code): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "sagepay_direct_order` WHERE `order_id` = '" . (int)$vendor_tx_code . "'");
	}

	/**
	 * addTransaction
	 *
	 * @param int   $sagepay_direct_order_id
	 * @param int   $type
	 * @param array $order_info
	 *
	 * @return void
	 */
	public function addTransaction(int $sagepay_direct_order_id, int $type, array $order_info): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sagepay_direct_order_transaction` SET `sagepay_direct_order_id` = '" . (int)$sagepay_direct_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "'");
	}

	private function getTransactions(int $sagepay_direct_order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_direct_order_transaction` WHERE `sagepay_direct_order_id` = '" . (int)$sagepay_direct_order_id . "'");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * recurringPayment
	 *
	 * @param array $item
	 * @param int   $vendor_tx_code
	 *
	 * @return void
	 */
	public function recurringPayment(array $item, int $vendor_tx_code): void {
		// Subscriptions
		$this->load->model('checkout/subscription');

		// Sagepay Direct
		$this->load->model('extension/payment/sagepay_direct');

		// Trial information
		if ($item['trial_status'] == 1) {
			$price = $item['trial_price'];

			$trial_amt = $this->currency->format($this->tax->calculate($item['trial_price'], $item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], false, false) * $item['quantity'] . ' ' . $this->session->data['currency'];
			$trial_text = sprintf($this->language->get('text_trial'), $trial_amt, $item['trial_cycle'], $item['trial_frequency'], $item['trial_duration']);
		} else {
			$price = $item['price'];

			$trial_text = '';
		}

		$subscription_amt = $this->currency->format($this->tax->calculate($item['price'], $item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], false, false) * $item['quantity'] . ' ' . $this->session->data['currency'];

		$item['description'] = [];

		$subscription_description = $trial_text . sprintf($this->language->get('text_subscription'), $subscription_amt, $item['cycle'], $item['frequency']);

		if ($item['duration'] > 0) {
			$subscription_description .= sprintf($this->language->get('text_length'), $item['duration']);
		}

		$item['description'] = $subscription_description;

		// Create new subscription and set to pending status as no payment has been made yet.
		$subscription_id = $this->model_checkout_subscription->addSubscription($this->session->data['order_id'], $item);

		$this->model_checkout_subscription->editReference($subscription_id, $vendor_tx_code);

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$sagepay_order_info = $this->getOrder($this->session->data['order_id']);

		$response_data = $this->setPaymentData($order_info, $sagepay_order_info, $price, $subscription_id, $item['name']);

		$next_payment = new \DateTime('now');
		$trial_end = new \DateTime('now');
		$subscription_end = new \DateTime('now');

		if ($item['trial_status'] == 1 && $item['trial_duration'] != 0) {
			$next_payment = $this->calculateSchedule($item['trial_frequency'], $next_payment, $item['trial_cycle']);
			$trial_end = $this->calculateSchedule($item['trial_frequency'], $trial_end, $item['trial_cycle'] * $item['trial_duration']);
		} elseif ($item['trial_status'] == 1) {
			$next_payment = $this->calculateSchedule($item['trial_frequency'], $next_payment, $item['trial_cycle']);

			$trial_end = new \DateTime('0000-00-00');
		}

		if ($trial_end > $subscription_end && $item['duration'] != 0) {
			$subscription_end = new \DateTime(date_format($trial_end, 'Y-m-d H:i:s'));

			$subscription_end = $this->calculateSchedule($item['frequency'], $subscription_end, $item['cycle'] * $item['duration']);
		} elseif ($trial_end == $subscription_end && $item['duration'] != 0) {
			$next_payment = $this->calculateSchedule($item['frequency'], $next_payment, $item['cycle']);
			$subscription_end = $this->calculateSchedule($item['frequency'], $subscription_end, $item['cycle'] * $item['duration']);
		} elseif ($trial_end > $subscription_end && $item['duration'] == 0) {
			$subscription_end = new \DateTime('0000-00-00');
		} elseif ($trial_end == $subscription_end && $item['duration'] == 0) {
			$next_payment = $this->calculateSchedule($item['frequency'], $next_payment, $item['cycle']);
			$subscription_end = new \DateTime('0000-00-00');
		}

		$this->addRecurringOrder($this->session->data['order_id'], $response_data, $subscription_id, date_format($trial_end, 'Y-m-d H:i:s'), date_format($subscription_end, 'Y-m-d H:i:s'));

		$transaction = [
			'order_id'       => $this->session->data['order_id'],
			'description'    => $response_data['Status'],
			'amount'         => $price,
			'payment_method' => $order_info['payment_method'],
			'payment_code'   => $order_info['payment_code']
		];

		if ($response_data['Status'] == 'OK') {
			$this->updateRecurringOrder($subscription_id, date_format($next_payment, 'Y-m-d H:i:s'));

			$this->addRecurringTransaction($subscription_id, $response_data, $transaction, 1);
		} else {
			$this->addRecurringTransaction($subscription_id, $response_data, $transaction, 4);
		}
	}

	private function setPaymentData(array $order_info, array $sagepay_order_info, float $price, int $subscription_id, string $recurring_name, $i = null): array {
		$url = '';

		if ($this->config->get('payment_sagepay_direct_test') == 'live') {
			$url = 'https://live.sagepay.com/gateway/service/repeat.vsp';
			$payment_data['VPSProtocol'] = '3.00';
		} elseif ($this->config->get('payment_sagepay_direct_test') == 'test') {
			$url = 'https://test.sagepay.com/gateway/service/repeat.vsp';
			$payment_data['VPSProtocol'] = '3.00';
		} elseif ($this->config->get('payment_sagepay_direct_test') == 'sim') {
			$url = 'https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRepeatTx';
			$payment_data['VPSProtocol'] = '2.23';
		}

		$payment_data['TxType'] = 'REPEAT';
		$payment_data['Vendor'] = $this->config->get('payment_sagepay_direct_vendor');
		$payment_data['VendorTxCode'] = $subscription_id . 'RSD' . date('YmdHis') . mt_rand(1, 999);
		$payment_data['Amount'] = $this->currency->format($price, $this->session->data['currency'], false, false);
		$payment_data['Currency'] = $this->session->data['currency'];
		$payment_data['Description'] = substr($recurring_name, 0, 100);
		$payment_data['RelatedVPSTxId'] = trim($sagepay_order_info['vps_tx_id'], '{}');
		$payment_data['RelatedVendorTxCode'] = $sagepay_order_info['vendor_tx_code'];
		$payment_data['RelatedSecurityKey'] = $sagepay_order_info['security_key'];
		$payment_data['RelatedTxAuthNo'] = $sagepay_order_info['tx_auth_no'];

		if ($order_info['shipping_lastname']) {
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
	 *
	 * @return array
	 */
	public function cronPayment(): array {
		// Orders
		$this->load->model('account/order');

		$i = 0;
		$subscriptions = $this->getProfiles();
		$cron_data = [];

		foreach ($subscriptions as $subscription) {
			$recurring_order = $this->getRecurringOrder($subscription['subscription_id']);

			$today = new \DateTime('now');
			$unlimited = new \DateTime('0000-00-00');
			$next_payment = new \DateTime($recurring_order['next_payment']);
			$trial_end = new \DateTime($recurring_order['trial_end']);
			$subscription_end = new \DateTime($recurring_order['subscription_end']);

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
				$this->addRecurringTransaction($subscription['subscription_id'], $response_data, $transaction, 1);

				$next_payment = $this->calculateSchedule($frequency, $next_payment, $cycle);
				$next_payment = date_format($next_payment, 'Y-m-d H:i:s');

				$this->updateRecurringOrder($subscription['subscription_id'], $next_payment);
			} else {
				$this->addRecurringTransaction($subscription['subscription_id'], $response_data, $transaction, 4);
			}
		}

		// Log
		$log = new \Log('sagepay_direct_recurring_orders.log');
		$log->write(print_r($cron_data, 1));

		return $cron_data;
	}

	private function calculateSchedule(string $frequency, string $next_payment, int $cycle): string {
		if ($frequency == 'semi_month') {
			// https://stackoverflow.com/a/35473574
			$day = date_create_from_format('j M, Y', $next_payment->date);
			$day = date_create($day);
			$day = date_format($day, 'd');
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

	private function addRecurringOrder(int $order_id, array $response_data, int $order_recurring_id, string $trial_end, string $subscription_end): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sagepay_direct_order_recurring` SET `order_id` = '" . (int)$order_id . "', `order_recurring_id` = '" . (int)$order_recurring_id . "', `vps_tx_id` = '" . $this->db->escape($response_data['VPSTxId']) . "', `vendor_tx_code` = '" . $this->db->escape($response_data['VendorTxCode']) . "', `security_key` = '" . $this->db->escape($response_data['SecurityKey']) . "', `tx_auth_no` = '" . $this->db->escape($response_data['TxAuthNo']) . "', `date_added` = NOW(), `date_modified` = NOW(), `next_payment` = NOW(), `trial_end` = '" . $this->db->escape($trial_end) . "', `subscription_end` = '" . $this->db->escape($subscription_end) . "', `currency_code` = '" . $this->db->escape($response_data['Currency']) . "', `total` = '" . $this->currency->format($response_data['Amount'], $response_data['Currency'], false, false) . "'");
	}

	private function updateRecurringOrder(int $order_recurring_id, string $next_payment): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_direct_order_recurring` SET `next_payment` = '" . $this->db->escape($next_payment) . "', `date_modified` = NOW() WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");
	}

	private function getRecurringOrder($order_recurring_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_direct_order_recurring` WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");

		return $query->row;
	}

	private function addRecurringTransaction(int $subscription_id, array $response_data, array $transaction, int $type): void {
		// Subscriptions
		$this->load->model('account/subscription');

		$subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

		if ($subscription_info) {
			// Subscriptions
			$this->load->model('checkout/subscription');

			$this->model_checkout_subscription->editReference($subscription_id, $response_data['VendorTxCode']);

			$this->model_account_subscription->editStatus($subscription_id, $type);
			$this->model_account_subscription->addTransaction($subscription_id, $transaction['order_id'], $transaction['description'], $transaction['amount'], $type, $transaction['payment_method'], $transaction['payment_code']);
		}
	}

	private function getProfiles(): array {
		$subscriptions = [];

		// Subscriptions
		$this->load->model('account/subscription');

		$sql = "SELECT `s`.`subscription_id` FROM `" . DB_PREFIX . "subscription` `s` JOIN `" . DB_PREFIX . "order` `o` USING(`order_id`) WHERE `o`.`payment_code` = 'sagepay_direct'";

		$query = $this->db->query($sql);

		foreach ($query->rows as $subscription) {
			$subscriptions[] = $this->model_account_subscription->getSubscription($subscription['subscription_id']);
		}

		return $subscriptions;
	}

	/**
	 * updateCronJobRunTime
	 *
	 * @return void
	 */
	public function updateCronJobRunTime(): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'sagepay_direct' AND `key` = 'payment_sagepay_direct_last_cron_job_run'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'sagepay_direct', `key` = 'payment_sagepay_direct_last_cron_job_run', `value` = NOW(), `serialized` = '0'");
	}

	/**
	 * sendCurl
	 *
	 * @param string $url
	 * @param array  $payment_data
	 * @param int    $i
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
		if ($this->config->get('payment_sagepay_direct_debug')) {
			// Log
			$log = new \Log('sagepay_direct.log');
			$backtrace = debug_backtrace();
			$log->write($backtrace[6]['class'] . '::' . $backtrace[6]['function'] . ' - ' . $title . ': ' . print_r($data, true));
		}
	}

	/**
	 * subscriptionPayments
	 */
	public function subscriptionPayments() {
		/*
		 * Used by the checkout to state the module
		 * supports subscriptions.
		 */

		return true;
	}
}
