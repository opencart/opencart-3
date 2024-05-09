<?php
/**
 * Class Theme
 *
 * @package \Install\Controller\Event
 */
class ControllerEventTheme extends Controller {
	/**
	 * Index
	 *
	 * @param mixed $view
	 * @param mixed $data
	 *
	 * @return void
	 */
	public function index(&$view, &$data): void {
		if (is_file(DIR_TEMPLATE . $view . '.twig')) {
			$this->config->set('template_engine', 'twig');
		} elseif (is_file(DIR_TEMPLATE . $view . '.tpl')) {
			$this->config->set('template_engine', 'php');
		}
	}
}
