<?php
class ModelExtensionPaymentPPProIframe extends Model {
    public function install(): void {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paypal_iframe_order` (
			  `paypal_iframe_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `date_modified` DATETIME NOT NULL,
			  `capture_status` ENUM('Complete','NotComplete') DEFAULT NULL,
			  `currency_code` CHAR(3) NOT NULL,
			  `authorization_id` VARCHAR(30) NOT NULL,
			  `total` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`paypal_iframe_order_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paypal_iframe_order_transaction` (
			  `paypal_iframe_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `paypal_iframe_order_id` int(11) NOT NULL,
			  `transaction_id` CHAR(20) NOT NULL,
			  `parent_id` CHAR(20) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `note` VARCHAR(255) NOT NULL,
			  `msgsubid` CHAR(38) NOT NULL,
			  `receipt_id` CHAR(20) NOT NULL,
			  `payment_type` ENUM('none','echeck','instant', 'refund', 'void') DEFAULT NULL,
			  `payment_status` CHAR(20) NOT NULL,
			  `pending_reason` CHAR(50) NOT NULL,
			  `transaction_entity` CHAR(50) NOT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  `debug_data` TEXT NOT NULL,
			  `call_data` TEXT NOT NULL,
			  PRIMARY KEY (`paypal_iframe_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paypal_iframe_order_transaction`;");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paypal_iframe_order`;");
    }

    private function getTransactions(int $paypal_iframe_order_id): array {
        $query = $this->db->query("SELECT `ot`.*, (SELECT COUNT(`ot2`.`paypal_iframe_order_id`) FROM `" . DB_PREFIX . "paypal_iframe_order_transaction` `ot2` WHERE `ot2`.`parent_id` = `ot`.`transaction_id`) AS `children` FROM `" . DB_PREFIX . "paypal_iframe_order_transaction` `ot` WHERE `paypal_iframe_order_id` = '" . (int)$paypal_iframe_order_id . "'");

        if ($query->num_rows) {
            return $query->rows;
        } else {
            return [];
        }
    }

    public function getTotalCaptured(int $paypal_iframe_order_id): float {
        $query = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "paypal_iframe_order_transaction` WHERE `paypal_iframe_order_id` = '" . (int)$paypal_iframe_order_id . "' AND `pending_reason` != 'authorization' AND (`payment_status` = 'Partially-Refunded' OR `payment_status` = 'Completed' OR `payment_status` = 'Pending') AND `transaction_entity` = 'payment'");

        return (float)$query->row['amount'];
    }

    public function getTotalRefunded(int $paypal_iframe_order_id): float {
        $query = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "paypal_iframe_order_transaction` WHERE `paypal_iframe_order_id` = '" . (int)$paypal_iframe_order_id . "' AND `payment_status` = 'Refunded'");

        return (float)$query->row['amount'];
    }

    public function getTotalRefundedTransaction(string $transaction_id): float {
        $query = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "paypal_iframe_order_transaction` WHERE `parent_id` = '" . $this->db->escape($transaction_id) . "' AND `payment_type` = 'refund'");

        return (float)$query->row['amount'];
    }

    public function getOrder(int $order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_iframe_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        if ($query->num_rows) {
            $order = $query->row;

            $order['transactions'] = $this->getTransactions($order['paypal_iframe_order_id']);
            $order['captured']     = $this->getTotalCaptured($order['paypal_iframe_order_id']);

            return $order;
        } else {
            return [];
        }
    }

    public function call($data) {
        if ($this->config->get('payment_pp_pro_iframe_test') == 1) {
            $api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $api_endpoint = 'https://api-3t.paypal.com/nvp';
        }

        $settings = [
            'USER'         => $this->config->get('payment_pp_pro_iframe_user'),
            'PWD'          => $this->config->get('payment_pp_pro_iframe_password'),
            'SIGNATURE'    => $this->config->get('payment_pp_pro_iframe_sig'),
            'VERSION'      => '84',
            'BUTTONSOURCE' => 'WM_PRO_OPENCART_UK_' . VERSION
        ];

        $this->log($data, 'Call data');

        $defaults = [CURLOPT_POST           => 1,
                     CURLOPT_HEADER         => 0,
                     CURLOPT_URL            => $api_endpoint,
                     CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",
                     CURLOPT_FRESH_CONNECT  => 1,
                     CURLOPT_RETURNTRANSFER => 1,
                     CURLOPT_FORBID_REUSE   => 1,
                     CURLOPT_TIMEOUT        => 0,
                     CURLOPT_SSL_VERIFYPEER => 0,
                     CURLOPT_SSL_VERIFYHOST => 0,
                     CURLOPT_POSTFIELDS     => http_build_query(array_merge($data, $settings), '', "&")
        ];

        $ch = curl_init();

        curl_setopt_array($ch, $defaults);

        if (!$result = curl_exec($ch)) {
            $log_data = [
                'curl_error' => curl_error($ch),
                'curl_errno' => curl_errno($ch)
            ];

            $this->log($log_data, 'CURL failed');

            return false;
        }

        $this->log($result, 'Result');

        curl_close($ch);

        return $this->cleanReturn($result);
    }

    public function updateOrder(string $capture_status, int $order_id): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "paypal_iframe_order` SET `date_modified` = NOW(), `capture_status` = '" . $this->db->escape($capture_status) . "' WHERE `order_id` = '" . (int)$order_id . "'");
    }

    public function updateTransaction(array $transaction): void {
        $this->db->query("
			UPDATE `" . DB_PREFIX . "paypal_iframe_order_transaction`
			SET `paypal_iframe_order_id` = '" . (int)$transaction['paypal_iframe_order_id'] . "',
				`transaction_id` = '" . $this->db->escape($transaction['transaction_id']) . "',
				`parent_id` = '" . $this->db->escape($transaction['parent_id']) . "',
				`date_added` = '" . $this->db->escape($transaction['date_added']) . "',
				`note` = '" . $this->db->escape($transaction['note']) . "',
				`msgsubid` = '" . $this->db->escape($transaction['msgsubid']) . "',
				`receipt_id` = '" . $this->db->escape($transaction['receipt_id']) . "',
				`payment_type` = '" . $this->db->escape($transaction['payment_type']) . "',
				`payment_status` = '" . $this->db->escape($transaction['payment_status']) . "',
				`pending_reason` = '" . $this->db->escape($transaction['pending_reason']) . "',
				`transaction_entity` = '" . $this->db->escape($transaction['transaction_entity']) . "',
				`amount` = '" . $this->db->escape($transaction['amount']) . "',
				`debug_data` = '" . $this->db->escape($transaction['debug_data']) . "',
				`call_data` = '" . $this->db->escape($transaction['call_data']) . "'
			WHERE `paypal_iframe_order_transaction_id` = '" . (int)$transaction['paypal_iframe_order_transaction_id'] . "'
		");
    }

    public function addTransaction(int $transaction_data, array $request_data = []): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "paypal_iframe_order_transaction` SET `paypal_iframe_order_id` = '" . (int)$transaction_data['paypal_iframe_order_id'] . "', `transaction_id` = '" . $this->db->escape($transaction_data['transaction_id']) . "', `parent_id` = '" . $this->db->escape($transaction_data['parent_id']) . "', `date_added` = NOW(), `note` = '" . $this->db->escape($transaction_data['note']) . "', `msgsubid` = '" . $this->db->escape($transaction_data['msgsubid']) . "', `receipt_id` = '" . $this->db->escape($transaction_data['receipt_id']) . "', `payment_type` = '" . $this->db->escape($transaction_data['payment_type']) . "', `payment_status` = '" . $this->db->escape($transaction_data['payment_status']) . "', `pending_reason` = '" . $this->db->escape($transaction_data['pending_reason']) . "', `transaction_entity` = '" . $this->db->escape($transaction_data['transaction_entity']) . "', `amount` = '" . (float)$transaction_data['amount'] . "', `debug_data` = '" . $this->db->escape($transaction_data['debug_data']) . "'");

        $paypal_iframe_order_transaction_id = $this->db->getLastId();

        if ($request_data) {
            $serialized_data = json_encode($request_data);

            $this->db->query("
				UPDATE `" . DB_PREFIX . "paypal_iframe_order_transaction`
				SET `call_data` = '" . $this->db->escape($serialized_data) . "'
				WHERE `paypal_iframe_order_transaction_id` = '" . (int)$paypal_iframe_order_transaction_id . "'
				LIMIT 1
			");
        }

        return $paypal_iframe_order_transaction_id;
    }

    public function log(array $data, string $title = null): void {
        if ($this->config->get('payment_pp_pro_iframe_debug')) {
            $log = new \Log('pp_pro_iframe.log');
            $log->write($title . ': ' . json_encode($data));
        }
    }

    public function getTransaction($transaction_id) {
        $call_data = [
            'METHOD'        => 'GetTransactionDetails',
            'TRANSACTIONID' => $transaction_id
        ];

        return $this->call($call_data);
    }

    public function getOrderId(string $transaction_id): int {
        $query = $this->db->query("SELECT o.`order_id` FROM `" . DB_PREFIX . "paypal_iframe_order_transaction` `ot` LEFT JOIN `" . DB_PREFIX . "paypal_iframe_order` o ON o.`paypal_iframe_order_id` = `ot`.`paypal_iframe_order_id` WHERE `ot`.`transaction_id` = '" . $this->db->escape($transaction_id) . "' LIMIT 1");

        if ($query->num_rows) {
            return $query->row['order_id'];
        } else {
            return 0;
        }
    }

    public function updateAuthorizationId(string $paypal_iframe_order_id, string $authorization_id): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "paypal_iframe_order` SET `authorization_id` = '" . $this->db->escape($authorization_id) . "' WHERE `paypal_iframe_order_id` = '" . $this->db->escape($paypal_iframe_order_id) . "'");
    }

    public function updateRefundTransaction(string $transaction_id, string $transaction_type): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "paypal_iframe_order_transaction` SET `payment_status` = '" . $this->db->escape($transaction_type) . "' WHERE `transaction_id` = '" . $this->db->escape($transaction_id) . "' LIMIT 1");
    }

    public function getFailedTransaction(int $paypal_iframe_order_transaction_id): array {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_iframe_order_transaction`WHERE paypal_iframe_order_transaction_id = '" . (int)$paypal_iframe_order_transaction_id . "'");

        if ($result->num_rows) {
            return $result->row;
        } else {
            return [];
        }
    }

    public function getLocalTransaction(string $transaction_id): array {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_iframe_order_transaction` WHERE `transaction_id` = '" . $this->db->escape($transaction_id) . "'");

        if ($result->num_rows) {
            return $result->row;
        } else {
            return [];
        }
    }

    protected function cleanReturn(string $data): array {
        $data = explode('&', $data);
        $arr  = [];

        foreach ($data as $k => $v) {
            $tmp          = explode('=', $v);
            $arr[$tmp[0]] = urldecode($tmp[1]);
        }

        return $arr;
    }
}