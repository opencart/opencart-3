<?php
/**
 * Class SagePay Direct
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentSagepayDirect extends Model {
	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sagepay_direct_order` (
			  `sagepay_direct_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `vps_tx_id` varchar(50),
			  `vendor_tx_code` varchar(50) NOT NULL,
			  `security_key` varchar(50) NOT NULL,
			  `tx_auth_no` int(50),
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  `release_status` int(1) NOT NULL DEFAULT '0',
			  `void_status` int(1) NOT NULL DEFAULT '0',
			  `settle_type` int(1) NOT NULL DEFAULT '0',
			  `rebate_status` int(1) NOT NULL DEFAULT '0',
			  `currency_code` varchar(3) NOT NULL,
			  `total` decimal(15,4) NOT NULL,
			  `card_id` int(11),
			  PRIMARY KEY (`sagepay_direct_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sagepay_direct_order_transaction` (
			  `sagepay_direct_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `sagepay_direct_order_id` int(11) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `type` enum('auth', 'payment', 'rebate', 'void') DEFAULT NULL,
			  `amount` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`sagepay_direct_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sagepay_direct_order_recurring` (
			  `sagepay_direct_order_recurring_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `order_recurring_id` int(11) NOT NULL,
			  `vps_tx_id` varchar(50),
			  `vendor_tx_code` varchar(50) NOT NULL,
			  `security_key` char(50) NOT NULL,
			  `tx_auth_no` int(50),
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  `next_payment` datetime NOT NULL,
			  `trial_end` datetime DEFAULT NULL,
			  `subscription_end` datetime DEFAULT NULL,
			  `currency_code` varchar(3) NOT NULL,
			  `total` decimal(15,4) NOT NULL,
			  PRIMARY KEY (`sagepay_direct_order_recurring_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sagepay_direct_card` (
			  `card_id` int(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` int(11) NOT NULL,
			  `token` varchar(50) NOT NULL,
			  `digits` varchar(4) NOT NULL,
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
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "sagepay_direct_order`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "sagepay_direct_order_transaction`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "sagepay_direct_order_recurring`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "sagepay_direct_card`");
	}

	/**
	 * Void
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function void(int $order_id): array {
		$sagepay_direct_order = $this->getOrder($order_id);

		if ($sagepay_direct_order && $sagepay_direct_order['release_status'] == 0) {
			$void_data = [];

			$url = '';

			if ($this->config->get('payment_sagepay_direct_test') == 'live') {
				$url = 'https://live.sagepay.com/gateway/service/void.vsp';

				$void_data['VPSProtocol'] = '3.00';
			} elseif ($this->config->get('payment_sagepay_direct_test') == 'test') {
				$url = 'https://test.sagepay.com/gateway/service/void.vsp';

				$void_data['VPSProtocol'] = '3.00';
			} elseif ($this->config->get('payment_sagepay_direct_test') == 'sim') {
				$url = 'https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorVoidTx';

				$void_data['VPSProtocol'] = '2.23';
			}

			$void_data['TxType'] = 'VOID';
			$void_data['Vendor'] = $this->config->get('payment_sagepay_direct_vendor');
			$void_data['VendorTxCode'] = $sagepay_direct_order['vendor_tx_code'];
			$void_data['VPSTxId'] = $sagepay_direct_order['vps_tx_id'];
			$void_data['SecurityKey'] = $sagepay_direct_order['security_key'];
			$void_data['TxAuthNo'] = $sagepay_direct_order['tx_auth_no'];

			return $this->sendCurl($url, $void_data);
		} else {
			return [];
		}
	}

	/**
	 * updateVoidStatus
	 *
	 * @param int $sagepay_direct_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateVoidStatus(int $sagepay_direct_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_direct_order` SET `void_status` = '" . (int)$status . "' WHERE `sagepay_direct_order_id` = '" . (int)$sagepay_direct_order_id . "'");
	}

	/**
	 * Release
	 *
	 * @param int   $order_id
	 * @param array $amount
	 *
	 * @return array
	 */
	public function release(int $order_id, float $amount): array {
		$sagepay_direct_order = $this->getOrder($order_id);

		$total_released = $this->getTotalReleased($sagepay_direct_order['sagepay_direct_order_id']);

		if ($sagepay_direct_order && $sagepay_direct_order['release_status'] == 0 && ($total_released + $amount <= $sagepay_direct_order['total'])) {
			$release_data = [];

			$url = '';

			if ($this->config->get('payment_sagepay_direct_test') == 'live') {
				$url = 'https://live.sagepay.com/gateway/service/release.vsp';

				$release_data['VPSProtocol'] = '3.00';
			} elseif ($this->config->get('payment_sagepay_direct_test') == 'test') {
				$url = 'https://test.sagepay.com/gateway/service/release.vsp';

				$release_data['VPSProtocol'] = '3.00';
			} elseif ($this->config->get('payment_sagepay_direct_test') == 'sim') {
				$url = 'https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorReleaseTx';

				$release_data['VPSProtocol'] = '2.23';
			}

			$release_data['TxType'] = 'RELEASE';
			$release_data['Vendor'] = $this->config->get('payment_sagepay_direct_vendor');
			$release_data['VendorTxCode'] = $sagepay_direct_order['vendor_tx_code'];
			$release_data['VPSTxId'] = $sagepay_direct_order['vps_tx_id'];
			$release_data['SecurityKey'] = $sagepay_direct_order['security_key'];
			$release_data['TxAuthNo'] = $sagepay_direct_order['tx_auth_no'];
			$release_data['Amount'] = $amount;

			return $this->sendCurl($url, $release_data);
		} else {
			return [];
		}
	}

	/**
	 * updateReleaseStatus
	 *
	 * @param int $sagepay_direct_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateReleaseStatus(int $sagepay_direct_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_direct_order` SET `release_status` = '" . (int)$status . "' WHERE `sagepay_direct_order_id` = '" . (int)$sagepay_direct_order_id . "'");
	}

	/**
	 * Rebate
	 *
	 * @param int   $order_id
	 * @param float $amount
	 *
	 * @return array
	 */
	public function rebate(int $order_id, float $amount): array {
		$sagepay_direct_order = $this->getOrder($order_id);

		if ($sagepay_direct_order && $sagepay_direct_order['rebate_status'] != 1) {
			$refund_data = [];

			$url = '';

			if ($this->config->get('payment_sagepay_direct_test') == 'live') {
				$url = 'https://live.sagepay.com/gateway/service/refund.vsp';

				$refund_data['VPSProtocol'] = '3.00';
			} elseif ($this->config->get('payment_sagepay_direct_test') == 'test') {
				$url = 'https://test.sagepay.com/gateway/service/refund.vsp';

				$refund_data['VPSProtocol'] = '3.00';
			} elseif ($this->config->get('payment_sagepay_direct_test') == 'sim') {
				$url = 'https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRefundTx';

				$refund_data['VPSProtocol'] = '2.23';
			}

			$refund_data['TxType'] = 'REFUND';
			$refund_data['Vendor'] = $this->config->get('payment_sagepay_direct_vendor');
			$refund_data['VendorTxCode'] = $sagepay_direct_order['sagepay_direct_order_id'] . mt_rand();
			$refund_data['Amount'] = $amount;
			$refund_data['Currency'] = $sagepay_direct_order['currency_code'];
			$refund_data['Description'] = substr($this->config->get('config_name'), 0, 100);
			$refund_data['RelatedVPSTxId'] = $sagepay_direct_order['vps_tx_id'];
			$refund_data['RelatedVendorTxCode'] = $sagepay_direct_order['vendor_tx_code'];
			$refund_data['RelatedSecurityKey'] = $sagepay_direct_order['security_key'];
			$refund_data['RelatedTxAuthNo'] = $sagepay_direct_order['tx_auth_no'];

			return $this->sendCurl($url, $refund_data);
		} else {
			return [];
		}
	}

	/**
	 * updateRebateStatus
	 *
	 * @param int $sagepay_direct_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateRebateStatus(int $sagepay_direct_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "sagepay_direct_order` SET `rebate_status` = '" . (int)$status . "' WHERE `sagepay_direct_order_id` = '" . (int)$sagepay_direct_order_id . "'");
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

	private function getTransactions($sagepay_direct_order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sagepay_direct_order_transaction` WHERE `sagepay_direct_order_id` = '" . (int)$sagepay_direct_order_id . "'");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * addTransaction
	 *
	 * @param int    $sagepay_direct_order_id
	 * @param string $type
	 * @param float  $total
	 *
	 * @return void
	 */
	public function addTransaction(int $sagepay_direct_order_id, string $type, float $total): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sagepay_direct_order_transaction` SET `sagepay_direct_order_id` = '" . (int)$sagepay_direct_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
	}

	/**
	 * getTotalReleased
	 *
	 * @param int $sagepay_direct_order_id
	 *
	 * @return float
	 */
	public function getTotalReleased(int $sagepay_direct_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "sagepay_direct_order_transaction` WHERE `sagepay_direct_order_id` = '" . (int)$sagepay_direct_order_id . "' AND (`type` = 'payment' OR `type` = 'rebate')");

		return (float)$query->row['total'];
	}

	/**
	 * getTotalRebated
	 *
	 * @param int $sagepay_direct_order_id
	 *
	 * @return float
	 */
	public function getTotalRebated(int $sagepay_direct_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "sagepay_direct_order_transaction` WHERE `sagepay_direct_order_id` = '" . (int)$sagepay_direct_order_id . "' AND `type` = 'rebate'");

		return (float)$query->row['total'];
	}

	/**
	 * sendCurl
	 *
	 * @param string $url
	 * @param array  $payment_data
	 *
	 * @return array
	 */
	public function sendCurl(string $url, array $payment_data): array {
		$data = [];

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

		foreach ($response_info as $i => $string) {
			if (strpos($string, '=')) {
				$parts = explode('=', $string, 2);

				$data['RepeatResponseData_' . $i][trim($parts[0])] = trim($parts[1]);
			} elseif (strpos($string, '=')) {
				$parts = explode('=', $string, 2);

				$data[trim($parts[0])] = trim($parts[1]);
			}
		}

		return $data;
	}

	/**
	 * Logger
	 *
	 * @param string $title
	 * @param array  $data
	 *
	 * @return void
	 */
	public function logger(string $title, array $data): void {
		if ($this->config->get('payment_sagepay_direct_debug')) {
			$log = new \Log('sagepay_direct.log');
			$log->write($title . ': ' . print_r($data, 1));
		}
	}
}
