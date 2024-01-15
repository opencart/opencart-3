<?php
/**
 * Class Securetrading Pp
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentSecureTradingPp extends Model {
	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "securetrading_pp_order` (
			  `securetrading_pp_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `transaction_reference` varchar(127) DEFAULT NULL,
			  `created` datetime NOT NULL,
			  `modified` datetime NOT NULL,
			  `release_status` int(1) NOT NULL DEFAULT '0',
			  `void_status` int(1) NOT NULL DEFAULT '0',
			  `rebate_status` int(1) NOT NULL DEFAULT '0',
			  `settle_type` int(1) NOT NULL DEFAULT '0',
			  `currency_code` varchar(3) NOT NULL,
			  `total` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`securetrading_pp_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "securetrading_pp_order_transaction` (
			  `securetrading_pp_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `securetrading_pp_order_id` int(11) NOT NULL,
			  `created` DATETIME NOT NULL,
			  `type` enum('auth', 'payment', 'rebate', 'reversed') DEFAULT NULL,
			  `amount` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`securetrading_pp_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "securetrading_pp_order`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "securetrading_pp_order_transaction`");
	}

	/**
	 * Void
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function void(int $order_id): array {
		$securetrading_pp_order = $this->getOrder($order_id);

		if ($securetrading_pp_order && $securetrading_pp_order['release_status'] == 0) {
			$requestblock_xml = new \SimpleXMLElement('<requestblock></requestblock>');
			$requestblock_xml->addAttribute('version', '3.67');
			$requestblock_xml->addChild('alias', $this->config->get('payment_securetrading_pp_webservice_username'));

			$request_node = $requestblock_xml->addChild('request');
			$request_node->addAttribute('type', 'TRANSACTIONUPDATE');

			$filter_node = $request_node->addChild('filter');
			$filter_node->addChild('sitereference', $this->config->get('payment_securetrading_pp_site_reference'));
			$filter_node->addChild('transactionreference', $securetrading_pp_order['transaction_reference']);

			$request_node->addChild('updates')->addChild('settlement')->addChild('settlestatus', 3);

			return $this->call($requestblock_xml->asXML());
		} else {
			return [];
		}
	}

	/**
	 * updateVoidStatus
	 *
	 * @param int $securetrading_pp_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateVoidStatus(int $securetrading_pp_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_pp_order` SET `void_status` = '" . (int)$status . "' WHERE `securetrading_pp_order_id` = '" . (int)$securetrading_pp_order_id . "'");
	}

	/**
	 * Release
	 *
	 * @param int   $order_id
	 * @param float $amount
	 *
	 * @return object|null
	 */
	public function release(int $order_id, float $amount): ?object {
		$securetrading_pp_order = $this->getOrder($order_id);

		$total_released = $this->getTotalReleased($securetrading_pp_order['securetrading_pp_order_id']);

		if ($securetrading_pp_order && $securetrading_pp_order['release_status'] == 0 && $total_released <= $amount) {
			$requestblock_xml = new \SimpleXMLElement('<requestblock></requestblock>');

			$requestblock_xml->addAttribute('version', '3.67');
			$requestblock_xml->addChild('alias', $this->config->get('payment_securetrading_pp_webservice_username'));

			$request_node = $requestblock_xml->addChild('request');
			$request_node->addAttribute('type', 'TRANSACTIONUPDATE');

			$filter_node = $request_node->addChild('filter');
			$filter_node->addChild('sitereference', $this->config->get('payment_securetrading_pp_site_reference'));
			$filter_node->addChild('transactionreference', $securetrading_pp_order['transaction_reference']);

			$settlement_node = $request_node->addChild('updates')->addChild('settlement');
			$settlement_node->addChild('settlestatus', 0);
			$settlement_node->addChild('settlemainamount', $amount)->addAttribute('currencycode', $securetrading_pp_order['currency_code']);

			return $this->call($requestblock_xml->asXML());
		} else {
			return null;
		}
	}

	/**
	 * updateReleaseStatus
	 *
	 * @param int $securetrading_pp_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateReleaseStatus(int $securetrading_pp_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_pp_order` SET `release_status` = '" . (int)$status . "' WHERE `securetrading_pp_order_id` = '" . (int)$securetrading_pp_order_id . "'");
	}

	/**
	 * updateRebateStatus
	 *
	 * @param int $securetrading_pp_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateRebateStatus(int $securetrading_pp_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_pp_order` SET `rebate_status` = '" . (int)$status . "' WHERE `securetrading_pp_order_id` = '" . (int)$securetrading_pp_order_id . "'");
	}

	/**
	 * updateForRebate
	 *
	 * @param int    $securetrading_pp_order_id
	 * @param string $order_ref
	 *
	 * @return void
	 */
	public function updateForRebate(int $securetrading_pp_order_id, string $order_ref): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_pp_order` SET `order_ref_previous` = '_multisettle_" . $this->db->escape($order_ref) . "' WHERE `securetrading_pp_order_id` = '" . (int)$securetrading_pp_order_id . "' LIMIT 1");
	}

	/**
	 * Rebate
	 *
	 * @param int   $order_id
	 * @param float $refunded_amount
	 *
	 * @return array
	 */
	public function rebate(int $order_id, float $refunded_amount): array {
		$securetrading_pp_order = $this->getOrder($order_id);

		if ($securetrading_pp_order && $securetrading_pp_order['rebate_status'] != 1) {
			$requestblock_xml = new \SimpleXMLElement('<requestblock></requestblock>');

			$requestblock_xml->addAttribute('version', '3.67');
			$requestblock_xml->addChild('alias', $this->config->get('payment_securetrading_pp_webservice_username'));

			$request_node = $requestblock_xml->addChild('request');
			$request_node->addAttribute('type', 'REFUND');

			$request_node->addChild('merchant')->addChild('orderreference', $order_id);

			$operation_node = $request_node->addChild('operation');
			$operation_node->addChild('accounttypedescription', 'ECOM');
			$operation_node->addChild('parenttransactionreference', $securetrading_pp_order['transaction_reference']);
			$operation_node->addChild('sitereference', $this->config->get('payment_securetrading_pp_site_reference'));

			$billing_node = $request_node->addChild('billing');
			$billing_node->addAttribute('currencycode', $securetrading_pp_order['currency_code']);
			$billing_node->addChild('amount', str_replace('.', '', $refunded_amount));

			return $this->call($requestblock_xml->asXML());
		} else {
			return [];
		}
	}

	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "securetrading_pp_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;
			$order['transactions'] = $this->getTransactions($order['securetrading_pp_order_id']);

			return $order;
		} else {
			return [];
		}
	}

	private function getTransactions($securetrading_pp_order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "securetrading_pp_order_transaction` WHERE `securetrading_pp_order_id` = '" . (int)$securetrading_pp_order_id . "'");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * addHistory
	 * 
	 * @param int    $order_id
	 * @param int    $order_status_id
	 * @param string $comment
	 * @param bool   $notify
	 * 
	 * @return void
	 */
	public function addHistory(int $order_id, int $order_status_id, string $comment = '', bool $notify = false): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET `order_id` = '" . (int)$order_id . "', `order_status_id` = '" . (int)$order_status_id . "', `comment` = '" . $this->db->escape($comment) . "', `notify` = '" . (bool)$notify . "', `date_added` = NOW()");
	}

	/**
	 * addTransaction
	 *
	 * @param int    $securetrading_pp_order_id
	 * @param string $type
	 * @param float  $total
	 *
	 * @return void
	 */
	public function addTransaction(int $securetrading_pp_order_id, string $type, float $total): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "securetrading_pp_order_transaction` SET `securetrading_pp_order_id` = '" . (int)$securetrading_pp_order_id . "', `created` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
	}

	/**
	 * getTotalReleased
	 *
	 * @param int $securetrading_pp_order_id
	 *
	 * @return float
	 */
	public function getTotalReleased(int $securetrading_pp_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "securetrading_pp_order_transaction` WHERE `securetrading_pp_order_id` = '" . (int)$securetrading_pp_order_id . "' AND (`type` = 'payment' OR `type` = 'rebate')");

		return (float)$query->row['total'];
	}

	/**
	 * getTotalRebated
	 *
	 * @param int $securetrading_pp_order_id
	 *
	 * @return float
	 */
	public function getTotalRebated(int $securetrading_pp_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "securetrading_pp_order_transaction` WHERE `securetrading_pp_order_id` = '" . (int)$securetrading_pp_order_id . "' AND `type` = 'rebate'");

		return (float)$query->row['total'];
	}

	/**
	 * increaseRefundedAmount
	 *
	 * @param int   $order_id
	 * @param float $amount
	 *
	 * @return void
	 */
	public function increaseRefundedAmount(int $order_id, float $amount): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_pp_order` SET `refunded` = (`refunded` + " . (float)$amount . ") WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Call
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function call(array $data): array {
		$ch = curl_init();

		$defaults = [
			CURLOPT_POST           => 1,
			CURLOPT_HEADER         => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_URL            => 'https://webservices.securetrading.net/xml/',
			CURLOPT_FRESH_CONNECT  => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE   => 1,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_HTTPHEADER     => [
				'User-Agent: OpenCart - Secure Trading PP',
				'Content-Length: ' . strlen($data),
				'Authorization: Basic ' . base64_encode($this->config->get('payment_securetrading_pp_webservice_username') . ':' . $this->config->get('payment_securetrading_pp_webservice_password'))
			],
			CURLOPT_POSTFIELDS => $data
		];

		curl_setopt_array($ch, $defaults);

		$response = curl_exec($ch);

		if ($response === false) {
			$this->log->write('Secure Trading PP CURL Error: (' . curl_errno($ch) . ') ' . curl_error($ch));
		}

		curl_close($ch);

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
		$log = new \Log('securetrading_pp.log');
		$log->write($message);
	}
}
