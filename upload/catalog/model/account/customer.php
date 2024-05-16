<?php
/**
 * Class Customer
 *
 * @package Catalog\Model\Account
 */
class ModelAccountCustomer extends Model {
	/**
	 * Add Customer
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function addCustomer(array $data): int {
		if (isset($data['customer_group_id']) && in_array($data['customer_group_id'], (array)$this->config->get('config_customer_group_display'))) {
			$customer_group_id = $data['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		// Customer Groups
		$this->load->model('account/customer_group');

		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer` SET `customer_group_id` = '" . (int)$customer_group_id . "', `store_id` = '" . (int)$this->config->get('config_store_id') . "', `language_id` = '" . (int)$this->config->get('config_language_id') . "', `firstname` = '" . $this->db->escape($data['firstname']) . "', `lastname` = '" . $this->db->escape($data['lastname']) . "', `email` = '" . $this->db->escape($data['email']) . "', `telephone` = '" . $this->db->escape($data['telephone']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', `password` = '" . $this->db->escape(password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT)) . "', `newsletter` = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', `ip` = '" . oc_get_ip() . "', `status` = '" . (int)!$customer_group_info['approval'] . "', `date_added` = NOW()");

		$customer_id = $this->db->getLastId();

		if ($customer_group_info['approval']) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_approval` SET `customer_id` = '" . (int)$customer_id . "', `type` = 'customer', `date_added` = NOW()");
		}

		return $customer_id;
	}

	/**
	 * Edit Customer
	 *
	 * @param int                  $customer_id
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function editCustomer(int $customer_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `firstname` = '" . $this->db->escape($data['firstname']) . "', `lastname` = '" . $this->db->escape($data['lastname']) . "', `email` = '" . $this->db->escape($data['email']) . "', `telephone` = '" . $this->db->escape($data['telephone']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
	}

	/**
	 * Edit Password
	 *
	 * @param string $email
	 * @param string $password
	 *
	 * @return void
	 */
	public function editPassword(string $email, string $password): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `password` = '" . $this->db->escape(password_hash(html_entity_decode($password, ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT)) . "', `code` = '' WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");
	}

	/**
	 * Edit Address Id
	 *
	 * @param int $customer_id
	 * @param int $address_id
	 *
	 * @return void
	 */
	public function editAddressId(int $customer_id, int $address_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `address_id` = '" . (int)$address_id . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "address` SET `default` = '1' WHERE `address_id` != '" . (int)$address_id . "' AND `customer_id` = '" . (int)$customer_id . "'");
	}

	/**
	 * Edit Code
	 *
	 * @param string $email
	 * @param string $code
	 *
	 * @return void
	 */
	public function editCode(string $email, string $code): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `code` = '" . $this->db->escape($code) . "' WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");
	}

	/**
	 * Edit Newsletter
	 *
	 * @param int $newsletter
	 *
	 * @return void
	 */
	public function editNewsletter(int $newsletter): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `newsletter` = '" . (int)$newsletter . "' WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");
	}

	/**
	 * Get Customer
	 *
	 * @param int $customer_id
	 *
	 * @return array<string, mixed>
	 */
	public function getCustomer(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE `customer_id` = '" . (int)$customer_id . "'");

		if ($query->num_rows) {
			return $query->row + ['custom_field' => json_decode($query->row['custom_field'], true)];
		} else {
			return [];
		}
	}

	/**
	 * Get Customer By Email
	 *
	 * @param string $email
	 *
	 * @return array<string, mixed>
	 */
	public function getCustomerByEmail(string $email): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");

		if ($query->num_rows) {
			return $query->row + ['custom_field' => json_decode($query->row['custom_field'], true)];
		} else {
			return [];
		}
	}

	/**
	 * Get Customer By Code
	 *
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 */
	public function getCustomerByCode(string $code): array {
		$query = $this->db->query("SELECT `customer_id`, `firstname`, `lastname`, `email` FROM `" . DB_PREFIX . "customer` WHERE `code` = '" . $this->db->escape($code) . "' AND `code` != ''");

		return $query->row;
	}

	/**
	 * Get Customer By Token
	 *
	 * @param string $token
	 *
	 * @return array<string, mixed>
	 */
	public function getCustomerByToken(string $token): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE `token` = '" . $this->db->escape($token) . "' AND `token` != ''");

		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `token` = ''");

		if ($query->num_rows) {
			return $query->row + ['custom_field' => json_decode($query->row['custom_field'], true)];
		} else {
			return [];
		}
	}

	/**
	 * Get Total Customers By Email
	 *
	 * @param string $email
	 *
	 * @return int
	 */
	public function getTotalCustomersByEmail(string $email): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer` WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Add Transaction
	 *
	 * @param int    $customer_id
	 * @param string $description
	 * @param float  $amount
	 * @param int    $order_id
	 *
	 * @return void
	 */
	public function addTransaction(int $customer_id, string $description, float $amount = 0, int $order_id = 0): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_transaction` SET `customer_id` = '" . (int)$customer_id . "', `order_id` = '" . (float)$order_id . "', `description` = '" . $this->db->escape($description) . "', `amount` = '" . (float)$amount . "', `date_added` = NOW()");
	}

	/**
	 * Delete Transaction By Order ID
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deleteTransactionByOrderId(int $order_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_transaction` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Get Transaction Total
	 *
	 * @param int $customer_id
	 *
	 * @return float
	 */
	public function getTransactionTotal(int $customer_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (float)$query->row['total'];
	}

	/**
	 * Get Total Transactions By Order ID
	 *
	 * @param int $order_id
	 *
	 * @return int
	 */
	public function getTotalTransactionsByOrderId(int $order_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_transaction` WHERE `order_id` = '" . (int)$order_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Reward Total
	 *
	 * @param int $customer_id
	 *
	 * @return int
	 */
	public function getRewardTotal(int $customer_id): int {
		$query = $this->db->query("SELECT SUM(`points`) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer_id . "'");

		if ($query->num_rows) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	/**
	 * Get Ips
	 *
	 * @param int $customer_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getIps(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ip` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return $query->rows;
	}

	/**
	 * Add Login
	 *
	 * @param int    $customer_id
	 * @param string $ip
	 * @param string $country
	 *
	 * @return void
	 */
	public function addLogin(int $customer_id, string $ip, string $country = ''): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_ip` SET `customer_id` = '" . (int)$customer_id . "', `store_id` = '" . (int)$this->config->get('config_store_id') . "', `ip` = '" . $this->db->escape($ip) . "', `country` = '" . $this->db->escape($country) . "', `date_added` = NOW()");
	}

	/**
	 * Add Login Attempt
	 *
	 * @param string $email
	 *
	 * @return void
	 */
	public function addLoginAttempt(string $email): void {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_login` WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower((string)$email)) . "' AND `ip` = '" . oc_get_ip() . "'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_login` SET `email` = '" . $this->db->escape(oc_strtolower((string)$email)) . "', `ip` = '" . oc_get_ip() . "', `total` = '1', `date_added` = '" . $this->db->escape(date('Y-m-d H:i:s')) . "', `date_modified` = '" . $this->db->escape(date('Y-m-d H:i:s')) . "'");
		} else {
			$this->db->query("UPDATE `" . DB_PREFIX . "customer_login` SET `total` = (`total` + 1), `date_modified` = '" . $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE `customer_login_id` = '" . (int)$query->row['customer_login_id'] . "'");
		}
	}

	/**
	 * Get Login Attempts
	 *
	 * @param string $email
	 *
	 * @return array<string, mixed>
	 */
	public function getLoginAttempts(string $email): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_login` WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");

		return $query->row;
	}

	/**
	 * Delete Login Attempts
	 *
	 * @param string $email
	 *
	 * @return void
	 */
	public function deleteLoginAttempts(string $email): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");
	}

	/**
	 * Add Affiliate
	 *
	 * @param int                  $customer_id
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function addAffiliate(int $customer_id, array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_affiliate` SET `customer_id` = '" . (int)$customer_id . "', `company` = '" . $this->db->escape($data['company']) . "', `website` = '" . $this->db->escape($data['website']) . "', `tracking` = '" . $this->db->escape(oc_token(10)) . "', `commission` = '" . (float)$this->config->get('config_affiliate_commission') . "', `tax` = '" . $this->db->escape($data['tax']) . "', `payment` = '" . $this->db->escape($data['payment']) . "', `cheque` = '" . $this->db->escape($data['cheque']) . "', `paypal` = '" . $this->db->escape($data['paypal']) . "', `bank_name` = '" . $this->db->escape($data['bank_name']) . "', `bank_branch_number` = '" . $this->db->escape($data['bank_branch_number']) . "', `bank_swift_code` = '" . $this->db->escape($data['bank_swift_code']) . "', `bank_account_name` = '" . $this->db->escape($data['bank_account_name']) . "', `bank_account_number` = '" . $this->db->escape($data['bank_account_number']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', `status` = '" . (int)!$this->config->get('config_affiliate_approval') . "'");

		if ($this->config->get('config_affiliate_approval')) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_approval` SET `customer_id` = '" . (int)$customer_id . "', `type` = 'affiliate', `date_added` = NOW()");
		}
	}

	/**
	 * Edit Affiliate
	 *
	 * @param int                  $customer_id
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function editAffiliate(int $customer_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer_affiliate` SET `company` = '" . $this->db->escape($data['company']) . "', `website` = '" . $this->db->escape($data['website']) . "', `commission` = '" . (float)$this->config->get('config_affiliate_commission') . "', `tax` = '" . $this->db->escape($data['tax']) . "', `payment` = '" . $this->db->escape($data['payment']) . "', `cheque` = '" . $this->db->escape($data['cheque']) . "', `paypal` = '" . $this->db->escape($data['paypal']) . "', `bank_name` = '" . $this->db->escape($data['bank_name']) . "', `bank_branch_number` = '" . $this->db->escape($data['bank_branch_number']) . "', `bank_swift_code` = '" . $this->db->escape($data['bank_swift_code']) . "', `bank_account_name` = '" . $this->db->escape($data['bank_account_name']) . "', `bank_account_number` = '" . $this->db->escape($data['bank_account_number']) . "',  `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
	}

	/**
	 * Get Affiliate
	 *
	 * @param int $customer_id
	 *
	 * @return array<string, mixed>
	 */
	public function getAffiliate(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_affiliate` WHERE `customer_id` = '" . (int)$customer_id . "'");

		if ($query->num_rows) {
			return $query->row + ['custom_field' => json_decode($query->row['custom_field'], true)];
		} else {
			return [];
		}
	}

	/**
	 * Get Affiliate By Tracking
	 *
	 * @param string $tracking
	 *
	 * @return array<string, mixed>
	 */
	public function getAffiliateByTracking(string $tracking): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_affiliate` WHERE `tracking` = '" . $this->db->escape($tracking) . "'");

		if ($query->num_rows) {
			return $query->row + ['custom_field' => json_decode($query->row['custom_field'], true)];
		} else {
			return [];
		}
	}

	/**
	 * Add Report
	 *
	 * @param int    $customer_id
	 * @param string $ip
	 * @param string $country
	 *
	 * @return void
	 */
	public function addReport(int $customer_id, string $ip, string $country = ''): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_affiliate_report` SET `customer_id` = '" . (int)$customer_id . "', `store_id` = '" . (int)$this->config->get('config_store_id') . "', `ip` = '" . $this->db->escape($ip) . "', `country` = '" . $this->db->escape($country) . "', `date_added` = NOW()");
	}
}
