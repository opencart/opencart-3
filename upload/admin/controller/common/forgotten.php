<?php
/**
 * Class Forgotten
 *
 * @package Admin\Controller\Common
 */
class ControllerCommonForgotten extends Controller {
	private array $error = [];

	/**
	 * @return void
	 */
	public function index(): void {
		if ($this->user->isLogged() && isset($this->request->get['user_token']) && ($this->request->get['user_token'] == $this->session->data['user_token'])) {
			$this->response->redirect($this->url->link('common/dashboard', '', true));
		}

		if (!$this->config->get('config_password')) {
			$this->response->redirect($this->url->link('common/login', '', true));
		}

		$this->load->language('common/forgotten');

		$this->document->setTitle($this->language->get('heading_title'));

		// Users
		$this->load->model('user/user');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_user_user->editCode($this->request->post['email'], oc_token(40));

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('common/login', '', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', '', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('common/forgotten', 'user_token=' . '', true)
		];

		$data['action'] = $this->url->link('common/forgotten', '', true);
		$data['cancel'] = $this->url->link('common/login', '', true);

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('common/forgotten', $data));
	}

	protected function validate() {
		$keys = ['email'];

		foreach ($keys as $key) {
			if (!isset($this->request->post[$key])) {
				$this->request->post[$key] = '';
			}
		}

		if (!$this->model_user_user->getTotalUsersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		}

		return !$this->error;
	}
}
