<?php
/**
 * Class G2apay
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentG2aPay extends Model {
	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE `" . DB_PREFIX . "g2apay_order` (
				`g2apay_order_id` int(11) NOT NULL AUTO_INCREMENT,
				`order_id` int(11) NOT NULL,
				`g2apay_transaction_id` varchar(255) NOT NULL,
				`date_added` datetime NOT NULL,
				`modified` datetime NOT NULL,
				`refund_status` int(1) NOT DEFAULT '0',
				`currency_code` varchar(3) NOT NULL,
				`total` decimal(15,4) NOT NULL,
				KEY `g2apay_transaction_id` (`g2apay_transaction_id`),
				PRIMARY KEY `g2apay_order_id` (`g2apay_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "g2apay_order_transaction` (
			  `g2apay_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `g2apay_order_id` int(11) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `type` enum('payment', 'refund') DEFAULT NULL,
			  `amount` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`g2apay_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "g2apay_order`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "g2apay_order_transaction`");
	}

	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "g2apay_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;
			$order['transactions'] = $this->getTransactions($order['g2apay_order_id'], $query->row['currency_code']);

			return $order;
		} else {
			return [];
		}
	}

	/**
	 * getTotalReleased
	 *
	 * @param int $g2apay_order_id
	 *
	 * @return float
	 */
	public function getTotalReleased(int $g2apay_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "g2apay_order_transaction` WHERE `g2apay_order_id` = '" . (int)$g2apay_order_id . "' AND (`type` = 'payment' OR `type` = 'refund')");

		return (float)$query->row['total'];
	}

	/**
	 * Refund
	 *
	 * @param array $g2apay_order
	 * @param float $amount
	 *
	 * @return array
	 */
	public function refund(array $g2apay_order, float $amount): array {
		if ($g2apay_order && $g2apay_order['refund_status'] != 1) {
			if ($this->config->get('payment_g2apay_environment') == 1) {
				$url = 'https://pay.g2a.com/rest/transactions/' . $g2apay_order['g2apay_transaction_id'];
			} else {
				$url = 'https://www.test.pay.g2a.com/rest/transactions/' . $g2apay_order['g2apay_transaction_id'];
			}

			$refunded_amount = round($amount, 2);
			$string = $g2apay_order['g2apay_transaction_id'] . $g2apay_order['order_id'] . round($g2apay_order['total'], 2) . $refunded_amount . html_entity_decode($this->config->get('payment_g2apay_secret'));
			$hash = hash('sha256', $string);

			$fields = [
				'action' => 'refund',
				'amount' => $refunded_amount,
				'hash'   => $hash
			];

			return $this->sendCurl($url, $fields);
		} else {
			return [];
		}
	}

	/**
	 * updatedRefundStatus
	 *
	 * @param int $g2apay_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateRefundStatus(int $g2apay_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "g2apay_order` SET `refund_status` = '" . (int)$status . "' WHERE `g2apay_order_id` = '" . (int)$g2apay_order_id . "'");
	}

	private function getTransactions(int $g2apay_order_id, string $currency_code): array {
		$transactions = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "g2apay_order_transaction` WHERE `g2apay_order_id` = '" . (int)$g2apay_order_id . "'");

		foreach ($query->rows as $row) {
			$row['amount'] = $this->currency->format($row['amount'], $currency_code, true, true);

			$transactions[] = $row;
		}

		return $transactions;
	}

	/**
	 * addTransaction
	 *
	 * @param int    $g2apay_order_id
	 * @param string $type
	 * @param float  $total
	 *
	 * @return void
	 */
	public function addTransaction(int $g2apay_order_id, string $type, float $total): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "g2apay_order_transaction` SET `g2apay_order_id` = '" . (int)$g2apay_order_id . "',`date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
	}

	/**
	 * getTotalRefunded
	 *
	 * @param int $g2apay_order_id
	 *
	 * @return float
	 */
	public function getTotalRefunded(int $g2apay_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "g2apay_order_transaction` WHERE `g2apay_order_id` = '" . (int)$g2apay_order_id . "' AND `type` = 'refund'");

		return (float)$query->row['total'];
	}

	/**
	 * sendCurl
	 *
	 * @param string $url
	 * @param array  $fields
	 *
	 * @return string
	 */
	public function sendCurl(string $url, array $fields): string {
		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));

		$auth_hash = hash('sha256', $this->config->get('payment_g2apay_api_hash') . $this->config->get('payment_g2apay_username') . html_entity_decode($this->config->get('payment_g2apay_secret')));
		$authorization = $this->config->get('payment_g2apay_api_hash') . ";" . $auth_hash;

		curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: " . $authorization]);

		$response = json_decode(curl_exec($curl));

		curl_close($curl);

		if (is_object($response)) {
			return (string)$response->status;
		} else {
			return str_replace('"', "", $response);
		}
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_g2apay_debug') == 1) {
			$backtrace = debug_backtrace();

			$log = new \Log('g2apay.log');
			$log->write('Origin: ' . $backtrace[6]['class'] . '::' . $backtrace[6]['function']);
			$log->write(print_r($message, 1));
		}
	}
}
