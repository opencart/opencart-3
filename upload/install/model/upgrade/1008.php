<?php
class ModelUpgrade1008 extends Model {
    public function upgrade() {
        //  Option
        $this->db->query("UPDATE `" . DB_PREFIX . "option` SET `type` = 'radio' WHERE `type` = 'image'");

        // Event
        $query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "event' AND COLUMN_NAME = 'status'");

        if (!$query->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "event` ADD `status` TINYINT(1) NOT NULL AFTER `action`");
        }

        $query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "event' AND COLUMN_NAME = 'date_added'");

        if (!$query->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "event` ADD `date_added` DATETIME NOT NULL AFTER `status`");
        }

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = 'dashboard'");

        if (!$query->num_rows) {
            // extension
            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'dashboard', `code` = 'activity'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'dashboard', `code` = 'sale'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'dashboard', `code` = 'recent'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'dashboard', `code` = 'order'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'dashboard', `code` = 'online'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'dashboard', `code` = 'map'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'dashboard', `code` = 'customer'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'dashboard', `code` = 'chart'");

            // setting
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_activity', `key` = 'dashboard_activity_status', `value` = '1', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_activity', `key` = 'dashboard_activity_sort_order', `value` = '7', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_sale', `key` = 'dashboard_sale_status', `value` = '1', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_sale', `key` = 'dashboard_sale_width', `value` = '3', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_chart', `key` = 'dashboard_chart_status', `value` = '1', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_chart', `key` = 'dashboard_chart_width', `value` = '6', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_customer', `key` = 'dashboard_customer_status', `value` = '1', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_customer', `key` = 'dashboard_customer_width', `value` = '3', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_map', `key` = 'dashboard_map_status', `value` = '1', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_map', `key` = 'dashboard_map_width', `value` = '6', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_online', `key` = 'dashboard_online_status', `value` = '1', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_online', `key` = 'dashboard_online_width', `value` = '3', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_order', `key` = 'dashboard_order_sort_order', `value` = '1', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_order', `key` = 'dashboard_order_status', `value` = '1', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_order', `key` = 'dashboard_order_width', `value` = '3', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_sale', `key` = 'dashboard_sale_sort_order', `value` = '2', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_customer', `key` = 'dashboard_customer_sort_order', `value` = '3', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_online', `key` = 'dashboard_online_sort_order', `value` = '4', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_map', `key` = 'dashboard_map_sort_order', `value` = '5', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_chart', `key` = 'dashboard_chart_sort_order', `value` = '6', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_recent', `key` = 'dashboard_recent_status', `value` = '1', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_recent', `key` = 'dashboard_recent_sort_order', `value` = '8', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_activity', `key` = 'dashboard_activity_width', `value` = '4', `serialized` = '0'");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'dashboard_recent', `key` = 'dashboard_recent_width', `value` = '8', `serialized` = '0'");

            // Coupon - Date Start
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "coupon' AND COLUMN_NAME = 'date_start' AND COLUMN_DEFAULT = '0000-00-00'");

			if ($query->num_rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "coupon` MODIFY `date_start` date NOT NULL");
			}

            // Coupon - Date End
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "coupon' AND COLUMN_NAME = 'date_end' AND COLUMN_DEFAULT = '0000-00-00'");

			if ($query->num_rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "coupon` MODIFY `date_end` date NOT NULL");
			}

            // Product - Date Available
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "product' AND COLUMN_NAME = 'date_available' AND COLUMN_DEFAULT = '0000-00-00'");

			if ($query->num_rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "product` MODIFY `date_available` date NOT NULL");
			}

            // Product Discount - Date Start
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "product_discount' AND COLUMN_NAME = 'date_start' AND COLUMN_DEFAULT = '0000-00-00'");

			if ($query->num_rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_discount` MODIFY `date_start` date NOT NULL");
			}

			// Product Discount - Date End
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "product_discount' AND COLUMN_NAME = 'date_end' AND COLUMN_DEFAULT = '0000-00-00'");

			if ($query->num_rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_discount` MODIFY `date_end` date NOT NULL");
			}

            // Product Special - Date Start
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "product_special' AND COLUMN_NAME = 'date_start' AND COLUMN_DEFAULT = '0000-00-00'");

			if ($query->num_rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_special` MODIFY `date_start` date NOT NULL");
			}

			// Product Special - Date End
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "product_special' AND COLUMN_NAME = 'date_end' AND COLUMN_DEFAULT = '0000-00-00'");

			if ($query->num_rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_special` MODIFY `date_end` date NOT NULL");
			}

            // Return - Date Ordered
			$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "return' AND COLUMN_NAME = 'date_ordered' AND COLUMN_DEFAULT = '0000-00-00'");

			if ($query->num_rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "return` MODIFY `date_ordered` date NOT NULL");
			}
        }

        // Add extension download for the admin extension store
        $query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "modification' AND COLUMN_NAME = 'extension_download_id'");

        if (!$query->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "modification` ADD `extension_download_id` INT(11) NOT NULL AFTER `modification_id`");
        }
    }
}
