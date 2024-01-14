<?php
namespace googleshopping\traits;

trait StoreLoader {
	protected function loadStore($store_id): void {
		$this->registry->set('setting', new \Config());

		$this->registry->get('load')->model('setting/setting');

		foreach ($this->registry->get('load')->model_setting_setting->getSetting('advertise_google', $store_id) as $key => $value) {
			$this->registry->get('setting')->set($key, $value);
		}
	}
}
