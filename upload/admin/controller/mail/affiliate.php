<?php
/**
 * Class Affiliate
 *
 * @package Admin\Controller\Mail
 */
class ControllerMailAffiliate extends Controller {
	/**
	 * Approve
	 *
	 * admin/model/customer/customer_approval/approveAffiliate/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function approve(string &$route, array &$args, mixed &$output): void {
		// Customers
		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomer($args[0]);

		if ($customer_info) {
			// Stores
			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

			if ($store_info) {
				$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
				$store_url = $store_info['url'];
			} else {
				$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
				$store_url = HTTP_CATALOG;
			}

			// Languages
			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			$language = new \Language($language_code);
			$language->load($language_code);
			$language->load('mail/affiliate_approve');

			$subject = sprintf($language->get('text_subject'), $store_name);

			$data['text_welcome'] = sprintf($language->get('text_welcome'), $store_name);
			$data['login'] = $store_url . 'index.php?route=affiliate/login';
			$data['store'] = $store_name;
			$data['store_url'] = $store_url;

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

				$mail->setTo($customer_info['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($store_name);
				$mail->setSubject($subject);
				$mail->setText($this->load->view('mail/affiliate_approve', $data));
				$mail->send();
			}
		}
	}

	/**
	 * Deny
	 *
	 * admin/model/customer/customer_approval/denyAffiliate/after
	 *
	 * @param string $route
	 * @param array  $args
	 * @param mixed  $output
	 *
	 * @return void
	 */
	public function deny(string &$route, array &$args, mixed &$output): void {
		// Customers
		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomer($args[0]);

		if ($customer_info) {
			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

			if ($store_info) {
				$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
				$store_url = $store_info['url'];
			} else {
				$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
				$store_url = HTTP_CATALOG;
			}

			// Languages
			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			$language = new \Language($language_code);
			$language->load($language_code);
			$language->load('mail/affiliate_deny');

			$subject = sprintf($language->get('text_subject'), $store_name);

			$data['text_welcome'] = sprintf($language->get('text_welcome'), $store_name);
			$data['contact'] = $store_url . 'index.php?route=information/contact';
			$data['store'] = $store_name;
			$data['store_url'] = $store_url;

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

				$mail->setTo($customer_info['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($store_name);
				$mail->setSubject($subject);
				$mail->setText($this->load->view('mail/affiliate_deny', $data));
				$mail->send();
			}
		}
	}
}
