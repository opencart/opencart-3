<?php
namespace googleshopping;

abstract class Library {
	protected object $registry;

	/**
	 * Constructor
	 * 
	 * @property Registry $registry
	 */
	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * Get
	 *
	 * @param mixed $key
	 *
	 * @return mixed
	 */
	public function __get($key) {
		return $this->registry->get($key);
	}

	/**
	 * Set
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function __set($key, $value): void {
		$this->registry->set($key, $value);
	}
}
