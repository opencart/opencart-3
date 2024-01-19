<?php
/**
 * Class Subscription
 *
 * @package Catalog\Controller\Mail
 */
class ControllerMailSubscription extends Controller {
	/**
	 * @param string $route
	 * @param array  $args
	 * @param mixed  $output
	 *
	 * @return void
	 *
	 * addHistory
	 */
	public function index(string &$route, array &$args, mixed &$output): void {
		if (isset($args[0])) {
			$subscription_id = $args[0];
		} else {
			$subscription_id = 0;
		}

		if (isset($args[1]['subscription'])) {
			$subscription = $args[1]['subscription'];
		} else {
			$subscription = [];
		}

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}

		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}
		/*
		$subscription['order_product_id']
		$subscription['customer_id']
		$subscription['order_id']
		$subscription['subscription_plan_id']
		$subscription['customer_payment_id'],
		$subscription['name']
		$subscription['description']
		$subscription['trial_price']
		$subscription['trial_frequency']
		$subscription['trial_cycle']
		$subscription['trial_duration']
		$subscription['trial_remaining']
		$subscription['trial_status']
		$subscription['price']
		$subscription['frequency']
		$subscription['cycle']
		$subscription['duration']
		$subscription['remaining']
		$subscription['date_next']
		$subscription['status']
		*/
	}

	/**
	 * Cancel
	 *
	 * @param string $route
	 * @param array  $args
	 * @param mixed  $output
	 *
	 * @return void
	 *
	 * catalog/model/checkout/order/editOrder/after
	 */
	public function cancel(string &$route, array &$args, mixed &$output): void {
		if (isset($args[0])) {
			$subscription_id = $args[0];
		} else {
			$subscription_id = 0;
		}

		if (isset($args[1]['subscription'])) {
			$subscription = $args[1]['subscription'];
		} else {
			$subscription = [];
		}

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}

		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}
		/*
		$subscription['order_product_id']
		$subscription['customer_id']
		$subscription['order_id']
		$subscription['subscription_plan_id']
		$subscription['customer_payment_id'],
		$subscription['name']
		$subscription['description']
		$subscription['trial_price']
		$subscription['trial_frequency']
		$subscription['trial_cycle']
		$subscription['trial_duration']
		$subscription['trial_remaining']
		$subscription['trial_status']
		$subscription['price']
		$subscription['frequency']
		$subscription['cycle']
		$subscription['duration']
		$subscription['remaining']
		$subscription['date_next']
		$subscription['status']
		*/

		// Subscriptions
		$this->load->model('account/subscription');

		$subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

		if ($subscription_info) {
			$this->load->language('mail/subscription');

			// Customers
			$this->load->model('account/customer');

			$customer_info = $this->model_account_customer->getCustomer($subscription_info['customer_id']);

			if ($customer_info && $customer_info['status'] && strtotime($subscription_info['date_added']) == strtotime($subscription['date_added']) && strtotime($subscription_info['date_next']) == strtotime($subscription['date_next']) && $customer_info['customer_id'] == $subscription['customer_id'] && $subscription['customer_id'] == $this->customer->getId() && $subscription_info['order_id'] == $subscription['order_id'] && $subscription_info['subscription_plan_id'] == $subscription['subscription_plan_id']) {
				// Only match the latest order ID of the same customer ID
				// since new subscriptions cannot be re-added with the same
				// order ID; only as a new order ID added by an extension

				// Payment Methods
				$this->load->model('account/payment_method');

				$payment_method = $this->model_account_payment_method->getPaymentMethod($subscription_info['customer_id'], $subscription['customer_payment_id']);

				if ($payment_method) {
					// Subscription Date
					$subscription_period = strtotime($subscription_info['date_next']);

					// We need to validate frequencies in compliance of the admin subscription plans
					// as with the use of the APIs
					if ($subscription_info['frequency'] == 'semi_month') {
						$period = strtotime("2 weeks");
					} else {
						$period = strtotime($subscription_info['cycle'] . ' ' . $subscription_info['frequency']);
					}

					// Calculates the remaining days between the subscription
					// promotional period and the date added period
					$period = ($subscription_period - $period);

					// Calculate remaining period of each features
					$cycle = round($period / (60 * 60 * 24));

					// If expired subscription without renewal process,
					// we cancel the subscription
					if ($cycle < 0 && $subscription_info['status'] && $subscription['status']) {
						// Orders
						$this->load->model('account/order');

						$order_info = $this->model_account_order->getOrder($subscription_info['customer_id']);

						if ($order_info) {
							// Cancel
							if ($this->config->get('payment_' . $payment_method['code'] . '_status')) {
								$this->load->model('extension/payment/' . $payment_method['code']);

								if (isset($this->{'model_extension_payment_' . $payment_method['code']}->cancel)) {
									$subscription_status_id = $this->{'model_extension_payment_' . $payment_method['code']}->cancel($subscription_info['subscription_id']);

									if ($subscription_status_id == $this->config->get('config_subscription_canceled_status_id')) {
										$subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

										if ($subscription_info) {
											// Since we send an email based on subscription statuses
											// and not based on promotional products, only subscribed
											// customers can receive the emails; either by automation
											// or on-demand.
											$this->load->language('mail/subscription_alert');

											// HTML Mail
											$data['text_received'] = $this->language->get('text_received');
											$data['text_orders_id'] = $this->language->get('text_orders_id');
											$data['text_subscription_id'] = $this->language->get('text_subscription_id');
											$data['text_date_added'] = $this->language->get('text_date_added');
											$data['text_subscription_status'] = $this->language->get('text_subscription_status');
											$data['text_comment'] = $this->language->get('text_comment');

											$data['order_id'] = $order_info['order_id'];
											$data['subscription_id'] = $subscription_id;

											// Languages
											$this->load->model('localisation/language');

											$language_info = $this->model_localisation_language->getLanguageByCode($this->config->get('config_language'));

											// Subscription Status
											$subscription_status_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription_status` WHERE `subscription_status_id` = '" . (int)$subscription_info['subscription_status_id'] . "' AND `language_id` = '" . (int)$language_info['language_id'] . "'");

											if ($subscription_status_query->num_rows) {
												$data['subscription_status'] = $subscription_status_query->row['name'];
											} else {
												$data['subscription_status'] = '';
											}

											if ($comment && $notify) {
												$data['comment'] = $comment;
											} else {
												$data['comment'] = '';
											}

											$data['date_added'] = date($this->language->get('date_format_short'), strtotime($subscription_info['date_added']));

											// Cancel Status
											$this->model_account_subscription->editStatus($subscription_id, 0);											

											// Mail
											if ($this->config->get('config_mail_engine')) {
												$mail = new \Mail($this->config->get('config_mail_engine'));
												$mail->parameter = $this->config->get('config_mail_parameter');
												$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
												$mail->smtp_username = $this->config->get('config_mail_smtp_username');
												$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
												$mail->smtp_port = $this->config->get('config_mail_smtp_port');
												$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

												$mail->setTo($this->config->get('config_email'));
												$mail->setFrom($this->config->get('config_email'));
												$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
												$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), $this->config->get('config_name'), $order_info['order_id']), ENT_QUOTES, 'UTF-8'));
												$mail->setText($this->load->view('mail/subscription_alert', $data));
												$mail->send();

												// Send to additional alert emails
												$emails = explode(',', $this->config->get('config_mail_alert_email'));

												foreach ($emails as $email) {
													$email = trim($email);

													if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
														$mail->setTo($email);
														$mail->send();
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
