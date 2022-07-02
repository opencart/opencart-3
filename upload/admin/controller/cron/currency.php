<?php
class ControllerCronCurrency extends Controller {
	// Requires converted engine.
	public function index(int $cron_id, string $code, string $cycle, string $date_added, string $date_modified): void {
		$currency_data = array(
			'config_currency'	=> $this->config->get('config_currency');
		);
		
		$this->load->controller('extension/currency/' . $this->config->get('config_currency_engine') . '/currency', $currency_data);
	}
}
