<?php
class ControllerExtensionSubscriptionPpExpress extends Controller {
    public function index(): string {
        $this->load->language('extension/subscription/pp_express');

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        if (isset($this->request->get['subscription_id'])) {
            $subscription_id = (int)$this->request->get['subscription_id'];
        } else {
            $subscription_id = 0;
        }

        $order_recurring_info = [];

        // Recurring
        if ($order_recurring_id) {
            $this->load->model('account/recurring');

            $order_recurring_info = $this->model_account_recurring->getRecurring($order_recurring_id);
        }

        // Subscription
        if ($subscription_id) {
            $this->load->model('account/subscription');

            $order_recurring_info = $this->model_account_subscription->getSubscription($subscription_id);
        }

        if ($order_recurring_info) {
            if ($order_recurring_id) {
                $data['continue'] = $this->url->link('account/recurring', '', true);
            } elseif ($subscription_id) {
                $data['continue'] = $this->url->link('account/subscription', '', true);
            }

            if ($order_recurring_info['status'] == 2 || $order_recurring_info['status'] == 3) {
                if ($order_recurring_id) {
                    $data['order_recurring_id'] = $order_recurring_id;
                } elseif ($subscription_id) {
                    $data['order_recurring_id'] = $subscription_id;
                }
            } else {
                $data['order_recurring_id'] = '';
            }

            return $this->load->view('extension/subscription/pp_express', $data);
        } else {
            return '';
        }
    }

    public function cancel(): void {
        $this->load->language('extension/subscription/pp_express');

        $json = [];

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        if (isset($this->request->get['subscription_id'])) {
            $subscription_id = (int)$this->request->get['subscription_id'];
        } else {
            $subscription_id = 0;
        }

        $order_recurring_info = [];

        // Recurring
        if ($order_recurring_id) {
            $this->load->model('account/recurring');

            $order_recurring_info = $this->model_account_recurring->getRecurring($order_recurring_id);
        }

        // Subscription
        if ($subscription_id) {
            $this->load->model('account/subscription');

            $order_recurring_info = $this->model_account_subscription->getSubscription($subscription_id);
        }

        if ($order_recurring_info && $order_recurring_info['reference']) {
            // Orders
            $this->load->model('checkout/order');

            $order_info = $this->model_checkout_order->getOrder($order_recurring_info['order_id']);

            if ($order_info) {
                if ($this->config->get('payment_pp_express_test')) {
                    $api_url = 'https://api-3t.sandbox.paypal.com/nvp';
                    $api_username = $this->config->get('payment_pp_express_sandbox_username');
                    $api_password = $this->config->get('payment_pp_express_sandbox_password');
                    $api_signature = $this->config->get('payment_pp_express_sandbox_signature');
                } else {
                    $api_url = 'https://api-3t.paypal.com/nvp';
                    $api_username = $this->config->get('payment_pp_express_username');
                    $api_password = $this->config->get('payment_pp_express_password');
                    $api_signature = $this->config->get('payment_pp_express_signature');
                }

                $request = [
                    'USER'         => $api_username,
                    'PWD'          => $api_password,
                    'SIGNATURE'    => $api_signature,
                    'VERSION'      => '109.0',
                    'BUTTONSOURCE' => 'OpenCart_3.0_EC',
                    'METHOD'       => 'ManageRecurringPaymentsProfileStatus',
                    'PROFILEID'    => $order_recurring_info['reference'],
                    'ACTION'       => 'Cancel'
                ];

                $curl = curl_init($api_url);

                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                $response = curl_exec($curl);

                if (!$response) {
                    $this->log(sprintf($this->language->get('error_curl'), curl_errno($curl), curl_error($curl)));
                }

                curl_close($curl);

                $response_info = [];

                parse_str($response, $response_info);

                if (isset($response_info['PROFILEID'])) {
                    // Notify the customer
                    $this->model_checkout_order->addOrderHistory($order_recurring_info['order_id'], $order_info['order_status_id'], $this->language->get('text_order_history_cancel'), true);

                    if ($order_recurring_id) {
                        $this->model_account_recurring->editStatus($order_recurring_id, 4);
                    } elseif ($subscription_id) {
                        $this->model_account_subscription->editStatus($subscription_id, 4);
                    }

                    $json['success'] = $this->language->get('text_cancelled');
                } else {
                    $json['error'] = sprintf($this->language->get('error_not_cancelled'), $response_info['L_LONGMESSAGE0']);
                }
            } else {
                $json['error'] = $this->language->get('error_no_order');
            }
        } else {
            $json['error'] = $this->language->get('error_not_found');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}