<?php
/**
 * @package        OpenCart
 * @author         Daniel Kerr
 * @copyright      Copyright (c) 2005 - 2022, OpenCart, Ltd. (https://www.opencart.com/)
 * @license        https://opensource.org/licenses/GPL-3.0
 * @link           https://www.opencart.com
 */

/**
 * Config class
 */
class Config {
	private array $data = [];

	/**
	 * Get
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get(string $key) {
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	/**
	 * Set
	 *
	 * @param string $key
	 * @param int    $value
	 *
	 * @return void
	 */
	public function set(string $key, int $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * Has
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool {
		return isset($this->data[$key]);
	}

	/**
	 * Load
	 *
	 * @param string $filename
  	 *
	 * @return string|bool
	 */
	public function load(string $filename): string|bool {
		$file = DIR_CONFIG . $filename . '.php';

		if (file_exists($file)) {
			$_ = [];

			require($file);

			$this->data = array_merge($this->data, $_);
		} else {
			trigger_error('Error: Could not load config ' . $filename . '!');
			exit();
		}
	}
}
