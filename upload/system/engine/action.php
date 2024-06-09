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
 * Class Action
 *
 * @package System\Engine
 */
class Action {
	/**
	 * @var string $id
	 */
	private string $id;
	/**
	 * @var string $route
	 */
	private string $route;
	/**
	 * @var string $method
	 */
	private string $method = 'index';

	/**
	 * Constructor
	 *
	 * @param string $route
	 */
	public function __construct(string $route) {
		$this->id = $route;

		$parts = explode('/', preg_replace('/[^a-zA-Z0-9_\/]/', '', $route));

		// Break apart the route
		while ($parts) {
			$file = DIR_APPLICATION . 'controller/' . implode('/', $parts) . '.php';

			if (is_file($file)) {
				$this->route = implode('/', $parts);
				break;
			} else {
				$this->method = array_pop($parts);
			}
		}
	}

	/**
	 * Get Id
	 *
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * Execute
	 *
	 * @param object               $registry
	 * @param array<string, mixed> $args
	 *
	 * @return mixed
	 */
	public function execute(object $registry, array $args = []) {
		// Stop any magical methods being called
		if (substr($this->method, 0, 2) == '__') {
			return new \Exception('Error: Calls to magic methods are not allowed!');
		}

		$file = DIR_APPLICATION . 'controller/' . $this->route . '.php';

		$class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', (string)$this->route);

		// Initialize the class
		if (is_file($file)) {
			include_once($file);

			$controller = new $class($registry);
		} else {
			return new \Exception('Error: Could not call ' . $this->route . '/' . $this->method . '!');
		}

		$reflection = new \ReflectionClass($class);

		if ($reflection->hasMethod($this->method) && $reflection->getMethod($this->method)->getNumberOfRequiredParameters() <= count($args)) {
			return call_user_func_array([$controller, $this->method], $args);
		} else {
			return new \Exception('Error: Could not call ' . $this->route . '/' . $this->method . '!');
		}
	}
}
