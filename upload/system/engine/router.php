<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2022, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Router class
*/
class Router {
	private object $registry;
	private array $pre_action = array();
	private object $error;
	
	/**
	 * Constructor
	 *
	 * @param	object	$route
 	*/
	public function __construct(object $registry) {
		$this->registry = $registry;
	}
	
	/**
	 * 
	 *
	 * @param	object	$pre_action
 	*/	
	public function addPreAction(Action $pre_action): void {
		$this->pre_action[] = $pre_action;
	}

	/**
	 * 
	 *
	 * @param	object	$action
	 * @param	object	$error
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
	 * 
	 *
	 * @param	object	$action
	 * @return	object
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