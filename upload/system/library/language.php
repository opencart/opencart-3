<?php
/**
 * @package        OpenCart
 *
 * @author         Daniel Kerr
 * @copyright      Copyright (c) 2005 - 2022, OpenCart, Ltd. (https://www.opencart.com/)
 * @license        https://opensource.org/licenses/GPL-3.0
 *
 * @see           https://www.opencart.com
 */

/**
 * Language class
 */
class Language {
	private $default = 'en-gb';
	private $directory;
	public $data = [];

	/**
	 * Constructor
	 *
	 * @param string $directory
	 */
	public function __construct(string $directory = '') {
		$this->directory = $directory;
	}

	/**
	 * Get
	 *
	 * @param string $key
	 */
	public function get(string $key) {
		return $this->data[$key] ?? $key;
	}

	/**
	 * Set
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function set(string $key, $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * All
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		return $this->data;
	}

	/**
	 * Load
	 *
	 * @param string $filename
	 * @param string $key
	 *
	 * @return array<string, mixed>
	 */
	public function load(string $filename, string $key = ''): array {
		if (!$key) {
			$_ = [];

			$file = DIR_LANGUAGE . $this->default . '/' . $filename . '.php';

			if (is_file($file)) {
				require($file);
			}

			$file = DIR_LANGUAGE . $this->directory . '/' . $filename . '.php';

			if (is_file($file)) {
				require($file);
			}

			$this->data = array_merge($this->data, $_);
		} else {
			// Put the language into a sub key
			$this->data[$key] = new self($this->directory);
			$this->data[$key]->load($filename);
		}

		return $this->data;
	}
}
