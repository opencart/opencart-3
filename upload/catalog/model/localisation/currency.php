<?php
/**
 * Class Currency
 *
 * Can be called from $this->load->model('localisation/currency');
 *
 * @package Catalog\Model\Localisation
 */
class ModelLocalisationCurrency extends Model {
	/**
	 * Get Currency By Code
	 *
	 * @param string $currency
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $currency_info = $this->model_localisation_currency->getCurrencyByCode($currency);
	 */
	public function getCurrencyByCode(string $currency): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "currency` WHERE `code` = '" . $this->db->escape($currency) . "'");

		return $query->row;
	}

	/**
	 * Get Currencies
	 *
	 * @return array<int, array<string, mixed>> currency records
	 *
	 * @example
	 *
	 * $currencies = $this->model_localisation_currency->getCurrencies();
	 */
	public function getCurrencies(): array {
		$currency_data = $this->cache->get('currency');

		if (!$currency_data) {
			$currency_data = [];

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency` WHERE `status` = '1' ORDER BY `title` ASC");

			foreach ($query->rows as $result) {
				$currency_data[$result['code']] = [
					'currency_id'   => $result['currency_id'],
					'title'         => $result['title'],
					'code'          => $result['code'],
					'symbol_left'   => $result['symbol_left'],
					'symbol_right'  => $result['symbol_right'],
					'decimal_place' => $result['decimal_place'],
					'value'         => $result['value'],
					'status'        => $result['status'],
					'date_modified' => $result['date_modified']
				];
			}

			$this->cache->set('currency', $currency_data);
		}

		return $currency_data;
	}
}
