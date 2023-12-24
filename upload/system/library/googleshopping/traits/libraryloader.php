<?php
namespace googleshopping\traits;

use googleshopping\Googleshopping;

trait LibraryLoader {
	protected function loadLibrary($store_id): void {
		$this->registry->set('googleshopping', new Googleshopping($this->registry, $store_id));
	}
}
