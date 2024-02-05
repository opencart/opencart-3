<?php
/**
 * Class Cardconnect
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentCardConnect extends Model {
	/**
	 * getMethod
	 *
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/cardconnect');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_cardconnect_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_cardconnect_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'cardconnect',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_cardconnect_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * getCardTypes
	 */
	public function getCardTypes() {
		$cards = [];

		$cards[] = [
			'text'  => 'Visa',
			'value' => 'VISA'
		];

		$cards[] = [
			'text'  => 'MasterCard',
			'value' => 'MASTERCARD'
		];

		$cards[] = [
			'text'  => 'Discover Card',
			'value' => 'DISCOVER'
		];

		$cards[] = [
			'text'  => 'American Express',
			'value' => 'AMEX'
		];

		return $cards;
	}

	/**
	 * getMonths
	 */
	public function getMonths() {
		$months = [];

		for ($i = 1; $i <= 12; $i++) {
			$months[] = [
				'text'  => sprintf('%02d', $i),
				'value' => sprintf('%02d', $i)
			];
		}

		return $months;
	}

	/**
	 * getYears
	 */
	public function getYears() {
		$years = [];

		$today = getdate();

		for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
			$years[] = [
				'text'  => sprintf('%02d', $i % 100),
				'value' => sprintf('%02d', $i % 100)
			];
		}

		return $years;
	}

	/**
	 * getCard
	 *
	 * @param string $token
	 * @param int    $customer_id
	 *
	 * @return array
	 */
	public function getCard(string $token, int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cardconnect_card` WHERE `token` = '" . $this->db->escape($token) . "' AND `customer_id` = '" . (int)$customer_id . "'");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * getCards
	 *
	 * @param int $customer_id
	 *
	 * @return array
	 */
	public function getCards(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cardconnect_card` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return $query->rows;
	}

	/**
	 * addCard
	 *
	 * @param int    $cardconnect_order_id
	 * @param int    $customer_id
	 * @param string $profileid
	 * @param string $token
	 * @param string $type
	 * @param string $account
	 * @param string $expiry
	 *
	 * @return void
	 */
	public function addCard(int $cardconnect_order_id, int $customer_id, string $profileid, string $token, string $type, string $account, string $expiry): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "cardconnect_card` SET `cardconnect_order_id` = '" . (int)$cardconnect_order_id . "', `customer_id` = '" . (int)$customer_id . "', `profileid` = '" . $this->db->escape($profileid) . "', `token` = '" . $this->db->escape($token) . "', `type` = '" . $this->db->escape($type) . "', `account` = '" . $this->db->escape($account) . "', `expiry` = '" . $this->db->escape($expiry) . "', `date_added` = NOW()");
	}

	/**
	 * deleteCard
	 *
	 * @param string $token
	 * @param int    $customer_id
	 *
	 * @return void
	 */
	public function deleteCard(string $token, int $customer_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "cardconnect_card` WHERE `token` = '" . $this->db->escape($token) . "' AND `customer_id` = '" . (int)$customer_id . "'");
	}

	/**
	 * addOrder
	 *
	 * @param int    $order_id
	 * @param int    $customer_id
	 * @param string $retref
	 * @param string $auth_code
	 * @param float  $total
	 * @param string $currency_code
	 * @param string $payment_method
	 *
	 * @return int
	 */
	public function addOrder(int $order_id, int $customer_id, string $retref, string $auth_code, float $total, string $currency_code, string $payment_method): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "cardconnect_order` SET `order_id` = '" . (int)$order_id . "', `customer_id` = '" . (int)$this->customer->getId() . "', `payment_method` = '" . $this->db->escape($payment_method) . "', `retref` = '" . $this->db->escape($retref) . "', `authcode` = '" . $this->db->escape($auth_code) . "', `currency_code` = '" . $this->db->escape($currency_code) . "', `total` = '" . $this->currency->format($total, $currency_code, false, false) . "', `date_added` = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * addTransaction
	 *
	 * @param int    $cardconnect_order_id
	 * @param string $type
	 * @param string $status
	 * @param string $retref
	 * @param float  $total
	 * @param string $currency_code
	 *
	 * @return void
	 */
	public function addTransaction(int $cardconnect_order_id, string $type, string $status, string $retref, float $total, string $currency_code): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "cardconnect_order_transaction` SET `cardconnect_order_id` = '" . (int)$cardconnect_order_id . "', `type` = '" . $this->db->escape($type) . "', `retref` = '" . $this->db->escape($retref) . "', `amount` = '" . (float)$this->currency->format($total, $currency_code, false, false) . "', `status` = '" . $this->db->escape($status) . "', `date_modified` = NOW(), `date_added` = NOW()");
	}

	/**
	 * getSettlementStatuses
	 *
	 * @param string $merchant_id
	 * @param string $date
	 *
	 * @return array
	 */
	public function getSettlementStatuses(string $merchant_id, string $date): array {
		$this->log('Getting settlement statuses from CardConnect');

		$url = 'https://' . $this->config->get('payment_cardconnect_site') . '.cardconnect.com:' . (($this->config->get('payment_cardconnect_environment') == 'live') ? 8443 : 6443) . '/cardconnect/rest/settlestat?merchid=' . $merchant_id . '&date=' . $date;

		$header = [];

		$header[] = 'Content-type: application/json';
		$header[] = 'Authorization: Basic ' . base64_encode($this->config->get('payment_cardconnect_api_username') . ':' . $this->config->get('payment_cardconnect_api_password'));

		$this->model_extension_payment_cardconnect->log('Header: ' . print_r($header, true));
		$this->model_extension_payment_cardconnect->log('URL: ' . $url);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$response_data = curl_exec($ch);

		if (curl_errno($ch)) {
			$this->model_extension_payment_cardconnect->log('cURL error: ' . curl_errno($ch));
		}

		curl_close($ch);

		$response_data = json_decode($response_data, true);

		$this->log('Response: ' . print_r($response_data, true));

		return $response_data;
	}

	/**
	 * updateTransactionStatusByRetref
	 *
	 * @param string $retref
	 * @param string $status
	 *
	 * @return void
	 */
	public function updateTransactionStatusByRetref(string $retref, string $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "cardconnect_order_transaction` SET `status` = '" . $this->db->escape($status) . "', `date_modified` = NOW() WHERE `retref` = '" . $this->db->escape($retref) . "'");
	}

	/**
	 * updateCronRunTime
	 *
	 * @return void
	 */
	public function updateCronRunTime(): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` = 'payment_cardconnect_cron_time'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'cardconnect', `key` = 'payment_cardconnect_cron_time', `value` = NOW(), `serialized` = '0'");
	}

	/**
	 * Log
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function log(string $message): void {
		if ($this->config->get('payment_cardconnect_debug')) {
			// Log
			$log = new \Log('cardconnect.log');
			$log->write($message);
		}
	}
}
