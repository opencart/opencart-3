<?php
/**
 * Class Order Status
 *
 * Can be called from $this->load->model('localisation/order_status');
 *
 * @package Admin\Model\Localisation
 */
class ModelLocalisationOrderStatus extends Model {
	/**
	 * Add Order Status
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return ?int
	 *
	 * @example
	 *
	 * $order_status_id = $this->model_localisation_order_status->addOrderStatus($data);
	 */
	public function addOrderStatus(array $data): ?int {
		$order_status_id = 0;

		foreach ($data['order_status'] as $language_id => $value) {
			if ($order_status_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");

				$order_status_id = $this->db->getLastId();
			} else {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET `order_status_id` = '" . (int)$order_status_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
			}
		}

		$this->cache->delete('order_status');

		return $order_status_id;
	}

	/**
	 * Edit Order Status
	 *
	 * @param int                  $order_status_id primary key of the order status record
	 * @param array<string, mixed> $data            array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_order_status->editOrderStatus($order_status_id, $data);
	 */
	public function editOrderStatus(int $order_status_id, array $data): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_status` WHERE `order_status_id` = '" . (int)$order_status_id . "'");

		foreach ($data['order_status'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET `order_status_id` = '" . (int)$order_status_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		$this->cache->delete('order_status');
	}

	/**
	 * Delete Order Status
	 *
	 * @param int $order_status_id primary key of the order status record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_order_status->deleteOrderStatus($order_status_id);
	 */
	public function deleteOrderStatus(int $order_status_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_status` WHERE `order_status_id` = '" . (int)$order_status_id . "'");

		$this->cache->delete('order_status');
	}

	/**
	 * Get Order Status
	 *
	 * @param int $order_status_id primary key of the order status record
	 *
	 * @return array<string, mixed> order status record that has order status ID
	 *
	 * @example
	 *
	 * $order_status_info = $this->model_localisation_order_status->getOrderStatus($order_status_id);
	 */
	public function getOrderStatus(int $order_status_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_status` WHERE `order_status_id` = '" . (int)$order_status_id . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Order Statuses
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> order status records
	 *
	 * @example
	 *
	 * $results = $this->model_localisation_order_status->getOrderStatuses();
	 */
	public function getOrderStatuses(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "order_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "'";

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
			$order_status_data = $this->cache->get('order_status.' . (int)$this->config->get('config_language_id'));

			if (!$order_status_data) {
				$query = $this->db->query("SELECT `order_status_id`, `name` FROM `" . DB_PREFIX . "order_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `name`");

				$order_status_data = $query->rows;

				$this->cache->set('order_status.' . (int)$this->config->get('config_language_id'), $order_status_data);
			}

			return $order_status_data;
		}
	}

	/**
	 * Get Descriptions
	 *
	 * @param int $order_status_id primary key of the order status record
	 *
	 * @return array<int, array<string, string>> description records that have order status ID
	 *
	 * @example
	 *
	 * $order_status = $this->model_localisation_order_status->getDescriptions($order_status_id);
	 */
	public function getDescriptions(int $order_status_id): array {
		$order_status_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_status` WHERE `order_status_id` = '" . (int)$order_status_id . "'");

		foreach ($query->rows as $result) {
			$order_status_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $order_status_data;
	}

	/**
	 * Get Total Order Statuses
	 *
	 * @return int total number of order status records
	 *
	 * @example
	 *
	 * $order_status_total = $this->model_localisation_order_status->getTotalOrderStatuses();
	 */
	public function getTotalOrderStatuses(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return (int)$query->row['total'];
	}
}
