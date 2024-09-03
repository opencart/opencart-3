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
 * Class Router
 */
class Router {
	/**
	 * @var object $registry
	 */
	private object $registry;
	/**
	 * @var array<string, mixed> $pre_action
	 */
	private array $pre_action = [];
	private $error;

	/**
	 * Constructor
	 *
	 * @param object $registry
	 */
	public function __construct(object $registry) {
		$this->registry = $registry;
	}

	/**
	 * Add Pre Action
	 *
	 * @param Action $pre_action
	 *
	 * @return void
	 */
	public function addPreAction(Action $pre_action): void {
		$this->pre_action[] = $pre_action;
	}

	/**
	 * Dispatch
	 *
	 * @param Action $action
	 * @param Action $error
	 *
	 * @return void
	 */
	public function dispatch(Action $action, Action $error): void {
		$this->error = $error;

		foreach ($this->pre_action as $pre_action) {
			$result = $this->execute($pre_action);

			if ($result instanceof Action) {
				$action = $result;
				break;
			}
		}

		while ($action instanceof Action) {
			$action = $this->execute($action);
		}
	}

	/**
	 * Execute
	 *
	 * @param Action $action
	 */
	private function execute(Action $action) {
		$result = $action->execute($this->registry);

		if ($result instanceof Action) {
			return $result;
		}

		if ($result instanceof \Exception) {
			$action = $this->error;

			$this->error = null;

			return $action;
		}
	}
}
