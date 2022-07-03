<?php
class ControllerStartupCustomer extends Controller {
	public function index(): void {
		$this->registry->set('customer', new \Cart\Customer($this->registry));

		// Customer Group		
		if (isset($this->session->data['customer']) && isset($this->session->data['customer']['customer_group_id'])) {
			// For API calls
			$this->config->set('config_customer_group_id', $this->session->data['customer']['customer_group_id']);
		} elseif ($this->customer->isLogged()) {
			// Logged in customers
			$this->config->set('config_customer_group_id', $this->customer->getGroupId());
		} elseif (isset($this->session->data['guest']) && isset($this->session->data['guest']['customer_group_id'])) {
			$this->config->set('config_customer_group_id', $this->session->data['guest']['customer_group_id']);
		} else {
			$this->config->set('config_customer_group_id', $this->config->get('config_customer_group_id'));
		}
	}
}