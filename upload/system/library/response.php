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
 * Response class
 */
class Response {
	private array $headers = [];
	private int $level = 0;
	private string $output = '';

	/**
	 * Constructor
	 *
	 * @param string $header
	 */
	public function addHeader(string $header): void {
		$this->headers[] = $header;
	}

	/**
	 * Redirect
	 *
	 * @param string $url
	 * @param int    $status
	 */
	public function redirect(string $url, int $status = 302): void {
		header('Location: ' . str_replace(['&amp;', "\n", "\r"], ['&', '', ''], $url), true, $status);
		exit();
	}

	/**
	 * setCompression
	 *
	 * @param int $level
	 */
	public function setCompression(int $level): void {
		$this->level = $level;
	}

	/**
	 * getOutput
	 *
	 * @return string
	 */
	public function getOutput(): string {
		return $this->output;
	}

	/**
	 * setOutput
	 *
	 * @param string $output
	 *
	 * @return void
	 */
	public function setOutput(string $output): void {
		$this->output = $output;
	}

	/**
	 * Compress
	 *
	 * @param mixed $data
	 * @param int   $level
	 *
	 * @return string
	 */
	private function compress($data, int $level = 0): string {
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))) {
			$encoding = 'gzip';
		}

		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip'))) {
			$encoding = 'x-gzip';
		}

		if (!isset($encoding) || ($level < -1 || $level > 9)) {
			return $data;
		}

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
		}

		if (headers_sent()) {
			return $data;
		}

		if (connection_status()) {
			return $data;
		}

		$this->addHeader('Content-Encoding: ' . $encoding);

		return gzencode($data, (int)$level);
	}

	/**
	 * Output
	 *
	 * Displays the set HTML output
	 *
	 * @return void
	 */
	public function output(): void {
		if ($this->output) {
			$output = $this->level ? $this->compress($this->output, $this->level) : $this->output;

			if (!headers_sent()) {
				foreach ($this->headers as $header) {
					header($header, true);
				}
			}

			echo $output;
		}
	}
}
