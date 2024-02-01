<?php
/**
 * Class Application
 *
 * @package Catalog\Controller\Startup
 */
class ControllerStartupApplication extends Controller {
	/**
	 * Index
	 * 
	 * @return void
	 */
	public function index(): void {
		// Weight
		$this->registry->set('weight', new \Cart\Weight($this->registry));

		// Length
		$this->registry->set('length', new \Cart\Length($this->registry));

		// Cart
		$this->registry->set('cart', new \Cart\Cart($this->registry));
	}
}
