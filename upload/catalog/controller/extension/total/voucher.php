<?php
class ControllerExtensionTotalVoucher extends Controller {
	public function index(): string|bool {
		if ($this->config->get('total_voucher_status')) {
			$this->load->language('extension/total/voucher');

			if (isset($this->session->data['voucher'])) {
				$data['voucher'] = $this->session->data['voucher'];
			} else {
				$data['voucher'] = '';
			}

			return $this->load->view('extension/total/voucher', $data);
		} else {
			return false;
		}
	}

	public function voucher(): void {
		$this->load->language('extension/total/voucher');

		$json = array();

		$this->load->model('extension/total/voucher');

		if (isset($this->request->post['voucher'])) {
			$voucher = $this->request->post['voucher'];
		} else {
			$voucher = '';
		}

		$voucher_info = $this->model_extension_total_voucher->getVoucher($voucher);

		if (empty($this->request->post['voucher'])) {
			$json['error'] = $this->language->get('error_empty');
		} elseif ($voucher_info) {
			$this->session->data['voucher'] = $this->request->post['voucher'];

			$this->session->data['success'] = $this->language->get('text_success');

			$json['redirect'] = $this->url->link('checkout/cart');
		} else {
			$json['error'] = $this->language->get('error_voucher');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// catalog/model/checkout/order/addOrderHistory/after
	public function send(string &$route, array &$args, mixed &$output): void {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($args[0]);

		// If order status in the complete range create any vouchers that where in the order need to be made available.
		if (in_array($order_info['order_status_id'], (array)$this->config->get('config_complete_status'))) {
			$voucher_query = $this->db->query("SELECT *, vtd.`name` AS theme FROM `" . DB_PREFIX . "voucher` v LEFT JOIN `" . DB_PREFIX . "voucher_theme` vt ON (v.`voucher_theme_id` = vt.`voucher_theme_id`) LEFT JOIN `" . DB_PREFIX . "voucher_theme_description` vtd ON (vt.`voucher_theme_id` = vtd.`voucher_theme_id`) WHERE v.`order_id` = '" . (int)$order_info['order_id'] . "' AND vtd.`language_id` = '" . (int)$order_info['language_id'] . "'");

			if ($voucher_query->num_rows) {
				// Send out any gift voucher mails
				$language = new \Language($order_info['language_code']);
				$language->load($order_info['language_code']);
				$language->load('mail/voucher');

				foreach ($voucher_query->rows as $voucher) {
					// HTML Mail
					$args['title'] = sprintf($language->get('text_subject'), $voucher['from_name']);

					$args['text_greeting'] = sprintf($language->get('text_greeting'), $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']));
					$args['text_from'] = sprintf($language->get('text_from'), $voucher['from_name']);
					$args['text_message'] = $language->get('text_message');
					$args['text_redeem'] = sprintf($language->get('text_redeem'), $voucher['code']);
					$args['text_footer'] = $language->get('text_footer');

					if (is_file(DIR_IMAGE . $voucher['image'])) {
						$args['image'] = $this->config->get('config_url') . 'image/' . $voucher['image'];
					} else {
						$args['image'] = '';
					}

					$args['store_name'] = $order_info['store_name'];
					$args['store_url'] = $order_info['store_url'];
					$args['message'] = nl2br($voucher['message']);

					if ($this->config->get('config_mail_engine')) {
						$mail = new \Mail($this->config->get('config_mail_engine'));
						$mail->parameter = $this->config->get('config_mail_parameter');
						$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
						$mail->smtp_username = $this->config->get('config_mail_smtp_username');
						$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
						$mail->smtp_port = $this->config->get('config_mail_smtp_port');
						$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

						$mail->setTo($voucher['to_email']);
						$mail->setFrom($this->config->get('config_email'));
						$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
						$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $voucher['from_name']), ENT_QUOTES, 'UTF-8'));
						$mail->setHtml($this->load->view('mail/voucher', $args));
						$mail->send();
					}
				}
			}
		}
	}
}