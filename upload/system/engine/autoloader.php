<?php
class Autoloader {
	private array $path = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		spl_autoload_extensions('.php');
	}

	/**
	 * Register
	 *
	 * @param string $namespace
	 * @param string $directory
	 * @param bool   $psr4
	 *
	 * @return void
	 *
	 * @psr-4 filename standard is stupid composer has lower case file structure than its packages have camelcase file names!
	 */
	public function register(string $namespace, string $directory, $psr4 = false): void {
		$this->path[$namespace] = [
			'directory' => $directory,
			'psr4'      => $psr4
		];
	}
}
