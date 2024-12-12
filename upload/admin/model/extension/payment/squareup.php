<?php
/**
 * Class Squareup
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentSquareup extends Model {
	public const RECURRING_ACTIVE = 1;
	public const RECURRING_INACTIVE = 2;
	public const RECURRING_CANCELLED = 3;
	public const RECURRING_SUSPENDED = 4;
	public const RECURRING_EXPIRED = 5;
	public const RECURRING_PENDING = 6;

	/**
	 * Get Transaction
	 *
	 * @param int $squareup_transaction_id
	 *
	 * @return array<string, string>
	 */
	public function getTransaction(int $squareup_transaction_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_transaction` WHERE `squareup_transaction_id` = '" . (int)$squareup_transaction_id . "'");

		return $query->row;
	}

	/**
	 * Get Transactions
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getTransactions(array $data): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "squareup_transaction`";

		if (isset($data['order_id']) && $data['order_id'] != '') {
			$sql .= " WHERE `order_id` = '" . (int)$data['order_id'] . "'";
		}

		$sql .= " ORDER BY `created_at` DESC";

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
	 * Get Total Transactions
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function getTotalTransactions(array $data): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "squareup_transaction`";

		if (isset($data['order_id']) && $data['order_id'] != '') {
			$sql .= " WHERE `order_id` = '" . (int)$data['order_id'] . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Update Transaction
	 *
	 * @param int                  $squareup_transaction_id
	 * @param string               $type
	 * @param array<string, mixed> $refunds
	 *
	 * @return void
	 */
	public function updateTransaction(int $squareup_transaction_id, string $type, array $refunds = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "squareup_transaction` SET `transaction_type` = '" . $this->db->escape($type) . "', `is_refunded` = '" . (int)!empty($refunds) . "', `refunds` = '" . $this->db->escape(json_encode($refunds)) . "' WHERE `squareup_transaction_id` = '" . (int)$squareup_transaction_id . "'");
	}

	/**
	 * Get Order Status Id
	 *
	 * @param int    $order_id
	 * @param string $transaction_status
	 *
	 * @return int
	 */
	public function getOrderStatusId(int $order_id, ?string $transaction_status = null): int {
		if ($transaction_status) {
			return (int)$this->config->get('payment_squareup_status_' . strtolower($transaction_status));
		} else {
			// Orders
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			return (int)$order_info['order_status_id'];
		}
	}

	/**
	 * Edit Order Subscription Status
	 *
	 * @param int $subscription_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function editOrderSubscriptionStatus(int $subscription_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `status` = '" . (int)$status . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Create Tables
	 *
	 * @return void
	 */
	public function createTables(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "squareup_customer` (
			`customer_id` int(11) NOT NULL,
			`sandbox` tinyint(1) NOT NULL,
			`square_customer_id` varchar(32) NOT NULL,
			PRIMARY KEY (`customer_id`, `sandbox`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "squareup_subscription` (
			`squareup_subscription_id` int(11) NOT NULL,
			`order_id` int(11) NOT NULL,
			`subscription_id` int(11) NOT NULL,
			`reference` varchar(40) NOT NULL,
			`status` tinyint(1) NOT NULL,
			`date_added` datetime NOT NULL,
			PRIMARY KEY (`squareup_subscription_id`),
			KEY (`order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "squareup_token` (
			`squareup_token_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`customer_id` int(11) NOT NULL,
			`sandbox` tinyint(1) NOT NULL,
			`token` varchar(40) NOT NULL,
			`date_added` datetime NOT NULL,
			`brand` VARCHAR(32) NOT NULL,
			`ends_in` VARCHAR(4) NOT NULL,
			PRIMARY KEY (`squareup_token_id`),
			KEY `get_cards` (`customer_id`, `sandbox`),
			KEY `verify_card_customer` (`squareup_token_id`, `customer_id`),
			KEY `card_exists` (`customer_id`, `brand`, `ends_in`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "squareup_transaction` (
            `squareup_transaction_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `transaction_id` varchar(40) NOT NULL,
            `merchant_id` varchar(32) NOT NULL,
            `location_id` varchar(32) NOT NULL,
            `order_id` int(11) NOT NULL,
            `transaction_type` varchar(20) NOT NULL,
            `transaction_amount` decimal(15,4) NOT NULL,
            `transaction_currency` varchar(3) NOT NULL,
            `billing_address_city` varchar(100) NOT NULL,
            `billing_address_company` varchar(100) NOT NULL,
            `billing_address_country` varchar(3) NOT NULL,
            `billing_address_postcode` varchar(10) NOT NULL,
            `billing_address_province` varchar(20) NOT NULL,
            `billing_address_street_1` varchar(100) NOT NULL,
            `billing_address_street_2` varchar(100) NOT NULL,
            `device_browser` varchar(255) NOT NULL,
            `device_ip` varchar(15) NOT NULL,
            `created_at` varchar(29) NOT NULL,
            `is_refunded` tinyint(1) NOT NULL,
            `refunded_at` varchar(29) NOT NULL,
            `tenders` text NOT NULL,
            `refunds` text NOT NULL,
            PRIMARY KEY (`squareup_transaction_id`),
            KEY `order_id` (`order_id`),
            KEY `transaction_id` (`transaction_id`)
		    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
	}
}
