<?php
namespace Cache;
class Memcached {
	/**
	 * @var int
	 */
	private int $expire;
	/**
	 * @var object
	 */
	private object $memcached;

	/**
	 * @var int
	 */
	public const CACHEDUMP_LIMIT = 9999;

	/**
	 * Constructor
	 *
	 * @param int $expire
	 */
	public function __construct(int $expire = 3600) {
		$this->expire = $expire;
		$this->memcached = new \Memcached();

		$this->memcached->addServer(CACHE_HOSTNAME, CACHE_PORT);
	}

	/**
	 * Get
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get(string $key) {
		return $this->memcached->get(CACHE_PREFIX . $key);
	}

	/**
	 * Set
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public function set(string $key, $value) {
		return $this->memcached->set(CACHE_PREFIX . $key, $value, $this->expire);
	}

	/**
	 * Delete
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function delete(string $key) {
		$this->memcached->delete(CACHE_PREFIX . $key);
	}
}
