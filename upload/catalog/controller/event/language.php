<?php
class ControllerEventLanguage extends Controller {
	public function index(string &$route, array &$args): void {
		foreach ($this->language->all() as $key => $value) {
			if (!isset($args[$key])) {
				$args[$key] = $value;
			}
		}
	}	
	
	// 1. Before controller load store all current loaded language data
	public function before(string &$route, mixed &$args): void {
		$this->language->set('backup', $this->language->all());
	}
	
	// 2. After contoller load restore old language data
	public function after(string &$route, mixed &$args, mixed &$output): void {
		$data = $this->language->get('backup');
		
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$this->language->set($key, $value);
			}
		}
	}
}