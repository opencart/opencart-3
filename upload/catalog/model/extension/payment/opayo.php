<?php
/**
 * Class Opayo
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentOpayo extends Model {
	/**
	 * Get Method
	 *
	 * @param array<string, mixed> $address
	 * @param float                $total
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address, float $total): array {
		$this->load->language('extension/payment/opayo');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_opayo_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if ($this->config->get('payment_opayo_total') > 0 && $this->config->get('payment_opayo_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_opayo_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'opayo',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_opayo_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * Get Cards
	 *
	 * @param int $customer_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getCards(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "opayo_card` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `card_id`");

		$card_data = [];

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
	 * Add Card
	 *
	 * @param array<string, mixed> $card_data
	 *
	 * @return int
	 */
	public function addCard(array $card_data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "opayo_card` SET `customer_id` = '" . (int)$card_data['customer_id'] . "', `digits` = '" . $this->db->escape($card_data['Last4Digits']) . "', `expiry` = '" . $this->db->escape($card_data['ExpiryDate']) . "', `type` = '" . $this->db->escape($card_data['CardType']) . "', `token` = '" . $this->db->escape($card_data['Token']) . "'");

		return $this->db->getLastId();
	}

	/**
	 * Update Card
	 *
	 * @param int    $card_id
	 * @param string $token
	 */
	public function updateCard(int $card_id, string $token): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "opayo_card` SET `token` = '" . $this->db->escape($token) . "' WHERE `card_id` = '" . (int)$card_id . "'");
	}

	/**
	 * Get Card
	 *
	 * @param int    $card_id
	 * @param string $token
	 *
	 * @return array|bool
	 */
	public function getCard(int $card_id, string $token): array|bool {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "opayo_card` WHERE (`card_id` = '" . $this->db->escape($card_id) . "' OR `token` = '" . $this->db->escape($token) . "') AND `customer_id` = '" . (int)$this->customer->getId() . "'");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return false;
		}
	}

	/**
	 * Delete Card
	 *
	 * @param int $card_id
	 *
	 * @return void
	 */
	public function deleteCard(int $card_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "opayo_card` WHERE `card_id` = '" . (int)$card_id . "'");
	}

	/**
	 * Add Order
	 *
	 * @param int                  $order_id
	 * @param array<string, mixed> $response_data
	 * @param array<string, mixed> $payment_data
	 * @param string               $card_id
	 *
	 * @return int
	 */
	public function addOrder(int $order_id, array $response_data, array $payment_data, string $card_id): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "opayo_order` SET `order_id` = '" . (int)$order_id . "', `vps_tx_id` = '" . $this->db->escape($response_data['VPSTxId']) . "', `vendor_tx_code` = '" . $this->db->escape($payment_data['VendorTxCode']) . "', `security_key` = '" . $this->db->escape($response_data['SecurityKey']) . "', `tx_auth_no` = '" . $this->db->escape($response_data['TxAuthNo']) . "', `currency_code` = '" . $this->db->escape($payment_data['Currency']) . "', `total` = '" . $this->currency->format($payment_data['Amount'], $payment_data['Currency'], false, false) . "', `card_id` = '" . (int)$card_id . "', `date_added` = NOW(), `date_modified` = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * Get Order
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "opayo_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;

			$order['transactions'] = $this->getOrderTransactions($order['opayo_order_id']);

			return $order;
		} else {
			return [];
		}
	}

	/**
	 * Update Order
	 *
	 * @param array<string, mixed> $order_info
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function updateOrder(array $order_info, array $data): int {
		$this->db->query("UPDATE `" . DB_PREFIX . "opayo_order` SET `security_key` = '" . $this->db->escape($data['SecurityKey']) . "',  `vps_tx_id` = '" . $this->db->escape($data['VPSTxId']) . "', `tx_auth_no` = '" . $this->db->escape($data['TxAuthNo']) . "' WHERE `order_id` = '" . (int)$order_info['order_id'] . "'");

		return $this->db->getLastId();
	}

	/**
	 * Edit Order Subscription Status
	 *
	 * @param int $subscription_id
	 * @param int $status
	 */
	public function editOrderSubscriptionStatus(int $subscription_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "opayo_subscription` SET `status` = '" . (int)$status . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Delete Order
	 *
	 * @param int $vendor_tx_code
	 *
	 * @ereturn void
	 */
	public function deleteOrder(int $vendor_tx_code): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "opayo_order` WHERE `order_id` = '" . (int)$vendor_tx_code . "'");
	}

	/**
	 * Add Transaction
	 *
	 * @param int                  $opayo_order_id
	 * @param string               $type
	 * @param array<string, mixed> $order_info
	 *
	 * @return void
	 */
	public function addTransaction(int $opayo_order_id, string $type, array $order_info): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "opayo_order_transaction` SET `opayo_order_id` = '" . (int)$opayo_order_id . "', `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "', `date_added` = NOW()");
	}

	/**
	 * Get Order Transactions
	 *
	 * @param int $opayo_order_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getOrderTransactions(int $opayo_order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "opayo_order_transaction` WHERE `opayo_order_id` = '" . (int)$opayo_order_id . "'");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * subscriptionPayment
	 *
	 * @param array<string, mixed> $item
	 * @param string               $vendor_tx_code
	 *
	 * @return void
	 */
	public function subscriptionPayment(array $item, string $vendor_tx_code): void {
		$this->load->model('checkout/subscription');
		$this->load->model('checkout/order');

		if ($item['subscription']['trial_status'] == 1) {
			$price = $item['subscription']['trial_price'];

			$trial_amt = $this->currency->format($this->tax->calculate($item['subscription']['trial_price'], $item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], false, false) * $item['quantity'] . ' ' . $this->session->data['currency'];
			$trial_text = sprintf($this->language->get('text_trial'), $trial_amt, $item['subscription']['trial_cycle'], $item['subscription']['trial_frequency'], $item['subscription']['trial_duration']);
		} else {
			$price = $item['subscription']['price'];

			$trial_text = '';
		}

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$opayo_order_info = $this->getOrder($this->session->data['order_id']);

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

		if (date_format($trial_end, 'Y-m-d H:i:s') >= date_format($subscription_end, 'Y-m-d H:i:s')) {
			$subscription_expiry = date_format($trial_end, 'Y-m-d');
		} else {
			$subscription_expiry = date_format($subscription_end, 'Y-m-d');
		}

		$subscription_frequency = date_diff(new \DateTime('now'), new \DateTime(date_format($next_payment, 'Y-m-d H:i:s')))->days;

		$response_data = $this->setPaymentData($order_info, $opayo_order_info, $price, $item['subscription']['subscription_id'], $item['subscription']['name'], $subscription_expiry, $subscription_frequency);

		$this->addSubscriptionOrder($this->session->data['order_id'], $response_data, $item['subscription']['subscription_id'], date_format($trial_end, 'Y-m-d H:i:s'), date_format($subscription_end, 'Y-m-d H:i:s'));

		if ($response_data['Status'] == 'OK') {
			$this->updateSubscriptionOrder($item['subscription']['subscription_id'], date_format($next_payment, 'Y-m-d H:i:s'));

			$this->addOrderTransaction($item['subscription']['subscription_id'], $response_data, 1);
		} else {
			$this->addOrderTransaction($item['subscription']['subscription_id'], $response_data, 4);
		}
	}

	/**
	 * Cron Payment
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function cronPayment(): array {
		$this->load->model('account/subscription');
		$this->load->model('checkout/order');
		$this->load->model('account/order');

		$limit = $this->config->get('config_pagination');

		$subscriptions = $this->model_account_subscription->getSubscriptions(0, $limit);

		$cron_data = [];

		$i = 0;

		foreach ($subscriptions as $subscription) {
			$subscription_info = $this->model_account_subscription->getSubscriptionByOrderProductId($subscription['order_id'], $subscription['order_product_id']);

			if ($subscription_info && $subscription_info['status'] == 1) {
				$opayo_order_info = $this->getOrder($subscription_info['order_id']);

				$today = new \DateTime('now');
				$unlimited = new \DateTime('0000-00-00');
				$next_payment = new \DateTime($subscription_info['date_next']);
				$trial_end = new \DateTime($subscription_info['trial_end']);
				$subscription_end = new \DateTime($subscription_info['subscription_end']);

				$order_info = $this->model_checkout_order->getOrder($subscription_info['order_id']);

				$order_product = $this->model_account_order->getProduct($subscription_info['order_id'], $subscription_info['order_product_id']);

				if ((date_format($today, 'Y-m-d H:i:s') > date_format($next_payment, 'Y-m-d H:i:s')) && (date_format($trial_end, 'Y-m-d H:i:s') > date_format($today, 'Y-m-d H:i:s') || date_format($trial_end, 'Y-m-d H:i:s') == date_format($unlimited, 'Y-m-d H:i:s'))) {
					$price = $this->currency->format($subscription_info['trial_price'], $order_info['currency_code'], false, false);
					$frequency = $subscription_info['trial_frequency'];
					$cycle = $subscription_info['trial_cycle'];
					$next_payment = $this->calculateSchedule($frequency, $next_payment, $cycle);
				} elseif ((date_format($today, 'Y-m-d H:i:s') > date_format($next_payment, 'Y-m-d H:i:s')) && (date_format($subscription_end, 'Y-m-d H:i:s') > date_format($today, 'Y-m-d H:i:s') || date_format($subscription_end, 'Y-m-d H:i:s') == date_format($unlimited, 'Y-m-d H:i:s'))) {
					$price = $this->currency->format($subscription_info['price'], $order_info['currency_code'], false, false);
					$frequency = $subscription_info['frequency'];
					$cycle = $subscription_info['cycle'];
					$next_payment = $this->calculateSchedule($frequency, $next_payment, $cycle);
				} else {
					continue;
				}

				if (date_format($trial_end, 'Y-m-d H:i:s') >= date_format($subscription_end, 'Y-m-d H:i:s')) {
					$subscription_expiry = date_format($trial_end, 'Y-m-d');
				} else {
					$subscription_expiry = date_format($subscription_end, 'Y-m-d');
				}

				$subscription_frequency = date_diff(new \DateTime('now'), new \DateTime(date_format($next_payment, 'Y-m-d H:i:s')))->days;

				$response_data = $this->setPaymentData($order_info, $opayo_order_info, $price, $subscription_info['subscription_id'], $order_product['name'], $subscription_expiry, $subscription_frequency, $i);

				$cron_data[] = $response_data;

				if ($response_data['RepeatResponseData_' . $i++]['Status'] == 'OK') {
					$this->addOrderTransaction($subscription_info['subscription_id'], $response_data, 1);

					$this->updateSubscriptionOrder($subscription_info['subscription_id'], date_format($next_payment, 'Y-m-d H:i:s'));
				} else {
					$this->addOrderTransaction($subscription_info['subscription_id'], $response_data, 4);
				}
			}
		}

		$log = new \Log('opayo_subscription_orders.log');
		$log->write(print_r($cron_data, true));

		return $cron_data;
	}

	/**
	 * Set Payment Data
	 *
	 * @param array<string, mixed> $order_info
	 * @param array<string, mixed> $opayo_order_info
	 * @param float                $price
	 * @param int                  $subscription_id
	 * @param string               $subscription_name
	 * @param string               $subscription_expiry
	 * @param int                  $subscription_frequency
	 * @param mixed|null           $i
	 *
	 * @return array<string, mixed>
	 */
	private function setPaymentData(array $order_info, array $opayo_order_info, float $price, int $subscription_id, string $subscription_name, string $subscription_expiry, int $subscription_frequency, $i = null): array {
		$payment_data = [];

		$url = '';

		// Setting
		$_config = new \Config();
		$_config->load('opayo');

		$config_setting = $_config->get('payze_opayo');

		$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_opayo_setting'));

		// https://en.wikipedia.org/wiki/Opayo
		if ($setting['general']['environment'] == 'live') {
			$url = 'https://live.opayo.eu.elavon.com/gateway/service/repeat.vsp';
			$payment_data['VPSProtocol'] = '4.00';
		} elseif ($setting['general']['environment'] == 'test') {
			$url = 'https://sandbox.opayo.eu.elavon.com/gateway/service/repeat.vsp';
			$payment_data['VPSProtocol'] = '4.00';
		}

		$payment_data['TxType'] = 'REPEAT';
		$payment_data['Vendor'] = $this->config->get('payment_opayo_vendor');
		$payment_data['VendorTxCode'] = $subscription_id . 'SD' . gmdate('YmdHis', time()) . mt_rand(1, 999);
		$payment_data['Amount'] = $this->currency->format($price, $this->session->data['currency'], false, false);
		$payment_data['Currency'] = $this->session->data['currency'];
		$payment_data['Description'] = substr($subscription_name, 0, 100);
		$payment_data['RelatedVPSTxId'] = trim($opayo_order_info['vps_tx_id'], '{}');
		$payment_data['RelatedVendorTxCode'] = $opayo_order_info['vendor_tx_code'];
		$payment_data['RelatedSecurityKey'] = $opayo_order_info['security_key'];
		$payment_data['RelatedTxAuthNo'] = $opayo_order_info['tx_auth_no'];
		$payment_data['COFUsage'] = 'SUBSEQUENT';
		$payment_data['InitiatedType'] = 'MIT';
		$payment_data['MITType'] = 'RECURRING';
		$payment_data['RecurringExpiry'] = $subscription_expiry;
		$payment_data['RecurringFrequency'] = $subscription_frequency;

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
		$this->db->query("INSERT INTO `" . DB_PREFIX . "opayo_subscription` SET `order_id` = '" . (int)$order_id . "', `subscription_id` = '" . (int)$subscription_id . "', `vps_tx_id` = '" . $this->db->escape($response_data['VPSTxId']) . "', `vendor_tx_code` = '" . $this->db->escape($response_data['VendorTxCode']) . "', `security_key` = '" . $this->db->escape($response_data['SecurityKey']) . "', `tx_auth_no` = '" . $this->db->escape($response_data['TxAuthNo']) . "', `date_added` = now(), `date_modified` = now(), `next_payment` = now(), `trial_end` = '" . $trial_end . "', `subscription_end` = '" . $subscription_end . "', `currency_code` = '" . $this->db->escape($response_data['Currency']) . "', `total` = '" . $this->currency->format($response_data['Amount'], $response_data['Currency'], false, false) . "'");
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
		$this->db->query("UPDATE `" . DB_PREFIX . "opayo_subscription` SET `next_payment` = '" . $this->db->escape($next_payment) . "', `date_modified` = NOW() WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Get Subscription Order
	 *
	 * @param int $subscription_id
	 *
	 * @return array
	 */
	private function getSubscriptionOrder(int $subscription_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "opayo_subscription` WHERE `subscription_id` = '" . (int)$subscription_id . "'");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * Add Order Transaction
	 *
	 * @param int                  $subscription_id
	 * @param array<string, mixed> $response_data
	 * @param int                  $type
	 *
	 * @return void
	 */
	private function addOrderTransaction(int $subscription_id, array $response_data, int $type): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "opayo_subscription_transaction` SET `subscription_id` = '" . (int)$subscription_id . "', `date_added` = NOW(), `amount` = '" . (float)$response_data['Amount'] . "', `type` = '" . (int)$type . "'");
	}

	/**
	 * Get Profiles
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getProfiles(): array {
		$order_recurring_data = [];

		$query = $this->db->query("SELECT `or`.`order_id`, `or`.`recurring_id` FROM `" . DB_PREFIX . "order_recurring` `or` JOIN `" . DB_PREFIX . "order` `o` USING(`order_id`) WHERE `o`.`payment_code` = 'opayo'");

		foreach ($query->rows as $recurring) {
			$order_recurring_data[] = $this->getProfile($recurring['order_id'], $recurring['recurring_id']);
		}

		return $order_recurring_data;
	}

	/**
	 * Get Profile
	 *
	 * @param int $order_id
	 * @param int $recurring_id
	 *
	 * @return array<string, mixed>
	 */
	public function getProfile(int $order_id, int $recurring_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE `order_id` = '" . (int)$order_id . "' AND `recurring_id` = '" . (int)$recurring_id . "'");

		return $query->row;
	}

	/**
	 * Update Cron Run Time
	 *
	 * @return void
	 */
	public function updateCronRunTime(): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'opayo' AND `key` = 'payment_opayo_last_cron_run'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'opayo', `key` = 'payment_opayo_last_cron_run', `value` = NOW(), `serialized` = '0'");
	}

	/**
	 * Send Curl
	 *
	 * @param string       $url
	 * @param array<mixed> $payment_data
	 * @param ?int         $i
	 *
	 * @return array<string, string>
	 */
	public function sendCurl(string $url, array $payment_data, $i = null): array {
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
	 * Log
	 *
	 * @param string  $title
	 * @param ?string $data
	 *
	 * @return ?object
	 */
	public function log(string $title, ?string $data): ?object {
		$_config = new \Config();
		$_config->load('opayo');

		$config_setting = $_config->get('opayo_setting');

		$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_opayo_setting'));

		if ($setting['general']['debug']) {
			$log = new \Log('opayo.log');
			$log->write($title . ': ' . print_r($data, 1));
		}

		return null;
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
