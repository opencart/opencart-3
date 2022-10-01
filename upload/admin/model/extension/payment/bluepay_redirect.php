<?php
class ModelExtensionPaymentBluepayredirect extends Model {
    public function install(): void {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bluepay_redirect_order` (
			  `bluepay_redirect_order_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `order_id` INT(11) NOT NULL,
			  `transaction_id` VARCHAR(50),
			  `date_added` DATETIME NOT NULL,
			  `date_modified` DATETIME NOT NULL,
			  `release_status` INT(1) DEFAULT 0,
			  `void_status` INT(1) DEFAULT 0,
			  `rebate_status` INT(1) DEFAULT 0,
			  `currency_code` CHAR(3) NOT NULL,
			  `total` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`bluepay_redirect_order_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bluepay_redirect_order_transaction` (
			  `bluepay_redirect_order_transaction_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `bluepay_redirect_order_id` INT(11) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `type` ENUM('auth', 'payment', 'rebate', 'void') DEFAULT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`bluepay_redirect_order_transaction_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bluepay_redirect_card` (
			  `card_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `customer_id` INT(11) NOT NULL,
			  `token` VARCHAR(50) NOT NULL,
			  `digits` VARCHAR(4) NOT NULL,
			  `expiry` VARCHAR(5) NOT NULL,
			  `type` VARCHAR(50) NOT NULL,
			  PRIMARY KEY (`card_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bluepay_redirect_order`;");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bluepay_redirect_order_transaction`;");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bluepay_redirect_card`;");
    }

    public function void(int $order_id): array {
        $bluepay_redirect_order = $this->getOrder($order_id);

        if (!empty($bluepay_redirect_order) && $bluepay_redirect_order['release_status'] == 1) {
            $void_data                      = [];
            $void_data['MERCHANT']          = $this->config->get('payment_bluepay_redirect_account_id');
            $void_data['TRANSACTION_TYPE']  = 'VOID';
            $void_data['MODE']              = strtoupper($this->config->get('payment_bluepay_redirect_test'));
            $void_data['RRNO']              = $bluepay_redirect_order['transaction_id'];
            $void_data['APPROVED_URL']      = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_redirect/callback';
            $void_data['DECLINED_URL']      = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_redirect/callback';
            $void_data['MISSING_URL']       = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_redirect/callback';
            $tamper_proof_data              = $this->config->get('payment_bluepay_redirect_secret_key') . $void_data['MERCHANT'] . $void_data['TRANSACTION_TYPE'] . $void_data['RRNO'] . $void_data['MODE'];
            $void_data['TAMPER_PROOF_SEAL'] = md5($tamper_proof_data);

            if (isset($this->request->server['REMOTE_ADDR'])) {
                $void_data['REMOTE_IP'] = $this->request->server['REMOTE_ADDR'];
            }

            $response_data = $this->sendCurl('https://secure.bluepay.com/interfaces/bp10emu', $void_data);

            return $response_data;
        } else {
            return [];
        }
    }

    public function updateVoidStatus(int $bluepay_redirect_order_id, int $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "bluepay_redirect_order` SET `void_status` = '" . (int)$status . "' WHERE `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "'");
    }

    public function release(int $order_id, float $amount): array {
        $bluepay_redirect_order = $this->getOrder($order_id);

        $total_released = $this->getTotalReleased($bluepay_redirect_order['bluepay_redirect_order_id']);

        if (!empty($bluepay_redirect_order) && $bluepay_redirect_order['release_status'] == 0 && ($total_released + $amount <= $bluepay_redirect_order['total'])) {
            $release_data                      = [];
            $release_data['MERCHANT']          = $this->config->get('payment_bluepay_redirect_account_id');
            $release_data['TRANSACTION_TYPE']  = 'CAPTURE';
            $release_data['MODE']              = strtoupper($this->config->get('payment_bluepay_redirect_test'));
            $release_data['RRNO']              = $bluepay_redirect_order['transaction_id'];
            $release_data['AMOUNT']            = $amount;
            $release_data['APPROVED_URL']      = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_redirect/callback';
            $release_data['DECLINED_URL']      = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_redirect/callback';
            $release_data['MISSING_URL']       = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_redirect/callback';
            $tamper_proof_data                 = $this->config->get('payment_bluepay_redirect_secret_key') . $release_data['MERCHANT'] . $release_data['TRANSACTION_TYPE'] . $release_data['AMOUNT'] . $release_data['RRNO'] . $release_data['MODE'];
            $release_data['TAMPER_PROOF_SEAL'] = md5($tamper_proof_data);

            if (isset($this->request->server['REMOTE_ADDR'])) {
                $release_data['REMOTE_IP'] = $this->request->server['REMOTE_ADDR'];
            }

            $response_data = $this->sendCurl('https://secure.bluepay.com/interfaces/bp10emu', $release_data);

            return $response_data;
        } else {
            return [];
        }
    }

    public function updateReleaseStatus(int $bluepay_redirect_order_id, int $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "bluepay_redirect_order` SET `release_status` = '" . (int)$status . "' WHERE `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "'");
    }

    public function rebate(int $order_id, float $amount): array {
        $bluepay_redirect_order = $this->getOrder($order_id);

        if (!empty($bluepay_redirect_order) && $bluepay_redirect_order['rebate_status'] != 1) {
            $rebate_data                      = [];
            $rebate_data['MERCHANT']          = $this->config->get('payment_bluepay_redirect_account_id');
            $rebate_data['TRANSACTION_TYPE']  = 'REFUND';
            $rebate_data['MODE']              = strtoupper($this->config->get('payment_bluepay_redirect_test'));
            $rebate_data['RRNO']              = $bluepay_redirect_order['transaction_id'];
            $rebate_data['AMOUNT']            = $amount;
            $rebate_data['APPROVED_URL']      = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_redirect/callback';
            $rebate_data['DECLINED_URL']      = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_redirect/callback';
            $rebate_data['MISSING_URL']       = HTTP_CATALOG . 'index.php?route=extension/payment/bluepay_redirect/callback';
            $tamper_proof_data                = $this->config->get('payment_bluepay_redirect_secret_key') . $rebate_data['MERCHANT'] . $rebate_data['TRANSACTION_TYPE'] . $rebate_data['AMOUNT'] . $rebate_data['RRNO'] . $rebate_data['MODE'];
            $rebate_data['TAMPER_PROOF_SEAL'] = md5($tamper_proof_data);

            if (isset($this->request->server['REMOTE_ADDR'])) {
                $rebate_data['REMOTE_IP'] = $this->request->server['REMOTE_ADDR'];
            }

            $response_data = $this->sendCurl('https://secure.bluepay.com/interfaces/bp10emu', $rebate_data);

            return $response_data;
        } else {
            return [];
        }
    }

    public function updateRebateStatus(int $bluepay_redirect_order_id, int $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "bluepay_redirect_order` SET `rebate_status` = '" . (int)$status . "' WHERE `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "'");
    }

    public function updateTransactionId(int $bluepay_redirect_order_id, int $transaction_id): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "bluepay_redirect_order` SET `transaction_id` = '" . (int)$transaction_id . "' WHERE `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "'");
    }

    public function getOrder(int $order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bluepay_redirect_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        if ($query->num_rows) {
            $order                 = $query->row;
            $order['transactions'] = $this->getTransactions($order['bluepay_redirect_order_id']);

            return $order;
        } else {
            return [];
        }
    }

    private function getTransactions(int $bluepay_redirect_order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bluepay_redirect_order_transaction` WHERE `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "'");

        if ($query->num_rows) {
            return $query->rows;
        } else {
            return [];
        }
    }

    public function addTransaction(int $bluepay_redirect_order_id, string $type, float $total): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "bluepay_redirect_order_transaction` SET `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$total . "'");
    }

    public function getTotalReleased(int $bluepay_redirect_order_id): float {
        $query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "bluepay_redirect_order_transaction` WHERE `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "' AND (`type` = 'payment' OR `type` = 'rebate')");

        return (float)$query->row['total'];
    }

    public function getTotalRebated(int $bluepay_redirect_order_id): float {
        $query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "bluepay_redirect_order_transaction` WHERE `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "' AND `type` = 'rebate'");

        return (float)$query->row['total'];
    }

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

    public function callback(): void {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($this->request->get));
    }

    public function logger($message): void {
        if ($this->config->get('payment_bluepay_redirect_debug') == 1) {
            $log = new \Log('bluepay_redirect.log');
            $log->write($message);
        }
    }
}
