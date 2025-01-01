<?php
/**
 * Class Reward
 * 
 * @example $reward_model = $this->model_account_reward;
 * 
 * Can be called from $this->load->model('account/reward');
 *
 * @package Catalog\Model\Account
 */
class ModelAccountReward extends Model {
	/**
	 * Get Rewards
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> reward records
	 */
	public function getRewards(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$this->customer->getId() . "'";

		$sort_data = [
			'points',
			'description',
			'date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `date_added`";
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
	 * Get Total Rewards
	 *
	 * @return int total number of reward records
	 */
	public function getTotalRewards(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Points
	 *
	 * @return int total number of point records
	 */
	public function getTotalPoints(): int {
		$query = $this->db->query("SELECT SUM(`points`) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$this->customer->getId() . "' GROUP BY `customer_id`");

		if ($query->num_rows) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}
}
