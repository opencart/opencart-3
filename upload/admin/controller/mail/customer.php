<?php
class ControllerMailCustomer extends Controller {
    // admin/model/customer/customer_approval/approveCustomer/after
    public function allow(string &$route, array &$args, mixed &$output): void {
        $this->load->model('customer/customer');

        $customer_info = $this->model_customer_customer->getCustomer($args[0]);

        if ($customer_info) {
            $this->load->model('setting/store');

            $store_info = $this->model_setting_store->getStore($customer_info['store_id']);

            if ($store_info) {
                $this->load->model('setting/setting');

                $store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
                $store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
                $store_url  = $store_info['url'];
            } else {
                $store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
                $store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
                $store_url  = HTTP_CATALOG;
            }

            $this->load->model('localisation/language');

            $language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

            if ($language_info) {
                $language_code = $language_info['code'];
            } else {
                $language_code = $this->config->get('config_language');
            }

            $language = new \Language($language_code);
            $language->load($language_code);
            $language->load('mail/customer_approve');

            $this->load->model('tool/image');

            if (is_file(DIR_IMAGE . $store_logo)) {
                $data['logo'] = $store_url . 'image/' . $store_logo;
            } else {
                $data['logo'] = '';
            }

            $subject = sprintf($language->get('text_subject'), $store_name);

            $data['text_welcome'] = sprintf($language->get('text_welcome'), $store_name);
            $data['login']        = $store_url . 'index.php?route=account/login';
            $data['store']        = $store_name;
            $data['store_url']    = $store_url;

            if ($this->config->get('config_mail_engine')) {
                $mail                = new \Mail($this->config->get('config_mail_engine'));
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
                $mail->setHtml($this->load->view('mail/customer_approve', $data));
                $mail->send();
            }
        }
    }

    // admin/model/customer/customer_approval/denyCustomer/after
    public function deny(string &$route, array &$args, mixed &$output): void {
        $this->load->model('customer/customer');

        $customer_info = $this->model_customer_customer->getCustomer($args[0]);

        if ($customer_info) {
            $this->load->model('setting/store');

            $store_info = $this->model_setting_store->getStore($customer_info['store_id']);

            if ($store_info) {
                $this->load->model('setting/setting');

                $store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
                $store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
                $store_url  = $store_info['url'];
            } else {
                $store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
                $store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
                $store_url  = HTTP_CATALOG;
            }

            $this->load->model('localisation/language');

            $language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

            if ($language_info) {
                $language_code = $language_info['code'];
            } else {
                $language_code = $this->config->get('config_language');
            }

            $language = new \Language($language_code);
            $language->load($language_code);
            $language->load('mail/customer_deny');

            $this->load->model('tool/image');

            if (is_file(DIR_IMAGE . $store_logo)) {
                $data['logo'] = $store_url . 'image/' . $store_logo;
            } else {
                $data['logo'] = '';
            }

            $subject = sprintf($language->get('text_subject'), $store_name);

            $data['text_welcome'] = sprintf($language->get('text_welcome'), $store_name);
            $data['contact']      = $store_url . 'index.php?route=information/contact';
            $data['store']        = $store_name;
            $data['store_url']    = $store_url;

            if ($this->config->get('config_mail_engine')) {
                $mail                = new \Mail($this->config->get('config_mail_engine'));
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
                $mail->setHtml($this->load->view('mail/customer_deny', $data));
                $mail->send();
            }
        }
    }
}