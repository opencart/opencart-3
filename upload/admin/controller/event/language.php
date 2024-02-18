<?php
/**
 * Class Language
 *
 * @package Admin\Controller\Event
 */
class ControllerEventLanguage extends Controller {
	/**
	 * Index
	 *
	 * @param string               $route
	 * @param array<string, mixed> $args
	 *
	 * @return void
	 *
	 * Dump all the language vars into the template.
	 *
	 * view/ * /before
	 */
	public function index(string &$route, array &$args): void {
		foreach ($this->language->all() as $key => $value) {
			if (!isset($args[$key])) {
				$args[$key] = $value;
			}
		}
	}

	/**
	 * Before
	 *
	 * @param string               $route
	 * @param array<string, mixed> $args
	 *
	 * @return void
	 *
	 * 1. Before controller load store all current loaded language data.
	 *
	 * controller/ * /before
	 */
	public function before(string &$route, mixed &$args): void {
		$this->language->set('backup', $this->language->all());
	}

	/**
	 * After
	 *
	 * @param string               $route
	 * @param array<string, mixed> $args
	 * @param mixed                $output
	 *
	 * @return void
	 *
	 * 2. After controller load restore old language data.
	 *
	 * controller/ * / * /after
	 */
	public function after(string &$route, array &$args, mixed &$output): void {
		$data = $this->language->get('backup');

		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$this->language->set($key, $value);
			}
		}
	}
}
