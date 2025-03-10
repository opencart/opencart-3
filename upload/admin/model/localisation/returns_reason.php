<?php
/**
 * Class Returns Reason
 *
 * Can be called using $this->load->model('localisation/return_reason');
 *
 * @package Admin\Model\Localisation
 */
class ModelLocalisationReturnsReason extends Model {
	/**
	 * Add Return Reason
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return ?int
	 *
	 * @example
	 *
	 * $return_reason_data['return_reason'][1] = [
	 *     'name' => 'Return Reason Name'
	 * ];
	 *
	 * $this->>load->model('localisation/return_reason');
	 *
	 * $return_reason_id = $this->model_localisation_return_reason->addReturnReason($return_reason_data);
	 */
	public function addReturnReason(array $data): ?int {
		$return_reason_id = 0;

		foreach ($data['return_reason'] as $language_id => $value) {
			if (!$return_reason_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "return_reason` SET `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");

				$return_reason_id = $this->db->getLastId();
			} else {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "return_reason` SET `return_reason_id` = '" . (int)$return_reason_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
			}
		}

		$this->cache->delete('return_reason');

		return $return_reason_id;
	}

	/**
	 * Edit Return Reason
	 *
	 * @param int                  $return_reason_id primary key of the return reason record
	 * @param array<string, mixed> $data             array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $return_reason_data['return_reason'][1] = [
	 *     'name' => 'Return Reason Name'
	 * ];
	 *
	 * $this->load->model('localisation/return_reason');
	 *
	 * $this->model_localisation_return_reason->editReturnReason($return_reason_id, $return_reason_data);
	 */
	public function editReturnReason(int $return_reason_id, array $data): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "return_reason` WHERE `return_reason_id` = '" . (int)$return_reason_id . "'");

		foreach ($data['return_reason'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "return_reason` SET `return_reason_id` = '" . (int)$return_reason_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
		}

		$this->cache->delete('return_reason');
	}

	/**
	 * Delete Return Reason
	 *
	 * @param int $return_reason_id primary key of the return reason record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('localisation/return_reason');
	 *
	 * $this->model_localisation_return_reason->deleteReturnReason($return_reason_id);
	 */
	public function deleteReturnReason(int $return_reason_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "return_reason` WHERE `return_reason_id` = '" . (int)$return_reason_id . "'");

		$this->cache->delete('return_reason');
	}

	/**
	 * Get Return Reason
	 *
	 * @param int $return_reason_id primary key of the return reason record
	 *
	 * @return array<string, mixed> return reason record that has return reason ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/return_reason');
	 *
	 * $return_reason_info = $this->model_localisation_return_reason->getReturnReason($return_reason_id);
	 */
	public function getReturnReason(int $return_reason_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "return_reason` WHERE `return_reason_id` = '" . (int)$return_reason_id . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Return Reasons
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> return reason records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort'  => 'name',
	 *     'order' => 'DESC',
	 *     'start' => 0,
	 *     'limit' => 10
	 * ];
	 *
	 * $this->load->model('localisation/return_reason');
	 *
	 * $results = $this->model_localisation_return_reason->getReturnReasons($filter_data);
	 */
	public function getReturnReasons(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "return_reason` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "'";

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
			$return_reason_data = $this->cache->get('return_reason.' . (int)$this->config->get('config_language_id'));

			if (!$return_reason_data) {
				$query = $this->db->query("SELECT `return_reason_id`, `name` FROM `" . DB_PREFIX . "return_reason` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `name`");

				$return_reason_data = $query->rows;

				$this->cache->set('return_reason.' . (int)$this->config->get('config_language_id'), $return_reason_data);
			}

			return $return_reason_data;
		}
	}

	/**
	 * Get Descriptions
	 *
	 * @param int $return_reason_id primary key of the return reason record
	 *
	 * @return array<int, array<string, string>> description records that have return reason ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/return_reason');
	 *
	 * $return_reason = $this->model_localisation_return_reason->getDescriptions($return_reason_id);
	 */
	public function getDescriptions(int $return_reason_id): array {
		$return_reason_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "return_reason` WHERE `return_reason_id` = '" . (int)$return_reason_id . "'");

		foreach ($query->rows as $result) {
			$return_reason_data[$result['language_id']] = ['name' => $result['name']];
		}

		return $return_reason_data;
	}

	/**
	 * Get Total Return Reasons
	 *
	 * @return int total number of return reason records
	 *
	 * @example
	 *
	 * $this->load->model('localisation/return_reason');
	 *
	 * $return_reason_total = $this->model_localisation_return_reason->getTotalReturnReasons();
	 */
	public function getTotalReturnReasons(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "return_reason` WHERE `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return (int)$query->row['total'];
	}
}
