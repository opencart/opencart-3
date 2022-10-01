<?php
class ModelExtensionFraudFraudLabsPro extends Model {
    public function install(): void {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "fraudlabspro` (
				`order_id` VARCHAR(11) NOT NULL,
				`is_country_match` CHAR(2) NOT NULL,
				`is_high_risk_country` CHAR(2) NOT NULL,
				`distance_in_km` VARCHAR(10) NOT NULL,
				`distance_in_mile` VARCHAR(10) NOT NULL,
				`ip_address` VARCHAR(15) NOT NULL,
				`ip_country` VARCHAR(2) NOT NULL,
				`ip_continent` VARCHAR(20) NOT NULL,
				`ip_region` VARCHAR(21) NOT NULL,
				`ip_city` VARCHAR(21) NOT NULL,
				`ip_latitude` VARCHAR(21) NOT NULL,
				`ip_longitude` VARCHAR(21) NOT NULL,
				`ip_timezone` VARCHAR(10) NOT NULL,
				`ip_elevation` VARCHAR(10) NOT NULL,
				`ip_domain` VARCHAR(50) NOT NULL,
				`ip_mobile_mnc` VARCHAR(100) NOT NULL,
				`ip_mobile_mcc` VARCHAR(100) NOT NULL,
				`ip_mobile_brand` VARCHAR(100) NOT NULL,
				`ip_netspeed` VARCHAR(10) NOT NULL,
				`ip_isp_name` VARCHAR(50) NOT NULL,
				`ip_usage_type` VARCHAR(30) NOT NULL,
				`is_free_email` CHAR(2) NOT NULL,
				`is_new_domain_name` CHAR(2) NOT NULL,
				`is_proxy_ip_address` CHAR(2) NOT NULL,
				`is_bin_found` CHAR(2) NOT NULL,
				`is_bin_country_match` CHAR(2) NOT NULL,
				`is_bin_name_match` CHAR(2) NOT NULL,
				`is_bin_phone_match` CHAR(2) NOT NULL,
				`is_bin_prepaid` CHAR(2) NOT NULL,
				`is_address_ship_forward` CHAR(2) NOT NULL,
				`is_bill_ship_city_match` CHAR(2) NOT NULL,
				`is_bill_ship_state_match` CHAR(2) NOT NULL,
				`is_bill_ship_country_match` CHAR(2) NOT NULL,
				`is_bill_ship_postal_match` CHAR(2) NOT NULL,
				`is_ip_blacklist` CHAR(2) NOT NULL,
				`is_email_blacklist` CHAR(2) NOT NULL,
				`is_credit_card_blacklist` CHAR(2) NOT NULL,
				`is_device_blacklist` CHAR(2) NOT NULL,
				`is_user_blacklist` CHAR(2) NOT NULL,
				`fraudlabspro_score` CHAR(3) NOT NULL,
				`fraudlabspro_distribution` CHAR(3) NOT NULL,
				`fraudlabspro_status` CHAR(10) NOT NULL,
				`fraudlabspro_id` CHAR(15) NOT NULL,
				`fraudlabspro_error` CHAR(3) NOT NULL,
				`fraudlabspro_message` VARCHAR(50) NOT NULL,
				`fraudlabspro_credits` VARCHAR(10) NOT NULL,
				`api_key` CHAR(32) NOT NULL,
				PRIMARY KEY (`order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

        $this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET `language_id` = '1', `name` = 'Fraud'");

        $status_fraud_id = $this->db->getLastId();

        $this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET `language_id` = '1', `name` = 'Fraud Review'");

        $status_fraud_review_id = $this->db->getLastId();

        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'fraudlabspro', `key` = 'fraud_fraudlabspro_score', `value` = '80', `serialized` = '0'");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'fraudlabspro', `key` = 'fraud_fraudlabspro_order_status_id', `value` = '" . (int)$status_fraud_id . "', `serialized` = '0'");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'fraudlabspro', `key` = 'fraud_fraudlabspro_review_status_id', `value` = '" . (int)$status_fraud_review_id . "', `serialized` = '0'");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'fraudlabspro', `key` = 'fraud_fraudlabspro_approve_status_id', `value` = '2', `serialized` = '0'");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `code` = 'fraudlabspro', `key` = 'fraud_fraudlabspro_reject_status_id', `value` = '8', `serialized` = '0'");

        // Events
        $this->load->model('setting/event');

        $this->model_setting_event->addEvent('fraud_fraudlabspro_history', 'catalog/model/checkout/order/addOrderHistory/after', 'extension/module/fraudlabspro/addOrderHistory');

        $this->cache->delete('order_status.' . (int)$this->config->get('config_language_id'));
    }

    public function uninstall(): void {
        //$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "fraudlabspro`");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_status` WHERE `name` = 'Fraud'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "order_status` WHERE `name` = 'Fraud Review'");

        // Events
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('fraud_fraudlabspro_history');
    }

    public function getOrder(int $order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "fraudlabspro` WHERE `order_id` = '" . (int)$order_id . "'");

        return $query->row;
    }
}