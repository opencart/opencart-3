<?php
/**
 * Class Language
 *
 * @package Catalog\Model\Localisation
 */
class ModelLocalisationLanguage extends Model {
	private array $data = [];

	/**
	 * Get Language
	 *
	 * @param int $language_id
	 *
	 * @return array<string, mixed>
	 */
	public function getLanguage(int $language_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `language_id` = '" . (int)$language_id . "'");

		return $query->row;
	}

	/**
	 * Get Language By Code
	 *
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 */
	public function getLanguageByCode(string $code): array {
		if (isset($this->data[$code])) {
			return $this->data[$code];
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($code) . "'");

		$language = $query->row;

		if ($language) {
			$language['image'] = HTTP_SERVER;

			if (!$language['extension']) {
				$language['image'] .= 'catalog/';
			} else {
				$language['image'] .= 'extension/' . $language['extension'] . '/catalog/';
			}

			$language['image'] .= 'language/' . $language['code'] . '/' . $language['code'] . '.png';
		}

		$this->data[$code] = $language;

		return $language;
	}

	/**
	 * Get Languages
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getLanguages(): array {
		$language_data = $this->cache->get('catalog.language');

		if (!$language_data) {
			$language_data = [];

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `status` = '1' ORDER BY `sort_order`, `name`");

			foreach ($query->rows as $result) {
				$language_data[$result['code']] = [
					'language_id' => $result['language_id'],
					'name'        => $result['name'],
					'code'        => $result['code'],
					'locale'      => $result['locale'],
					'image'       => $result['image'],
					'directory'   => $result['directory'],
					'sort_order'  => $result['sort_order'],
					'status'      => $result['status']
				];
			}

			$this->cache->set('catalog.language', $language_data);
		}

		return $language_data;
	}
}
