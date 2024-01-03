<?php
namespace googleshopping;

abstract class Library {
	protected $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * Get
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function __get($key) {
		return $this->registry->get($key);
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
		$this->registry->set($key, $value);
	}
}
