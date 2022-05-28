<?php
class ControllerStartupRouter extends Controller {
	public function index() {
		if (isset($this->request->get['route']) && $this->request->get['route'] != 'action/route') {
			$route = $this->request->get['route'];			
		} else {
			$route = $this->config->get('action_default');
		}
		
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		return new \Action($route);
	}
}