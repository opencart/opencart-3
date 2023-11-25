<?php
/**
 * @package        OpenCart
 * @author         Daniel Kerr
 * @copyright      Copyright (c) 2005 - 2022, OpenCart, Ltd. (https://www.opencart.com/)
 * @license        https://opensource.org/licenses/GPL-3.0
 * @link           https://www.opencart.com
 */

/**
 * Router class
 */
class Router {
    private object $registry;
    private array $pre_action = [];
    private object $error;

    /**
     * Constructor
     *
     * @param object $registry
     */
    public function __construct(object $registry) {
        $this->registry = $registry;
    }

    /**
     * @param Action $pre_action
	 *
	 * @return void
     */
    public function addPreAction(Action $pre_action): void {
        $this->pre_action[] = $pre_action;
    }

    /**
     * @param object $action
     * @param object $error
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
     * @param Action $action
     */
    private function execute(Action $action) {
        $result = $action->execute($this->registry);

        if ($result instanceof Action) {
            return $result;
        }

        if ($result instanceof Exception) {
            $action = $this->error;

            $this->error = null;

            return $action;
        }
    }
}
