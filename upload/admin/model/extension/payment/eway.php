<?php
class ModelExtensionPaymentEway extends Model {
    public function install(): void {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "eway_order` (
			  `eway_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `created` DATETIME NOT NULL,
			  `modified` DATETIME NOT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  `currency_code` CHAR(3) NOT NULL,
			  `transaction_id` VARCHAR(24) NOT NULL,
			  `debug_data` TEXT,
			  `capture_status` INT(1) DEFAULT NULL,
			  `void_status` INT(1) DEFAULT NULL,
			  `refund_status` INT(1) DEFAULT NULL,
			  PRIMARY KEY (`eway_order_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "eway_transactions` (
			  `eway_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `eway_order_id` int(11) NOT NULL,
			  `transaction_id` VARCHAR(24) NOT NULL,
			  `created` DATETIME NOT NULL,
			  `type` ENUM('auth', 'payment', 'refund', 'void') DEFAULT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`eway_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "eway_card` (
			  `card_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` INT(11) NOT NULL,
			  `order_id` INT(11) NOT NULL,
			  `token` VARCHAR(50) NOT NULL,
			  `digits` VARCHAR(4) NOT NULL,
			  `expiry` VARCHAR(5) NOT NULL,
			  `type` VARCHAR(50) NOT NULL,
			  PRIMARY KEY (`card_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall(): void {
        //$this->model_setting_setting->deleteSetting($this->request->get['extension']);

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "eway_order`;");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "eway_transactions`;");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "eway_card`;");
    }

    public function getOrder(int $order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "eway_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        if ($query->num_rows) {
            $order = $query->row;

            $order['transactions'] = $this->getTransactions($order['eway_order_id']);

            return $order;
        } else {
            return [];
        }
    }

    public function addRefundRecord(array $order, object $result): void {
        $transaction_id = $result->TransactionID;
        $total_amount   = $result->Refund->TotalAmount / 100;
        $refund_amount  = $order['refund_amount'] + $total_amount;

        if (isset($order['refund_transaction_id']) && $order['refund_transaction_id'] != '') {
            $order['refund_transaction_id'] .= ',';
        }

        $order['refund_transaction_id'] .= $transaction_id;

        $this->db->query("UPDATE `" . DB_PREFIX . "eway_order` SET `modified` = NOW(), `refund_amount` = '" . (double)$refund_amount . "', `refund_transaction_id` = '" . $this->db->escape($order['refund_transaction_id']) . "' WHERE `eway_order_id` = '" . $order['eway_order_id'] . "'");
    }

    public function capture(int $order_id, float $capture_amount, array $currency): string {
        $eway_order = $this->getOrder($order_id);

        if ($eway_order && $capture_amount > 0) {
            $capture_data                        = new \stdClass();
            $capture_data->Payment               = new \stdClass();
            $capture_data->Payment->TotalAmount  = (int)(number_format($capture_amount, 2, '.', '') * 100);
            $capture_data->Payment->CurrencyCode = $currency;
            $capture_data->TransactionID         = $eway_order['transaction_id'];

            if ($this->config->get('payment_eway_test')) {
                $url = 'https://api.sandbox.ewaypayments.com/CapturePayment';
            } else {
                $url = 'https://api.ewaypayments.com/CapturePayment';
            }

            $response = $this->sendCurl($url, $capture_data);

            return json_decode($response);
        } else {
            return '';
        }
    }

    public function updateCaptureStatus(int $eway_order_id, int $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "eway_order` SET `capture_status` = '" . (int)$status . "' WHERE `eway_order_id` = '" . (int)$eway_order_id . "'");
    }

    public function updateTransactionId(int $eway_order_id, string $transaction_id): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "eway_order` SET `transaction_id` = '" . $this->db->escape($transaction_id) . "' WHERE `eway_order_id` = '" . (int)$eway_order_id . "'");
    }

    public function void(int $order_id): string {
        $eway_order = $this->getOrder($order_id);

        if ($eway_order) {
            $data                = new \stdClass();
            $data->TransactionID = $eway_order['transaction_id'];

            if ($this->config->get('payment_eway_test')) {
                $url = 'https://api.sandbox.ewaypayments.com/CancelAuthorisation';
            } else {
                $url = 'https://api.ewaypayments.com/CancelAuthorisation';
            }

            $response = $this->sendCurl($url, $data);

            return json_decode($response);
        } else {
            return '';
        }
    }

    public function updateVoidStatus(int $eway_order_id, int $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "eway_order` SET `void_status` = '" . (int)$status . "' WHERE `eway_order_id` = '" . (int)$eway_order_id . "'");
    }

    public function refund(int $order_id, float $refund_amount): array {
        $eway_order = $this->getOrder($order_id);

        if ($eway_order && $refund_amount > 0) {
            $refund_data                        = new \stdClass();
            $refund_data->Refund                = new \stdClass();
            $refund_data->Refund->TotalAmount   = (int)(number_format($refund_amount, 2, '.', '') * 100);
            $refund_data->Refund->TransactionID = $eway_order['transaction_id'];
            $refund_data->Refund->CurrencyCode  = $eway_order['currency_code'];

            if ($this->config->get('payment_eway_test')) {
                $url = 'https://api.sandbox.ewaypayments.com/Transaction/' . $eway_order['transaction_id'] . '/Refund';
            } else {
                $url = 'https://api.ewaypayments.com/Transaction/' . $eway_order['transaction_id'] . '/Refund';
            }

            $response = $this->sendCurl($url, $refund_data);

            return json_decode($response);
        } else {
            return [];
        }
    }

    public function updateRefundStatus(int $eway_order_id, int $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "eway_order` SET `refund_status` = '" . (int)$status . "' WHERE `eway_order_id` = '" . (int)$eway_order_id . "'");
    }

    public function sendCurl(string $url, array $data): string {
        $ch = curl_init($url);

        $eway_username = html_entity_decode($this->config->get('payment_eway_username'), ENT_QUOTES, 'UTF-8');
        $eway_password = html_entity_decode($this->config->get('payment_eway_password'), ENT_QUOTES, 'UTF-8');

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_USERPWD, $eway_username . ':' . $eway_password);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

        $response = curl_exec($ch);

        if (curl_errno($ch) != CURLE_OK) {
            $response = new \stdClass();

            $response->Errors = "POST Error: " . curl_error($ch) . " URL: $url";
            $response         = json_encode($response);
        } else {
            $info = curl_getinfo($ch);

            if ($info['http_code'] == 401 || $info['http_code'] == 404) {
                $response         = new \stdClass();
                $response->Errors = "Please check the API Key and Password";
                $response         = json_encode($response);
            }
        }

        curl_close($ch);

        return $response;
    }

    private function getTransactions(int $eway_order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "eway_transactions` WHERE `eway_order_id` = '" . (int)$eway_order_id . "'");

        if ($query->num_rows) {
            return $query->rows;
        } else {
            return [];
        }
    }

    public function addTransaction(int $eway_order_id, string $transactionid, string $type, float $total, string $currency): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "eway_transactions` SET `eway_order_id` = '" . (int)$eway_order_id . "', `created` = NOW(), `transaction_id` = '" . $this->db->escape($transactionid) . "', `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($total, $currency, false, false) . "'");
    }

    public function getTotalCaptured(int $eway_order_id): float {
        $query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "eway_transactions` WHERE `eway_order_id` = '" . (int)$eway_order_id . "' AND `type` = 'payment' ");

        return (float)$query->row['total'];
    }

    public function getTotalRefunded(int $eway_order_id): float {
        $query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "eway_transactions` WHERE `eway_order_id` = '" . (int)$eway_order_id . "' AND `type` = 'refund'");

        return (float)$query->row['total'];
    }
}