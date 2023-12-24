<?php
/**
 * Class Ecb
 *
 * @package Admin\Model\Extension\Currency
 */
class ModelExtensionCurrencyEcb extends Model {
	public function editValueByCode(string $code, float $value): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$value . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape((string)$code) . "'");

		$this->cache->delete('currency');
	}
}
