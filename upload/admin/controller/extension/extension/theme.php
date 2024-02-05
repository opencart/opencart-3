<?php
/**
 * Class Theme
 *
 * @package Admin\Controller\Extension\Extension
 */
class ControllerExtensionExtensionTheme extends Controller {
	/**
	 * @var array<string, string>
	 */
	private array $error = [];

	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/extension/theme');

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
		$this->load->language('extension/extension/theme');

		// Extensions
		$this->load->model('setting/extension');

		if ($this->validate()) {
			$callable = [$this->{'model_setting_extension'}, 'install'];

			if (is_callable($callable)) {
				$this->model_setting_extension->install('theme', $this->request->get['extension']);
			}

			// User Groups
			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/theme/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/theme/' . $this->request->get['extension']);

			// Call install method if it exists
			$this->load->controller('extension/theme/' . $this->request->get['extension'] . '/install');

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
		$this->load->language('extension/extension/theme');

		// Extensions
		$this->load->model('setting/extension');

		if ($this->validate()) {
			$callable = [$this->{'model_setting_extension'}, 'uninstall'];

			if (is_callable($callable)) {
				$this->model_setting_extension->uninstall('theme', $this->request->get['extension']);
			}

			// Call uninstall method if it exists
			$this->load->controller('extension/theme/' . $this->request->get['extension'] . '/uninstall');

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

		$extensions = $this->model_setting_extension->getExtensionsByType('theme');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_APPLICATION . 'controller/extension/theme/' . $value . '.php') && !is_file(DIR_APPLICATION . 'controller/theme/' . $value . '.php')) {
				$callable = [$this->{'model_setting_extension'}, 'uninstall'];

				if (is_callable($callable)) {
					$this->model_setting_extension->uninstall('theme', $value);
				}

				unset($extensions[$key]);
			}
		}

		// Stores
		$this->load->model('setting/store');

		// Settings
		$this->load->model('setting/setting');

		$stores = $this->model_setting_store->getStores();

		$data['extensions'] = [];

		// Compatibility code for old extension folders
		$files = glob(DIR_APPLICATION . 'controller/extension/theme/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->load->language('extension/theme/' . $extension, 'extension');

				$store_data = [];

				$store_data[] = [
					'name'   => $this->config->get('config_name'),
					'edit'   => $this->url->link('extension/theme/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&store_id=0', true),
					'status' => $this->config->get('theme_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
				];

				foreach ($stores as $store) {
					$store_data[] = [
						'name'   => $store['name'],
						'edit'   => $this->url->link('extension/theme/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store['store_id'], true),
						'status' => $this->model_setting_setting->getSetting('theme_' . $extension . '_status', $store['store_id']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
					];
				}

				$data['extensions'][] = [
					'name'      => $this->language->get('extension')->get('heading_title'),
					'install'   => $this->url->link('extension/extension/theme/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'uninstall' => $this->url->link('extension/extension/theme/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'installed' => in_array($extension, $extensions),
					'store'     => $store_data
				];
			}
		}

		$data['promotion'] = $this->load->controller('extension/extension/promotion');

		$this->response->setOutput($this->load->view('extension/extension/theme', $data));
	}

	/**
	 * Validate
	 *
	 * @return bool
	 */
	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/extension/theme')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
