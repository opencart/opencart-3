<?php
/**
 * Class Subscription
 *
 * Can be called from $this->load->model('checkout/subscription');
 *
 * @package Catalog\Model\Checkout
 */
class ModelCheckoutSubscription extends Model {
	/**
	 * Add Subscription
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new subscription record
	 *
	 * @example
	 *
	 * $data = [
	 *   'order_id'             => $order_info['order_id'],
	 *   'store_id'             => $order_info['store_id'],
	 *   'customer_id'          => $order_info['customer_id'],
	 *   'payment_address_id'   => $order_info['payment_address_id'],
	 *   'payment_method'       => $order_info['payment_method'],
	 *   'shipping_address_id'  => $order_info['shipping_address_id'],
	 *   'shipping_method'      => $order_info['shipping_method'],
	 *   'subscription_plan_id' => $order_subscription_info['subscription_plan_id'],
	 *   'trial_price'          => $order_subscription_info['trial_price'],
	 *   'trial_frequency'      => $order_subscription_info['trial_frequency'],
	 *   'trial_cycle'          => $order_subscription_info['trial_cycle'],
	 *   'trial_duration'       => $order_subscription_info['trial_duration'],
	 *   'trial_status'         => $order_subscription_info['trial_status'],
	 *   'price'                => $order_subscription_info['price'],
	 *   'frequency'            => $order_subscription_info['frequency'],
	 *   'cycle'                => $order_subscription_info['cycle'],
	 *   'duration'             => $order_subscription_info['duration'],
	 *   'comment'              => $order_info['comment'],
	 *   'affiliate_id'         => $order_info['affiliate_id'],
	 *   'marketing_id'         => $order_info['marketing_id'],
	 *   'tracking'             => $order_info['tracking'],
	 *   'language_id'          => $order_info['language_id'],
	 *   'currency_id'          => $order_info['currency_id']
	 * ];
	 *
	 * $this->load->model('checkout/subscription');
	 *
	 * $subscription_id = $this->model_checkout_subscription->addSubscription($data);
	 */
	public function addSubscription(array $data): int {
		if ($data['trial_status'] && $data['trial_duration']) {
			$trial_remaining = $data['trial_duration'] - 1;
			$remaining = $data['duration'];
		} elseif ($data['duration']) {
			$trial_remaining = $data['trial_duration'];
			$remaining = $data['duration'] - 1;
		} else {
			$trial_remaining = $data['trial_duration'];
			$remaining = $data['duration'];
		}

		if ($data['trial_status'] && $data['trial_duration']) {
			$date_next = date('Y-m-d', strtotime('+' . $data['trial_cycle'] . ' ' . $data['trial_frequency']));
		} else {
			$date_next = date('Y-m-d', strtotime('+' . $data['cycle'] . ' ' . $data['frequency']));
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription` SET
			`order_product_id` = '" . (int)$data['order_product_id'] . "',
			`order_id` = '" . (int)$data['order_id'] . "',
			`store_id` = '" . (int)$data['store_id'] . "',
			`customer_id` = '" . (int)$data['customer_id'] . "',
			`payment_address_id` = '" . (int)$data['payment_address_id'] . "',
			`payment_method` = '" . $this->db->escape($data['payment_method'] ? json_encode($data['payment_method']) : '') . "',
			`shipping_address_id` = '" . (int)$data['shipping_address_id'] . "',
			`shipping_method` = '" . $this->db->escape($data['shipping_method'] ? json_encode($data['shipping_method']) : '') . "',
			`product_id` = '" . (int)$data['product_id'] . "',
			`option` = '" . $this->db->escape($data['option'] ? json_encode($data['option']) : '') . "',
			`quantity` = '" . (int)$data['quantity'] . "',
			`subscription_plan_id` = '" . (int)$data['subscription_plan_id'] . "',
			`trial_price` = '" . (float)$data['trial_price'] . "',
			`trial_frequency` = '" . $this->db->escape($data['trial_frequency']) . "',
			`trial_cycle` = '" . (int)$data['trial_cycle'] . "',
			`trial_duration` = '" . (int)$data['trial_duration'] . "',
			`trial_remaining` = '" . (int)$trial_remaining . "',
			`trial_status` = '" . (int)$data['trial_status'] . "',
			`price` = '" . (float)$data['price'] . "',
			`frequency` = '" . $this->db->escape($data['frequency']) . "',
			`cycle` = '" . (int)$data['cycle'] . "',
			`duration` = '" . (int)$data['duration'] . "',
			`remaining` = '" . (int)$trial_remaining . "',
			`date_next` = '" . $this->db->escape($date_next) . "',
			`comment` = '" . $this->db->escape($data['comment']) . "',
			`affiliate_id` = '" . (int)$data['affiliate_id'] . "',
			`marketing_id` = '" . (int)$data['marketing_id'] . "',
			`tracking` = '" . $this->db->escape($data['tracking']) . "',
			`language_id` = '" . (int)$data['language_id'] . "',
			`currency_id` = '" . (int)$data['currency_id'] . "',
			`ip` = '" . $this->db->escape($data['ip']) . "',
			`forwarded_ip` = '" . $this->db->escape($data['forwarded_ip']) . "',
			`user_agent` = '" . $this->db->escape($data['user_agent']) . "',
			`accept_language` = '" . $this->db->escape($data['accept_language']) . "',
			`date_added` = NOW(),
			`date_modified` = NOW()
		");

		return $this->db->getLastId();
	}

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
	 * $this->model_checkout_subscription->editSubscription($subscription_id, $data);
	 */
	public function editSubscription(int $subscription_id, array $data): void {
		if ($data['trial_status'] && $data['trial_duration']) {
			$trial_remaining = $data['trial_duration'] - 1;
			$remaining = $data['duration'];
		} elseif ($data['duration']) {
			$trial_remaining = $data['trial_duration'];
			$remaining = $data['duration'] - 1;
		} else {
			$trial_remaining = $data['trial_duration'];
			$remaining = $data['duration'];
		}

		if ($data['trial_status'] && $data['trial_duration']) {
			$date_next = date('Y-m-d', strtotime('+' . $data['trial_cycle'] . ' ' . $data['trial_frequency']));
		} else {
			$date_next = date('Y-m-d', strtotime('+' . $data['cycle'] . ' ' . $data['frequency']));
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET
			`order_id` = '" . (int)$data['order_id'] . "',
			`order_product_id` = '" . (int)$data['order_product_id'] . "',
			`store_id` = '" . (int)$data['store_id'] . "',
			`customer_id` = '" . (int)$data['customer_id'] . "',
			`payment_address_id` = '" . (int)$data['payment_address_id'] . "',
			`payment_method` = '" . $this->db->escape($data['payment_method'] ? json_encode($data['payment_method']) : '') . "',
			`shipping_address_id` = '" . (int)$data['shipping_address_id'] . "',
			`shipping_method` = '" . $this->db->escape($data['shipping_method'] ? json_encode($data['shipping_method']) : '') . "',
			`product_id` = '" . (int)$data['product_id'] . "',
			`option` = '" . $this->db->escape($data['option'] ? json_encode($data['option']) : '') . "',
			`quantity` = '" . (int)$data['quantity'] . "',
			`subscription_plan_id` = '" . (int)$data['subscription_plan_id'] . "',
			`trial_price` = '" . (float)$data['trial_price'] . "',
			`trial_frequency` = '" . $this->db->escape($data['trial_frequency']) . "',
			`trial_cycle` = '" . (int)$data['trial_cycle'] . "',
			`trial_duration` = '" . (int)$data['trial_duration'] . "',
			`trial_remaining` = '" . (int)$trial_remaining . "',
			`trial_status` = '" . (int)$data['trial_status'] . "',
			`price` = '" . (float)$data['price'] . "',
			`frequency` = '" . $this->db->escape($data['frequency']) . "',
			`cycle` = '" . (int)$data['cycle'] . "',
			`duration` = '" . (int)$data['duration'] . "',
			`remaining` = '" . (int)$remaining . "',
			`date_next` = '" . $this->db->escape($date_next) . "',
			`comment` = '" . $this->db->escape($data['comment']) . "',
			`affiliate_id` = '" . (int)$data['affiliate_id'] . "',
			`marketing_id` = '" . (int)$data['marketing_id'] . "',
			`tracking` = '" . $this->db->escape($data['tracking']) . "',
			`language_id` = '" . (int)$data['language_id'] . "',
			`currency_id` = '" . (int)$data['currency_id'] . "',
			`ip` = '" . $this->db->escape($data['ip']) . "',
			`forwarded_ip` = '" . $this->db->escape($data['forwarded_ip']) . "',
			`user_agent` = '" . $this->db->escape($data['user_agent']) . "',
			`accept_language` = '" . $this->db->escape($data['accept_language']) . "',
			`date_modified` = NOW()
			WHERE `subscription_id` = '" . (int)$subscription_id . "'
		");
	}

	/**
	 * Delete Subscription By Order ID
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_checkout_subscription->deleteSubscriptionByOrderId($order_id);
	 */
	public function deleteSubscriptionByOrderId(int $order_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "subscription` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Get Subscription By Order Product ID
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return array<string, mixed> subscription record that has order ID, order product ID
	 *
	 * @example
	 *
	 * $subscription_info = $this->model_checkout_subscription->getSubscriptionByOrderProductId($order_id, $order_product_id);
	 */
	public function getSubscriptionByOrderProductId(int $order_id, int $order_product_id): array {
		$subscription_data = [];

		$query = $this->db->query("SELECT * FROM  `" . DB_PREFIX . "subscription` WHERE `order_id` = '" . (int)$order_id . "' AND `order_product_id` = '" . (int)$order_product_id . "'");

		if ($query->num_rows) {
			$subscription_data = $query->row;

			$subscription_data['option'] = ($query->row['option'] ? json_decode($query->row['option'], true) : '');
			$subscription_data['payment_method'] = ($query->row['payment_method'] ? json_decode($query->row['payment_method'], true) : '');
			$subscription_data['shipping_method'] = ($query->row['shipping_method'] ? json_decode($query->row['shipping_method'], true) : '');
		}

		return $subscription_data;
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
	 * $this->model_checkout_subscription->addHistory($subscription_id, $subscription_status_id, $comment, $notify);
	 */
	public function addHistory(int $subscription_id, int $subscription_status_id, string $comment = '', bool $notify = false): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_history` SET `subscription_id` = '" . (int)$subscription_id . "', `subscription_status_id` = '" . (int)$subscription_status_id . "', `comment` = '" . $this->db->escape($comment) . "', `notify` = '" . (int)$notify . "', `date_added` = NOW()");

		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `subscription_status_id` = '" . (int)$subscription_status_id . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	/**
	 * Edit Subscription Status
	 *
	 * @param int $subscription_id        primary key of the subscription record
	 * @param int $subscription_status_id primary key of the subscription status record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_checkout_subscription->editSubscriptionStatus($subscription_id, $subscription_status_id);
	 */
	public function editSubscriptionStatus(int $subscription_id, bool $subscription_status_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `subscription_status_id` = '" . (int)$subscription_status_id . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
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
	 * $this->model_checkout_subscription->editTrialRemaining($subscription_id, $trial_remaining);
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
	 * $this->model_checkout_subscription->editDateNext($subscription_id, $date_next);
	 */
	public function editDateNext(int $subscription_id, string $date_next): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `date_next` = '" . $this->db->escape($date_next) . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
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
	 * $subscriptions = $this->model_checkout_subscription->getSubscriptions();
	 */
	public function getSubscriptions(array $data): array {
		$sql = "SELECT `s`.`subscription_id`, `s`.*, CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) AS `customer`, (SELECT `ss`.`name` FROM `" . DB_PREFIX . "subscription_status` `ss` WHERE `ss`.`subscription_status_id` = `s`.`subscription_status_id` AND `ss`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `subscription_status` FROM `" . DB_PREFIX . "subscription` `s` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`s`.`order_id` = `o`.`order_id`)";

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

		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) LIKE '" . $this->db->escape((string)$data['filter_customer'] . '%') . "'";
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
}
