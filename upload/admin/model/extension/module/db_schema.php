<?php
/**
 * Class DbSchema
 *
 * @package Admin\Model\Extension\Module
 */
class ModelExtensionModuleDbSchema extends Model {
	/**
	 * getTable
	 *
	 * @param string $name
	 *
	 * @return array
	 */
	public function getTable(string $name): array {
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . $name . "'");

		return $query->rows;
	}

	/**
	 * getTables
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function getTables(array $data = []): array {
		$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "'";

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
	 * getTotalTables
	 *
	 * @return int
	 */
	public function getTotalTables(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "'");

		return (int)$query->row['total'];
	}
}
