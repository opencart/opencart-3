<?php
/**
 * Class Currency
 *
 * @package Admin\Controller\Extension\Extension
 */
class ControllerExtensionExtensionCurrency extends Controller {
	private array $error = [];

	/**
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/extension/currency');

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
		$this->load->language('extension/extension/currency');

		// Extensions
		$this->load->model('setting/extension');

		if ($this->validate()) {
			$this->model_setting_extension->install('currency', $this->request->get['extension']);

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/currency/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/currency/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('extension/currency/' . $this->request->get['extension'] . '/install');

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
		$this->load->language('extension/extension/currency');

		// Extensions
		$this->load->model('setting/extension');

		if ($this->validate()) {
			$this->model_setting_extension->uninstall('currency', $this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('extension/currency/' . $this->request->get['extension'] . '/uninstall');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

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

		$extensions = $this->model_setting_extension->getExtensionsByType('currency');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_APPLICATION . 'controller/extension/currency/' . $value . '.php') && !is_file(DIR_APPLICATION . 'controller/currency/' . $value . '.php')) {
				$this->model_setting_extension->uninstall('currency', $value);

				unset($extensions[$key]);
			}
		}

		$data['extensions'] = [];

		// Compatibility code for old extension folders
		$files = glob(DIR_APPLICATION . 'controller/extension/currency/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->load->language('extension/currency/' . $extension, 'extension');

				$data['extensions'][] = [
					'name'      => $this->language->get('extension')->get('heading_title'),
					'status'    => $this->config->get('currency_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'install'   => $this->url->link('extension/extension/currency/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'uninstall' => $this->url->link('extension/extension/currency/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'installed' => in_array($extension, $extensions),
					'edit'      => $this->url->link('extension/currency/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
				];
			}
		}

		$data['promotion'] = $this->load->controller('extension/extension/promotion');

		$this->response->setOutput($this->load->view('extension/extension/currency', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/extension/currency')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
