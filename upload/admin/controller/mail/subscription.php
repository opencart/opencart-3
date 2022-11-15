<?php
class ControllerMailSubscription extends Controller {
    // admin/controller/sale/subscription/addHistory/after
    public function history(string &$route, array &$args, mixed &$output): void {
        if (isset($args[0])) {
            $subscription_id = $args[0];
        } else {
            $subscription_id = 0;
        }

        if (isset($args[1])) {
            $subscription_status_id = $args[1];
        } else {
            $subscription_status_id = 0;
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

        $this->load->model('sale/subscription');

        $subscription_info = $this->model_checkout_subscription->getSubscription($subscription_id);

        if ($subscription_info) {
            // Subscription Statuses
            $this->load->model('localisation/subscription_status');

            $subscription_status_info = $this->model_localisation_subscription_status->getSubscriptionStatus($subscription_status_id);

            if ($subscription_status_info) {
                // Customers
                $this->load->model('customer/customer');

                $customer_payment_info = $this->model_customer_customer->getPaymentMehod($subscription_info['customer_id'], $subscription_info['customer_payment_id']);

                if ($customer_payment_info) {
                    $customer_info = $this->model_customer_customer->getCustomer($subscription_info['customer_id']);

                    if ($customer_info) {
                        // Settings
                        $this->load->model('setting/setting');

                        $store_info = $this->model_setting_setting->getSetting('config', $customer_info['store_id']);

                        if ($store_info) {
                            $from = $store_info['config_email'];
                            $store_name = $store_info['config_name'];
                            $store_url = $store_info['config_url'];
                            $alert_email = $store_info['config_mail_alert_email'];
                        } else {
                            $from = $this->config->get('config_email');
                            $store_name = $this->config->get('config_name');
                            $store_url = HTTP_CATALOG;
                            $alert_email = $this->config->get('config_mail_alert_email');
                        }

                        // Languages
                        $this->load->model('localisation/language');

                        $language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

                        if ($language_info) {
                            if ($comment && $notify) {
                                $data['comment'] = nl2br($comment);
                            } else {
                                $data['comment'] = '';
                            }

                            $data['subscription_status'] = $subscription_status_info['name'];

                            // Load the language for any mails that might be required to be sent out
                            $language = new \Language($language_info['code']);
                            $language->load($language_info['code']);
                            $language->load('mail/subscription');

                            $data['date_added'] = date($language->get('date_format_short'), $subscription_info['date_added']);

                            $data['text_footer'] = $language->get('text_footer');

                            if ($this->config->get('config_mail_engine')) {
                                $mail = new \Mail($this->config->get('config_mail_engine'));
                                $mail->parameter = $this->config->get('config_mail_parameter');
                                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                                $mail->setTo($customer_info['email']);
                                $mail->setFrom($from);
                                $mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
                                $mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $store_name), ENT_QUOTES, 'UTF-8'));
                                $mail->setText($this->load->view('mail/subscription_history', $data));
                                $mail->send();
                            }
                        }
                    }
                }
            }
        }
    }

    // admin/controller/sale/subscription/addTransaction/after
    public function transaction(string &$route, array &$args, mixed &$output): void {

    }
}