<?php
/**
 * Class Language
 *
 * @package Catalog\Controller\Event
 */
class ControllerEventLanguage extends Controller {
	// view/*/before
	// Dump all the language vars into the template.
	/**
	 * @param string $route
	 * @param array  $args
	 *
	 * @return void
	 */
    public function index(string &$route, array &$args): void {
        foreach ($this->language->all() as $key => $value) {
            if (!isset($args[$key])) {
                $args[$key] = $value;
            }
        }
    }

	// controller/*/before
	// 1. Before controller load store all current loaded language data
	/**
	 * @param string $route
	 * @param array  $args
	 *
	 * @return void
	 */
    public function before(string &$route, mixed &$args): void {
        $this->language->set('backup', $this->language->all());
    }

	// controller/*/after
	// 2. After controller load restore old language data
	/**
	 * @param string $route
	 * @param array  $args
	 * @param mixed  $output
	 *
	 * @return void
	 */
    public function after(string &$route, mixed &$args, mixed &$output): void {
        $data = $this->language->get('backup');

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->language->set($key, $value);
            }
        }
    }
}
