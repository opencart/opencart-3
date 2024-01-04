<?php
namespace googleshopping;

/**
 * Log class
 */
class Log {
	private $handle;

	/**
	 * Constructor
	 *
	 * @param string $filename
	 * @param mixed  $max_size
	 */
	public function __construct($filename, $max_size = 8388608) {
		$file = DIR_LOGS . $filename;

		clearstatcache(true);

		if ((!file_exists($file) && !is_writable(DIR_LOGS)) || (file_exists($file) && !is_writable($file))) {
			// Do nothing, as we have no permissions
			return;
		}

		if (file_exists($file) && filesize($file) >= $max_size) {
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
