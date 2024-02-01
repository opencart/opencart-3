<?php
/**
 * Class Squareup
 *
 * @package Catalog\Controller\Extension\Payment
 */
class ControllerExtensionPaymentSquareup extends Controller
{
    /**
     * @return string
     */
    public function index(): string
    {
        $this->load->language('extension/payment/squareup');

        // Squareup
        $this->load->library('squareup');

        $data['action'] = $this->url->link('extension/payment/squareup/checkout', '', true);
        $data['squareup_js_api'] = Squareup::PAYMENT_FORM_URL;

        if (!empty($this->session->data['payment_address']['postcode'])) {
            $data['payment_zip'] = $this->session->data['payment_address']['postcode'];
        } else {
            $data['payment_zip'] = '';
        }

        if ($this->config->get('payment_squareup_enable_sandbox')) {
            $data['app_id'] = $this->config->get('payment_squareup_sandbox_client_id');
            $data['sandbox_message'] = $this->language->get('warning_test_mode');
        } else {
            $data['app_id'] = $this->config->get('payment_squareup_client_id');
            $data['sandbox_message'] = '';
        }

        $data['cards'] = [];

        if ($this->customer->isLogged()) {
            $data['is_logged'] = true;

            // Squareup
            $this->load->model('extension/credit_card/squareup');

            $cards = $this->model_extension_credit_card_squareup->getCards($this->customer->getId(), $this->config->get('payment_squareup_enable_sandbox'));

            foreach ($cards as $card) {
                $data['cards'][] = [
                    'id'   => $card['squareup_token_id'],
                    'text' => sprintf($this->language->get('text_card_ends_in'), $card['brand'], $card['ends_in'])
                ];
            }
        } else {
            $data['is_logged'] = false;
        }

        return $this->load->view('extension/payment/squareup', $data);
    }

    /**
     * Checkout
     *
     * @return void
     */
    public function checkout(): void
    {
        if (!isset($this->session->data['order_id'])) {
            return;
        }

        $this->load->language('extension/payment/squareup');

        $json = [];

        // Orders
        $this->load->model('checkout/order');

        // Subscriptions
        $this->load->model('account/subscription');

        // Countries
        $this->load->model('localisation/country');

        // Payment Squareup
        $this->load->model('extension/payment/squareup');

        // Credit Card Squareup
        $this->load->model('extension/credit_card/squareup');

        // Squareup
        $this->load->library('squareup');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $billing_country_info = $this->model_localisation_country->getCountry($order_info['payment_country_id']);

        if (!empty($billing_country_info)) {
            $billing_address = [
                'first_name'     => $order_info['payment_firstname'],
                'last_name'      => $order_info['payment_lastname'],
                'address_line_1' => $order_info['payment_address_1'],
                'address_line_2' => $order_info['payment_address_2'],
                'locality'       => $order_info['payment_city'],
                'sublocality'    => $order_info['payment_zone'],
                'postal_code'    => $order_info['payment_postcode'],
                'country'        => $billing_country_info['iso_code_2'],
                'organization'   => $order_info['payment_company']
            ];
        } else {
            $billing_address = [];
        }

        $shipping_country_info = $this->model_localisation_country->getCountry($order_info['shipping_country_id']);

        if (!empty($shipping_country_info)) {
            $shipping_address = [
                'first_name'     => $order_info['shipping_firstname'],
                'last_name'      => $order_info['shipping_lastname'],
                'address_line_1' => $order_info['shipping_address_1'],
                'address_line_2' => $order_info['shipping_address_2'],
                'locality'       => $order_info['shipping_city'],
                'sublocality'    => $order_info['shipping_zone'],
                'postal_code'    => $order_info['shipping_postcode'],
                'country'        => $shipping_country_info['iso_code_2'],
                'organization'   => $order_info['shipping_company']
            ];
        } else {
            $shipping_address = [];
        }

        try {
            // Ensure we have registered the customer with Square
            $square_customer = $this->model_extension_credit_card_squareup->getCustomer($this->customer->getId(), $this->config->get('payment_squareup_enable_sandbox'));

            if (!$square_customer && $this->customer->isLogged()) {
                $square_customer = $this->squareup->addLoggedInCustomer();

                $this->model_extension_credit_card_squareup->addCustomer($square_customer);
            }

            $use_saved = false;
            $square_card_id = null;

            // check if user is logged in and wanted to save this card
            if ($this->customer->isLogged() && !empty($this->request->post['squareup_select_card'])) {
                $card_verified = $this->model_extension_credit_card_squareup->verifyCardCustomer($this->request->post['squareup_select_card'], $this->customer->getId());

                if (!$card_verified) {
                    throw new \Squareup\Exception($this->registry, $this->language->get('error_card_invalid'));
                }

                $card = $this->model_extension_credit_card_squareup->getCard($this->request->post['squareup_select_card']);
                $use_saved = true;

                $square_card_id = $card['token'];
            } elseif ($this->customer->isLogged() && isset($this->request->post['squareup_save_card'])) {
                // Save the card
                $card_data = [
                    'card_nonce'      => $this->request->post['squareup_nonce'],
                    'billing_address' => $billing_address,
                    'cardholder_name' => $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname']
                ];

                $square_card = $this->squareup->addCard($square_customer['square_customer_id'], $card_data);

                if (!$this->model_extension_credit_card_squareup->cardExists($this->customer->getId(), $square_card)) {
                    $this->model_extension_credit_card_squareup->addCard($this->customer->getId(), $this->config->get('payment_squareup_enable_sandbox'), $square_card);
                }

                $use_saved = true;
                $square_card_id = $square_card['id'];
            }

            // Prepare Transaction
            $transaction_data = [
                'idempotency_key' => uniqid(),
                'amount_money'    => [
                    'amount'   => $this->squareup->lowestDenomination($order_info['total'], $order_info['currency_code']),
                    'currency' => $order_info['currency_code']
                ],
                'billing_address'     => $billing_address,
                'buyer_email_address' => $order_info['email'],
                'delay_capture'       => !$this->cart->hasSubscription() && $this->config->get('payment_squareup_delay_capture'),
                'integration_id'      => Squareup::SQUARE_INTEGRATION_ID
            ];

            if ($shipping_address) {
                $transaction_data['shipping_address'] = $shipping_address;
            }

            if ($use_saved) {
                $transaction_data['customer_card_id'] = $square_card_id;
                $transaction_data['customer_id'] = $square_customer['square_customer_id'];
            } else {
                $transaction_data['card_nonce'] = $this->request->post['squareup_nonce'];
            }

            if (isset($transaction['tenders'][0]['card_details']['status'])) {
                $transaction_status = strtolower($transaction['tenders'][0]['card_details']['status']);
            } else {
                $transaction_status = '';
            }

            $transaction = $this->squareup->addSubscriptionTransaction($transaction_data, $transaction_status);

            if (isset($this->request->server['HTTP_USER_AGENT'])) {
                $user_agent = $this->request->server['HTTP_USER_AGENT'];
            } else {
                $user_agent = '';
            }

            if (isset($this->request->server['REMOTE_ADDR'])) {
                $ip = $this->request->server['REMOTE_ADDR'];
            } else {
                $ip = '';
            }

            if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
                $forwarded_ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
                $forwarded_ip = $this->request->server['HTTP_CLIENT_IP'];
            } else {
                $forwarded_ip = '';
            }

            if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                $accept_language = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
            } else {
                $accept_language = '';
            }

            $this->model_extension_payment_squareup->addTransaction($transaction, $this->config->get('payment_squareup_merchant_id'), $billing_address, $this->session->data['order_id'], $user_agent, $ip);

            $this->load->model('checkout/subscription');

            $order_status_id = $this->config->get('payment_squareup_status_' . $transaction_status);

            $order_products = $this->model_checkout_order->getProducts($this->session->data['order_id']);

            if ($order_status_id) {
                if ($this->cart->hasProducts() && $transaction_status == 'captured') {
                    foreach ($this->cart->getProducts() as $item) {
                        foreach ($order_products as $order_product) {
                            $subscription_info = $this->model_checkout_subscription->getSubscriptionByOrderProductId($this->session->data['order_id'], $order_product['order_product_id']);

                            if ($subscription_info && $order_product['product_id'] == $item['product_id'] && $item['product_id'] == $subscription_info['product_id']) {
                                $item['subscription']['subscription_id'] = $subscription_info['subscription_id'];
                                $item['subscription']['order_id'] = $this->session->data['order_id'];
                                $item['subscription']['order_product_id'] = $order_product['order_product_id'];
                                $item['subscription']['name'] = $item['name'];
                                $item['subscription']['product_id'] = $item['product_id'];
                                $item['subscription']['tax'] = $this->tax->getTax($item['price'], $item['tax_class_id']);
                                $item['subscription']['quantity'] = $item['quantity'];
                                $item['subscription']['store_id'] = $this->config->get('config_store_id');
                                $item['subscription']['customer_id'] = $this->customer->getId();
                                $item['subscription']['payment_address_id'] = $subscription_info['payment_address_id'];
                                $item['subscription']['payment_method'] = $subscription_info['payment_method'];
                                $item['subscription']['shipping_address_id'] = $subscription_info['shipping_address_id'];
                                $item['subscription']['shipping_method'] = $subscription_info['shipping_method'];
                                $item['subscription']['comment'] = $subscription_info['comment'];
                                $item['subscription']['affiliate_id'] = $subscription_info['affiliate_id'];
                                $item['subscription']['marketing_id'] = $subscription_info['marketing_id'];
                                $item['subscription']['tracking'] = $subscription_info['tracking'];
                                $item['subscription']['language_id'] = $this->config->get('config_language_id');
                                $item['subscription']['currency_id'] = $subscription_info['currency_id'];
                                $item['subscription']['ip'] = $ip;
                                $item['subscription']['forwarded_ip'] = $forwarded_ip;
                                $item['subscription']['user_agent'] = $user_agent;
                                $item['subscription']['accept_language'] = $accept_language;

                                $this->model_extension_payment_squareup->subscriptionPayment($item, $this->session->data['order_id']);
                            }
                        }
                    }
                }

                $order_status_comment = $this->language->get('squareup_status_comment_' . $transaction_status);

                $this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id, $order_status_comment, true);
            }

            $json['redirect'] = $this->url->link('checkout/success', '', true);
        } catch (\Squareup\Exception $e) {
            if ($e->isCurlError()) {
                $json['error'] = $this->language->get('text_token_issue_customer_error');
            } elseif ($e->isAccessTokenRevoked()) {
                // Send reminder e-mail to store admin to refresh the token
                $this->model_extension_payment_squareup->tokenRevokedEmail();

                $json['error'] = $this->language->get('text_token_issue_customer_error');
            } elseif ($e->isAccessTokenExpired()) {
                // Send reminder e-mail to store admin to refresh the token
                $this->model_extension_payment_squareup->tokenExpiredEmail();

                $json['error'] = $this->language->get('text_token_issue_customer_error');
            } else {
                $json['error'] = $e->getMessage();
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
