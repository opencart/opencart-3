<?php
/**
 * Class Currency
 *
 * @package Admin\Controller\Event
 */
class ControllerEventCurrency extends Controller {
	/**
	 *
     * admin/model/setting/setting/editSetting
     * admin/model/localisation/currency/addCurrency
     * admin/model/localisation/currency/editCurrency
	 *
	 * @param string $route
	 * @param array  $args
	 * @param mixed  $output
	 *
	 * @return void
	 */
	 public function index(string &$route, array &$args, mixed &$output) {
         if ($route == 'model/setting/setting/editSetting' && $args[0] == 'config' && isset($args[1]['config_currency'])) {
             $this->load->controller('extension/currency/' . $this->config->get('config_currency_engine') . '/currency', $args[1]['config_currency']);
         } else {
             $this->load->controller('extension/currency/' . $this->config->get('config_currency_engine') . '/currency', $this->config->get('config_currency'));
         }
     }
}
