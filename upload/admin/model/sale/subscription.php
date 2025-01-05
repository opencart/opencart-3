<?php
/**
 * Class Subscription
 *
 * Can be called from $this->load->model('sale/subscription');
 *
 * @package Admin\Model\Sale
 */
class ModelSaleSubscription extends Model {
	/**
	 * Edit Subscription
	 *
	 * @param int                  $subscription_id primary key of the subscription record
	 * @param array<string, mixed> $data            array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_subscription->editSubscription($subscription_id, $data);
	 */
	public function editSubscription(int $subscription_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `subscription_plan_id` = '" . (int)$data['subscription_plan_id'] . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Edit Payment Method
	 *
	 * @param int $subscription_id     primary key of the subscription record
	 * @param int $customer_payment_id primary key of the customer payment record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_subscription->editPaymentMethod($subscription_id, $customer_payment_id);
	 */
	public function editPaymentMethod(int $subscription_id, int $customer_payment_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `customer_payment_id ` = '" . (int)$customer_payment_id . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Edit Subscription Plan
	 *
	 * @param int $subscription_id      primary key of the subscription record
	 * @param int $subscription_plan_id primary key of the subscription plan record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_subscription->editSubscriptionPlan($subscription_id, $subscription_plan_id);
	 */
	public function editSubscriptionPlan(int $subscription_id, int $subscription_plan_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `subscription_plan_id` = '" . (int)$subscription_plan_id . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Edit Remaining
	 *
	 * @param int $subscription_id primary key of the subscription record
	 * @param int $remaining
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_subscription->editRemaining($subscription_id, $remaining);
	 */
	public function editRemaining(int $subscription_id, int $remaining): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `remaining` = '" . (int)$remaining . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Edit Trial Remaining
	 *
	 * @param int $subscription_id primary key of the subscription record
	 * @param int $trial_remaining
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_subscription->editTrialRemaining($subscription_id, $trial_remaining);
	 */
	public function editTrialRemaining(int $subscription_id, int $trial_remaining): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `trial_remaining` = '" . (int)$trial_remaining . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Edit Date Next
	 *
	 * @param int    $subscription_id primary key of the subscription record
	 * @param string $date_next
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_subscription->editDateNext($subscription_id, $date_next);
	 */
	public function editDateNext(int $subscription_id, string $date_next): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `date_next` = '" . $this->db->escape($date_next) . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Delete Subscription By Customer Payment ID
	 *
	 * @param int $customer_payment_id primary key of the customer payment record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_subscription->deleteSubscriptionByCustomerPaymentId($customer_payment_id);
	 */
	public function deleteSubscriptionByCustomerPaymentId(int $customer_payment_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "subscription` WHERE `customer_payment_id ` = '" . (int)$customer_payment_id . "'");
	}

	/**
	 * Get Subscription
	 *
	 * @param int $subscription_id primary key of the subscription record
	 *
	 * @return array<string, mixed> subscription record that has subscription ID
	 *
	 * @example
	 *
	 * $subscription_info = $this->model_sale_subscription->getSubscription($subscription_id);
	 */
	public function getSubscription(int $subscription_id): array {
		$subscription_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription` WHERE `subscription_id` = '" . (int)$subscription_id . "'");

		if ($query->num_rows) {
			$subscription_data = $query->row;

			$subscription_data['option'] = ($query->row['option'] ? json_decode($query->row['option'], true) : '');
			$subscription_data['payment_method'] = ($query->row['payment_method'] ? json_decode($query->row['payment_method'], true) : '');
			$subscription_data['shipping_method'] = ($query->row['shipping_method'] ? json_decode($query->row['shipping_method'], true) : '');
		} else {
			return [];
		}

		return $subscription_data;
	}

	/**
	 * Get Subscription By Order Product ID
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return array<string, mixed> subscription record that has order ID
	 *
	 * @example
	 *
	 * $subscription_info = $this->model_sale_subscription->getSubscriptionByOrderProductId($order_id, $order_product_id);
	 */
	public function getSubscriptionByOrderProductId(int $order_id, int $order_product_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription` WHERE `order_id` = '" . (int)$order_id . "' AND `order_product_id` = '" . (int)$order_product_id . "'");

		if ($query->num_rows) {
			$query->row['option'] = ($query->row['option'] ? json_decode($query->row['option'], true) : '');
			$query->row['payment_method'] = ($query->row['payment_method'] ? json_decode($query->row['payment_method'], true) : '');
			$query->row['shipping_method'] = ($query->row['shipping_method'] ? json_decode($query->row['shipping_method'], true) : '');

			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * Get Subscriptions
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> subscription records
	 *
	 * @example
	 *
	 * $results = $this->model_sale_subscription->getSubscriptions();
	 */
	public function getSubscriptions(array $data): array {
		$sql = "SELECT `s`.`subscription_id`, `s`.*, CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) AS customer, (SELECT `ss`.`name` FROM `" . DB_PREFIX . "subscription_status` `ss` WHERE `ss`.`subscription_status_id` = `s`.`subscription_status_id` AND `ss`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `subscription_status` FROM `" . DB_PREFIX . "subscription` `s` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`s`.`order_id` = `o`.`order_id`)";

		$implode = [];

		if (!empty($data['filter_subscription_id'])) {
			$implode[] = "`s`.`subscription_id` = '" . (int)$data['filter_subscription_id'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$implode[] = "`s`.`order_id` = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_order_product_id'])) {
			$implode[] = "`s`.`order_product_id` = '" . (int)$data['filter_order_product_id'] . "'";
		}

		if (!empty($data['filter_customer_id'])) {
			$implode[] = "`s`.`customer_id` = " . (int)$data['filter_customer_id'];
		}

		if (!empty($data['filter_customer'])) {
			$implode[] = "LCASE(CONCAT(`o`.`firstname`, ' ', `o`.`lastname`)) LIKE '" . $this->db->escape(oc_strtolower($data['filter_customer']) . '%') . "'";
		}

		if (!empty($data['filter_date_next'])) {
			$implode[] = "DATE(`s`.`date_next`) = DATE('" . $this->db->escape((string)$data['filter_date_next']) . "')";
		}

		if (!empty($data['filter_subscription_status_id'])) {
			$implode[] = "`s`.`subscription_status_id` = '" . (int)$data['filter_subscription_status_id'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$implode[] = "DATE(`s`.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$implode[] = "DATE(`s`.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_to']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = [
			's.subscription_id',
			's.order_id',
			's.reference',
			'customer',
			's.subscription_status',
			's.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `s`.`subscription_id`";
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
	 * Get Total Subscriptions
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of subscription records
	 *
	 * @example
	 *
	 * $subscription_total = $this->model_sale_subscription->getTotalSubscriptions();
	 */
	public function getTotalSubscriptions(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription` `s` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`s`.`order_id` = `o`.`order_id`)";

		$implode = [];

		if (!empty($data['filter_subscription_id'])) {
			$implode[] = "`s`.`subscription_id` = '" . (int)$data['filter_subscription_id'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$implode[] = "`s`.`order_id` = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer_id'])) {
			$implode[] = "`s`.`customer_id` = " . (int)$data['filter_customer_id'];
		}

		if (!empty($data['filter_customer'])) {
			$implode[] = "LCASE(CONCAT(`o`.`firstname`, ' ', `o`.`lastname`)) LIKE '" . $this->db->escape(oc_strtolower($data['filter_customer']) . '%') . "'";
		}

		if (!empty($data['filter_subscription_status_id'])) {
			$implode[] = "`s`.`subscription_status_id` = '" . (int)$data['filter_subscription_status_id'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$implode[] = "DATE(`s`.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$implode[] = "DATE(`s`.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_to']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Subscriptions By Store ID
	 *
	 * @param int $store_id
	 *
	 * @return int total number of subscription records that have store ID
	 *
	 * @example
	 *
	 * $subscription_total = $this->model_sale_subscription->getTotalSubscriptionsByStoreId($store_id);
	 */
	public function getTotalSubscriptionsByStoreId(int $store_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription` WHERE `store_id` = '" . (int)$store_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Subscriptions By Subscription Status ID
	 *
	 * @param int $subscription_status_id primary key of the subscription status record
	 *
	 * @return int total number of subscription records that have subscription status ID
	 *
	 * @example
	 *
	 * $subscription_total = $this->model_sale_subscription->getTotalSubscriptionsBySubscriptionStatusId($subscription_status_id);
	 */
	public function getTotalSubscriptionsBySubscriptionStatusId(int $subscription_status_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription` WHERE `subscription_status_id` = '" . (int)$subscription_status_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Add History
	 *
	 * @param int    $subscription_id        primary key of the subscription record
	 * @param int    $subscription_status_id primary key of the subscription status record
	 * @param string $comment
	 * @param bool   $notify
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_sale_subscription->addHistory($subscription_id, $subscription_status_id, $comment, $notify);
	 */
	public function addHistory(int $subscription_id, int $subscription_status_id, string $comment = '', bool $notify = false): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_history` SET `subscription_id` = '" . (int)$subscription_id . "', `subscription_status_id` = '" . (int)$subscription_status_id . "', `comment` = '" . $this->db->escape($comment) . "', `notify` = '" . (int)$notify . "', `date_added` = NOW()");

		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `subscription_status_id` = '" . (int)$subscription_status_id . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Get Histories
	 *
	 * @param int $subscription_id primary key of the subscription record
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>> history records that have subscription ID
	 *
	 * @example
	 *
	 * $results = $this->model_sale_subscription->getHistories($subscription_id, $start, $limit);
	 */
	public function getHistories(int $subscription_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT `sh`.`date_added`, `ss`.`name` AS `status`, `sh`.`comment`, `sh`.`notify` FROM `" . DB_PREFIX . "subscription_history` `sh` LEFT JOIN `" . DB_PREFIX . "subscription_status` `ss` ON `sh`.`subscription_status_id` = `ss`.`subscription_status_id` WHERE `sh`.`subscription_id` = '" . (int)$subscription_id . "' AND `ss`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `sh`.`date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	/**
	 * Get Total Histories
	 *
	 * @param int $subscription_id primary key of the subscription record
	 *
	 * @return int total number of history records that have subscription ID
	 *
	 * @example
	 *
	 * $history_total = $this->model_sale_subscription->getTotalHistories($subscription_id);
	 */
	public function getTotalHistories(int $subscription_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription_history` WHERE `subscription_id` = '" . (int)$subscription_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Histories By Subscription Status ID
	 *
	 * @param int $subscription_status_id primary key of the subscription status record
	 *
	 * @return int total number of subscription status records that have subscription status ID
	 *
	 * @example
	 *
	 * $history_total = $this->model_sale_subscription->getTotalHistoriesBySubscriptionStatusId($subscription_status_id);
	 */
	public function getTotalHistoriesBySubscriptionStatusId(int $subscription_status_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription_history` WHERE `subscription_status_id` = '" . (int)$subscription_status_id . "'");

		return (int)$query->row['total'];
	}
}
