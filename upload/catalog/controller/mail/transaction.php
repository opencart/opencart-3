<?php
/**
 * Class Transaction
 *
 * @package Catalog\Controller\Mail
 */
class ControllerMailTransaction extends Controller {
	/**
	 * Index
	 *
	 * @param string               $route
	 * @param array<string, mixed> $args
	 * @param mixed                $output
	 *
	 * @return void
	 *
	 * catalog/model/account/customer/addTransaction/after
	 */
	public function index(string &$route, array &$args, mixed &$output): void {
		$this->load->language('mail/transaction');

		// Customers
		$this->load->model('account/customer');

		$customer_info = $this->model_account_customer->getCustomer($args[0]);

		if ($customer_info) {
			// Stores
			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

			if ($store_info) {
				$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
				$store_url = $store_info['store_url'];
			} else {
				$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
				$store_url = $this->config->get('config_url');
			}

			// Languages
			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			// Load the language for any mails that might be required to be sent out
			$language = new \Language($language_code);
			$language->load($language_code);
			$language->load('mail/transaction');

			$subject = sprintf($language->get('text_subject'), $store_name);

			$data['text_received'] = sprintf($language->get('text_received'), $store_name);
			$data['amount'] = $this->currency->format($args[2], $this->config->get('config_currency'));
			$data['total'] = $this->currency->format($this->model_account_customer->getTransactionTotal($args[0]), $this->config->get('config_currency'));
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
				$mail->setHtml($this->load->view('mail/transaction', $data));
				$mail->send();
			}
		}
	}
}
