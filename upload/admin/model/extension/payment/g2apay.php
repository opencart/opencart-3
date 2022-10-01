<?php
class ModelExtensionPaymentG2aPay extends Model {
    public function install(): void {
        $this->db->query("
			CREATE TABLE `" . DB_PREFIX . "g2apay_order` (
				`g2apay_order_id` INT(11) NOT NULL AUTO_INCREMENT,
				`order_id` int(11) NOT NULL,
				`g2apay_transaction_id` varchar(255) NOT NULL,
				`date_added` DATETIME NOT NULL,
				`modified` DATETIME NOT NULL,
				`refund_status` INT(1) DEFAULT NULL,
				`currency_code` CHAR(3) NOT NULL,
				`total` DECIMAL( 10, 2 ) NOT NULL,
				KEY `g2apay_transaction_id` (`g2apay_transaction_id`),
				PRIMARY KEY `g2apay_order_id` (`g2apay_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "g2apay_order_transaction` (
			  `g2apay_order_transaction_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `g2apay_order_id` INT(11) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `type` ENUM('payment', 'refund') DEFAULT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`g2apay_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
			");
    }

    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "g2apay_order`;");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "g2apay_order_transaction`;");
    }

    public function getOrder(int $order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "g2apay_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        if ($query->num_rows) {
            $order                 = $query->row;
            $order['transactions'] = $this->getTransactions($order['g2apay_order_id'], $query->row['currency_code']);

            return $order;
        } else {
            return [];
        }
    }

    public function getTotalReleased(int $g2apay_order_id): float {
        $query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "g2apay_order_transaction` WHERE `g2apay_order_id` = '" . (int)$g2apay_order_id . "' AND (`type` = 'payment' OR `type` = 'refund')");

        return (float)$query->row['total'];
    }

    public function refund(array $g2apay_order, float $amount): array {
        if (!empty($g2apay_order) && $g2apay_order['refund_status'] != 1) {
            if ($this->config->get('payment_g2apay_environment') == 1) {
                $url = 'https://pay.g2a.com/rest/transactions/' . $g2apay_order['g2apay_transaction_id'];
            } else {
                $url = 'https://www.test.pay.g2a.com/rest/transactions/' . $g2apay_order['g2apay_transaction_id'];
            }

            $refunded_amount = round($amount, 2);
            $string          = $g2apay_order['g2apay_transaction_id'] . $g2apay_order['order_id'] . round($g2apay_order['total'], 2) . $refunded_amount . html_entity_decode($this->config->get('payment_g2apay_secret'));
            $hash            = hash('sha256', $string);

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

    public function updateRefundStatus(int $g2apay_order_id, int $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "g2apay_order` SET `refund_status` = '" . (int)$status . "' WHERE `g2apay_order_id` = '" . (int)$g2apay_order_id . "'");
    }

    private function getTransactions(int $g2apay_order_id, string $currency_code): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "g2apay_order_transaction` WHERE `g2apay_order_id` = '" . (int)$g2apay_order_id . "'");

        $transactions = [];

        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                $row['amount']  = $this->currency->format($row['amount'], $currency_code, true, true);
                $transactions[] = $row;
            }

            return $transactions;
        } else {
            return [];
        }
    }

    public function addTransaction(int $g2apay_order_id, string $type, float $total): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "g2apay_order_transaction` SET `g2apay_order_id` = '" . (int)$g2apay_order_id . "',`date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (double)$total . "'");
    }

    public function getTotalRefunded(int $g2apay_order_id): float {
        $query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "g2apay_order_transaction` WHERE `g2apay_order_id` = '" . (int)$g2apay_order_id . "' AND `type` = 'refund'");

        return (float)$query->row['total'];
    }

    public function sendCurl($url, $fields) {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));

        $auth_hash     = hash('sha256', $this->config->get('payment_g2apay_api_hash') . $this->config->get('payment_g2apay_username') . html_entity_decode($this->config->get('payment_g2apay_secret')));
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

    public function logger(string $message): void {
        if ($this->config->get('payment_g2apay_debug') == 1) {
            $backtrace = debug_backtrace();

            $log = new \Log('g2apay.log');
            $log->write('Origin: ' . $backtrace[6]['class'] . '::' . $backtrace[6]['function']);
            $log->write(print_r($message, 1));
        }
    }
}