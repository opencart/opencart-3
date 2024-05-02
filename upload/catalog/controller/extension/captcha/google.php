<?php
/**
 * Class Google
 *
 * @package Catalog\Controller\Extension\Captcha
 */
class ControllerExtensionCaptchaGoogle extends Controller {
	/**
	 * Index
	 *
	 * @param array<string, mixed> $error
	 *
	 * @return string
	 */
	public function index(array $error = []): string {
		$this->load->language('extension/captcha/google');

		if (isset($error['captcha'])) {
			$data['error_captcha'] = $error['captcha'];
		} else {
			$data['error_captcha'] = '';
		}

		$data['route'] = $this->request->get['route'];
		$data['site_key'] = $this->config->get('captcha_google_key');

		return $this->load->view('extension/captcha/google', $data);
	}

	/**
	 * Validate
	 *
	 * @return array<string, string>
	 */
	public function validate(): string {
		if (empty($this->session->data['gcaptcha'])) {
			$this->load->language('extension/captcha/google');

			if (!isset($this->request->post['g-recaptcha-response'])) {
				return $this->language->get('error_captcha');
			}

			$recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->config->get('captcha_google_secret')) . '&response=' . $this->request->post['g-recaptcha-response'] . '&remoteip=' . $this->request->server['REMOTE_ADDR']);
			$recaptcha = json_decode($recaptcha, true);

			if ((!isset($recaptcha['success']) || !$recaptcha['success']) || (!isset($this->session->data['gcaptcha'])) || ($this->session->data['gcaptcha'] != $this->request->post['g-recaptcha-response'])) {
				return $this->language->get('error_captcha');
			} else {
				$this->session->data['gcaptcha'] = '';
			}
		}
	}
}
