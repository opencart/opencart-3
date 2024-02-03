<?php
namespace googleshopping\traits;

trait StoreLoader {
	protected function loadStore($store_id): void {
		$this->registry->set('setting', new \Config());

		foreach ($this->getSetting('advertise_google', $store_id) as $key => $value) {
			$this->registry->get('setting')->set($key, $value);
		}
	}

	/**
	 * Get Setting
	 *
	 * @param string $code
	 * @param int    $store_id
	 *
	 * @return array<string, mixed>
	 */
	protected function getSetting(string $code, int $store_id = 0): array {
		$data = [];

		$query = $this->registry->get('db')->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->registry->get('db')->escape($code) . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$data[$result['key']] = $result['value'];
			} else {
				$data[$result['key']] = json_decode($result['value'], true);
			}
		}

		return $data;
	}
}
