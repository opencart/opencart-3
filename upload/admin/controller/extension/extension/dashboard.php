<?php
/**
 * Class Dashboard
 *
 * @package Admin\Controller\Extension\Extension
 */
class ControllerExtensionExtensionDashboard extends Controller {
	/**
	 * @var array<string, string> $error
	 */
	private array $error = [];

	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/extension/dashboard');

		// Extensions
		$this->load->model('setting/extension');

		$this->getList();
	}

	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->load->language('extension/extension/dashboard');

		// Extensions
		$this->load->model('setting/extension');

		if ($this->validate()) {
			$callable = [$this->{'model_setting_extension'}, 'install'];

			if (is_callable($callable)) {
				$callable('dashboard', $this->request->get['extension']);
			}

			// User Groups
			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/dashboard/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/dashboard/' . $this->request->get['extension']);

			// Call install method if it exists
			$this->load->controller('extension/dashboard/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->load->language('extension/extension/dashboard');

		// Extensions
		$this->load->model('setting/extension');

		if ($this->validate()) {
			$callable = [$this->{'model_setting_extension'}, 'uninstall'];

			if (is_callable($callable)) {
				$callable('dashboard', $this->request->get['extension']);
			}

			// Call uninstall method if it exists
			$this->load->controller('extension/dashboard/' . $this->request->get['extension'] . '/uninstall');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	/**
	 * Get List
	 *
	 * @return void
	 */
	protected function getList(): void {
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$extensions = $this->model_setting_extension->getExtensionsByType('dashboard');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_APPLICATION . 'controller/extension/dashboard/' . $value . '.php')) {
				$callable = [$this->{'model_setting_extension'}, 'uninstall'];

				if (is_callable($callable)) {
					$callable('dashboard', $value);
				}

				unset($extensions[$key]);
			}
		}

		$data['extensions'] = [];

		// Compatibility code for old extension folders
		$files = glob(DIR_APPLICATION . 'controller/extension/dashboard/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				// Compatibility code for old extension folders
				$this->load->language('extension/dashboard/' . $extension, 'extension');

				$data['extensions'][] = [
					'name'       => $this->language->get('extension')->get('heading_title'),
					'width'      => $this->config->get('dashboard_' . $extension . '_width'),
					'status'     => $this->config->get('dashboard_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'sort_order' => $this->config->get('dashboard_' . $extension . '_sort_order'),
					'install'    => $this->url->link('extension/extension/dashboard/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'uninstall'  => $this->url->link('extension/extension/dashboard/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'installed'  => in_array($extension, $extensions),
					'edit'       => $this->url->link('extension/dashboard/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
				];
			}
		}

		$data['promotion'] = $this->load->controller('extension/extension/promotion');

		$this->response->setOutput($this->load->view('extension/extension/dashboard', $data));
	}

	/**
	 * Validate
	 *
	 * @return bool
	 */
	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/extension/dashboard')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
