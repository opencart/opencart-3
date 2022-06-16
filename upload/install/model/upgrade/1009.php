<?php
class ModelUpgrade1009 extends Model {
	public function upgrade() {
		// Config
		$config = new \Config();
			
		$setting_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '0'");
			
		foreach ($setting_query->rows as $setting) {
			$config->set($setting['key'], $setting['value']);
		}
			
		// Affiliate customer merge code
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate'");		
		
		if ($query->num_rows) {
			// Removing affiliate and moving to the customer account.
			$affiliate_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "affiliate`");
			
			foreach ($affiliate_query->rows as $affiliate) {
				$customer_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE `email` = '" . $this->db->escape($affiliate['email']) . "'");
				
				if (!$customer_query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer` SET `customer_group_id` = '" . (int)$config->get('config_customer_group_id') . "', `language_id` = '" . (int)$config->get('config_customer_group_id') . "', `firstname` = '" . $this->db->escape($affiliate['firstname']) . "', `lastname` = '" . $this->db->escape($affiliate['lastname']) . "', `email` = '" . $this->db->escape($affiliate['email']) . "', `telephone` = '" . $this->db->escape($affiliate['telephone']) . "', `password` = '" . $this->db->escape($affiliate['password']) . "', `salt` = '" . $this->db->escape($affiliate['salt']) . "', `cart` = '" . $this->db->escape('') . "', `wishlist` = '" . $this->db->escape('') . "', `newsletter` = '0', `custom_field` = '" . $this->db->escape('') . "', `ip` = '" . $this->db->escape($affiliate['ip']) . "', `status` = '" . $this->db->escape($affiliate['status']) . "', `approved` = '" . (int)$affiliate['approved'] . "', `date_added` = '" . $this->db->escape($affiliate['date_added']) . "'");
					
					$customer_id = $this->db->getLastId();
					
					$this->db->query("INSERT INTO `" . DB_PREFIX . "address` SET `customer_id` = '" . (int)$customer_id . "', `firstname` = '" . $this->db->escape($affiliate['firstname']) . "', `lastname` = '" . $this->db->escape($affiliate['lastname']) . "', `company` = '" . $this->db->escape($affiliate['company']) . "', `address_1` = '" . $this->db->escape($affiliate['address_1']) . "', `address_2` = '" . $this->db->escape($affiliate['address_2']) . "', `city` = '" . $this->db->escape($affiliate['city']) . "', `postcode` = '" . $this->db->escape($affiliate['postcode']) . "', `zone_id` = '" . (int)$affiliate['zone_id'] . "', `country_id` = '" . (int)$affiliate['country_id'] . "', `custom_field` = '" . $this->db->escape('') . "'");
			
					$address_id = $this->db->getLastId();
			
					$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `address_id` = '" . (int)$address_id . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
				} else {
					$customer_id = $customer_query->row['customer_id'];
				}
				
				$customer_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_affiliate` WHERE `customer_id` = '" . (int)$customer_id . "'");
				
				if (!$customer_query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_affiliate` SET `customer_id` = '" . (int)$customer_id . "', `company` = '" . $this->db->escape($affiliate['company']) . "', `tracking` = '" . $this->db->escape($affiliate['code']) . "', `commission` = '" . (float)$affiliate['commission'] . "', `tax` = '" . $this->db->escape($affiliate['tax']) . "', `payment` = '" . $this->db->escape($affiliate['payment']) . "', `cheque` = '" . $this->db->escape($affiliate['cheque']) . "', `paypal` = '" . $this->db->escape($affiliate['paypal']) . "', `bank_name` = '" . $this->db->escape($affiliate['bank_name']) . "', `bank_branch_number` = '" . $this->db->escape($affiliate['bank_branch_number']) . "', `bank_account_name` = '" . $this->db->escape($affiliate['bank_account_name']) . "', `bank_account_number` = '" . $this->db->escape($affiliate['bank_account_number']) . "', `status` = '" . (int)$affiliate['status'] . "', `date_added` = '" . $this->db->escape($affiliate['date_added']) . "'");
				}
				
				$affiliate_transaction_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "affiliate_transaction` WHERE `affiliate_id` = '" . (int)$affiliate['affiliate_id'] . "'");
			
				foreach ($affiliate_transaction_query->rows as $affiliate_transaction) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_transaction` SET `customer_id` = '" . (int)$customer_id . "', `order_id` = '" . (int)$affiliate_transaction['order_id'] . "', `description` = '" . $this->db->escape($affiliate_transaction['description']) . "', `amount` = '" . (float)$affiliate_transaction['amount'] . "', `date_added` = '" . $this->db->escape($affiliate_transaction['date_added']) . "'");
					
					$this->db->query("DELETE FROM `" . DB_PREFIX . "affiliate_transaction` WHERE `affiliate_transaction_id` = '" . (int)$affiliate_transaction['affiliate_transaction_id'] . "'");
				}
				
				$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `affiliate_id` = '" . (int)$customer_id . "' WHERE `affiliate_id` = '" . (int)$affiliate['affiliate_id'] . "'");
			}
			
			$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate`");
			
			$affiliate_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate_activity'");
			
			if (!$affiliate_query->num_rows) {
				$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate_activity`");
			}
			
			$affiliate_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate_login'");
			
			if (!$affiliate_query->num_rows) {			
				$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate_login`");
			}
			
			$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate_transaction`");
		}
	
		// Api
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "api' AND COLUMN_NAME = 'name'");

		if ($query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "api` DROP COLUMN `username`");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "api` CHANGE COLUMN `name` `username` VARCHAR(64) NOT NULL");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "api` MODIFY `username` VARCHAR(64) NOT NULL AFTER `api_id`");
		}
		
		// Events
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "event' AND COLUMN_NAME = 'sort_order'");
		
		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "event` ADD `sort_order` INT(3) NOT NULL AFTER `action`");
		}

		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "event' AND COLUMN_NAME = 'date_added'");
		
		if ($query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "event` DROP COLUMN `date_added`");
		}
		
		// Events - Admin GDPR
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `trigger` = 'admin/model/customer/gdpr/editStatus/after'");
		
		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `code` = 'admin_mail_gdpr', `trigger` = 'admin/model/customer/gdpr/editStatus/after', `action` = 'mail/gdpr', `status` = '1', `sort_order` = '0'");
		}
		
		// Events - Catalog GDPR
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `trigger` = 'catalog/model/account/gdpr/addGdpr/after'");
		
		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `code` = 'mail_gdpr', `trigger` = 'catalog/model/account/gdpr/addGdpr/after', `action` = 'mail/gdpr', `status` = '1', `sort_order` = '0'");
		}
		
		// Layouts - GDPR Information
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "layout` WHERE `name` = 'Account'");
		
		if ($query->num_rows) {
			$layout_route = $this->db->query("SELECT * FROM `" . DB_PREFIX . "layout_route` WHERE `layout_id` = '" . (int)$query->row['layout_id'] . "' AND `route` = 'information/gdpr'");
			
			if (!$layout_route->num_rows) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "layout_route` SET `layout_id` = '" . (int)$query->row['layout_id'] . "', `store_id` = '0', `route` = 'information/gdpr'");
			}
		}

		// Returns
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = 'admin/model/sale/returns/addReturnHistory/after', `action` = 'mail/returns' WHERE `action` = 'mail/return'");
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = 'catalog/model/account/returns/addReturn/after' WHERE `action` = 'event/statistics/addReturn'");
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = 'catalog/model/account/returns/addReturn/after' WHERE `action` = 'event/activity/addReturn'");		
		$this->db->query("UPDATE `" . DB_PREFIX . "statistics` SET `code` = 'returns' WHERE `code` = 'return'");
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `trigger` = 'admin/model/sale/return/deleteReturn/after'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `trigger` = 'admin/model/sale/return/addReturn/after'");
		
		$event = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `trigger` = 'admin/model/sale/returns/addReturn/after'");
		
		if (!$event->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `trigger` = 'admin/model/sale/returns/deleteReturn/after', `action` = 'event/statistics/deleteReturn', `status` = '1', `sort_order` = '0'");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `trigger` = 'admin/model/sale/returns/addReturn/after', `action` = 'event/statistics/addReturn', `status` = '1', `sort_order` = '0'");
		}
		
		$event = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `trigger` = 'admin/model/sale/review/addReview/after'");
		
		if (!$event->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `trigger` = 'admin/model/sale/returns/deleteReview/after', `action` = 'event/statistics/deleteReview', `status` = '1', `sort_order` = '0'");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `trigger` = 'admin/model/sale/review/addReview/after', `action` = 'event/statistics/addReview', `status` = '1', `sort_order` = '0'");
		}
		
		$config_captcha_page = json_decode((array)$config->get('config_captcha_page'), true);
			
		$search = array_search('return', $config_captcha_page);
			
		if ($search) {
			$config_captcha_page[$search] = str_replace($config_captcha_page[$search], 'returns', $config_captcha_page[$search]);
				
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '" . json_encode($this->db->escape($config_captcha_page)) . "' WHERE `key` = 'config_captcha_page'");
		}		
		
		// Config Session Expire
		if (!$config->has('config_session_expire')) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_session_expire', `value` = '3600000000', `serialized` = '0'");
		}
		
		// Config Cookie ID
		if (!$config->has('config_cookie_id')) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_cookie_id', `value` = '0', `serialized` = '0'");
		}
		
		// Config GDPR ID
		if (!$config->has('config_gdpr_id')) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_gdpr_id', `value` = '0', `serialized` = '0'");
		}
		
		if (!$config->has('config_gdpr_limit')) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_gdpr_limit', `value` = '180', `serialized` = '0'");
		}
		
		// Config affiliate Status ID
		if (!$config->has('config_affiliate_status')) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_affiliate_status', `value` = '1', `serialized` = '0'");
		}
		
		// Config affiliate expire
		if (!$config->has('config_affiliate_expire')) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_affiliate_expire', `value` = '3600000000', `serialized` = '0'");
		}
		
		// Country address_format_id
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "country' AND COLUMN_NAME = 'address_format_id'");
		
		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "country` ADD COLUMN `address_format_id` int(11) NOT NULL AFTER `address_format`");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "country` DROP COLUMN `address_format`");
		}		
		
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "address_format'");
		
		if ($query->num_rows) {
			$address_format_total = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "address_format`");
			
			if (!$address_format_total->row['total']) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "address_format` SET `name` = 'Address Format', `address_format` = '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city}, {zone} {postcode}\r\n{country}'");
			}
		}
		
		// Country
		$this->db->query("UPDATE `" . DB_PREFIX . "country` SET `address_format_id` = '1' WHERE `address_format_id` = '0'");
		
		// OPENCART_SERVER
		$upgrade = true;
		
		$file = DIR_OPENCART . 'admin/config.php';
		
		$lines = file(DIR_OPENCART . 'admin/config.php');

		foreach ($lines as $line) {
			if (strpos(strtoupper($line), 'OPENCART_SERVER') !== false) {
				$upgrade = false;
				break;
			}
		}

		if ($upgrade) {
			$output = '';

			foreach ($lines as $line_id => $line) {
				if (strpos($line, 'DB_PREFIX') !== false) {
					$output .= $line . "\n\n";
					$output .= 'define(\'OPENCART_SERVER\', \'http://www.opencart.com/\');' . "\n";
				} else {
					$output .= $line;
				}
			}

			$handle = fopen($file, 'w');

			fwrite($handle, $output);

			fclose($handle);
		}
	
		$files = glob(DIR_OPENCART . '{config.php,admin/config.php}', GLOB_BRACE);

		foreach ($files as $file) {
			$lines = file($file);
	
			for ($i = 0; $i < count($lines); $i++) { 
				if ((strpos($lines[$i], 'DIR_IMAGE') !== false) && (strpos($lines[$i + 1], 'DIR_STORAGE') === false)) {
					array_splice($lines, $i + 1, 0, array('define(\'DIR_STORAGE\', DIR_SYSTEM . \'storage/\');'));
				}

				if ((strpos($lines[$i], 'DIR_MODIFICATION') !== false) && (strpos($lines[$i + 1], 'DIR_SESSION') === false)) {
					array_splice($lines, $i + 1, 0, array('define(\'DIR_SESSION\', DIR_STORAGE . \'session/\');'));
				}

				if (strpos($lines[$i], 'DIR_CACHE') !== false) {
					$lines[$i] = 'define(\'DIR_CACHE\', DIR_STORAGE . \'cache/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_DOWNLOAD') !== false) {
					$lines[$i] = 'define(\'DIR_DOWNLOAD\', DIR_STORAGE . \'download/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_LOGS') !== false) {
					$lines[$i] = 'define(\'DIR_LOGS\', DIR_STORAGE . \'logs/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_MODIFICATION') !== false) {
					$lines[$i] = 'define(\'DIR_MODIFICATION\', DIR_STORAGE . \'modification/\');' . "\n";
				}
				
				if (strpos($lines[$i], 'DIR_SESSION') !== false) {
					$lines[$i] = 'define(\'DIR_SESSION\', DIR_STORAGE . \'session/\');' . "\n";
				}				
	
				if (strpos($lines[$i], 'DIR_UPLOAD') !== false) {
					$lines[$i] = 'define(\'DIR_UPLOAD\', DIR_STORAGE . \'upload/\');' . "\n";
				}
			}
			
			$output = implode('', $lines);
			
			$handle = fopen($file, 'w');

			fwrite($handle, $output);

			fclose($handle);
		}
	}
}
