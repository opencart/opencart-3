<?php
/**
 * Class Ecb
 *
 * @package Catalog\Model\Extension\Currency
 */
class ModelExtensionCurrencyEcb extends Model {
	/**
	 * editValueByCode
	 *
	 * @param string $code
	 * @param float  $value
	 *
	 * @return void
	 */
	public function editValueByCode(string $code, float $value): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$value . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape((string)$code) . "'");

		$this->cache->delete('currency');
	}
}
