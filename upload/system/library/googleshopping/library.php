<?php
namespace googleshopping;

/**
 * Library Abstract Class
 */
abstract class Library {
	/**
	 * @property \Registry $registry
	 */
	protected object $registry;

	/**
	 * Constructor
	 *
	 * @property \Registry $registry
	 *
	 * @param mixed $registry
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
