<?php
namespace Cache;
class Mem {
		private int $expire;
	private object $memcache;

	const CACHEDUMP_LIMIT = 9999;

	public function __construct(int $expire = 3600) {
		$this->expire = $expire;

		$this->memcache = new \Memcache();
		$this->memcache->pconnect(CACHE_HOSTNAME, CACHE_PORT);
	}

	public function get(string $key): array|string|null {
		return $this->memcache->get(CACHE_PREFIX . $key);
	}

	public function set(string $key, array|string|null $value) {
		return $this->memcache->set(CACHE_PREFIX . $key, $value, MEMCACHE_COMPRESSED, $this->expire);
	}

	public function delete($key) {
		$this->memcache->delete(CACHE_PREFIX . $key);
	}
}
