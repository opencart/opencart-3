<?php
/**
 * Class Activity
 *
 * Can be called using $this->load->model('account/activity');
 *
 * @package Catalog\Model\Account
 */
class ModelAccountActivity extends Model {
	/**
	 * Add Activity
	 *
	 * @param string               $key
	 * @param array<string, mixed> $data array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $activity_data = [
	 *     'key'  => '',
	 *     'data' => [],
	 *     'ip'   => ''
	 * ];
	 *
	 * $this->load->model('account/activity');
	 *
	 * $this->model_account_activity->addActivity($key, $data);
	 */
	public function addActivity(string $key, array $data): void {
		if (isset($data['customer_id'])) {
			$customer_id = $data['customer_id'];
		} else {
			$customer_id = 0;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_activity` SET `customer_id` = '" . (int)$customer_id . "', `key` = '" . $this->db->escape($key) . "', `data` = '" . $this->db->escape(json_encode($data)) . "', `ip` = '" . $this->db->escape(oc_get_ip()) . "', `date_added` = NOW()");
	}
}
