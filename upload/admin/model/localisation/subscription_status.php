<?php
class ModelLocalisationSubscriptionStatus extends Model {
	public function addSubscriptionStatus($data) {
		foreach ($data['subscription_status'] as $language_id => $value) {
			if (isset($subscription_status_id)) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_status` SET `subscription_status_id` = '" . (int)$subscription_status_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
			} else {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_status` SET `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");

				$subscription_status_id = $this->db->getLastId();
			}
		}

		$this->cache->delete('subscription_status');

		return $subscription_status_id;
	}

	public function editSubscriptionStatus($subscription_status_id, $data) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "subscription_status` WHERE `subscription_status_id` = '" . (int)$subscription_status_id . "'");

		foreach ($data['subscription_status'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "subscription_status` SET `subscription_status_id` = '" . (int)$subscription_status_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		$this->cache->delete('subscription_status');
	}

	public function deleteSubscriptionStatus($subscription_status_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "subscription_status` WHERE `subscription_status_id` = '" . (int)$subscription_status_id . "'");

		$this->cache->delete('subscription_status');
	}

	public function getSubscriptionStatus($subscription_status_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription_status` WHERE `subscription_status_id` = '" . (int)$subscription_status_id . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getSubscriptionStatuses($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "subscription_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "'";

			$sql .= " ORDER BY `name`";

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
		} else {
			$subscription_status_data = $this->cache->get('subscription_status.' . (int)$this->config->get('config_language_id'));

			if (!$subscription_status_data) {
				$query = $this->db->query("SELECT `subscription_status_id`, `name` FROM `" . DB_PREFIX . "subscription_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `name`");

				$subscription_status_data = $query->rows;

				$this->cache->set('subscription_status.' . (int)$this->config->get('config_language_id'), $subscription_status_data);
			}

			return $subscription_status_data;
		}
	}

	public function getDescriptions($subscription_status_id) {
		$subscription_status_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription_status` WHERE `subscription_status_id` = '" . (int)$subscription_status_id . "'");

		foreach ($query->rows as $result) {
			$subscription_status_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $subscription_status_data;
	}

	public function getTotalSubscriptionStatuses() {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "subscription_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return (int)$query->row['total'];
	}
}
