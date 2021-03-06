<?php
class ControllerEventCurrency extends Controller {
	// model/setting/setting/editSetting
	// model/localisation/currency/addCurrency
	// model/localisation/currency/editCurrency
	public function index(string &$route, array &$args, mixed &$output): void {
		if ($route == 'model/setting/setting/editSetting' && $args[0] == 'config' && isset($args[1]['config_currency'])) {
			$currency = $args[1]['config_currency'];
		} else {
			$currency = $this->config->get('config_currency');
		}
		
		$currency_data = array(
			'config_currency'	=> $currency
		);

		$this->load->model('setting/extension');

		$extension_info = $this->model_setting_extension->getExtensionByCode('currency', $this->config->get('config_currency_engine'));

		if ($extension_info) {
			$this->load->controller('extension/' . $extension_info['extension'] . '/currency/' . $extension_info['code'] . '|currency', $currency_data);
		}
	}
}
