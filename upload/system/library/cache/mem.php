<?php
namespace Cache;
class Mem {
    private int    $expire;
    private object $memcache;

    const CACHEDUMP_LIMIT = 9999;

    public function __construct(int $expire = 3600) {
        $this->expire = $expire;

        $this->memcache = new \Memcache();
        $this->memcache->pconnect(CACHE_HOSTNAME, CACHE_PORT);
    }

	/**
	 * Get
	 *
	 * @param string $key
	 */
    public function get(string $key) {
        return $this->memcache->get(CACHE_PREFIX . $key);
    }

	/**
	 * Set
	 *
	 * @param string $key
	 */
    public function set(string $key, $value) {
        return $this->memcache->set(CACHE_PREFIX . $key, $value, MEMCACHE_COMPRESSED, $this->expire);
    }

	/**
	 * Delete
	 *
	 * @param string $key
	 */
    public function delete(string $key) {
        $this->memcache->delete(CACHE_PREFIX . $key);
    }
}
