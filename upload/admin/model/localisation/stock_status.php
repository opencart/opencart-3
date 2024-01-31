<?php
/**
 * Class Stock Status
 *
 * @package Admin\Model\Localisation
 */
class ModelLocalisationStockStatus extends Model {
	/**
	 * Add Stock Status
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return ?int
	 */
	public function addStockStatus(array $data): ?int {
		$stock_status_id = 0;

		foreach ($data['stock_status'] as $language_id => $value) {
			if (!$stock_status_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "stock_status` SET `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");

				$stock_status_id = $this->db->getLastId();
			} else {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "stock_status` SET `stock_status_id` = '" . (int)$stock_status_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
			}
		}

		$this->cache->delete('stock_status');

		return $stock_status_id;
	}

	/**
	 * Edit Stock Status
	 *
	 * @param int                  $stock_status_id
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function editStockStatus(int $stock_status_id, array $data): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "stock_status` WHERE `stock_status_id` = '" . (int)$stock_status_id . "'");

		foreach ($data['stock_status'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "stock_status` SET `stock_status_id` = '" . (int)$stock_status_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		$this->cache->delete('stock_status');
	}

	/**
	 * Delete Stock Status
	 *
	 * @param int $stock_status_id
	 *
	 * @return void
	 */
	public function deleteStockStatus(int $stock_status_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "stock_status` WHERE `stock_status_id` = '" . (int)$stock_status_id . "'");

		$this->cache->delete('stock_status');
	}

	/**
	 * Get Stock Status
	 *
	 * @param int $stock_status_id
	 *
	 * @return array<string, mixed>
	 */
	public function getStockStatus(int $stock_status_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "stock_status` WHERE `stock_status_id` = '" . (int)$stock_status_id . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Stock Statuses
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getStockStatuses(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "stock_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "'";

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
			$stock_status_data = $this->cache->get('stock_status.' . (int)$this->config->get('config_language_id'));

			if (!$stock_status_data) {
				$query = $this->db->query("SELECT `stock_status_id`, `name` FROM `" . DB_PREFIX . "stock_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `name`");

				$stock_status_data = $query->rows;

				$this->cache->set('stock_status.' . (int)$this->config->get('config_language_id'), $stock_status_data);
			}

			return $stock_status_data;
		}
	}

	/**
	 * Get Descriptions
	 *
	 * @param int $stock_status_id
	 *
	 * @return array<int, array<string, string>>
	 */
	public function getDescriptions(int $stock_status_id): array {
		$stock_status_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "stock_status` WHERE `stock_status_id` = '" . (int)$stock_status_id . "'");

		foreach ($query->rows as $result) {
			$stock_status_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $stock_status_data;
	}

	/**
	 * Get Total Stock Statuses
	 *
	 * @return int
	 */
	public function getTotalStockStatuses(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "stock_status` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return (int)$query->row['total'];
	}
}
