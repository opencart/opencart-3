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
 * Proxy class
 *
 * @template TWraps of Model
 *
 * @mixin TWraps
 */
class Proxy {
	protected array $data = [];

	/**
	 * Get
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function __get($key) {
		return $this->data[$key];
	}

	/**
	 * Set
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return void
	 */
	public function __set($key, $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * Call
	 *
	 * @param $key
	 * @param $args
	 *
	 * @return mixed|void
	 */
	public function __call($key, $args) {
		$arg_data = [];

		$args = func_get_args();

		foreach ($args as $arg) {
			$arg_data[] = &$arg;
		}

		if (isset($this->data[$key])) {
			return ($this->data[$key])(...$arg_data);
		} else {
			$trace = debug_backtrace();

			exit('<b>Notice</b>:  Undefined property: Proxy::' . $key . ' in <b>' . $trace[1]['file'] . '</b> on line <b>' . $trace[1]['line'] . '</b>');
		}
	}
}
