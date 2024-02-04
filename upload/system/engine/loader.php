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
 * Class Loader
 *
 * @mixin Loader
 */
class Loader {
	protected object $registry;

	/**
	 * Constructor
	 *
	 * @property Registry $registry
	 *
	 * @param mixed $registry
	 */
	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * Controller
	 *
	 * @param string               $route
	 * @param array<string, mixed> $data
	 *
	 * @throws \Exception
	 *
	 * Removing the mixed output as a temporary workaround since admin extension
	 * installers don't seem to like that really much
	 */
	public function controller(string $route, array $data = []) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

		// Keep the original trigger
		$trigger = $route;

		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('controller/' . $trigger . '/before', [&$route, &$data]);

		// Make sure it's only the last event that returns an output if required.
		if ($result != null && !$result instanceof \Exception) {
			$output = $result;
		} else {
			$action = new \Action($route);
			$output = $action->execute($this->registry, [&$data]);
		}

		// Trigger the post events
		$result = $this->registry->get('event')->trigger('controller/' . $trigger . '/after', [
			&$route,
			&$data,
			&$output
		]);

		if ($result && !$result instanceof \Exception) {
			$output = $result;
		}

		if (!$output instanceof \Exception) {
			return $output;
		}
	}

	/**
	 * Model
	 *
	 * @param string $route
	 *
	 * @return void
	 */
	public function model(string $route): void {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

		if (!$this->registry->has('model_' . str_replace('/', '_', $route))) {
			$file = DIR_APPLICATION . 'model/' . $route . '.php';
			$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);

			if (is_file($file)) {
				include_once($file);

				$proxy = new \Proxy();

				// Overriding models is a little harder so we have to use PHP's magic methods
				// In future version we can use runkit
				foreach (get_class_methods($class) as $method) {
					if ((substr($method, 0, 2) != '__') && is_callable($class, $method)) {
						$proxy->{$method} = $this->callback($this->registry, $route . '/' . $method);
					}
				}

				$this->registry->set('model_' . str_replace('/', '_', $route), $proxy);
			} else {
				throw new \Exception('Error: Could not load model ' . $route . '!');
			}
		}
	}

	/**
	 * View
	 *
	 * @param string               $route
	 * @param array<string, mixed> $data
	 * @param string               $code
	 *
	 * @return string
	 */
	public function view(string $route, array $data = [], string $code = ''): string {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

		// Keep the original trigger
		$trigger = $route;

		// Template contents. Not the output!
		$code = '';

		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('view/' . $trigger . '/before', [&$route, &$data, &$code]);

		// Make sure it's only the last event that returns an output if required.
		if ($result && !$result instanceof \Exception) {
			$output = $result;
		} else {
			$template = new \Template($this->registry->get('config')->get('template_engine'));

			foreach ($data as $key => $value) {
				$template->set($key, $value);
			}

			$output = $template->render($this->registry->get('config')->get('template_directory') . $route, $code);
		}

		// Trigger the post events
		$result = $this->registry->get('event')->trigger('view/' . $trigger . '/after', [&$route, &$data, &$output]);

		if ($result && !$result instanceof \Exception) {
			$output = $result;
		}

		return $output;
	}

	/**
	 * Library
	 *
	 * @param string  $route
	 * @param array[] $args
	 */
	public function library(string $route, array &...$args): void {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

		$file  = DIR_SYSTEM . 'library/' . $route . '.php';
		$class = str_replace('/', '\\', $route);

		if (is_file($file)) {
			include_once($file);

			$this->registry->set(basename($route), new $class($this->registry));
		} else {
			throw new \Exception('Error: Could not load library ' . $route . '!');
		}
	}

	/**
	 * Helper
	 *
	 * @param string $route
	 *
	 * @return void
	 */
	public function helper(string $route): void {
		$file = DIR_SYSTEM . 'helper/' . preg_replace('/[^a-zA-Z0-9_\/]/', '', $route) . '.php';

		if (is_file($file)) {
			include_once($file);
		} else {
			throw new \Exception('Error: Could not load helper ' . $route . '!');
		}
	}

	/**
	 * Config
	 *
	 * @param string $route
	 */
	public function config(string $route): void {
		$this->registry->get('event')->trigger('config/' . $route . '/before', [&$route]);

		$this->registry->get('config')->load($route);

		$this->registry->get('event')->trigger('config/' . $route . '/after', [&$route]);
	}

	/**
	 * Language
	 *
	 * @param string $route
	 * @param string $key
	 *
	 * @return array
	 */
	public function language(string $route, string $key = ''): array {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

		// Keep the original trigger
		$trigger = $route;

		$result = $this->registry->get('event')->trigger('language/' . $trigger . '/before', [&$route, &$key]);

		if ($result && !$result instanceof \Exception) {
			$output = $result;
		} else {
			$output = $this->registry->get('language')->load($route, $key);
		}

		$result = $this->registry->get('event')->trigger('language/' . $trigger . '/after', [&$route, &$key, &$output]);

		if ($result && !$result instanceof \Exception) {
			$output = $result;
		}

		return $output;
	}

	/**
	 * Callback
	 *
	 * @param mixed $registry
	 * @param mixed $route
	 *
	 * @return array<string, string>
	 */
	protected function callback($registry, $route): array {
		return function($args) use ($registry, $route) {
			static $model;

			$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

			// Keep the original trigger
			$trigger = $route;

			// Trigger the pre events
			$result = $registry->get('event')->trigger('model/' . $trigger . '/before', [&$route, &$args]);

			if ($result && !$result instanceof \Exception) {
				$output = $result;
			} else {
				$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', substr($route, 0, strrpos($route, '/')));

				// Store the model object
				$key = substr($route, 0, strrpos($route, '/'));

				if (!isset($model[$key])) {
					$model[$key] = new $class($registry);
				}

				$method = substr($route, strrpos($route, '/') + 1);

				$callable = [$model[$key], $method];

				if (is_callable($callable)) {
					$output = $callable(...$args);
				} else {
					throw new \Exception('Error: Could not call model/' . $route . '!');
				}
			}

			// Trigger the post events
			$result = $registry->get('event')->trigger('model/' . $trigger . '/after', [&$route, &$args, &$output]);

			if ($result && !$result instanceof \Exception) {
				$output = $result;
			}

			return $output;
		};
	}
}
