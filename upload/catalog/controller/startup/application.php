<?php
class ControllerStartupApplication extends Controller {
	public function index(): void {
		// Weight
		$this->registry->set('weight', new \Cart\Weight($this->registry));

		// Length
		$this->registry->set('length', new \Cart\Length($this->registry));

		// Cart
		$this->registry->set('cart', new \Cart\Cart($this->registry));
	}
}