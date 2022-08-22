<?php
class ModelUpgrade1009 extends Model {
	public function upgrade() {
		// Affiliate customer merge code
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate'");		
		
		if ($query->num_rows) {
			// Config
			$config = new \Config();
				
			$setting_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '0'");
				
			foreach ($setting_query->rows as $setting) {
				$config->set($setting['key'], $setting['value']);
			}
			
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
		
		// Events - Promotion
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `trigger` = 'admin/controller/extension/extension/promotion/after'");
		
		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `code` = 'admin_promotion', `trigger` = 'admin/controller/extension/extension/promotion/after', `action` = 'extension/extension/promotion/getList', `status` = '1', `sort_order` = '0'");
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
		
		// Account Subscription Information
		$event = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `trigger` = 'catalog/controller/account/account/after'");
		
		if (!$event->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `code` = 'account_customer_subscription', `trigger` = 'catalog/controller/account/account/after', `action` = 'extension/module/account/subscription', `status` = '1', `sort_order` = '0'");
		}
		
		// Config Session Expire
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_session_expire'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_session_expire', `value` = '86400', `serialized` = '0'");
		}
		
		// Config - SameSite
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_session_samesite'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_session_samesite', `value` = 'Strict', `serialized` = '0'");
		}

		// Config Cookie ID
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_cookie_id'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_cookie_id', `value` = '0', `serialized` = '0'");
		}

		// Config GDPR ID
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_gdpr_id'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_gdpr_id', `value` = '0', `serialized` = '0'");
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_gdpr_limit'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_gdpr_limit', `value` = '180', `serialized` = '0'");
		}

		// Config affiliate Status ID
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_affiliate_status'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_affiliate_status', `value` = '1', `serialized` = '0'");
		}

		// Config affiliate expire
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_affiliate_expire'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_affiliate_expire', `value` = '86400', `serialized` = '0'");
		}

		// Config Subscriptions
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_subscription_status_id'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_subscription_status_id', `value` = '1', `serialized` = '0'");
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_subscription_active_status_id'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_subscription_active_status_id', `value` = '2', `serialized` = '0'");
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_subscription_expired_status_id'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_subscription_expired_status_id', `value` = '6', `serialized` = '0'");
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_subscription_canceled_status_id'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_subscription_canceled_status_id', `value` = '4', `serialized` = '0'");
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_subscription_failed_status_id'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_subscription_failed_status_id', `value` = '3', `serialized` = '0'");
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_subscription_denied_status_id'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_subscription_denied_status_id', `value` = '5', `serialized` = '0'");
		}

		// Config - Fraud Status ID
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_fraud_status_id'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_fraud_status_id', `value` = '8', `serialized` = '0'");
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
		
		// Addresses
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "address' AND COLUMN_NAME = 'default'");
		
		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "address` ADD COLUMN `default` tinyint(1) NOT NULL AFTER `custom_field`");
		}
		
		// Information - Subscriptions
		$information_id = $this->db->query("INSERT INTO `" . DB_PREFIX . "information` SET `bottom` = '1', `sort_order` = '5', `status` = '1'");
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "information_description` SET (`information_id` = '" . (int)$information_id . "', `language_id` = '1', `title` = 'Subscriptions', `description` = 'Within the next couple of months, our store will be introducing a new subscription system where customers will have the ability to handle customer payments with their accounts and our store to provide better services with larger subscription products.', `meta_title` = 'Subscriptions', `meta_description` = '', `meta_keyword` = ''");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_store` SET `information_id` = '" . (int)$information_id . "', `store_id` = '0'");
		
		// Subscription Statuses
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_status` SET `subscription_status_id` = '1', `language_id` = '1', `name` = 'Pending'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_status` SET `subscription_status_id` = '2', `language_id` = '1', `name` = 'Active'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_status` SET `subscription_status_id` = '3', `language_id` = '1', `name` = 'Failed'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_status` SET `subscription_status_id` = '4', `language_id` = '1', `name` = 'Cancelled'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_status` SET `subscription_status_id` = '5', `language_id` = '1', `name` = 'Denied'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_status` SET `subscription_status_id` = '6', `language_id` = '1', `name` = 'Expired'");
		
		// Cart - Subscriptions
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "cart' AND COLUMN_NAME = 'subscription_plan_id'");

		if (!$query->num_rows) {
			$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "cart`");

			$this->db->query("ALTER TABLE `" . DB_PREFIX . "cart` DROP COLUMN `recurring_id`");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "cart` ADD COLUMN `subscription_plan_id` int(11) NOT NULL AFTER `product_id`");
		}
		
		// Recurring to Subscription Plans
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "recurring' AND TABLE_NAME = '" . DB_PREFIX . "recurring_description' AND TABLE_NAME = '" . DB_PREFIX . "product_recurring'");
		
		if ($query->num_rows) {
			$languages = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language`");
			
			if ($languages->num_rows) {
				foreach ($languages->rows as $language) {
					$recurrings = $this->db->query("SELECT * FROM `" . DB_PREFIX . "recurring` r LEFT JOIN `" . DB_PREFIX . "recurring_description` rd ON (r.`recurring_id` = rd.`recurring_id`) WHERE rd.`language_id` = '" . (int)$language['language_id'] . "'");
					
					if ($recurrings->num_rows) {
						$recurring_data = array();
						
						foreach ($recurrings->rows as $recurring) {
							$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_plan` SET `trial_price` = '" . (float)$recurring['trial_price'] . "', `trial_frequency` = '" . $this->db->escape($recurring['trial_frequency']) . "', `trial_duration` = '" . (int)$recurring['trial_duration'] . "', `trial_cycle` = '" . (int)$recurring['trial_cycle'] . "', `trial_status` = '" . (int)$recurring['trial_status'] . "', `price` = '" . (float)$recurring['price'] . "', `frequency` = '" . $this->db->escape($recurring['frequency']) . "', `duration` = '" . (int)$recurring['duration'] . "', `cycle` = '" . (int)$recurring['cycle'] . "', `status` = '" . (bool)$recurring['status'] . "', `sort_order` = '" . (int)$recurring['sort_order'] . "'");
							
							$subscription_plan_id = $this->db->getLastId();
							
							$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_plan_description` SET `subscription_plan_id` = '" . (int)$subscription_plan_id . "', `name` = '" . $this->db->escape($recurring['recurring_name']) . "', `description` = '" . $this->db->escape($recurring['recurring_description']) . "', `language_id` = '" . (int)$language['language_id'] . "'");
						}
					}
				}
				
				if (isset($subscription_plan_id)) {
					$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "recurring_description`");
					$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_recurring`");
					$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "recurring`");
				}
			}
		}
		
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
