<?php
/**
 * Class Gdpr
 *
 * @package Catalog\Controller\Mail
 */
class ControllerMailGdpr extends Controller {
	/**
	 * Index
	 *
	 * @param string $route
	 * @param array  $args
	 * @param mixed  $output
	 *
	 * @throws \Exception
	 *
	 * catalog/model/account/gdpr/addGdpr
	 *
	 * @return void
	 */
	public function index(string &$route, array &$args, mixed &$output): void {
		// $args[0] $code
		// $args[1] $email
		// $args[2] $action

		$this->load->language('mail/gdpr');

		if ($this->config->get('config_logo')) {
			$data['logo'] = $this->config->get('config_url') . 'image/' . html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
		} else {
			$data['logo'] = '';
		}

		$data['text_request'] = $this->language->get('text_' . $args[2]);
		$data['button_confirm'] = $this->language->get('button_' . $args[2]);
		$data['confirm'] = $this->url->link('information/gdpr/success', 'code=' . $args[0]);
		$data['ip'] = $this->request->server['REMOTE_ADDR'];

		$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
		$data['store_name'] = $store_name;
		$data['store_url'] = $this->config->get('config_url');

		if ($this->config->get('config_mail_engine')) {
			$mail_option = [
				'parameter'     => $this->config->get('config_mail_parameter'),
				'smtp_hostname' => $this->config->get('config_mail_smtp_hostname'),
				'smtp_username' => $this->config->get('config_mail_smtp_username'),
				'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
				'smtp_port'     => $this->config->get('config_mail_smtp_port'),
				'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
			];

			$mail = new \Mail($this->config->get('config_mail_engine'), $mail_option);

			$mail->setTo($args[1]);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject(sprintf($this->language->get('text_subject'), $store_name));
			$mail->setHtml($this->load->view('mail/gdpr', $data));
			$mail->send();
		}
	}

	/**
	 * Remove
	 *
	 * @param string $route
	 * @param array  $args
	 * @param mixed  $output
	 *
	 * @throws \Exception
	 *
	 * catalog/model/account/gdpr/editStatus/after
	 *
	 * @return void
	 */
	public function remove(string &$route, array &$args, mixed &$output): void {
		if (isset($args[0])) {
			$gdpr_id = $args[0];
		} else {
			$gdpr_id = 0;
		}

		if (isset($args[1])) {
			$status = $args[1];
		} else {
			$status = 0;
		}

		// GDPR
		$this->load->model('account/gdpr');

		$gdpr_info = $this->model_account_gdpr->getGdpr($gdpr_id);

		if ($gdpr_info && $gdpr_info['action'] == 'remove' && $status == 3) {
			// Settings
			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($gdpr_info['store_id']);

			if ($store_info) {
				$this->load->model('setting/setting');

				$store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
				$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
				$store_url = $store_info['url'];
			} else {
				$store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
				$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
				$store_url = HTTP_SERVER;
			}

			// Send the email in the correct language
			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($gdpr_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			// Load the language for any mails that might be required to be sent out
			$language = new \Language($language_code);
			$language->load($language_code);
			$language->load('mail/gdpr_delete');

			$subject = sprintf($language->get('text_subject'), $store_name);

			// Images
			$this->load->model('tool/image');

			if ($this->config->get('config_logo')) {
				$data['logo'] = $this->config->get('config_url') . 'image/' . html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
			} else {
				$data['logo'] = '';
			}

			// Customers
			$this->load->model('account/customer');

			$customer_info = $this->model_account_customer->getCustomerByEmail($gdpr_info['email']);

			if ($customer_info) {
				$data['text_hello'] = sprintf($language->get('text_hello'), html_entity_decode($customer_info['firstname'], ENT_QUOTES, 'UTF-8'));
			} else {
				$data['text_hello'] = sprintf($language->get('text_hello'), $this->language->get('mail_text_user'));
			}

			$data['store_name'] = $store_name;
			$data['store_url'] = $store_url;
			$data['contact'] = $store_url . 'index.php?route=information/contact';

			if ($this->config->get('config_mail_engine')) {
				$mail_option = [
					'parameter'     => $this->config->get('config_mail_parameter'),
					'smtp_hostname' => $this->config->get('config_mail_smtp_hostname'),
					'smtp_username' => $this->config->get('config_mail_smtp_username'),
					'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
					'smtp_port'     => $this->config->get('config_mail_smtp_port'),
					'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
				];

				$mail = new \Mail($this->config->get('config_mail_engine'), $mail_option);

				$mail->setTo($gdpr_info['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($store_name);
				$mail->setSubject($subject);
				$mail->setHtml($this->load->view('mail/gdpr_delete', $data));
				$mail->send();
			}
		}
	}
}
