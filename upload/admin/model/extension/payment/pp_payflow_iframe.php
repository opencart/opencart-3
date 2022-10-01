<?php
class ModelExtensionPaymentPPPayflowIFrame extends Model {
    public function install(): void {
        $this->db->query("
			CREATE TABLE `" . DB_PREFIX . "paypal_payflow_iframe_order` (
				`order_id` int(11) NOT NULL,
				`secure_token_id` varchar(255) NOT NULL,
				`transaction_reference` varchar(255) DEFAULT NULL,
				`transaction_type` varchar(1) DEFAULT NULL,
				`complete` tinyint(4) NOT NULL DEFAULT '0',
				PRIMARY KEY(`order_id`),
				KEY `secure_token_id` (`secure_token_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci");

        $this->db->query("
			CREATE TABLE `" . DB_PREFIX . "paypal_payflow_iframe_order_transaction` (
				`order_id` int(11) NOT NULL,
				`transaction_reference` varchar(255) NOT NULL,
				`transaction_type` char(1) NOT NULL,
				`time` datetime NOT NULL,
				`amount` decimal(10,4) DEFAULT NULL,
				PRIMARY KEY (`transaction_reference`),
				KEY `order_id` (`order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paypal_payflow_iframe_order`;");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paypal_payflow_iframe_order_transaction`;");
    }

    public function log(string $message): void {
        if ($this->config->get('payment_pp_payflow_iframe_debug')) {
            $log = new \Log('payflow-iframe.log');
            $log->write($message);
        }
    }

    public function getOrder(int $order_id): array {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_payflow_iframe_order` WHERE `order_id` = '" . (int)$order_id . "'");

        if ($result->num_rows) {
            $order = $result->row;
        } else {
            $order = [];
        }

        return $order;
    }

    public function updateOrderStatus(int $order_id, int $status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "paypal_payflow_iframe_order` SET `complete` = '" . (int)$status . "' WHERE order_id = '" . (int)$order_id . "'");
    }

    public function addTransaction(array $data): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "paypal_payflow_iframe_order_transaction` SET `order_id` = '" . (int)$data['order_id'] . "', `transaction_reference` = '" . $this->db->escape($data['transaction_reference']) . "', `transaction_type` = '" . $this->db->escape($data['type']) . "', `time` = NOW(),`amount` = '" . $this->db->escape($data['amount']) . "'");
    }

    public function getTransactions(int $order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_payflow_iframe_order_transaction` WHERE `order_id` = '" . (int)$order_id . "' ORDER BY `time` ASC");

        return $query->rows;
    }

    public function getTransaction(string $transaction_reference): array {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_payflow_iframe_order_transaction` WHERE `transaction_reference` = '" . $this->db->escape($transaction_reference) . "'");

        if ($result->num_rows) {
            $transaction = $result->row;
        } else {
            $transaction = [];
        }

        return $transaction;
    }

    public function call(array $data): array {
        $default_parameters = [
            'USER'         => $this->config->get('payment_pp_payflow_iframe_user'),
            'VENDOR'       => $this->config->get('payment_pp_payflow_iframe_vendor'),
            'PWD'          => $this->config->get('payment_pp_payflow_iframe_password'),
            'PARTNER'      => $this->config->get('payment_pp_payflow_iframe_partner'),
            'BUTTONSOURCE' => 'OpenCart_Cart_PFP'
        ];

        $call_parameters = array_merge($data, $default_parameters);

        if ($this->config->get('payment_pp_payflow_iframe_test')) {
            $url = 'https://pilot-payflowpro.paypal.com';
        } else {
            $url = 'https://payflowpro.paypal.com';
        }

        $query_params = [];

        foreach ($call_parameters as $key => $value) {
            $query_params[] = $key . '=' . utf8_decode($value);
        }

        $this->log('Call data: ' . implode('&', $query_params));

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, implode('&', $query_params));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        $this->log('Response data: ' . $response);

        $response_params = [];

        parse_str($response, $response_params);

        return $response_params;
    }
}