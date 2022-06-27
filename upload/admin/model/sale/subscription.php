<?php
class ModelSaleSubscription extends Model {
	public function editSubscription($subscription_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `subscription_plan_id` = '" . (int)$data['subscription_plan_id'] . "', `customer_payment_id` = '" . (int)$data['customer_payment_id'] . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	public function editPaymentMethod($subscription_id, $customer_payment_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `customer_payment_id ` = '" . (int)$customer_payment_id . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	public function editRemaining($subscription_id, $remaining) {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `remaining` = '" .  (int)$remaining .  "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	public function editTrialRemaining($subscription_id, $trial_remaining) {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `trial_remaining` = '" .  (int)$trial_remaining .  "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	public function editDateNext($subscription_id, $date_next) {
		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `date_next` = '" . $this->db->escape($date_next) . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	public function getSubscription(int $subscription_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription` WHERE `subscription_id` = '" . (int)$subscription_id . "'");

		return $query->row;
	}

	public function getSubscriptions($data) {
		$sql = "SELECT `s`.`subscription_id`, `s`.*, CONCAT(o.`firstname`, ' ', o.`lastname`) AS customer, (SELECT ss.`name` FROM `" . DB_PREFIX . "subscription_status` ss WHERE ss.`subscription_status_id` = s.`subscription_status_id` AND ss.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS subscription_status FROM `" . DB_PREFIX . "subscription` `s` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`s`.`order_id` = `o`.`order_id`)";

		$implode = array();

		if (!empty($data['filter_subscription_id'])) {
			$implode[] = "`s`.`subscription_id` = '" . (int)$data['filter_subscription_id'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$implode[] = "`s`.`order_id` = '" . (int)$data['filter_order_id'] . "'";
		}
		
		if (!empty($data['filter_order_product_id'])) {
			$implode[] = "`s`.`order_product_id` = '" . (int)$data['filter_order_product_id'] . "'";
		}

		if (!empty($data['filter_reference'])) {
			$implode[] = "`s`.`reference` LIKE '" . $this->db->escape($data['filter_reference'] . '%') . "'";
		}

		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(o.`firstname`, ' ', o.`lastname`) LIKE '" . $this->db->escape($data['filter_customer'] . '%') . "'";
		}

		if (!empty($data['filter_date_next'])) {
			$implode[] = "DATE(`s`.`date_next`) = DATE('" . $this->db->escape($data['filter_date_next']) . "')";
		}

		if (!empty($data['filter_subscription_status_id'])) {
			$implode[] = "`s`.`subscription_status_id` = '" . (int)$data['filter_subscription_status_id'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$implode[] = "DATE(s.`date_added`) >= DATE('" . $this->db->escape($data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$implode[] = "DATE(s.`date_added`) <= DATE('" . $this->db->escape($data['filter_date_to']) . "')";
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

	public function getTotalSubscriptions($data = array()) {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription` `s` LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`s`.`order_id` = o.`order_id`)";

		$implode = array();

		if (!empty($data['filter_subscription_id'])) {
			$implode[] .= "`s`.`subscription_id` = '" . (int)$data['filter_subscription_id'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$implode[] .= "`s`.`order_id` = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_reference'])) {
			$implode[] .= "`s`.`reference` LIKE '" . $this->db->escape($data['filter_reference'] . '%') . "'";
		}

		if (!empty($data['filter_customer'])) {
			$implode[] .= "CONCAT(o.`firstname`, ' ', o.`lastname`) LIKE '" . $this->db->escape($data['filter_customer'] . '%') . "'";
		}

		if (!empty($data['filter_subscription_status_id'])) {
			$implode[] .= "`s`.`subscription_status_id` = '" . (int)$data['filter_subscription_status_id'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$implode[] = "DATE(s.`date_added`) >= DATE('" . $this->db->escape($data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$implode[] = "DATE(s.`date_added`) <= DATE('" . $this->db->escape($data['filter_date_to']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getTotalSubscriptionsBySubscriptionStatusId($subscription_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription` WHERE `subscription_status_id` = '" . (int)$subscription_status_id . "'");

		return (int)$query->row['total'];
	}

	public function addTransaction($subscription_id, $description = '', $amount = 0) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_transaction` SET `subscription_id` = '" . (int)$subscription_id . "', `description` = '" . $this->db->escape($description) . "', `amount` = '" . (float)$amount . "', `date_added` = NOW()");
	}

	public function getTransactions($subscription_id) {
		$transaction_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription_transaction` WHERE `subscription_id` = '" . (int)$subscription_id . "' ORDER BY `date_added` DESC");

		foreach ($query->rows as $result) {
			$transaction_data[] = [
				'date_added'  => $result['date_added'],
				'description' => $result['description'],
				'amount'      => $result['amount'],
				'order_id'    => $result['order_id']
			];
		}

		return $transaction_data;
	}

	public function getTotalTransactions($subscription_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription_transaction` WHERE `subscription_id` = '" . (int)$subscription_id . "'");

		return (int)$query->row['total'];
	}

	public function addHistory($subscription_id, $subscription_status_id, $comment = '', $notify = false) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_history` SET `subscription_id` = '" . (int)$subscription_id . "', `subscription_status_id` = '" . (int)$subscription_status_id . "', `comment` = '" . $this->db->escape($comment) . "', `notify` = '" . (int)$notify . "', `date_added` = NOW()");

		$this->db->query("UPDATE `" . DB_PREFIX . "subscription` SET `subscription_status_id` = '" . (int)$subscription_status_id . "' WHERE `subscription_id` = '" . (int)$subscription_id . "'");
	}

	public function getHistories($subscription_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT sh.`date_added`, ss.`name` AS status, sh.`comment`, sh.`notify` FROM `" . DB_PREFIX . "subscription_history` sh LEFT JOIN `" . DB_PREFIX . "subscription_status` ss ON sh.`subscription_status_id` = ss.`subscription_status_id` WHERE sh.`subscription_id` = '" . (int)$subscription_id . "' AND ss.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY sh.`date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalHistories($subscription_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription_history` WHERE `subscription_id` = '" . (int)$subscription_id . "'");

		return (int)$query->row['total'];
	}

	public function getTotalHistoriesBySubscriptionStatusId($subscription_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription_history` WHERE `subscription_status_id` = '" . (int)$subscription_status_id . "'");

		return (int)$query->row['total'];
	}
}
