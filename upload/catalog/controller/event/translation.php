<?php
/**
 * Class Translation
 *
 * @package Catalog\Controller\Event
 */
class ControllerEventTranslation extends Controller {
	/**
	 * @param string $route
	 * @param string $key
	 *
	 * @return void
	 */
    public function index(string &$route, string &$key): void {
        // Translations
        $this->load->model('design/translation');

        $results = $this->model_design_translation->getTranslations($route);

        foreach ($results as $result) {
            if (!$key) {
                $this->language->set($result['key'], html_entity_decode($result['value'], ENT_QUOTES, 'UTF-8'));
            } else {
                $this->language->get($key)->set($result['key'], html_entity_decode($result['value'], ENT_QUOTES, 'UTF-8'));
            }
        }
    }
}
