<?php
class ControllerMailSubscription extends Controller {
    public function index(string &$route, array &$args, &$output): void {
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

        if (isset($args[1]['subscription'])) {
            $subscription = $args[1]['subscription'];
        } else {
            $subscription = [];
        }
        /*
        $subscription['order_product_id']
        $subscription['customer_id']
        $subscription['order_id']
        $subscription['subscription_plan_id']
        $subscription['name']
        $subscription['description']
        $subscription['trial_price']
        $subscription['trial_frequency']
        $subscription['trial_cycle']
        $subscription['trial_duration' ]
        $subscription['trial_remaining' ]
        $subscription['trial_status' ]
        $subscription['price' ]
        $subscription['frequency' ]
        $subscription['cycle' ]
        $subscription['duration' ]
        $subscription['remaining' ]
        $subscription['date_next']
        $subscription['status'	]
        */

        // Subscription
        $this->load->model('account/subscription');

        $subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

        if ($subscription_info) {
            // Orders
            $this->load->model('checkout/order');

            $order_info = $this->model_checkout_order->getOrder($subscription_info['order_id']);

            if ($order_info) {
                // Stores
                $this->load->model('setting/store');

                // Settings
                $this->load->model('setting/setting');

                $store_info = $this->model_setting_store->getStore($order_info['store_id']);

                if ($store_info) {
                    $store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
                    $store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');

                    $store_url = $store_info['url'];
                } else {
                    $store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
                    $store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

                    $store_url = HTTP_SERVER;
                }

                // Subscription Status
                $subscription_status_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription_status` WHERE `subscription_status_id` = '" . (int)$subscription_status_id . "' AND `language_id` = '" . (int)$order_info['language_id'] . "'");

                if ($subscription_status_query->num_rows) {
                    $data['order_status'] = $subscription_status_query->row['name'];
                } else {
                    $data['order_status'] = '';
                }

                // Languages
                $this->load->model('localisation/language');

                $language_info = $this->model_localisation_language->getLanguage($order_info['language_id']);

                // We need to compare both language IDs as they both need to match.
                if ($language_info) {
                    $language_code = $language_info['code'];
                } else {
                    $language_code = $this->config->get('config_language');
                }

                // Load the language for any mails using a different country code and prefixing it, so it does not pollute the main data pool.
                $this->language->load($language_code, 'mail', $language_code);
                $this->language->load('mail/order_add', 'mail', $language_code);

                // Add language vars to the template folder
                $results = $this->language->all('mail');

                foreach ($results as $key => $value) {
                    $data[$key] = $value;
                }

                $subject = sprintf($this->language->get('mail_text_subject'), $store_name, $order_info['order_id']);

                // Image files
                $this->load->model('tool/image');

                if (is_file(DIR_IMAGE . $store_logo)) {
                    $data['logo'] = $store_url . 'image/' . $store_logo;
                } else {
                    $data['logo'] = '';
                }

                // Orders
                $this->load->model('account/order');

                $data['title'] = sprintf($this->language->get('mail_text_subject'), $store_name, $order_info['order_id']);

                $data['text_greeting'] = sprintf($this->language->get('mail_text_greeting'), $order_info['store_name']);

                $data['store'] = $store_name;
                $data['store_url'] = $order_info['store_url'];

                $data['customer_id'] = $order_info['customer_id'];
                $data['link'] = $order_info['store_url'] . 'index.php?route=account/subscription.info&subscription_id=' . $subscription_id;

                $data['order_id'] = $order_info['order_id'];
                $data['date_added'] = date($this->language->get('mail_date_format_short'), strtotime($subscription_info['date_added']));
                $data['payment_method'] = $order_info['payment_method'];
                $data['email'] = $order_info['email'];
                $data['telephone'] = $order_info['telephone'];
                $data['ip'] = $order_info['ip'];

                // Subscription
                if ($comment && $notify) {
                    $data['comment'] = nl2br($comment);
                } else {
                    $data['comment'] = '';
                }

                $data['description'] = $subscription_info['description'];

                // Products
                $order_product = $this->model_account_order->getOrderProduct($subscription_info['order_id'], $subscription_info['order_product_id']);

                $data['name'] = $order_product['name'];
                $data['quantity'] = $order_product['quantity'];

                $data['order'] = $this->url->link('account/order/info', 'customer_token=' . $this->session->data['customer_token'] . '&order_id=' . $subscription_info['order_id']);
                $data['product'] = $this->url->link('product/product', 'customer_token=' . $this->session->data['customer_token'] . '&product_id=' . $subscription_info['product_id']);

                // Settings
                $from = $this->model_setting_setting->getValue('config_email', $order_info['store_id']);

                if (!$from) {
                    $from = $this->config->get('config_email');
                }

                // Mail
                if ($this->config->get('config_mail_engine')) {
                    $mail = new \Mail($this->config->get('config_mail_engine'));
                    $mail->parameter = $this->config->get('config_mail_parameter');
                    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                    $mail->setTo($order_info['email']);
                    $mail->setFrom($from);
                    $mail->setSender($store_name);
                    $mail->setSubject($subject);
                    $mail->setHtml($this->load->view('mail/subscription', $data));
                    $mail->send();
                }
            }
        }
    }
}