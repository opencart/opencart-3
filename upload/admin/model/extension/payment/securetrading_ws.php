<?php
/**
 * Class Securetrading Ws
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentSecureTradingWs extends Model {
	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "securetrading_ws_order` (
			  `securetrading_ws_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `md` varchar(1024) DEFAULT NULL,
			  `transaction_reference` varchar(127) DEFAULT NULL,
			  `created` datetime NOT NULL,
			  `modified` datetime NOT NULL,
			  `release_status` int(1) NOT NULL DEFAULT '0',
			  `void_status` int(1) NOT NULL DEFAULT '0',
			  `rebate_status` int(1) NOT NULL DEFAULT '0',
			  `settle_type` int(1) NOT NULL DEFAULT '0',
			  `currency_code` varchar(3) NOT NULL,
			  `total` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`securetrading_ws_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "securetrading_ws_order_transaction` (
			  `securetrading_ws_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `securetrading_ws_order_id` int(11) NOT NULL,
			  `created` datetime NOT NULL,
			  `type` enum(\'auth\',\'payment\',\'rebate\',\'reversed\') DEFAULT NULL,
			  `amount` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`securetrading_ws_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "securetrading_ws_order`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "securetrading_ws_order_transaction`");
	}

	/**
	 * Void
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function void(int $order_id): array {
		$securetrading_ws_order = $this->getOrder($order_id);

		if ($securetrading_ws_order && $securetrading_ws_order['release_status'] == 0) {
			$requestblock_xml = new \SimpleXMLElement('<requestblock></requestblock>');
			$requestblock_xml->addAttribute('version', '3.67');
			$requestblock_xml->addChild('alias', $this->config->get('payment_securetrading_ws_username'));

			$request_node = $requestblock_xml->addChild('request');
			$request_node->addAttribute('type', 'TRANSACTIONUPDATE');

			$filter_node = $request_node->addChild('filter');
			$filter_node->addChild('sitereference', $this->config->get('payment_securetrading_ws_site_reference'));
			$filter_node->addChild('transactionreference', $securetrading_ws_order['transaction_reference']);

			$request_node->addChild('updates')->addChild('settlement')->addChild('settlestatus', 3);

			return $this->call($requestblock_xml->asXML());
		} else {
			return [];
		}
	}

	/**
	 * updateVoidStatus
	 *
	 * @param int $securetrading_ws_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateVoidStatus(int $securetrading_ws_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_ws_order` SET `void_status` = '" . (int)$status . "' WHERE `securetrading_ws_order_id` = '" . (int)$securetrading_ws_order_id . "'");
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
		$securetrading_ws_order = $this->getOrder($order_id);

		$total_released = $this->getTotalReleased($securetrading_ws_order['securetrading_ws_order_id']);

		if ($securetrading_ws_order && $securetrading_ws_order['release_status'] == 0 && $total_released <= $amount) {
			$requestblock_xml = new \SimpleXMLElement('<requestblock></requestblock>');

			$requestblock_xml->addAttribute('version', '3.67');
			$requestblock_xml->addChild('alias', $this->config->get('payment_securetrading_ws_username'));

			$request_node = $requestblock_xml->addChild('request');
			$request_node->addAttribute('type', 'TRANSACTIONUPDATE');

			$filter_node = $request_node->addChild('filter');
			$filter_node->addChild('sitereference', $this->config->get('payment_securetrading_ws_site_reference'));
			$filter_node->addChild('transactionreference', $securetrading_ws_order['transaction_reference']);

			$settlement_node = $request_node->addChild('updates')->addChild('settlement');
			$settlement_node->addChild('settlestatus', 0);
			$settlement_node->addChild('settlemainamount', $amount)->addAttribute('currencycode', $securetrading_ws_order['currency_code']);

			return $this->call($requestblock_xml->asXML());
		} else {
			return null;
		}
	}

	/**
	 * updateReleaseStatus
	 *
	 * @param int $securetrading_ws_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateReleaseStatus(int $securetrading_ws_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_ws_order` SET `release_status` = '" . (int)$status . "' WHERE `securetrading_ws_order_id` = '" . (int)$securetrading_ws_order_id . "'");
	}

	/**
	 * updateRebateStatus
	 *
	 * @param int $securetrading_ws_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateRebateStatus(int $securetrading_ws_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_ws_order` SET `rebate_status` = '" . (int)$status . "' WHERE `securetrading_ws_order_id` = '" . (int)$securetrading_ws_order_id . "'");
	}

	/**
	 * updateForRebate
	 *
	 * @param int    $securetrading_ws_order_id
	 * @param string $order_ref
	 *
	 * @return void
	 */
	public function updateForRebate(int $securetrading_ws_order_id, string $order_ref): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_ws_order` SET `order_ref_previous` = '_multisettle_" . $this->db->escape($order_ref) . "' WHERE `securetrading_ws_order_id` = '" . (int)$securetrading_ws_order_id . "' LIMIT 1");
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
		$securetrading_ws_order = $this->getOrder($order_id);

		if ($securetrading_ws_order && $securetrading_ws_order['rebate_status'] != 1) {
			$requestblock_xml = new \SimpleXMLElement('<requestblock></requestblock>');

			$requestblock_xml->addAttribute('version', '3.67');
			$requestblock_xml->addChild('alias', $this->config->get('payment_securetrading_ws_username'));

			$request_node = $requestblock_xml->addChild('request');
			$request_node->addAttribute('type', 'REFUND');

			$request_node->addChild('merchant')->addChild('orderreference', $order_id);

			$operation_node = $request_node->addChild('operation');
			$operation_node->addChild('accounttypedescription', 'ECOM');
			$operation_node->addChild('parenttransactionreference', $securetrading_ws_order['transaction_reference']);
			$operation_node->addChild('sitereference', $this->config->get('payment_securetrading_ws_site_reference'));

			$billing_node = $request_node->addChild('billing');
			$billing_node->addAttribute('currencycode', $securetrading_ws_order['currency_code']);
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
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "securetrading_ws_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;
			$order['transactions'] = $this->getTransactions($order['securetrading_ws_order_id']);

			return $order;
		} else {
			return [];
		}
	}

	private function getTransactions($securetrading_ws_order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "securetrading_ws_order_transaction` WHERE `securetrading_ws_order_id` = '" . (int)$securetrading_ws_order_id . "'");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * addTransaction
	 *
	 * @param int    $securetrading_ws_order_id
	 * @param string $type
	 * @param float  $total
	 *
	 * @return void
	 */
	public function addTransaction(int $securetrading_ws_order_id, string $type, float $total): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "securetrading_ws_order_transaction` SET `securetrading_ws_order_id` = '" . (int)$securetrading_ws_order_id . "', `created` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
	}

	/**
	 * getTotalReleased
	 *
	 * @param int $securetrading_ws_order_id
	 *
	 * @return float
	 */
	public function getTotalReleased(int $securetrading_ws_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "securetrading_ws_order_transaction` WHERE `securetrading_ws_order_id` = '" . (int)$securetrading_ws_order_id . "' AND (`type` = 'payment' OR `type` = 'rebate')");

		return (float)$query->row['total'];
	}

	/**
	 * getTotalRebated
	 *
	 * @param int $securetrading_ws_order_id
	 *
	 * @return float
	 */
	public function getTotalRebated(int $securetrading_ws_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "securetrading_ws_order_transaction` WHERE `securetrading_ws_order_id` = '" . (int)$securetrading_ws_order_id . "' AND `type` = 'rebate'");

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
		$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_ws_order` SET `refunded` = (`refunded` + " . (float)$amount . ") WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * getCsv
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return string
	 */
	public function getCsv(array $data): string {
		$ch = curl_init();

		$post_data = [];

		$post_data['sitereferences'] = $this->config->get('payment_securetrading_ws_site_reference');
		$post_data['startdate'] = $data['date_from'];
		$post_data['enddate'] = $data['date_to'];
		$post_data['accounttypedescriptions'] = 'ECOM';

		if ($data['detail']) {
			$post_data['optionalfields'] = [
				'parenttransactionreference',
				'accounttypedescription',
				'requesttypedescription',
				'mainamount',
				'currencyiso3a',
				'errorcode',
				'authcode',
				'customerip',
				'fraudrating',
				'orderreference',
				'paymenttypedescription',
				'maskedpan',
				'expirydate',
				'settlestatus',
				'settlemainamount',
				'settleduedate',
				'securityresponsesecuritycode',
				'securityresponseaddress',
				'securityresponsepostcode',
				'billingprefixname',
				'billingfirstname',
				'billingmiddlename',
				'billinglastname',
				'billingpremise',
				'billingstreet',
				'billingtown',
				'billingcounty',
				'billingemail',
				'billingcountryiso2a',
				'billingpostcode',
				'billingtelephones',
				'customerprefixname',
				'customerfirstname',
				'customermiddlename',
				'customerlastname',
				'customerpremise',
				'customerstreet',
				'customertown',
				'customercounty',
				'customeremail',
				'customercountryiso2a',
				'customerpostcode',
				'customertelephones'
			];
		} else {
			$post_data['optionalfields'] = [
				'orderreference',
				'currencyiso3a',
				'errorcode',
				'paymenttypedescription',
				'settlestatus',
				'requesttypedescription',
				'mainamount',
				'billingfirstname',
				'billinglastname'
			];
		}

		if (isset($data['currency']) && $data['currency'] != '') {
			$post_data['currencyiso3as'] = $data['currency'];
		}

		if (isset($data['status']) && $data['status'] != '') {
			$post_data['errorcodes'] = $data['status'];
		}

		if (isset($data['payment_type']) && $data['payment_type'] != '') {
			$post_data['paymenttypedescriptions'] = $data['payment_type'];
		}

		if (isset($data['request']) && $data['request'] != '') {
			$post_data['requesttypedescriptions'] = $data['request'];
		}

		if (isset($data['settle_status']) && $data['settle_status'] != '') {
			$post_data['settlestatuss'] = $data['settle_status'];
		}

		$defaults = [
			CURLOPT_POST           => 1,
			CURLOPT_HEADER         => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_URL            => 'https://myst.securetrading.net/auto/transactions/transactionsearch',
			CURLOPT_FRESH_CONNECT  => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE   => 1,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_HTTPHEADER     => [
				'User-Agent: OpenCart - Secure Trading WS',
				'Authorization: Basic ' . base64_encode($this->config->get('payment_securetrading_ws_csv_username') . ':' . $this->config->get('payment_securetrading_ws_csv_password'))
			],
			CURLOPT_POSTFIELDS => $this->encodePost($post_data)
		];

		curl_setopt_array($ch, $defaults);

		$response = curl_exec($ch);

		if ($response === false) {
			$this->log->write('Secure Trading WS CURL Error: (' . curl_errno($ch) . ') ' . curl_error($ch));
		}

		curl_close($ch);

		if ((empty($response) || $response === 'No records found for search') || (preg_match('/401 Authorization Required/', $response))) {
			return '';
		} else {
			return $response;
		}
	}

	private function encodePost($data) {
		$params = [];

		foreach ($data as $key => $value) {
			if (!empty($value) && is_array($value)) {
				foreach ($value as $v) {
					$params[] = $key . '=' . rawurlencode($v);
				}
			} else {
				$params[] = $key . '=' . rawurlencode($value);
			}
		}

		return implode('&', $params);
	}

	/**
	 * Call
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array
	 */
	public function call($data): array {
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
				'User-Agent: OpenCart - Secure Trading WS',
				'Content-Length: ' . strlen($data),
				'Authorization: Basic ' . base64_encode($this->config->get('payment_securetrading_ws_username') . ':' . $this->config->get('payment_securetrading_ws_password'))
			],
			CURLOPT_POSTFIELDS => $data
		];

		curl_setopt_array($ch, $defaults);

		$response = curl_exec($ch);

		if ($response === false) {
			$this->log->write('Secure Trading WS CURL Error: (' . curl_errno($ch) . ') ' . curl_error($ch));
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
		$log = new \Log('securetrading_ws.log');
		$log->write($message);
	}
}
