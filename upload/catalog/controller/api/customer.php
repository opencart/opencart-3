<?php
class ControllerApiCustomer extends Controller {
    public function index(): void {
        $this->load->language('api/customer');

        // Delete past customer in case there is an error
        unset($this->session->data['customer']);

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            // Add keys for missing post vars
            $keys = [
                'customer_id',
                'customer_group_id',
                'firstname',
                'lastname',
                'email',
                'telephone',
            ];

            foreach ($keys as $key) {
                if (!isset($this->request->post[$key])) {
                    $this->request->post[$key] = '';
                }
            }

            // Customer
            if ($this->request->post['customer_id']) {
                $this->load->model('account/customer');

                $customer_info = $this->model_account_customer->getCustomer($this->request->post['customer_id']);

                if (!$customer_info || !$this->customer->login($customer_info['email'], '', true)) {
                    $json['error']['warning'] = $this->language->get('error_customer');
                }
            }

            if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
                $json['error']['firstname'] = $this->language->get('error_firstname');
            }

            if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
                $json['error']['lastname'] = $this->language->get('error_lastname');
            }

            if ((utf8_strlen($this->request->post['email']) > 96) || (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL))) {
                $json['error']['email'] = $this->language->get('error_email');
            }

            if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
                $json['error']['telephone'] = $this->language->get('error_telephone');
            }

            // Customer Group
            if (in_array($this->request->post['customer_group_id'], (array)$this->config->get('config_customer_group_display'))) {
                $customer_group_id = (int)$this->request->post['customer_group_id'];
            } else {
                $customer_group_id = $this->config->get('config_customer_group_id');
            }

            // Custom field validation
            $this->load->model('account/custom_field');

            $custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

            foreach ($custom_fields as $custom_field) {
                if ($custom_field['location'] == 'account') {
                    if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                        $json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                    } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !preg_match(html_entity_decode($custom_field['validation'], ENT_QUOTES, 'UTF-8'), $this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                        $json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
                    }
                }
            }

            if (!$json) {
                $this->session->data['customer'] = [
                    'customer_id'       => (int)$this->request->post['customer_id'],
                    'customer_group_id' => $customer_group_id,
                    'firstname'         => $this->request->post['firstname'],
                    'lastname'          => $this->request->post['lastname'],
                    'email'             => $this->request->post['email'],
                    'telephone'         => $this->request->post['telephone'],
                    'custom_field'      => isset($this->request->post['custom_field']) ? $this->request->post['custom_field'] : []
                ];

                $json['success'] = $this->language->get('text_success');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function addSubscriptionTransaction() {
        $this->load->language('api/customer');

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function validateSubscriptionTransaction() {
        $this->load->language('api/customer');

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            if (isset($this->session->data['subscription_id'])) {
                $subscription_id = (int)$this->session->data['subscription_id'];
            } else {
                $subscription_id = 0;
            }

            $this->load->model('account/subscription');

            $subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

            if (!$subscription_info) {
                $json['error']['subscription'] = $this->language->get('error_subscription');
            } else {
                if (isset($this->session->data['reference'])) {
                    $reference = $this->session->data['reference'];
                } else {
                    $reference = '';
                }

                if (isset($this->session->data['order_subscription_transaction_id'])) {
                    $order_subscription_transaction_id = (int)$this->session->data['order_subscription_transaction_id'];
                } else {
                    $order_subscription_transaction_id = 0;
                }

                $reference                           = $this->model_account_subscription->getSubscriptionByReference($reference);
                $order_subscription_transaction_info = $this->model_account_subscription->getSubscriptionByOrderTransactionId($order_subscription_transaction_id);

                if (!$reference && !$order_subscription_transaction_info) {
                    $json['error']['transaction']        = $this->language->get('error_transaction');
                } else {
                    if (isset($this->session->data['order_id'])) {
                        $order_id = (int)$this->session->data['order_id'];
                    } else {
                        $order_id = 0;
                    }

                    $this->load->model('checkout/order');

                    $order_info = $this->model_checkout_order->getOrder($order_id);

                    if (!$order_info) {
                        $json['error']['order'] = $this->language->get('error_order');
                    } else {
                        if ((!isset($this->request->post['description'])) || ((utf8_strlen($this->request->post['description']) < 3) || (utf8_strlen($this->request->post['description']) > 255))) {
                            $json['error']['description'] = $this->language->get('error_description');
                        }

                        if (empty($this->session->data['type'])) {
                            $json['error']['type'] = $this->language->get('error_type');
                        }

                        if (empty($this->request->post['amount'])) {
                            $this->request->post['amount'] = 0;
                        }

                        if (empty($this->request->post['payment_method'])) {
                            $json['error']['payment_method'] = $this->language->get('error_payment_method');
                        }

                        if (empty($this->request->post['payment_code'])) {
                            $json['error']['payment_code'] = $this->language->get('error_payment_code');
                        }

                        if (!$json) {
                            $this->model_account_subscription->addTransaction($subscription_id, $order_id, $order_subscription_transaction_id, $this->request->post['description'], $this->request->post['amount'], $this->session->data['type'], $this->request->post['payment_method'], $this->request->post['payment_code']);

                            $json['success'] = true;
                        }
                    }
                }
            }
        }

        unset($this->session->data['subscription_id']);
        unset($this->session->data['reference']);
        unset($this->session->data['order_subscription_transaction_id']);
        unset($this->session->data['type']);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}