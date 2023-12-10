<?php
/**
 * Class Firstdata
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentFirstdata extends Model {
	/**
	 * getMethod
	 */
    public function getMethod(array $address): array {
        $this->load->language('extension/payment/firstdata');

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_firstdata_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

        if (!$this->config->get('payment_firstdata_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = [];

        if ($status) {
            $method_data = [
                'code'       => 'firstdata',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_firstdata_sort_order')
            ];
        }

        return $method_data;
    }

	/**
	 * addOrder
	 */
    public function addOrder($order_info, $order_ref, $transaction_date) {
        if ($this->config->get('payment_firstdata_auto_settle') == 1) {
            $settle_status = 1;
        } else {
            $settle_status = 0;
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "firstdata_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `order_ref` = '" . $this->db->escape($order_ref) . "', `tdate` = '" . $this->db->escape($transaction_date) . "', `date_added` = NOW(), `date_modified` = NOW(), `capture_status` = '" . (int)$settle_status . "', `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `total` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) . "'");

        return $this->db->getLastId();
    }

	/**
	 * getOrder
	 */
    public function getOrder($order_id) {
        $order = $this->db->query("SELECT * FROM `" . DB_PREFIX . "firstdata_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        return $order->row;
    }

	/**
	 * addTransaction
	 */
    public function addTransaction($fd_order_id, $type, $order_info = []) {
        if (!empty($order_info)) {
            $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        } else {
            $amount = 0.00;
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "firstdata_order_transaction` SET `firstdata_order_id` = '" . (int)$fd_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . (float)$amount . "'");
    }

	/**
	 * Logger
	 */
    public function logger($message) {
        if ($this->config->get('payment_firstdata_debug') == 1) {
            // Log
            $log = new \Log('firstdata.log');
            $log->write($message);
        }
    }

	/**
	 * mapCurrency
	 */
    public function mapCurrency($code) {
        $currency = [];

        $currency = [
            'GBP' => 826,
            'USD' => 840,
            'EUR' => 978,
        ];

        if (array_key_exists($code, $currency)) {
            return $currency[$code];
        } else {
            return false;
        }
    }

	/**
	 * getScoredCards
	 */
    public function getStoredCards() {
        $customer_id = $this->customer->getId();

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "firstdata_card` WHERE `customer_id` = '" . (int)$customer_id . "'");

        return $query->rows;
    }

	/**
	 * storeCard
	 */
    public function storeCard($token, $customer_id, $month, $year, $digits) {
        $existing_card = $this->db->query("SELECT * FROM `" . DB_PREFIX . "firstdata_card` WHERE `token` = '" . $this->db->escape($token) . "' AND `customer_id` = '" . (int)$customer_id . "' LIMIT 1");

        if ($existing_card->num_rows > 0) {
            $this->db->query("UPDATE `" . DB_PREFIX . "firstdata_card` SET `expire_month` = '" . $this->db->escape($month) . "', `expire_year` = '" . $this->db->escape($year) . "', `digits` = '" . $this->db->escape($digits) . "'");
        } else {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "firstdata_card` SET `customer_id` = '" . (int)$customer_id . "', `date_added` = NOW(), `token` = '" . $this->db->escape($token) . "', `expire_month` = '" . $this->db->escape($month) . "', `expire_year` = '" . $this->db->escape($year) . "', `digits` = '" . $this->db->escape($digits) . "'");
        }
    }

	/**
	 * responseHash
	 */
    public function responseHash($total, $currency, $txn_date, $approval_code) {
        $tmp = $total . $this->config->get('payment_firstdata_secret') . $currency . $txn_date . $this->config->get('payment_firstdata_merchant_id') . $approval_code;
        $ascii = bin2hex($tmp);

        return sha1($ascii);
    }

	/**
	 * updateVoidStatus
	 */
    public function updateVoidStatus($order_id, $status) {
        $this->db->query("UPDATE `" . DB_PREFIX . "firstdata_order` SET `void_status` = '" . (int)$status . "' WHERE `order_id` = '" . (int)$order_id . "'");
    }

	/**
	 * updateCaptureStatus
	 */
    public function updateCaptureStatus($order_id, $status) {
        $this->db->query("UPDATE `" . DB_PREFIX . "firstdata_order` SET `capture_status` = '" . (int)$status . "' WHERE `order_id` = '" . (int)$order_id . "'");
    }
}
