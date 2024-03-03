<?php
/**
 * Class Worldpay
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentWorldpay extends Model {
	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "worldpay_order` (
			  `worldpay_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `order_code` varchar(50),
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  `refund_status` int(1) NOT NULL DEFAULT '0',
			  `currency_code` varchar(3) NOT NULL,
			  `total` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`worldpay_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "worldpay_order_transaction` (
			  `worldpay_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `worldpay_order_id` int(11) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `type` enum(\\'payment\\',\\'refund\\') DEFAULT NULL,
			  `amount` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`worldpay_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "worldpay_order_subscription` (
			  `worldpay_order_subscription_id` int(11) NOT NULL AUTO_INCREMENT,
			  `subscription_id` int(11) NOT NULL,
			  `order_id` int(11) NOT NULL,
			  `order_code` varchar(50),
			  `token` varchar(50),
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  `next_payment` datetime NOT NULL,
			  `trial_end` datetime DEFAULT NULL,
			  `subscription_end` datetime DEFAULT NULL,
			  `currency_code` varchar(3) NOT NULL,
			  `total` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`worldpay_order_subscription_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "worldpay_card` (
			  `card_id` int(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` int(11) NOT NULL,
			  `order_id` int(11) NOT NULL,
			  `token` varchar(50) NOT NULL,
			  `digits` varchar(22) NOT NULL,
			  `expiry` varchar(5) NOT NULL,
			  `type` varchar(50) NOT NULL,
			  PRIMARY KEY (`card_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "worldpay_order`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "worldpay_order_transaction`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "worldpay_order_subscription`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "worldpay_card`");
	}

	/**
	 * Refund
	 *
	 * @param int   $order_id
	 * @param float $amount
	 *
	 * @return array<string, string>
	 */
	public function refund(int $order_id, float $amount): array {
		$worldpay_order = $this->getOrder($order_id);

		if ($worldpay_order && $worldpay_order['refund_status'] != 1) {
			$order['refundAmount'] = (int)($amount * 100);
			$url = $worldpay_order['order_code'] . '/refund';

			return $this->sendCurl($url, $order);
		} else {
			return [];
		}
	}

	/**
	 * Update Refund Status
	 *
	 * @param int $worldpay_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateRefundStatus(int $worldpay_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "worldpay_order` SET `refund_status` = '" . (int)$status . "' WHERE `worldpay_order_id` = '" . (int)$worldpay_order_id . "'");
	}

	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array<string, string>
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "worldpay_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = [];

			$order = $query->row;

			$transactions = $this->db->query("SELECT * FROM `" . DB_PREFIX . "worldpay_order_transaction` WHERE `worldpay_order_id` = '" . (int)$query->row['worldpay_order_id'] . "'");

			foreach ($transactions->rows as $transaction) {
				$transaction['amount'] = $this->currency->format($transaction['amount'], $query->row['currency_code'], false);
				$order['transactions'][] = $transaction;
			}

			return $order;
		} else {
			return [];
		}
	}

	/**
	 * Add Transaction
	 *
	 * @param int    $worldpay_order_id
	 * @param string $type
	 * @param float  $total
	 *
	 * @return void
	 */
	public function addTransaction(int $worldpay_order_id, string $type, float $total): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "worldpay_order_transaction` SET `worldpay_order_id` = '" . (int)$worldpay_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
	}

	/**
	 * Get Total Released
	 *
	 * @param int $worldpay_order_id
	 *
	 * @return float
	 */
	public function getTotalReleased(int $worldpay_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "worldpay_order_transaction` WHERE `worldpay_order_id` = '" . (int)$worldpay_order_id . "' AND (`type` = 'payment' OR `type` = 'refund')");

		return (float)$query->row['total'];
	}

	/**
	 * Get Total Refunded
	 *
	 * @param int $worldpay_order_id
	 *
	 * @return float
	 */
	public function getTotalRefunded(int $worldpay_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "worldpay_order_transaction` WHERE `worldpay_order_id` = '" . (int)$worldpay_order_id . "' AND `type` = 'refund'");

		return (float)$query->row['total'];
	}

	/**
	 * sendCurl
	 *
	 * @param string               $url
	 * @param array<string, mixed> $order
	 *
	 * @return array<string, string>
	 */
	public function sendCurl(string $url, array $order): array {
		$response = [];

		$json = json_encode($order);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, 'https://api.worldpay.com/v1/orders/' . $url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			"Authorization: " . $this->config->get('payment_worldpay_service_key'),
			"Content-Type: application/json",
			"Content-Length: " . strlen($json)
		]);

		$result = json_decode(curl_exec($curl));

		curl_close($curl);

		if (isset($result)) {
			$response['status'] = $result->httpStatusCode;
			$response['message'] = $result->message;
			$response['full_details'] = $result;
		} else {
			$response['status'] = 'success';
		}

		return $response;
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_worldpay_debug') == 1) {
			$log = new \Log('worldpay.log');
			$log->write($message);
		}
	}
}
