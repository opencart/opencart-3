<?php
/**
 * Class Bluepay Hosted
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentBluePayHosted extends Model {
	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bluepay_hosted_order` (
			  `bluepay_hosted_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `transaction_id` varchar(50) NOT NULL,			  
			  `release_status` int(1) NOT NULL DEFAULT '0',
			  `void_status` int(1) NOT NULL DEFAULT '0',
			  `rebate_status` int(1) NOT NULL DEFAULT '0',
			  `currency_code` varchar(3) NOT NULL,
			  `total` decimal(15,4) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  PRIMARY KEY (`bluepay_hosted_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bluepay_hosted_order_transaction` (
			  `bluepay_hosted_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `bluepay_hosted_order_id` int(11) NOT NULL,			  
			  `type` enum('auth', 'payment', 'rebate', 'void') DEFAULT NULL,
			  `amount` decimal(15,4) NOT NULL,
			  `date_added` datetime NOT NULL,
			  PRIMARY KEY (`bluepay_hosted_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bluepay_hosted_card` (
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
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bluepay_hosted_order`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bluepay_hosted_order_transaction`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bluepay_hosted_card`");
	}

	/**
	 * addOrder
	 * 
	 * @param array $order_info
	 * @param array $response_data
	 * 
	 * @return int
	 */
	public function addOrder($order_info, $response_data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "bluepay_hoster_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `transaction_id` = '" . $this->db->escape($response_data['TRANSACTION_ID']) . "', `release_status` = '0', `void_status` = '0', `rebate_status` = '0', `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `total` = '" . (float)$order_info['total'] . "', `date_added` = NOW(), `date_modified` = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * Void
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function void(int $order_id): array {
		$bluepay_hosted_order = $this->getOrder($order_id);

		if ($bluepay_hosted_order && $bluepay_hosted_order['release_status'] == 1) {
			$void_data = [];

			$void_data['MERCHANT'] = $this->config->get('payment_bluepay_hosted_account_id');
			$void_data['TRANSACTION_TYPE'] = 'VOID';
			$void_data['MODE'] = strtoupper($this->config->get('payment_bluepay_hosted_test'));
			$void_data['RRNO'] = $bluepay_hosted_order['transaction_id'];
			$void_data['APPROVED_URL'] = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_hosted/adminCallback';
			$void_data['DECLINED_URL'] = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_hosted/adminCallback';
			$void_data['MISSING_URL'] = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_hosted/adminCallback';
			$tamper_proof_data = $this->config->get('payment_bluepay_hosted_secret_key') . $void_data['MERCHANT'] . $void_data['TRANSACTION_TYPE'] . $void_data['RRNO'] . $void_data['MODE'];
			$void_data['TAMPER_PROOF_SEAL'] = md5($tamper_proof_data);

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$void_data['REMOTE_IP'] = $this->request->server['REMOTE_ADDR'];
			}

			$this->logger('$void_data:\r\n' . print_r($void_data, 1));

			return $this->sendCurl('https://secure.bluepay.com/interfaces/bp10emu', $void_data);
		} else {
			return [];
		}
	}

	/**
	 * updateVoidStatus
	 *
	 * @param int $bluepay_hosted_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateVoidStatus(int $bluepay_hosted_order_id, int $status): void {
		$this->logger('$bluepay_hosted_order_id:\r\n' . print_r($bluepay_hosted_order_id, 1));
		$this->logger('$status:\r\n' . print_r($status, 1));

		$this->db->query("UPDATE `" . DB_PREFIX . "bluepay_hosted_order` SET `void_status` = '" . (int)$status . "' WHERE `bluepay_hosted_order_id` = '" . (int)$bluepay_hosted_order_id . "'");
	}

	/**
	 * Release
	 *
	 * @param int   $order_id
	 * @param float $amount
	 *
	 * @return array
	 */
	public function release(int $order_id, float $amount): array {
		$bluepay_hosted_order = $this->getOrder($order_id);
		$total_released = $this->getTotalReleased($bluepay_hosted_order['bluepay_hosted_order_id']);

		if ($bluepay_hosted_order && $bluepay_hosted_order['release_status'] == 0 && ($total_released + $amount <= $bluepay_hosted_order['total'])) {
			$release_data = [];

			$release_data['MERCHANT'] = $this->config->get('payment_bluepay_hosted_account_id');
			$release_data['TRANSACTION_TYPE'] = 'CAPTURE';
			$release_data['MODE'] = strtoupper($this->config->get('payment_bluepay_hosted_test'));
			$release_data['RRNO'] = $bluepay_hosted_order['transaction_id'];
			$release_data['APPROVED_URL'] = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_hosted/adminCallback';
			$release_data['DECLINED_URL'] = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_hosted/adminCallback';
			$release_data['MISSING_URL'] = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_hosted/adminCallback';

			$tamper_proof_data = $this->config->get('payment_bluepay_hosted_secret_key') . $release_data['MERCHANT'] . $release_data['TRANSACTION_TYPE'] . $release_data['RRNO'] . $release_data['MODE'];

			$release_data['TAMPER_PROOF_SEAL'] = md5($tamper_proof_data);

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$release_data['REMOTE_IP'] = $this->request->server['REMOTE_ADDR'];
			}

			return $this->sendCurl('https://secure.bluepay.com/interfaces/bp10emu', $release_data);
		} else {
			return [];
		}
	}

	/**
	 * updateReleaseStatus
	 *
	 * @param int $bluepay_hosted_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateReleaseStatus(int $bluepay_hosted_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "bluepay_hosted_order` SET `release_status` = '" . (int)$status . "' WHERE `bluepay_hosted_order_id` = '" . (int)$bluepay_hosted_order_id . "'");
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
		$bluepay_hosted_order = $this->getOrder($order_id);

		if ($bluepay_hosted_order && $bluepay_hosted_order['rebate_status'] != 1) {
			$rebate_data = [];

			$rebate_data['MERCHANT'] = $this->config->get('payment_bluepay_hosted_account_id');
			$rebate_data['TRANSACTION_TYPE'] = 'REFUND';
			$rebate_data['MODE'] = strtoupper($this->config->get('payment_bluepay_hosted_test'));
			$rebate_data['RRNO'] = $bluepay_hosted_order['transaction_id'];
			$rebate_data['AMOUNT'] = $amount;
			$rebate_data['APPROVED_URL'] = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_hosted/adminCallback';
			$rebate_data['DECLINED_URL'] = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_hosted/adminCallback';
			$rebate_data['MISSING_URL'] = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_hosted/adminCallback';
			$tamper_proof_data = $this->config->get('payment_bluepay_hosted_secret_key') . $rebate_data['MERCHANT'] . $rebate_data['TRANSACTION_TYPE'] . $rebate_data['AMOUNT'] . $rebate_data['RRNO'] . $rebate_data['MODE'];
			$rebate_data['TAMPER_PROOF_SEAL'] = md5($tamper_proof_data);

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$rebate_data['REMOTE_IP'] = $this->request->server['REMOTE_ADDR'];
			}

			return $this->sendCurl('https://secure.bluepay.com/interfaces/bp10emu', $rebate_data);
		} else {
			return [];
		}
	}

	/**
	 * updateRebaseStatus
	 *
	 * @param int $bluepay_hosted_order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function updateRebateStatus(int $bluepay_hosted_order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "bluepay_hosted_order` SET `rebate_status` = '" . (int)$status . "' WHERE `bluepay_hosted_order_id` = '" . (int)$bluepay_hosted_order_id . "'");
	}

	/**
	 * updateTransactionId
	 *
	 * @param int $bluepay_hosted_order_id
	 * @param int $transaction_id
	 *
	 * @return void
	 */
	public function updateTransactionId(int $bluepay_hosted_order_id, int $transaction_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "bluepay_hosted_order` SET `transaction_id` = '" . (int)$transaction_id . "' WHERE `bluepay_hosted_order_id` = '" . (int)$bluepay_hosted_order_id . "'");
	}

	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bluepay_hosted_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;
			$order['transactions'] = $this->getTransactions($order['bluepay_hosted_order_id']);

			return $order;
		} else {
			return [];
		}
	}

	/**
	 * getTransactions
	 * 
	 * @param int $bluepay_hosted_order_id
	 * 
	 * @return array
	 */
	private function getTransactions(int $bluepay_hosted_order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bluepay_hosted_order_transaction` WHERE `bluepay_hosted_order_id` = '" . (int)$bluepay_hosted_order_id . "'");

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
	public function addHistory(int $order_id, int $order_status_id, string $comment, bool $notify = false): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET `order_id` = '" . (int)$order_id . "', `order_status_id` = '" . (int)$order_status_id . "', `comment` = '" . $this->db->escape($comment) . "', `notify` = '" . (bool)$notify . "', `date_added` = NOW()");
	}

	/**
	 * addTransaction
	 *
	 * @param int    $bluepay_hosted_order_id
	 * @param string $type
	 * @param float  $total
	 *
	 * @return void
	 */
	public function addTransaction(int $bluepay_hosted_order_id, string $type, float $total): void {
		$this->logger('$type:\r\n' . print_r($type, 1));
		$this->logger('$total:\r\n' . print_r($total, 1));

		$this->db->query("INSERT INTO `" . DB_PREFIX . "bluepay_hosted_order_transaction` SET `bluepay_hosted_order_id` = '" . (int)$bluepay_hosted_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
	}

	/**
	 * getTotalReleased
	 *
	 * @param int $bluepay_hosted_order_id
	 *
	 * @return float
	 */
	public function getTotalReleased(int $bluepay_hosted_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "bluepay_hosted_order_transaction` WHERE `bluepay_hosted_order_id` = '" . (int)$bluepay_hosted_order_id . "' AND (`type` = 'payment' OR `type` = 'rebate')");

		return (float)$query->row['total'];
	}

	/**
	 * getTotalRebated
	 *
	 * @param int $bluepay_hosted_order_id
	 *
	 * @return float
	 */
	public function getTotalRebated(int $bluepay_hosted_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "bluepay_hosted_order_transaction` WHERE `bluepay_hosted_order_id` = '" . (int)$bluepay_hosted_order_id . "' AND `type` = 'rebate'");

		return (float)$query->row['total'];
	}

	/**
	 * sendCurl
	 *
	 * @param string $url
	 * @param array  $post_data
	 *
	 * @return array
	 */
	public function sendCurl(string $url, array $post_data): array {
		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

		$response_data = curl_exec($curl);

		curl_close($curl);

		return json_decode($response_data, true);
	}

	/**
	 * adminCallback
	 *
	 * @return void
	 */
	public function adminCallback(): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($this->request->get));
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_bluepay_hosted_debug') == 1) {
			$log = new \Log('bluepay_hosted.log');
			$log->write($message);
		}
	}
}
