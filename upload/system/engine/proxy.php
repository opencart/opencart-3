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
 * Class Proxy
 *
 * @template TWraps of \Model
 *
 * @mixin TWraps
 */
class Proxy {
	/**
	 * @var array<string, array<string, mixed>> $data
	 */
	protected array $data = [];

	/**
	 * __get
	 *
	 * @param mixed $key
	 *
	 * @return mixed
	 */
	public function __get($key) {
		return $this->data[$key];
	}

	/**
	 * __set
	 *
	 * @param string $key
	 * @param object $value
	 *
	 * @return void
	 */
	public function __set(string $key, object $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * __call
	 *
	 * @param string               $key
	 * @param array<string, mixed> $args
	 *
	 * @return mixed
	 */
	public function __call(string $key, array $args) {
		$arg_data = [];

		$args = func_get_args();

		foreach ($args as $arg) {
			$arg_data[] = &$arg;
		}

		if (isset($this->data[$key])) {
			return ($this->data[$key])(...$arg_data);
		} else {
			$trace = debug_backtrace();

			throw new \Exception('<b>Notice</b>:  Undefined property: Proxy::' . $key . ' in <b>' . $trace[1]['file'] . '</b> on line <b>' . $trace[1]['line'] . '</b>');
		}
	}
}
