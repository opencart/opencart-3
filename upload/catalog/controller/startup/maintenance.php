<?php
class ControllerStartupMaintenance extends Controller {
	public function index(): object|null {
		if ($this->config->get('config_maintenance')) {
			// Route
			if (isset($this->request->get['route'])) {
				$route = $this->request->get['route'];
			} else {
				$route = $this->config->get('action_default');
			}

			$ignore = array(
				'common/language/language',
				'common/currency/currency'
			);

			// Show site if logged in as admin
			$user = new \Cart\User($this->registry);

			if (substr($route, 0, 3) != 'api' && !in_array($route, $ignore) && !$user->isLogged()) {
				return new \Action('common/maintenance');
			}
		}

		return null;
	}
}
