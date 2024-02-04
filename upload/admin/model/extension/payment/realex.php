<?php
/**
 * Class Realex
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentRealex extends Model {
	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "realex_order` (
			  `realex_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `order_ref` varchar(50) NOT NULL,
			  `order_ref_previous` varchar(50) NOT NULL,
			  `pasref` varchar(50) NOT NULL,
			  `pasref_previous` varchar(50) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  `capture_status` int(1) NOT NULL DEFAULT '0',
			  `void_status` int(1) NOT NULL DEFAULT '0',
			  `settle_type` int(1) NOT NULL DEFAULT '0',
			  `rebate_status` int(1) NOT NULL DEFAULT '0',
			  `currency_code` varchar(3) NOT NULL,
			  `authcode` varchar(30) NOT NULL,
			  `account` varchar(30) NOT NULL,
			  `total` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`realex_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "realex_order_transaction` (
			  `realex_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `realex_order_id` int(11) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `type` enum(\\'auth\\',\\'payment\\',\\'rebate\\',\\'void\\') DEFAULT NULL,
			  `amount` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`realex_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
	}

	/**
	 * Void
	 *
	 * @param int $order_id
	 *
	 * @return object|null
	 */
	public function void(int $order_id): ?object {
		$realex_order = $this->getOrder($order_id);

		if ($realex_order) {
			$timestamp = date('YmdHis');
			$merchant_id = $this->config->get('payment_realex_merchant_id');
			$secret = $this->config->get('payment_realex_secret');

			$this->logger('Void hash construct: ' . $timestamp . '.' . $merchant_id . '.' . $realex_order['order_ref'] . '...');

			$tmp = $timestamp . '.' . $merchant_id . '.' . $realex_order['order_ref'] . '...';
			$hash = sha1($tmp);
			$tmp = $hash . '.' . $secret;
			$hash = sha1($tmp);

			$xml = '';
			$xml .= '<request type="void" timestamp="' . $timestamp . '">';
			$xml .= '<merchantid>' . $merchant_id . '</merchantid>';
			$xml .= '<account>' . $realex_order['account'] . '</account>';
			$xml .= '<orderid>' . $realex_order['order_ref'] . '</orderid>';
			$xml .= '<pasref>' . $realex_order['pasref'] . '</pasref>';
			$xml .= '<authcode>' . $realex_order['authcode'] . '</authcode>';
			$xml .= '<sha1hash>' . $hash . '</sha1hash>';
			$xml .= '</request>';

			$this->logger('Void XML request:\r\n' . print_r(simplexml_load_string($xml), 1));

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, "https://epage.payandshop.com/epage-remote.cgi");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "OpenCart " . VERSION);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($ch);

			curl_close($ch);

			$xml = simplexml_load_string($response);
			$encode = json_encode($xml);

			return json_decode($encode, true);
		} else {
			return null;
		}
	}

	/**
	 * updateVoidStatus
	 *
	 * @param int $realex_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateVoidStatus(int $realex_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "realex_order` SET `void_status` = '" . (int)$status . "' WHERE `realex_order_id` = '" . (int)$realex_order_id . "'");
	}

	/**
	 * Capture
	 *
	 * @param int   $order_id
	 * @param float $amount
	 *
	 * @return object|null
	 */
	public function capture(int $order_id, float $amount): ?object {
		$realex_order = $this->getOrder($order_id);

		if ($realex_order && $realex_order['capture_status'] == 0) {
			$timestamp = date('YmdHis');
			$merchant_id = $this->config->get('payment_realex_merchant_id');
			$secret = $this->config->get('payment_realex_secret');

			if ($realex_order['settle_type'] == 2) {
				$this->logger('Capture hash construct: ' . $timestamp . '.' . $merchant_id . '.' . $realex_order['order_ref'] . '.' . (int)round($amount * 100) . '.' . (string)$realex_order['currency_code'] . '.');

				$tmp = $timestamp . '.' . $merchant_id . '.' . $realex_order['order_ref'] . '.' . (int)round($amount * 100) . '.' . (string)$realex_order['currency_code'] . '.';
				$hash = sha1($tmp);
				$tmp = $hash . '.' . $secret;
				$hash = sha1($tmp);
				$settle_type = 'multisettle';
				$xml_amount = '<amount currency="' . (string)$realex_order['currency_code'] . '">' . (int)round($amount * 100) . '</amount>';
			} else {
				//$this->logger('Capture hash construct: ' . $timestamp . '.' . $merchant_id . '.' . $realex_order['order_ref'] . '...');
				$this->logger('Capture hash construct: ' . $timestamp . '.' . $merchant_id . '.' . $realex_order['order_ref'] . '.' . (int)round($amount * 100) . '.' . (string)$realex_order['currency_code'] . '.');

				$tmp = $timestamp . '.' . $merchant_id . '.' . $realex_order['order_ref'] . '.' . (int)round($amount * 100) . '.' . (string)$realex_order['currency_code'] . '.';
				$hash = sha1($tmp);
				$tmp = $hash . '.' . $secret;
				$hash = sha1($tmp);
				$settle_type = 'settle';
				$xml_amount = '<amount currency="' . (string)$realex_order['currency_code'] . '">' . (int)round($amount * 100) . '</amount>';
			}

			$xml = '';
			$xml .= '<request type="' . $settle_type . '" timestamp="' . $timestamp . '">';
			$xml .= '<merchantid>' . $merchant_id . '</merchantid>';
			$xml .= '<account>' . $realex_order['account'] . '</account>';
			$xml .= '<orderid>' . $realex_order['order_ref'] . '</orderid>';
			$xml .= $xml_amount;
			$xml .= '<pasref>' . $realex_order['pasref'] . '</pasref>';
			$xml .= '<autosettle flag="1" />';
			$xml .= '<authcode>' . $realex_order['authcode'] . '</authcode>';
			$xml .= '<sha1hash>' . $hash . '</sha1hash>';
			$xml .= '</request>';

			$this->logger('Settle XML request:\r\n' . print_r(simplexml_load_string($xml), 1));

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, "https://epage.payandshop.com/epage-remote.cgi");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "OpenCart " . VERSION);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($ch);

			curl_close($ch);

			$xml = simplexml_load_string($response);
			$encode = json_encode($xml);

			return json_decode($encode, true);
		} else {
			return null;
		}
	}

	/**
	 * updateCaptureStatus
	 *
	 * @param int $realex_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateCaptureStatus(int $realex_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "realex_order` SET `capture_status` = '" . (int)$status . "' WHERE `realex_order_id` = '" . (int)$realex_order_id . "'");
	}

	/**
	 * updateForRebate
	 *
	 * @param int    $realex_order_id
	 * @param string $pas_ref
	 * @param string $order_ref
	 *
	 * @return void
	 */
	public function updateForRebate(int $realex_order_id, string $pas_ref, string $order_ref): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "realex_order` SET `order_ref_previous` = '_multisettle_" . $this->db->escape($order_ref) . "', `pasref_previous` = '" . $this->db->escape($pas_ref) . "' WHERE `realex_order_id` = '" . (int)$realex_order_id . "' LIMIT 1");
	}

	/**
	 * Rebate
	 *
	 * @param int   $order_id
	 * @param float $amount
	 *
	 * @return object|null
	 */
	public function rebate(int $order_id, float $amount): ?object {
		$realex_order = $this->getOrder($order_id);

		if ($realex_order && $realex_order['rebate_status'] != 1) {
			$timestamp = date('YmdHis');
			$merchant_id = $this->config->get('payment_realex_merchant_id');
			$secret = $this->config->get('payment_realex_secret');

			if ($realex_order['settle_type'] == 2) {
				$order_ref = '_multisettle_' . $realex_order['order_ref'];

				if (empty($realex_order['pasref_previous'])) {
					$pas_ref = $realex_order['pasref'];
				} else {
					$pas_ref = $realex_order['pasref_previous'];
				}
			} else {
				$order_ref = $realex_order['order_ref'];
				$pas_ref = $realex_order['pasref'];
			}

			$this->logger('Rebate hash construct: ' . $timestamp . '.' . $merchant_id . '.' . $order_ref . '.' . (int)round($amount * 100) . '.' . $realex_order['currency_code'] . '.');

			$tmp = $timestamp . '.' . $merchant_id . '.' . $order_ref . '.' . (int)round($amount * 100) . '.' . $realex_order['currency_code'] . '.';
			$hash = sha1($tmp);
			$tmp = $hash . '.' . $secret;
			$hash = sha1($tmp);
			$rebate_hash = sha1($this->config->get('payment_realex_rebate_password'));

			$xml = '';
			$xml .= '<request type="rebate" timestamp="' . $timestamp . '">';
			$xml .= '<merchantid>' . $merchant_id . '</merchantid>';
			$xml .= '<account>' . $realex_order['account'] . '</account>';
			$xml .= '<orderid>' . $order_ref . '</orderid>';
			$xml .= '<pasref>' . $pas_ref . '</pasref>';
			$xml .= '<authcode>' . $realex_order['authcode'] . '</authcode>';
			$xml .= '<amount currency="' . (string)$realex_order['currency_code'] . '">' . (int)round($amount * 100) . '</amount>';
			$xml .= '<refundhash>' . $rebate_hash . '</refundhash>';
			$xml .= '<sha1hash>' . $hash . '</sha1hash>';
			$xml .= '</request>';

			$this->logger('Rebate XML request:\r\n' . print_r(simplexml_load_string($xml), 1));

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, "https://epage.payandshop.com/epage-remote.cgi");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "OpenCart " . VERSION);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($ch);

			curl_close($ch);

			$xml = simplexml_load_string($response);
			$encode = json_encode($xml);

			return json_decode($encode, true);
		} else {
			return null;
		}
	}

	/**
	 * updateRebateStatus
	 *
	 * @param int $realex_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateRebateStatus(int $realex_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "realex_order` SET `rebate_status` = '" . (int)$status . "' WHERE `realex_order_id` = '" . (int)$realex_order_id . "'");
	}

	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function getOrder(int $order_id): array {
		$this->logger('getOrder - ' . $order_id);

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "realex_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;
			$order['transactions'] = $this->getTransactions($order['realex_order_id']);

			$this->logger(print_r($order, 1));

			return $order;
		} else {
			return [];
		}
	}

	/**
	 * Get Transactions
	 * 
	 * @param  int $realex_order_id
	 * 
	 * @return array<int, array<string, mixed>>
	 */
	private function getTransactions(int $realex_order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "realex_order_transaction` WHERE `realex_order_id` = '" . (int)$realex_order_id . "'");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * Add Transaction
	 *
	 * @param int    $realex_order_id
	 * @param string $type
	 * @param float  $total
	 *
	 * @return void
	 */
	public function addTransaction(int $realex_order_id, string $type, float $total): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "realex_order_transaction` SET `realex_order_id` = '" . (int)$realex_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_realex_debug') == 1) {
			$log = new \Log('realex.log');
			$log->write($message);
		}
	}

	/**
	 * Get Total Captured
	 *
	 * @param int $realex_order_id
	 *
	 * @return float
	 */
	public function getTotalCaptured(int $realex_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "realex_order_transaction` WHERE `realex_order_id` = '" . (int)$realex_order_id . "' AND (`type` = 'payment' OR `type` = 'rebate')");

		return (float)$query->row['total'];
	}

	/**
	 * Get Total Rebated
	 *
	 * @param int $realex_order_id
	 *
	 * @return float
	 */
	public function getTotalRebated(int $realex_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "realex_order_transaction` WHERE `realex_order_id` = '" . (int)$realex_order_id . "' AND `type` = 'rebate'");

		return (float)$query->row['total'];
	}
}
