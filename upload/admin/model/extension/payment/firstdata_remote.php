<?php
/**
 * Class Firstdata Remote
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentFirstdataRemote extends Model {
	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "firstdata_remote_order` (
			  `firstdata_remote_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `order_ref` varchar(50) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  `tdate` varchar(30) NOT NULL,
			  `capture_status` int(1) NOT NULL DEFAULT '0',
			  `void_status` int(1) NOT NULL DEFAULT '0',
			  `refund_status` int(1) NOT NULL DEFAULT '0',
			  `currency_code` varchar(3) NOT NULL,
			  `authcode` varchar(30) NOT NULL,
			  `total` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`firstdata_remote_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "firstdata_remote_order_transaction` (
			  `firstdata_remote_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `firstdata_remote_order_id` int(11) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `type` enum(\\'auth\\',\\'payment\\',\\'refund\\',\\'void\\') DEFAULT NULL,
			  `amount` DECIMAL(15,4) NOT NULL,
			  PRIMARY KEY (`firstdata_remote_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "firstdata_remote_card` (
			  `firstdata_remote_card_id` int(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` int(11) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `digits` varchar(4) NOT NULL,
			  `expire_month` int(2) NOT NULL,
			  `expire_year` int(2) NOT NULL,
			  `card_type` varchar(15) NOT NULL,
			  `token` varchar(64) NOT NULL,
			  PRIMARY KEY (`firstdata_remote_card_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "firstdata_remote_order`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "firstdata_remote_order_transaction`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "firstdata_remote_card`");
	}

	/**
	 * Call
	 *
	 * @param string $xml
	 *
	 * @return false|SimpleXMLElement
	 */
	public function call(string $xml): false|SimpleXMLElement {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://test.ipg-online.com/ipgapi/services');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
		curl_setopt($ch, CURLOPT_HTTPAUTH, 'CURLAUTH_BASIC');
		curl_setopt($ch, CURLOPT_USERPWD, $this->config->get('payment_firstdata_remote_user_id') . ':' . $this->config->get('payment_firstdata_remote_password'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_CAINFO, $this->config->get('payment_firstdata_remote_ca'));
		curl_setopt($ch, CURLOPT_SSLCERT, $this->config->get('payment_firstdata_remote_certificate'));
		curl_setopt($ch, CURLOPT_SSLKEY, $this->config->get('payment_firstdata_remote_key'));
		curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->config->get('payment_firstdata_remote_key_pw'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//curl_setopt($ch, CURLOPT_STDERR, fopen(DIR_LOGS . '/headers.txt', 'w+'));
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		$response = curl_exec($ch);

		$this->logger('Post data: ' . print_r($this->request->post, 1));
		$this->logger('Request: ' . $xml);
		$this->logger('Curl error #: ' . curl_errno($ch));
		$this->logger('Curl error text: ' . curl_error($ch));
		$this->logger('Curl response info: ' . print_r(curl_getinfo($ch), 1));
		$this->logger('Curl response: ' . $response);

		curl_close($ch);

		return $response;
	}

	/**
	 * Void
	 *
	 * @param int    $order_ref
	 * @param string $tdate
	 *
	 * @return array<string, mixed>
	 */
	public function void(int $order_ref, string $tdate): array {
		$response = [];

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
		$xml .= '<SOAP-ENV:Header />';
		$xml .= '<SOAP-ENV:Body>';
		$xml .= '<ipgapi:IPGApiOrderRequest xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1" xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi">';
		$xml .= '<v1:Transaction>';
		$xml .= '<v1:CreditCardTxType>';
		$xml .= '<v1:Type>void</v1:Type>';
		$xml .= '</v1:CreditCardTxType>';
		$xml .= '<v1:TransactionDetails>';
		$xml .= '<v1:OrderId>' . $order_ref . '</v1:OrderId>';
		$xml .= '<v1:TDate>' . $tdate . '</v1:TDate>';
		$xml .= '</v1:TransactionDetails>';
		$xml .= '</v1:Transaction>';
		$xml .= '</ipgapi:IPGApiOrderRequest>';
		$xml .= '</SOAP-ENV:Body>';
		$xml .= '</SOAP-ENV:Envelope>';

		$xml = simplexml_load_string($this->call($xml));

		$xml->registerXPathNamespace('ipgapi', 'http://ipg-online.com/ipgapi/schemas/ipgapi');
		$xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');

		$fault = $xml->xpath('//soap:Fault');

		$response['fault'] = '';

		if (!empty($fault[0]) && isset($fault[0]->detail)) {
			$response['fault'] = (string)$fault[0]->detail;
		}

		$string = $xml->xpath('//ipgapi:ErrorMessage');
		$response['error'] = (isset($string[0]) ? (string)$string[0] : '');

		$string = $xml->xpath('//ipgapi:TransactionResult');
		$response['transaction_result'] = (isset($string[0]) ? (string)$string[0] : '');

		return $response;
	}

	/**
	 * Update Void Status
	 *
	 * @param int $firstdata_remote_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateVoidStatus(int $firstdata_remote_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "firstdata_remote_order` SET `void_status` = '" . (int)$status . "' WHERE `firstdata_remote_order_id` = '" . (int)$firstdata_remote_order_id . "'");
	}

	/**
	 * Capture
	 *
	 * @param int    $order_ref
	 * @param float  $total
	 * @param string $currency_code
	 *
	 * @return array<string, mixed>
	 */
	public function capture(int $order_ref, float $total, string $currency_code): array {
		$response = [];

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
		$xml .= '<SOAP-ENV:Header />';
		$xml .= '<SOAP-ENV:Body>';
		$xml .= '<ipgapi:IPGApiOrderRequest xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1" xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi">';
		$xml .= '<v1:Transaction>';
		$xml .= '<v1:CreditCardTxType>';
		$xml .= '<v1:Type>postAuth</v1:Type>';
		$xml .= '</v1:CreditCardTxType>';
		$xml .= '<v1:Payment>';
		$xml .= '<v1:ChargeTotal>' . $total . '</v1:ChargeTotal>';
		$xml .= '<v1:Currency>' . $this->mapCurrency($currency_code) . '</v1:Currency>';
		$xml .= '</v1:Payment>';
		$xml .= '<v1:TransactionDetails>';
		$xml .= '<v1:OrderId>' . $order_ref . '</v1:OrderId>';
		$xml .= '</v1:TransactionDetails>';
		$xml .= '</v1:Transaction>';
		$xml .= '</ipgapi:IPGApiOrderRequest>';
		$xml .= '</SOAP-ENV:Body>';
		$xml .= '</SOAP-ENV:Envelope>';

		$xml = simplexml_load_string($this->call($xml));

		$xml->registerXPathNamespace('ipgapi', 'http://ipg-online.com/ipgapi/schemas/ipgapi');
		$xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');

		$fault = $xml->xpath('//soap:Fault');

		$response['fault'] = '';

		if (!empty($fault[0]) && isset($fault[0]->detail)) {
			$response['fault'] = (string)$fault[0]->detail;
		}

		$string = $xml->xpath('//ipgapi:ErrorMessage');
		$response['error'] = (isset($string[0]) ? (string)$string[0] : '');

		$string = $xml->xpath('//ipgapi:TransactionResult');
		$response['transaction_result'] = (isset($string[0]) ? (string)$string[0] : '');

		return $response;
	}

	/**
	 * Update Capture Status
	 *
	 * @param int $firstdata_remote_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateCaptureStatus(int $firstdata_remote_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "firstdata_remote_order` SET `capture_status` = '" . (int)$status . "' WHERE `firstdata_remote_order_id` = '" . (int)$firstdata_remote_order_id . "'");
	}

	/**
	 * Refund
	 *
	 * @param int    $order_ref
	 * @param float  $total
	 * @param string $currency_code
	 *
	 * @return array<string, mixed>
	 */
	public function refund(int $order_ref, float $total, string $currency_code): array {
		$response = [];

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
		$xml .= '<SOAP-ENV:Header />';
		$xml .= '<SOAP-ENV:Body>';
		$xml .= '<ipgapi:IPGApiOrderRequest xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1" xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi">';
		$xml .= '<v1:Transaction>';
		$xml .= '<v1:CreditCardTxType>';
		$xml .= '<v1:Type>return</v1:Type>';
		$xml .= '</v1:CreditCardTxType>';
		$xml .= '<v1:Payment>';
		$xml .= '<v1:ChargeTotal>' . $total . '</v1:ChargeTotal>';
		$xml .= '<v1:Currency>' . $this->mapCurrency($currency_code) . '</v1:Currency>';
		$xml .= '</v1:Payment>';
		$xml .= '<v1:TransactionDetails>';
		$xml .= '<v1:OrderId>' . $order_ref . '</v1:OrderId>';
		$xml .= '</v1:TransactionDetails>';
		$xml .= '</v1:Transaction>';
		$xml .= '</ipgapi:IPGApiOrderRequest>';
		$xml .= '</SOAP-ENV:Body>';
		$xml .= '</SOAP-ENV:Envelope>';

		$xml = simplexml_load_string($this->call($xml));

		$xml->registerXPathNamespace('ipgapi', 'http://ipg-online.com/ipgapi/schemas/ipgapi');
		$xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');

		$fault = $xml->xpath('//soap:Fault');

		$response['fault'] = '';

		if (!empty($fault[0]) && isset($fault[0]->detail)) {
			$response['fault'] = (string)$fault[0]->detail;
		}

		$string = $xml->xpath('//ipgapi:ErrorMessage');
		$response['error'] = (isset($string[0]) ? (string)$string[0] : '');

		$string = $xml->xpath('//ipgapi:TransactionResult');
		$response['transaction_result'] = (isset($string[0]) ? (string)$string[0] : '');

		return $response;
	}

	/**
	 * Update Refund Status
	 *
	 * @param int $firstdata_remote_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateRefundStatus(int $firstdata_remote_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "firstdata_remote_order` SET `refund_status` = '" . (int)$status . "' WHERE `firstdata_remote_order_id` = '" . (int)$firstdata_remote_order_id . "'");
	}

	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "firstdata_remote_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;
			$order['transactions'] = $this->getTransactions($order['firstdata_remote_order_id']);

			return $order;
		} else {
			return [];
		}
	}

	/**
	 * Get Transactions
	 *
	 * @param int $firstdata_remote_order_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getTransactions(int $firstdata_remote_order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "firstdata_remote_order_transaction` WHERE `firstdata_remote_order_id` = '" . (int)$firstdata_remote_order_id . "'");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * Add Transaction
	 *
	 * @param int    $firstdata_remote_order_id
	 * @param string $type
	 * @param float  $total
	 *
	 * @return void
	 */
	public function addTransaction(int $firstdata_remote_order_id, string $type, float $total): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "firstdata_remote_order_transaction` SET `firstdata_remote_order_id` = '" . (int)$firstdata_remote_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_firstdata_remote_debug') == 1) {
			$log = new \Log('firstdata_remote.log');
			$log->write($message);
		}
	}

	/**
	 * Get Total Captured
	 *
	 * @param int $firstdata_order_id
	 *
	 * @return float
	 */
	public function getTotalCaptured(int $firstdata_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "firstdata_remote_order_transaction` WHERE `firstdata_remote_order_id` = '" . (int)$firstdata_order_id . "' AND (`type` = 'payment' OR `type` = 'refund')");

		return (float)$query->row['total'];
	}

	/**
	 * Get Total Refunded
	 *
	 * @param int $firstdata_order_id
	 *
	 * @return float
	 */
	public function getTotalRefunded(int $firstdata_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "firstdata_remote_order_transaction` WHERE `firstdata_remote_order_id` = '" . (int)$firstdata_order_id . "' AND `type` = 'refund'");

		return (float)$query->row['total'];
	}

	/**
	 * Map Currency
	 *
	 * @param string $code
	 *
	 * @return string
	 */
	public function mapCurrency(string $code): string {
		$currency = [
			'GBP' => 826,
			'USD' => 840,
			'EUR' => 978
		];

		if (array_key_exists($code, $currency)) {
			return $currency[$code];
		} else {
			return '';
		}
	}
}
