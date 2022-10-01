<?php
class ControllerMailReward extends Controller {
    // admin/model/customer/customer/addReward/after
    public function deny(string &$route, array &$args, mixed &$output): void {
        if (isset($args[0])) {
            $customer_id = $args[0];
        } else {
            $customer_id = 0;
        }

        if (isset($args[1])) {
            $description = $args[1];
        } else {
            $description = '';
        }

        if (isset($args[2])) {
            $points = $args[2];
        } else {
            $points = 0;
        }

        if (isset($args[3])) {
            $order_id = $args[3];
        } else {
            $order_id = 0;
        }

        // Customer
        $this->load->model('customer/customer');

        $customer_info = $this->model_customer_customer->getCustomer($customer_id);

        if ($customer_info) {
            $this->load->language('mail/reward');

            // Store
            $this->load->model('setting/store');

            $store_info = $this->model_setting_store->getStore($customer_info['store_id']);

            if ($store_info) {
                $store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
                $store_url  = $store_info['url'];
            } else {
                $store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
                $store_url  = HTTP_CATALOG;
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
            $language->load('mail/reward');

            $subject = sprintf($language->get('text_subject'), $store_name);

            $data['text_received'] = sprintf($language->get('text_received'), $points);
            $data['text_total']    = sprintf($language->get('text_total'), $this->model_customer_customer->getRewardTotal($customer_id));
            $data['store']         = $store_name;
            $data['store_url']     = $store_url;

            if ($this->config->get('config_mail_engine')) {
                $mail                = new \Mail($this->config->get('config_mail_engine'));
                $mail->protocol      = $this->config->get('config_mail_protocol');
                $mail->parameter     = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port     = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($customer_info['email']);
                $mail->setFrom($this->config->get('config_email'));
                $mail->setSender($store_name);
                $mail->setSubject($subject);
                $mail->setHtml($this->load->view('mail/reward', $data));
                $mail->send();
            }
        }
    }
}