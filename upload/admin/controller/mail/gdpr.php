<?php
/**
 * Class Gdpr
 *
 * @package Admin\Controller\Mail
 */
class ControllerMailGdpr extends Controller {
	/**
	 * Index
	 *
	 * admin/model/customer/gdpr/editStatus
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function index(string &$route, array &$args, mixed &$output): void {
		$this->load->model('customer/gdpr');

		$gdpr_info = $this->model_customer_gdpr->getGdpr($args[0]);

		if ($gdpr_info) {
			// Choose which mail to send

			// Export plus complete
			if ($gdpr_info['action'] == 'export' && (int)$args[1] == 3) {
				$this->export($gdpr_info);
			}

			// Approve plus processing
			if ($gdpr_info['action'] == 'approve' && (int)$args[1] == 2) {
				$this->approve($gdpr_info);
			}

			// Remove plus complete
			if ($gdpr_info['action'] == 'remove' && (int)$args[1] == 3) {
				$this->remove($gdpr_info);
			}

			// Deny
			if ($args[1] == -1) {
				$this->deny($gdpr_info);
			}
		}
	}

	/**
	 * Export
	 *
	 * @param array $gdpr_info
	 *
	 * @return void
	 */
	public function export(array $gdpr_info): void {
		$this->load->language('mail/gdpr_export');

		// Stores
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
			$store_url = HTTP_CATALOG;
		}

		$subject = sprintf($this->language->get('text_subject'), $store_name);

		if (is_file(DIR_IMAGE . $store_logo)) {
			$data['logo'] = $store_url . 'image/' . $store_logo;
		} else {
			$data['logo'] = '';
		}

		// Customers
		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomerByEmail($gdpr_info['email']);

		if ($customer_info) {
			$data['text_hello'] = sprintf($this->language->get('text_hello'), html_entity_decode($customer_info['firstname'], ENT_QUOTES, 'UTF-8'));
		} else {
			$data['text_hello'] = sprintf($this->language->get('text_hello'), $this->language->get('text_user'));
		}

		// Personal info
		if ($customer_info) {
			$data['customer_id'] = $customer_info['customer_id'];
			$data['firstname'] = $customer_info['firstname'];
			$data['lastname'] = $customer_info['lastname'];
			$data['email'] = $customer_info['email'];
			$data['telephone'] = $customer_info['telephone'];
		}

		// Addresses
		$data['addresses'] = [];

		if ($customer_info) {
			$results = $this->model_customer_customer->getAddresses($customer_info['customer_id']);

			foreach ($results as $result) {
				$address = [
					'firstname' => $result['firstname'],
					'lastname'  => $result['lastname'],
					'address_1' => $result['address_1'],
					'address_2' => $result['address_2'],
					'city'      => $result['city'],
					'postcode'  => $result['postcode'],
					'country'   => $result['country'],
					'zone'      => $result['zone']
				];

				if (!in_array($address, $data['addresses'])) {
					$data['addresses'][] = $address;
				}
			}
		}

		// Order Addresses
		$this->load->model('sale/order');

		$results = $this->model_sale_order->getOrders(['filter_email' => $gdpr_info['email']]);

		foreach ($results as $result) {
			$order_info = $this->model_sale_order->getOrder($result['order_id']);

			$address = [
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'country'   => $order_info['payment_country'],
				'zone'      => $order_info['payment_zone']
			];

			if (!in_array($address, $data['addresses'])) {
				$data['addresses'][] = $address;
			}

			$address = [
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'country'   => $order_info['shipping_country'],
				'zone'      => $order_info['shipping_zone']
			];

			if (!in_array($address, $data['addresses'])) {
				$data['addresses'][] = $address;
			}
		}

		// Ip's
		$data['ips'] = [];

		if ($customer_info) {
			$results = $this->model_customer_customer->getIps($customer_info['customer_id']);

			foreach ($results as $result) {
				$data['ips'][] = [
					'ip'         => $result['ip'],
					'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added']))
				];
			}
		}

		$data['store_name'] = $store_name;
		$data['store_url'] = $store_url;

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
		$mail->setHtml($this->load->view('mail/gdpr_export', $data));
		$mail->send();
	}

	/**
	 * Approve
	 *
	 * @param array $gdpr_info
	 *
	 * @return void
	 */
	public function approve(array $gdpr_info): void {
		$this->load->model('setting/store');

		$store_info = $this->model_setting_store->getStore($gdpr_info['store_id']);

		if ($store_info) {
			$this->load->model('setting/setting');
			$this->load->language('mail/gdpr_aprove');

			$store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
			$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
			$store_url = $store_info['url'];
		} else {
			$store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			$store_url = HTTP_CATALOG;
		}

		$subject = sprintf($this->language->get('text_subject'), $store_name);

		// Images
		$this->load->model('tool/image');

		if (is_file(DIR_IMAGE . $store_logo)) {
			$data['logo'] = $store_url . 'image/' . $store_logo;
		} else {
			$data['logo'] = '';
		}

		// Customers
		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomerByEmail($gdpr_info['email']);

		if ($customer_info) {
			$data['text_hello'] = sprintf($this->language->get('text_hello'), html_entity_decode($customer_info['firstname'], ENT_QUOTES, 'UTF-8'));
		} else {
			$data['text_hello'] = sprintf($this->language->get('text_hello'), $this->language->get('text_user'));
		}

		$data['text_gdpr'] = sprintf($this->language->get('text_gdpr'), $this->config->get('config_gdpr_limit'));
		$data['text_a'] = sprintf($this->language->get('text_a'), $this->config->get('config_gdpr_limit'));

		$data['store_name'] = $store_name;
		$data['store_url'] = $store_url;

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
		$mail->setHtml($this->load->view('mail/gdpr_approve', $data));
		$mail->send();
	}

	/**
	 * Deny
	 *
	 * @param array $gdpr_info
	 *
	 * @return void
	 */
	public function deny(array $gdpr_info): void {
		$this->load->language('mail/gdpr_deny');

		// Stores
		$this->load->model('setting/store');

		$store_info = $this->model_setting_store->getStore($gdpr_info['store_id']);

		if ($store_info) {
			// Settings
			$this->load->model('setting/setting');

			$store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
			$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
			$store_url = $store_info['url'];
		} else {
			$store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			$store_url = HTTP_CATALOG;
		}

		$subject = sprintf($this->language->get('text_subject'), $store_name);

		// Images
		$this->load->model('tool/image');

		if (is_file(DIR_IMAGE . $store_logo)) {
			$data['logo'] = $store_url . 'image/' . $store_logo;
		} else {
			$data['logo'] = '';
		}

		$data['text_request'] = $this->language->get('text_' . $gdpr_info['action']);

		// Customers
		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomerByEmail($gdpr_info['email']);

		if ($customer_info) {
			$data['text_hello'] = sprintf($this->language->get('text_hello'), html_entity_decode($customer_info['firstname'], ENT_QUOTES, 'UTF-8'));
		} else {
			$data['text_hello'] = sprintf($this->language->get('text_hello'), $this->language->get('text_user'));
		}

		$data['store_name'] = $store_name;
		$data['store_url'] = $store_url;
		$data['contact'] = $store_url . 'index.php?route=information/contact';

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
		$mail->setHtml($this->load->view('mail/gdpr_deny', $data));
		$mail->send();
	}

	/**
	 * Remove
	 *
	 * @param array $gdpr_info
	 *
	 * @return void
	 */
	public function remove(array $gdpr_info): void {
		$this->load->language('mail/gdpr_delete');

		// Stores
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
			$store_url = HTTP_CATALOG;
		}

		$subject = sprintf($this->language->get('text_subject'), $store_name);

		// Images
		$this->load->model('tool/image');

		if (is_file(DIR_IMAGE . $store_logo)) {
			$data['logo'] = $store_url . 'image/' . $store_logo;
		} else {
			$data['logo'] = '';
		}

		// Customers
		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomerByEmail($gdpr_info['email']);

		if ($customer_info) {
			$data['text_hello'] = sprintf($this->language->get('text_hello'), html_entity_decode($customer_info['firstname'], ENT_QUOTES, 'UTF-8'));
		} else {
			$data['text_hello'] = sprintf($this->language->get('text_hello'), $this->language->get('text_user'));
		}

		$data['store_name'] = $store_name;
		$data['store_url'] = $store_url;
		$data['contact'] = $store_url . 'index.php?route=information/contact';

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
