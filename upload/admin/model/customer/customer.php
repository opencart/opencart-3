<?php
/**
 * Class Customer
 *
 * @package Admin\Model\Customer
 */
class ModelCustomerCustomer extends Model {
	/**
	 * addCustomer
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function addCustomer(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer` SET `customer_group_id` = '" . (int)$data['customer_group_id'] . "', `firstname` = '" . $this->db->escape($data['firstname']) . "', `lastname` = '" . $this->db->escape($data['lastname']) . "', `email` = '" . $this->db->escape($data['email']) . "', `telephone` = '" . $this->db->escape($data['telephone']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', `newsletter` = '" . (int)$data['newsletter'] . "', `password` = '" . $this->db->escape(password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT)) . "', `status` = '" . (int)$data['status'] . "', `safe` = '" . (int)$data['safe'] . "', `date_added` = NOW()");

		$customer_id = $this->db->getLastId();

		if (isset($data['address'])) {
			foreach ($data['address'] as $address) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "address` SET `customer_id` = '" . (int)$customer_id . "', `firstname` = '" . $this->db->escape($address['firstname']) . "', `lastname` = '" . $this->db->escape($address['lastname']) . "', `company` = '" . $this->db->escape($address['company']) . "', `address_1` = '" . $this->db->escape($address['address_1']) . "', `address_2` = '" . $this->db->escape($address['address_2']) . "', `city` = '" . $this->db->escape($address['city']) . "', `postcode` = '" . $this->db->escape($address['postcode']) . "', `country_id` = '" . (int)$address['country_id'] . "', `zone_id` = '" . (int)$address['zone_id'] . "', `custom_field` = '" . $this->db->escape(isset($address['custom_field']) ? json_encode($address['custom_field']) : '') . "'");

				if (isset($address['default'])) {
					$address_id = $this->db->getLastId();

					$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `address_id` = '" . (int)$address_id . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
				}
			}
		}

		return $customer_id;
	}

	/**
	 * editCustomer
	 *
	 * @param int   $customer_id
	 * @param array $data
	 *
	 * @return void
	 */
	public function editCustomer(int $customer_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `customer_group_id` = '" . (int)$data['customer_group_id'] . "', `firstname` = '" . $this->db->escape($data['firstname']) . "', `lastname` = '" . $this->db->escape($data['lastname']) . "', `email` = '" . $this->db->escape($data['email']) . "', `telephone` = '" . $this->db->escape($data['telephone']) . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', `newsletter` = '" . (int)$data['newsletter'] . "', `status` = '" . (int)$data['status'] . "', `safe` = '" . (int)$data['safe'] . "' WHERE `customer_id` = '" . (int)$customer_id . "'");

		if ($data['password']) {
			$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `password` = '" . $this->db->escape(password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT)) . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$customer_id . "'");

		if (isset($data['address'])) {
			foreach ($data['address'] as $address) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "address` SET `address_id` = '" . (int)$address['address_id'] . "', `customer_id` = '" . (int)$customer_id . "', `firstname` = '" . $this->db->escape($address['firstname']) . "', `lastname` = '" . $this->db->escape($address['lastname']) . "', `company` = '" . $this->db->escape($address['company']) . "', `address_1` = '" . $this->db->escape($address['address_1']) . "', `address_2` = '" . $this->db->escape($address['address_2']) . "', `city` = '" . $this->db->escape($address['city']) . "', `postcode` = '" . $this->db->escape($address['postcode']) . "', `country_id` = '" . (int)$address['country_id'] . "', `zone_id` = '" . (int)$address['zone_id'] . "', `custom_field` = '" . $this->db->escape(isset($address['custom_field']) ? json_encode($address['custom_field']) : '') . "'");

				if (isset($address['default'])) {
					$address_id = $this->db->getLastId();

					$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `address_id` = '" . (int)$address_id . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
				}
			}
		}
	}

	/**
	 * editToken
	 *
	 * @param int    $customer_id
	 * @param string $token
	 *
	 * @return void
	 */
	public function editToken(int $customer_id, string $token): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `token` = '" . $this->db->escape($token) . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
	}

	/**
	 * deleteCustomer
	 *
	 * @param int $customer_id
	 *
	 * @return void
	 */
	public function deleteCustomer(int $customer_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_activity` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_affiliate` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_affiliate_report` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_approval` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_history` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_payment` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_ip` WHERE `customer_id` = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$customer_id . "'");
	}

	/**
	 * getCustomer
	 *
	 * @param int $customer_id
	 *
	 * @return array
	 */
	public function getCustomer(int $customer_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "customer` WHERE `customer_id` = '" . (int)$customer_id . "'");

		if ($query->num_rows) {
			return $query->row + ['custom_field' => json_decode($query->row['custom_field'], true)];
		} else {
			return [];
		}
	}

	/**
	 * getCustomerByEmail
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	public function getCustomerByEmail(string $email): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "customer` WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");

		if ($query->num_rows) {
			return $query->row + ['custom_field' => json_decode($query->row['custom_field'], true)];
		} else {
			return [];
		}
	}

	/**
	 * getCustomers
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function getCustomers(array $data = []): array {
		$sql = "SELECT *, CONCAT(`c`.`firstname`, ' ', `c`.`lastname`) AS `name`, `cgd`.`name` AS `customer_group` FROM `" . DB_PREFIX . "customer` `c` LEFT JOIN `" . DB_PREFIX . "customer_group_description` `cgd` ON (`c`.`customer_group_id` = `cgd`.`customer_group_id`)";

		$sql .= " WHERE `cgd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND CONCAT(`c`.`firstname`, ' ', `c`.`lastname`) LIKE '" . $this->db->escape('%' . $data['filter_name'] . '%') . "'";
		}

		if (!empty($data['filter_email'])) {
			$sql .= " AND `c`.`email` LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (isset($data['filter_newsletter']) && $data['filter_newsletter'] != '') {
			$sql .= " AND `c`.`newsletter` = '" . (int)$data['filter_newsletter'] . "'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$sql .= " AND `c`.`customer_group_id` = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		if (!empty($data['filter_ip'])) {
			$sql .= " AND `c`.`customer_id` IN(SELECT `customer_id` FROM `" . DB_PREFIX . "customer_ip` WHERE `ip` = '" . $this->db->escape($data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND `c`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(`c`.`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$sort_data = [
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.ip',
			'c.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * getAddress
	 *
	 * @param int $address_id
	 *
	 * @return array
	 */
	public function getAddress(int $address_id): array {
		$address_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "address` WHERE `address_id` = '" . (int)$address_id . "'");

		if ($address_query->num_rows) {
			$country_query = $this->db->query("SELECT *, `c`.`name` FROM `" . DB_PREFIX . "country` `c` LEFT JOIN `" . DB_PREFIX . "address_format` `af` ON (`c`.`address_format_id` = `af`.`address_format_id`) WHERE `c`.`country_id` = '" . (int)$address_query->row['country_id'] . "'");

			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$address_query->row['zone_id'] . "'");

			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			return [
				'address_id'     => $address_query->row['address_id'],
				'customer_id'    => $address_query->row['customer_id'],
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'custom_field'   => json_decode($address_query->row['custom_field'], true),
				'default'        => $address_query->row['default']
			];
		}

		return [];
	}

	/**
	 * getAddresses
	 *
	 * @param int $customer_id
	 *
	 * @return array
	 */
	public function getAddresses(int $customer_id): array {
		$address_data = [];

		$query = $this->db->query("SELECT `address_id` FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$customer_id . "'");

		foreach ($query->rows as $result) {
			$address_info = $this->getAddress($result['address_id']);

			if ($address_info) {
				$address_data[$result['address_id']] = $address_info;
			}
		}

		return $address_data;
	}

	/**
	 * getTotalCustomers
	 *
	 * @param array $data
	 * @param int   $customer_id
	 *
	 * @return int
	 */
	public function getTotalCustomers(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer` `c`";

		$implode = [];

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(`c`.`firstname`, ' ', `c`.`lastname`) LIKE '%" . $this->db->escape($data['filter_name'] . '%') . "'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "`c`.`email` LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (isset($data['filter_newsletter']) && $data['filter_newsletter'] != '') {
			$implode[] = "`c`.`newsletter` = '" . (int)$data['filter_newsletter'] . "'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$implode[] = "`c`.`customer_group_id` = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		if (!empty($data['filter_ip'])) {
			$implode[] = "`c`.`customer_id` IN(SELECT `customer_id` FROM `" . DB_PREFIX . "customer_ip` WHERE `ip` = '" . $this->db->escape($data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "`c`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(`c`.`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * getTotalAddressesByCustomerId
	 *
	 * @param int $customer_id
	 *
	 * @return int
	 */
	public function getTotalAddressesByCustomerId(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * getTotalAddressesByCountryId
	 *
	 * @param int $country_id
	 *
	 * @return int
	 */
	public function getTotalAddressesByCountryId(int $country_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "address` WHERE `country_id` = '" . (int)$country_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * getTotalAddressesByZoneId
	 *
	 * @param int $zone_id
	 *
	 * @return int
	 */
	public function getTotalAddressesByZoneId(int $zone_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "address` WHERE `zone_id` = '" . (int)$zone_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * getTotalCustomersByCustomerGroupId
	 *
	 * @param int $customer_group_id
	 *
	 * @return int
	 */
	public function getTotalCustomersByCustomerGroupId(int $customer_group_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer` WHERE `customer_group_id` = '" . (int)$customer_group_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * addHistory
	 *
	 * @param int    $customer_id
	 * @param string $comment
	 *
	 * @return void
	 */
	public function addHistory(int $customer_id, string $comment): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_history` SET `customer_id` = '" . (int)$customer_id . "', `comment` = '" . $this->db->escape(strip_tags($comment)) . "', `date_added` = NOW()");
	}

	/**
	 * getHistories
	 *
	 * @param int $customer_id
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getHistories(int $customer_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT `comment`, `date_added` FROM `" . DB_PREFIX . "customer_history` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * getTotalHistories
	 *
	 * @param int $customer_id
	 *
	 * @return int
	 */
	public function getTotalHistories(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_history` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * addTransaction
	 *
	 * @param int    $customer_id
	 * @param string $description
	 * @param float  $amount
	 * @param int    $order_id
	 *
	 * @return void
	 */
	public function addTransaction(int $customer_id, string $description = '', float $amount = 0, int $order_id = 0): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_transaction` SET `customer_id` = '" . (int)$customer_id . "', `order_id` = '" . (int)$order_id . "', `description` = '" . $this->db->escape($description) . "', `amount` = '" . (float)$amount . "', `date_added` = NOW()");
	}

	/**
	 * deleteTransactionByOrderId
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deleteTransactionByOrderId(int $order_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_transaction` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * getTransactions
	 *
	 * @param int $customer_id
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getTransactions(int $customer_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * getTotalTransactions
	 *
	 * @param int $customer_id
	 *
	 * @return int
	 */
	public function getTotalTransactions(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * getTransactionTotal
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
	 * getTotalTransactionsByOrderId
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
	 * deletePaymentMethod
	 *
	 * @param int $customer_payment_id
	 *
	 * @return void
	 */
	public function deletePaymentMethod(int $customer_payment_id): void {
		$this->db->query("DELETE `" . DB_PREFIX . "customer_payment` WHERE `customer_payment_id` = '" . (int)$customer_payment_id . "'");
	}

	/**
	 * editPaymentMethodStatus
	 *
	 * @param int  $customer_payment_id
	 * @param bool $status
	 *
	 * @return void
	 */
	public function editPaymentMethodStatus(int $customer_payment_id, bool $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer_payment` SET `status` = '" . (int)$status . "' WHERE `customer_payment_id` = '" . (int)$customer_payment_id . "'");
	}

	/**
	 * getPaymentMethod
	 *
	 * @param int $customer_id
	 * @param int $customer_payment_id
	 *
	 * @return array
	 */
	public function getPaymentMethod(int $customer_id, int $customer_payment_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_payment` WHERE `customer_id` = '" . (int)$customer_id . "' AND `customer_payment_id` = '" . (int)$customer_payment_id . "'");

		return $query->row;
	}

	/**
	 * getPaymentMethods
	 *
	 * @param int $customer_id
	 *
	 * @return array
	 */
	public function getPaymentMethods(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_payment` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return $query->rows;
	}

	/**
	 * getTotalPaymentMethods
	 *
	 * @param int $customer_id
	 *
	 * @return int
	 */
	public function getTotalPaymentMethods(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_payment` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * addReward
	 *
	 * @param int    $customer_id
	 * @param string $description
	 * @param int    $points
	 * @param int    $order_id
	 *
	 * @return void
	 */
	public function addReward(int $customer_id, string $description = '', int $points = 0, int $order_id = 0): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_reward` SET `customer_id` = '" . (int)$customer_id . "', `order_id` = '" . (int)$order_id . "', `points` = '" . (int)$points . "', `description` = '" . $this->db->escape($description) . "', `date_added` = NOW()");
	}

	/**
	 * deleteReward
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deleteReward(int $order_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_reward` WHERE `order_id` = '" . (int)$order_id . "' AND `points` > '0'");
	}

	/**
	 * getRewards
	 *
	 * @param int $customer_id
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getRewards(int $customer_id, int $start = 0, int $limit = 10): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * getTotalRewards
	 *
	 * @param int $customer_id
	 *
	 * @return int
	 */
	public function getTotalRewards(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * getRewardTotal
	 *
	 * @param int $customer_id
	 *
	 * @return int
	 */
	public function getRewardTotal(int $customer_id): int {
		$query = $this->db->query("SELECT SUM(`points`) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * getTotalCustomerRewardsByOrderId
	 *
	 * @param int $order_id
	 *
	 * @return int
	 */
	public function getTotalCustomerRewardsByOrderId(int $order_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `order_id` = '" . (int)$order_id . "' AND `points` > '0'");

		return (int)$query->row['total'];
	}

	/**
	 * getIps
	 *
	 * @param int $customer_id
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getIps(int $customer_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ip` WHERE `customer_id` = '" . (int)$customer_id . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * getTotalIps
	 *
	 * @param int $customer_id
	 *
	 * @return int
	 */
	public function getTotalIps(int $customer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_ip` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * getTotalCustomersByIp
	 *
	 * @param string $ip
	 *
	 * @return int
	 */
	public function getTotalCustomersByIp(string $ip): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_ip` WHERE `ip` = '" . $this->db->escape($ip) . "'");

		return (int)$query->row['total'];
	}

	/**
	 * getTotalLoginAttempts
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	public function getTotalLoginAttempts(string $email): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_login` WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");

		return $query->row;
	}

	/**
	 * deleteLoginAttempts
	 *
	 * @param string $email
	 *
	 * @return void
	 */
	public function deleteLoginAttempts(string $email): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE LCASE(`email`) = '" . $this->db->escape(oc_strtolower($email)) . "'");
	}
}
