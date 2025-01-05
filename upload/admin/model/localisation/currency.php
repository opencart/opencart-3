<?php
/**
 * Class Currency
 *
 * Can be called from $this->load->model('localisation/currency');
 *
 * @package Admin\Model\Localisation
 */
class ModelLocalisationCurrency extends Model {
	/**
	 * Add Currency
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new currency record
	 *
	 * @example
	 *
	 * $currency_id = $this->model_localisation_currency->addCurrency($data);
	 */
	public function addCurrency(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "currency` SET `title` = '" . $this->db->escape($data['title']) . "', `code` = '" . $this->db->escape($data['code']) . "', `symbol_left` = '" . $this->db->escape($data['symbol_left']) . "', `symbol_right` = '" . $this->db->escape($data['symbol_right']) . "', `decimal_place` = '" . $this->db->escape($data['decimal_place']) . "', `value` = '" . $this->db->escape($data['value']) . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW()");

		$currency_id = $this->db->getLastId();

		$this->cache->delete('currency');

		return $currency_id;
	}

	/**
	 * Edit Currency
	 *
	 * @param int                  $currency_id primary key of the currency record
	 * @param array<string, mixed> $data        array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_currency->editCurrency($currency_id, $data);
	 */
	public function editCurrency(int $currency_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `title` = '" . $this->db->escape($data['title']) . "', `code` = '" . $this->db->escape($data['code']) . "', `symbol_left` = '" . $this->db->escape($data['symbol_left']) . "', `symbol_right` = '" . $this->db->escape($data['symbol_right']) . "', `decimal_place` = '" . $this->db->escape($data['decimal_place']) . "', `value` = '" . $this->db->escape($data['value']) . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW() WHERE `currency_id` = '" . (int)$currency_id . "'");

		$this->cache->delete('currency');
	}

	/**
	 * Edit Value By Code
	 *
	 * @param string $code
	 * @param float  $value
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_currency->editValueByCode($code, $value);
	 */
	public function editValueByCode(string $code, float $value): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "currency` SET `value` = '" . (float)$value . "', `date_modified` = NOW() WHERE `code` = '" . $this->db->escape($code) . "'");

		$this->cache->delete('currency');
	}

	/**
	 * Delete Currency
	 *
	 * @param int $currency_id primary key of the currency record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_currency->deleteCurrency($currency_id);
	 */
	public function deleteCurrency(int $currency_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "currency` WHERE `currency_id` = '" . (int)$currency_id . "'");

		$this->cache->delete('currency');
	}

	/**
	 * Get Currency
	 *
	 * @param int $currency_id primary key of the currency record
	 *
	 * @return array<string, mixed> currency record that has currency ID
	 *
	 * @example
	 *
	 * $currency_info = $this->model_localisation_currency->getCurrency($currency_id);
	 */
	public function getCurrency(int $currency_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "currency` WHERE `currency_id` = '" . (int)$currency_id . "'");

		return $query->row;
	}

	/**
	 * Get Currency By Code
	 *
	 * @param string $currency primary key of the currency record
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
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<string, array<string, mixed>> currency records
	 *
	 * @example
	 *
	 * $results = $this->model_localisation_currency->getCurrencies();
	 */
	public function getCurrencies(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "currency`";

			$sort_data = [
				'title',
				'code',
				'value',
				'date_modified'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY `title`";
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
		} else {
			$currency_data = [];

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency` ORDER BY `title` ASC");

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

			return $currency_data;
		}
	}

	/**
	 * Refresh
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_localisation_currency->refresh();
	 */
	public function refresh(): void {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

		if ($status == 200) {
			$dom = new \DOMDocument('1.0', 'UTF-8');
			$dom->loadXml($response);

			$cube = $dom->getElementsByTagName('Cube')->item(0);

			$currencies = [];

			$currencies['EUR'] = 1.0000;

			foreach ($cube->getElementsByTagName('Cube') as $currency) {
				if ($currency->getAttribute('currency')) {
					$currencies[$currency->getAttribute('currency')] = $currency->getAttribute('rate');
				}
			}

			if ($currencies) {
				$value = $currencies['EUR'];

				// Currencies
				$this->load->model('localisation/currency');

				$results = $this->model_localisation_currency->getCurrencies();

				foreach ($results as $result) {
					if (isset($currencies[$result['code']])) {
						$this->editValueByCode($result['code'], 1 / ($value * ($value / $currencies[$result['code']])));
					}
				}

				$this->editValueByCode('', '1.00000');
			}
		}

		$this->cache->delete('currency');
	}

	/**
	 * Get Total Currencies
	 *
	 * @return int total number of currency records
	 *
	 * @example
	 *
	 * $currency_total = $this->model_localisation_currency->getTotalCurrencies();
	 */
	public function getTotalCurrencies(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "currency`");

		return (int)$query->row['total'];
	}
}
