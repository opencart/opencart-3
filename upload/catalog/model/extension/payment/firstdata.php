<?php
/**
 * Class Firstdata
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentFirstdata extends Model {
	/**
	 * Get Method
	 *
	 * @param array<string, mixed> $address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/firstdata');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_firstdata_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_firstdata_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'firstdata',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_firstdata_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * Add Order
	 *
	 * @param array<string, mixed> $order_info
	 * @param string               $order_ref
	 * @param string               $transaction_date
	 *
	 * @return int
	 */
	public function addOrder(array $order_info, string $order_ref, string $transaction_date): int {
		if ($this->config->get('payment_firstdata_auto_settle') == 1) {
			$settle_status = 1;
		} else {
			$settle_status = 0;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "firstdata_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `order_ref` = '" . $this->db->escape($order_ref) . "', `tdate` = '" . $this->db->escape($transaction_date) . "', `date_added` = NOW(), `date_modified` = NOW(), `capture_status` = '" . (int)$settle_status . "', `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `total` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) . "'");

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
		$order = $this->db->query("SELECT * FROM `" . DB_PREFIX . "firstdata_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		return $order->row;
	}

	/**
	 * Add Transaction
	 *
	 * @param int    $fd_order_id
	 * @param string $type
	 * @param float  $total
	 * @param string $currency_code
	 * @param string $currency_value
	 *
	 * @return void
	 */
	public function addTransaction(int $fd_order_id, string $type, float $total = 0, string $currency_code = '', string $currency_value = ''): void {
		if ($total && $currency_code && $currency_value) {
			$amount = $this->currency->format($total, $currency_code, $currency_value, false);
		} else {
			$amount = 0.00;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "firstdata_order_transaction` SET `firstdata_order_id` = '" . (int)$fd_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$amount . "'");
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_firstdata_debug') == 1) {
			// Log
			$log = new \Log('firstdata.log');
			$log->write($message);
		}
	}

	/**
	 * Map Currency
	 *
	 * @param string $code
	 *
	 * @return string
	 */
	public function mapCurrency(string $code): string {
		$currency = [];

		$currency = [
			'GBP' => 826,
			'USD' => 840,
			'EUR' => 978,
		];

		if (array_key_exists($code, $currency)) {
			return $currency[$code];
		} else {
			return '';
		}
	}

	/**
	 * Get Scored Cards
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getStoredCards(): array {
		$customer_id = $this->customer->getId();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "firstdata_card` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return $query->rows;
	}

	/**
	 * Store Card
	 *
	 * @param string $token
	 * @param int    $customer_id
	 * @param string $month
	 * @param string $year
	 * @param string $digits
	 *
	 * @return void
	 */
	public function storeCard(string $token, int $customer_id, string $month, string $year, string $digits): void {
		$existing_card = $this->db->query("SELECT * FROM `" . DB_PREFIX . "firstdata_card` WHERE `token` = '" . $this->db->escape($token) . "' AND `customer_id` = '" . (int)$customer_id . "' LIMIT 1");

		if ($existing_card->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "firstdata_card` SET `expire_month` = '" . $this->db->escape($month) . "', `expire_year` = '" . $this->db->escape($year) . "', `digits` = '" . $this->db->escape($digits) . "'");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "firstdata_card` SET `customer_id` = '" . (int)$customer_id . "', `date_added` = NOW(), `token` = '" . $this->db->escape($token) . "', `expire_month` = '" . $this->db->escape($month) . "', `expire_year` = '" . $this->db->escape($year) . "', `digits` = '" . $this->db->escape($digits) . "'");
		}
	}

	/**
	 * Response Hash
	 *
	 * @param float  $total
	 * @param string $currency
	 * @param string $txn_date
	 * @param string $approval_code
	 *
	 * @return string
	 */
	public function responseHash(float $total, string $currency, string $txn_date, string $approval_code): string {
		$tmp = $total . $this->config->get('payment_firstdata_secret') . $currency . $txn_date . $this->config->get('payment_firstdata_merchant_id') . $approval_code;
		$ascii = bin2hex($tmp);

		return sha1($ascii);
	}

	/**
	 * Update Void Status
	 *
	 * @param int $order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateVoidStatus(int $order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "firstdata_order` SET `void_status` = '" . (int)$status . "' WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Update Capture Status
	 *
	 * @param int $order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateCaptureStatus(int $order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "firstdata_order` SET `capture_status` = '" . (int)$status . "' WHERE `order_id` = '" . (int)$order_id . "'");
	}
}
