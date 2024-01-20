<?php

namespace Session;
/**
 * Class File
 *
 * @package System\Library\Session
 */
class File {
	private object $config;

	/**
	 * Constructor
	 *
	 * @param object $registry
	 */
	public function __construct(object $registry) {
		$this->config = $registry->get('config');
	}

	/**
	 * Read
	 *
	 * @param string $session_id
	 *
	 * @return array
	 */
	public function read(string $session_id): array {
		$file = DIR_SESSION . 'sess_' . basename($session_id);

		if (is_file($file)) {
			$size = filesize($file);

			if ($size) {
				$handle = fopen($file, 'r');

				flock($handle, LOCK_SH);

				$data = fread($handle, $size);

				flock($handle, LOCK_UN);

				fclose($handle);

				return json_decode($data, true);
			} else {
				return [];
			}
		}

		return [];
	}

	/**
	 * Write
	 *
	 * @param string $session_id
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function write(string $session_id, array $data): bool {
		$file = DIR_SESSION . 'sess_' . basename($session_id);

		$handle = fopen($file, 'c');

		flock($handle, LOCK_EX);
		fwrite($handle, json_encode($data));
		ftruncate($handle, ftell($handle));
		fflush($handle);
		flock($handle, LOCK_UN);
		fclose($handle);

		return true;
	}

	/**
	 * Destroy
	 *
	 * @param string $session_id
	 *
	 * @return void
	 */
	public function destroy(string $session_id): void {
		$file = DIR_SESSION . 'sess_' . basename($session_id);

		if (is_file($file)) {
			unlink($file);
		}
	}

	/**
	 * GC
	 *
	 * @return void
	 */
	public function gc(): void {
		if (round(mt_rand(1, $this->config->get('session_divisor') / $this->config->get('session_probability'))) == 1) {
			$expire = time() - $this->config->get('session_expire');

			$files = glob(DIR_SESSION . 'sess_*');

			foreach ($files as $file) {
				if (is_file($file) && filemtime($file) < $expire) {
					unlink($file);
				}
			}
		}
	}
}
