<?php
class ControllerEventTheme extends Controller {
    public function index(string &$route, array &$args): void {
        // This is only here for compatibility with old templates
        if (substr($route, -3) == 'tpl') {
            $view = substr($route, 0, -3);
        }

        if (is_file(DIR_TEMPLATE . $route . '.twig')) {
            $this->config->set('template_engine', 'twig');
        } elseif (is_file(DIR_TEMPLATE . $route . '.tpl')) {
            $this->config->set('template_engine', 'template');
        }
    }
}
