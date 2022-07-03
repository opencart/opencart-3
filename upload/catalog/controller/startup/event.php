<?php
class ControllerStartupEvent extends Controller {
	public function index(): void {
		// Add events from the DB
		$this->load->model('setting/event');
		
		$results = $this->model_setting_event->getEvents();
		
		foreach ($results as $result) {
			$part = explode('/', $result['trigger']);

			if ($part[0] == 'catalog') {
				array_shift($part);

				$this->event->register(implode('/', $part), new \Action($result['action']), $result['sort_order']);
			}
		}
	}
}