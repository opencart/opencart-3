<?php
namespace googleshopping;

/**
 * Log class
 */
class Log {
	/**
	 * @var mixed
	 */
	private mixed $handle;

	/**
	 * Constructor
	 *
	 * @param string $filename
	 * @param mixed  $max_size
	 */
	public function __construct(string $filename, $max_size = 8388608) {
		$file = DIR_LOGS . $filename;

		clearstatcache(true);

		if ((!is_file($file) && !is_writable(DIR_LOGS)) || (is_file($file) && !is_writable($file))) {
			// Do nothing, as we have no permissions
			return;
		}

		if (is_file($file) && filesize($file) >= $max_size) {
			$mode = 'wb';
		} else {
			$mode = 'ab';
		}

		$this->handle = @fopen(DIR_LOGS . $filename, $mode);
	}

	/**
	 * Write
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function write(string $message): void {
		if (is_resource($this->handle)) {
			fwrite($this->handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		if (is_resource($this->handle)) {
			fclose($this->handle);
		}
	}
}
