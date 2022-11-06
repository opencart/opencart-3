<?php
class ControllerExtensionModuleSubscription extends Controller {
    public function renew(): void {
        $this->load->language('extension/module/subscription');

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            // Extensions
            $this->load->model('setting/extension');

            $filter_data = [
                'filter_subscription_id'        => $this->request->get['subscription_id'],
                'filter_subscription_status_id' => $this->config->get('config_subscription_active_status_id'),
                'filter_date_next'              => date('Y-m-d H:i:s')
            ];

            $this->load->model('account/subscription');

            $results = $this->model_account_subscription->getSubscriptions($filter_data);

            if ($results) {
                $this->load->model('checkout/order');

                foreach ($results as $result) {
                    if ($result['trial_status'] && (!$result['trial_duration'] || $result['trial_remaining'])) {
                        $amount = $result['trial_price'];
                    } elseif (!$result['duration'] || $result['remaining']) {
                        $amount = $result['price'];
                    }

                    $subscription_status_id = $this->config->get('config_subscription_status_id');

                    // Get the payment method used by the subscription
                    $payment_info = $this->model_customer_customer->getPaymentMethod($result['customer_id'], $result['customer_payment_id']);

                    if ($payment_info) {
                        // Check payment status
                        if ($this->config->get('payment_' . $payment_info['code'] . '_status')) {
                            $this->load->model('extension/payment/' . $payment_info['code']);

                            if (property_exists($this->{'model_extension_payment_' . $payment_info['code']}, 'charge')) {
                                $subscription_status_id = $this->{'model_extension_payment_' . $payment_info['code']}->charge($result['customer_id'], $result['customer_payment_id'], $amount);

                                // Transaction
                                if ($this->config->get('config_subscription_active_status_id') == $subscription_status_id) {
                                    $this->model_account_subscription->addTransaction($result['subscription_id'], $this->language->get('text_success'), $amount, $result['order_id']);
                                }
                            } else {
                                // Failed if payment method does not have recurring payment method
                                $subscription_status_id = $this->config->get('config_subscription_failed_status_id');

                                $this->model_checkout_subscription->addHistory($result['subscription_id'], $subscription_status_id, $this->language->get('error_recurring'), true);
                            }
                        } else {
                            $subscription_status_id = $this->config->get('config_subscription_failed_status_id');

                            $this->model_checkout_subscription->addHistory($result['subscription_id'], $subscription_status_id, $this->language->get('error_extension'), true);
                        }
                    } else {
                        $subscription_status_id = $this->config->get('config_subscription_failed_status_id');

                        $this->model_checkout_subscription->addHistory($result['subscription_id'], $subscription_status_id, sprintf($this->language->get('error_payment'), ''), true);
                    }

                    // History
                    if ($result['subscription_status_id'] != $subscription_status_id) {
                        $this->model_checkout_subscription->addHistory($result['subscription_id'], $subscription_status_id, 'payment extension ' . $result['payment_code'] . ' could not be loaded', true);
                    }

                    // Success
                    if ($this->config->get('config_subscription_active_status_id') == $subscription_status_id) {
                        // Trial
                        if ($result['trial_status'] && (!$result['trial_duration'] || $result['trial_remaining'])) {
                            if ($result['trial_duration'] && $result['trial_remaining']) {
                                $this->model_account_subscription->editTrialRemaining($result['subscription_id'], $result['trial_remaining'] - 1);
                            }

                            $this->model_account_subscription->editDateNext($result['subscription_id'], date('Y-m-d', strtotime('+' . $result['trial_cycle'] . ' ' . $result['trial_frequency'])));
                        } elseif (!$result['duration'] || $result['remaining']) {
                            // Subscription
                            if ($result['duration'] && $result['remaining']) {
                                $this->model_account_subscription->editRemaining($result['subscription_id'], $result['remaining'] - 1);
                            }

                            $this->model_account_subscription->editDateNext($result['subscription_id'], date('Y-m-d', strtotime('+' . $result['cycle'] . ' ' . $result['frequency'])));
                        }
                    }
                }
            }
        }

        $json['status'] = $this->language->get('text_status');

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}