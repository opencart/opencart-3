<?php
class ControllerExtensionRecurringSquareup extends Controller {
    public function index(): string {
        $this->load->language('extension/recurring/squareup');

		$this->load->model('extension/payment/squareup');
        $this->load->model('account/subscription');		        

        if (isset($this->request->get['subscription_id'])) {
            $subscription_id = (int)$this->request->get['subscription_id'];
        } else {
            $subscription_id = 0;
        }

        $subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

        if ($subscription_info) {
            $data['cancel_url'] = html_entity_decode($this->url->link('extension/recurring/squareup/cancel', 'subscription_id=' . $subscription_id, 'SSL'));

            $data['continue'] = $this->url->link('account/subscription', '', true);    

            if ($subscription_info['status'] == ModelExtensionPaymentSquareup::RECURRING_ACTIVE) {
                $data['subscription_id'] = $subscription_id;
            } else {
                $data['subscription_id'] = '';
            }

            return $this->load->view('extension/recurring/squareup', $data);
        }
    }

    public function cancel(): void {
        $this->load->language('extension/recurring/squareup');
		
		$json = array();

		$this->load->model('extension/payment/squareup');
        $this->load->model('account/subscription');		        

        if (isset($this->request->get['subscription_id'])) {
            $subscription_id = (int)$this->request->get['subscription_id'];
        } else {
            $subscription_id = 0;
        }

        $subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

        if ($subscription_info) {
            $this->model_account_subscription->editStatus($subscription_id, ModelExtensionPaymentSquareup::RECURRING_CANCELLED);

            $this->load->model('checkout/order');

            $order_info = $this->model_checkout_order->getOrder($subscription_info['order_id']);

            $this->model_checkout_order->addOrderHistory($subscription_info['order_id'], $order_info['order_status_id'], $this->language->get('text_order_history_cancel'), true);

            $json['success'] = $this->language->get('text_canceled');
        } else {
            $json['error'] = $this->language->get('error_not_found');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function recurring(): void {
        $this->load->language('extension/payment/squareup');

        $this->load->model('extension/payment/squareup');

        if (!$this->model_extension_payment_squareup->validateCRON()) {
            return;
        }

        $this->load->library('squareup');

        $result = array(
            'transaction_success' 	=> array(),
            'transaction_error' 	=> array(),
            'transaction_fail' 		=> array(),
            'token_update_error' 	=> ''
        );

        $result['token_update_error'] = $this->model_extension_payment_squareup->updateToken();

        $this->load->model('checkout/order');

        foreach ($this->model_extension_payment_squareup->nextRecurringPayments() as $payment) {
            try {
                if (!$payment['is_free']) {
                    $transaction = $this->squareup->addTransaction($payment['transaction']);

                    $transaction_status = !empty($transaction['tenders'][0]['card_details']['status']) ?
                        strtolower($transaction['tenders'][0]['card_details']['status']) : '';

                    $target_currency = $transaction['tenders'][0]['amount_money']['currency'];

                    $amount = $this->squareup->standardDenomination($transaction['tenders'][0]['amount_money']['amount'], $target_currency);

                    $this->model_extension_payment_squareup->addTransaction($transaction, $this->config->get('payment_squareup_merchant_id'), $payment['billing_address'], $payment['order_id'], 'CRON JOB', '127.0.0.1');

                    $reference = $transaction['id'];
                } else {
                    $amount = 0;
                    $target_currency = $this->config->get('config_currency');
                    $reference = '';
                    $transaction_status = 'captured';
                }

                $success = $transaction_status == 'captured';

                $this->model_extension_payment_squareup->addRecurringTransaction($payment['order_recurring_id'], $reference, $amount, $success);

                $trial_expired = false;
                $recurring_expired = false;
                $profile_suspended = false;

                if ($success) {
                    $trial_expired = $this->model_extension_payment_squareup->updateRecurringTrial($payment['order_recurring_id']);

                    $recurring_expired = $this->model_extension_payment_squareup->updateRecurringExpired($payment['order_recurring_id']);

                    $result['transaction_success'][$payment['order_recurring_id']] = $this->currency->format($amount, $target_currency);
                } else {
                    // Transaction was not successful. Suspend the subscription profile.
                    $profile_suspended = $this->model_extension_payment_squareup->suspendRecurringProfile($payment['order_recurring_id']);

                    $result['transaction_fail'][$payment['order_recurring_id']] = $this->currency->format($amount, $target_currency);
                }

                $order_status_id = $this->config->get('payment_squareup_status_' . $transaction_status);

                if ($order_status_id) {
                    if (!$payment['is_free']) {
                        $order_status_comment = $this->language->get('squareup_status_comment_' . $transaction_status);
                    } else {
                        $order_status_comment = '';
                    }

                    if ($profile_suspended) {
                        $order_status_comment .= $this->language->get('text_squareup_profile_suspended');
                    }

                    if ($trial_expired) {
                        $order_status_comment .= $this->language->get('text_squareup_trial_expired');
                    }

                    if ($recurring_expired) {
                        $order_status_comment .= $this->language->get('text_squareup_subscription_expired');
                    }

                    if ($success) {
                        $notify = (bool)$this->config->get('payment_squareup_notify_recurring_success');
                    } else {
                        $notify = (bool)$this->config->get('payment_squareup_notify_recurring_fail');
                    }

                    $this->model_checkout_order->addOrderHistory($payment['order_id'], $order_status_id, trim($order_status_comment), $notify);
                }
            } catch (\Squareup\Exception $e) {
                $result['transaction_error'][] = '[ID: ' . $payment['order_recurring_id'] . '] - ' . $e->getMessage();
            }
        }

        if ($this->config->get('payment_squareup_cron_email_status')) {
            $this->model_extension_payment_squareup->cronEmail($result);
        }
    }
}