<?php
/**
 * Class PayPal
 *
 * @package Catalog\Controller\Extension\Payment
 */
class ControllerExtensionPaymentPayPal extends Controller
{
    /**
     * @var array
     */
    private array $error = [];

    /**
     * @return string
     */
    public function index(): string
    {
        $this->load->model('extension/payment/paypal');

        $agree_status = $this->model_extension_payment_paypal->getAgreeStatus();

        if ($this->config->get('payment_paypal_status') && $this->config->get('payment_paypal_client_id') && $this->config->get('payment_paypal_secret') && !$this->webhook() && !$this->cron() && $agree_status) {
            $this->load->language('extension/payment/paypal');

            $_config = new \Config();
            $_config->load('paypal');

            $config_setting = $_config->get('paypal_setting');

            $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

            $data['client_id'] = $this->config->get('payment_paypal_client_id');
            $data['secret'] = $this->config->get('payment_paypal_secret');
            $data['merchant_id'] = $this->config->get('payment_paypal_merchant_id');
            $data['environment'] = $this->config->get('payment_paypal_environment');
            $data['partner_id'] = $setting['partner'][$data['environment']]['partner_id'];
            $data['partner_attribution_id'] = $setting['partner'][$data['environment']]['partner_attribution_id'];
            $data['checkout_mode'] = $setting['general']['checkout_mode'];
            $data['transaction_method'] = $setting['general']['transaction_method'];

            $data['button_status'] = $setting['button']['checkout']['status'];
            $data['googlepay_button_status'] = $setting['googlepay_button']['status'];
            $data['applepay_button_status'] = $setting['applepay_button']['status'];
            $data['card_status'] = $setting['card']['status'];

            require_once DIR_SYSTEM . 'library/paypal/paypal.php';

            $paypal_info = [
                'partner_id'             => $data['partner_id'],
                'client_id'              => $data['client_id'],
                'secret'                 => $data['secret'],
                'environment'            => $data['environment'],
                'partner_attribution_id' => $data['partner_attribution_id']
            ];

            $paypal = new PayPal($paypal_info);

            $token_info = [
                'grant_type' => 'client_credentials'
            ];

            $paypal->setAccessToken($token_info);

            $data['client_token'] = $paypal->getClientToken();

            if ($paypal->hasErrors()) {
                $error_messages = [];

                $errors = $paypal->getErrors();

                foreach ($errors as $error) {
                    if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                        $error['message'] = $this->language->get('error_timeout');
                    }

                    if (isset($error['details'][0]['description'])) {
                        $error_messages[] = $error['details'][0]['description'];
                    } elseif (isset($error['message'])) {
                        $error_messages[] = $error['message'];
                    }

                    $this->model_extension_payment_paypal->log($error, $error['message']);
                }

                $this->error['warning'] = implode(' ', $error_messages);
            }

            if (!empty($this->error['warning'])) {
                $this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
            }

            return $this->load->view('extension/payment/paypal/paypal', $data);
        }

        return '';
    }

    /**
     * Modal
     *
     * @return void
     */
    public function modal(): void
    {
        $this->load->language('extension/payment/paypal');

        $_config = new \Config();
        $_config->load('paypal');

        $config_setting = $_config->get('paypal_setting');

        $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

        $data['client_id'] = $this->config->get('payment_paypal_client_id');
        $data['secret'] = $this->config->get('payment_paypal_secret');
        $data['merchant_id'] = $this->config->get('payment_paypal_merchant_id');
        $data['environment'] = $this->config->get('payment_paypal_environment');
        $data['partner_id'] = $setting['partner'][$data['environment']]['partner_id'];
        $data['partner_attribution_id'] = $setting['partner'][$data['environment']]['partner_attribution_id'];
        $data['transaction_method'] = $setting['general']['transaction_method'];

        $data['button_status'] = $setting['button']['checkout']['status'];
        $data['googlepay_button_status'] = $setting['googlepay_button']['status'];
        $data['applepay_button_status'] = $setting['applepay_button']['status'];
        $data['card_status'] = $setting['card']['status'];

        require_once DIR_SYSTEM . 'library/paypal/paypal.php';

        $paypal_info = [
            'partner_id'             => $data['partner_id'],
            'client_id'              => $data['client_id'],
            'secret'                 => $data['secret'],
            'environment'            => $data['environment'],
            'partner_attribution_id' => $data['partner_attribution_id']
        ];

        $paypal = new PayPal($paypal_info);

        $token_info = [
            'grant_type' => 'client_credentials'
        ];

        $paypal->setAccessToken($token_info);

        $data['client_token'] = $paypal->getClientToken();

        if ($paypal->hasErrors()) {
            $error_messages = [];

            $errors = $paypal->getErrors();

            foreach ($errors as $error) {
                if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                    $error['message'] = $this->language->get('error_timeout');
                }

                if (isset($error['details'][0]['description'])) {
                    $error_messages[] = $error['details'][0]['description'];
                } elseif (isset($error['message'])) {
                    $error_messages[] = $error['message'];
                }

                $this->model_extension_payment_paypal->log($error, $error['message']);
            }

            $this->error['warning'] = implode(' ', $error_messages);
        }

        if (!empty($this->error['warning'])) {
            $this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
        }

        $data['error'] = $this->error;

        $this->response->setOutput($this->load->view('extension/payment/paypal/paypal_modal', $data));
    }

    /**
     * Get Data
     *
     * @return void
     */
    public function getData(): void
    {
        $this->load->model('extension/payment/paypal');

        $json = [];

        $agree_status = $this->model_extension_payment_paypal->getAgreeStatus();

        if ($this->config->get('payment_paypal_status') && $this->config->get('payment_paypal_client_id') && $this->config->get('payment_paypal_secret') && $agree_status && !empty($this->request->post['page_code'])) {
            $this->load->language('extension/payment/paypal');

            $this->load->model('localisation/country');
            $this->load->model('checkout/order');

            $_config = new \Config();
            $_config->load('paypal');

            $config_setting = $_config->get('paypal_setting');

            $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

            $json['page_code'] = $this->request->post['page_code'];
            $json['client_id'] = $this->config->get('payment_paypal_client_id');
            $json['secret'] = $this->config->get('payment_paypal_secret');
            $json['merchant_id'] = $this->config->get('payment_paypal_merchant_id');
            $json['environment'] = $this->config->get('payment_paypal_environment');
            $json['partner_id'] = $setting['partner'][$json['environment']]['partner_id'];
            $json['partner_attribution_id'] = $setting['partner'][$json['environment']]['partner_attribution_id'];
            $json['transaction_method'] = $setting['general']['transaction_method'];

            $country = $this->model_extension_payment_paypal->getCountryByCode($setting['general']['country_code']);

            $json['locale'] = preg_replace('/-(.+?)+/', '', $this->config->get('config_language')) . '_' . $country['iso_code_2'];

            $json['currency_code'] = $this->session->data['currency'];
            $json['currency_value'] = $this->currency->getValue($this->session->data['currency']);

            if (empty($setting['currency'][$json['currency_code']]['status'])) {
                $json['currency_code'] = $setting['general']['currency_code'];
                $json['currency_value'] = $setting['general']['currency_value'];
            }

            $json['decimal_place'] = $setting['currency'][$json['currency_code']]['decimal_place'];

            $json['components'] = [];

            if ($this->request->post['page_code'] == 'home') {
                if ($setting['message']['home']['status'] && ($json['currency_code'] == $setting['general']['currency_code'])) {
                    $json['components'][] = 'messages';
                    $json['message_status'] = $setting['message']['home']['status'];
                    $json['message_insert_tag'] = html_entity_decode($setting['message']['home']['insert_tag']);
                    $json['message_insert_type'] = $setting['message']['home']['insert_type'];
                    $json['message_align'] = $setting['message']['home']['align'];
                    $json['message_size'] = $setting['message']['home']['size'];
                    $json['message_width'] = $setting['message_width'][$json['message_size']];
                    $json['message_layout'] = $setting['message']['home']['layout'];
                    $json['message_text_color'] = $setting['message']['home']['text_color'];
                    $json['message_text_size'] = $setting['message']['home']['text_size'];
                    $json['message_flex_color'] = $setting['message']['home']['flex_color'];
                    $json['message_flex_ratio'] = $setting['message']['home']['flex_ratio'];

                    $item_total = 0;

                    foreach ($this->cart->getProducts() as $product) {
                        $product_price = $this->tax->calculate($product['price'], $product['tax_class_id'], true);

                        $item_total += $product_price * $product['quantity'];
                    }

                    if (!empty($this->session->data['vouchers'])) {
                        foreach ($this->session->data['vouchers'] as $voucher) {
                            $item_total += $voucher['amount'];
                        }
                    }

                    $json['message_amount'] = number_format($item_total * $json['currency_value'], $json['decimal_place'], '.', '');
                }
            }

            if (($this->request->post['page_code'] == 'product') && !empty($this->request->post['product_id'])) {
                if ($setting['button']['product']['status']) {
                    $json['components'][] = 'buttons';
                    $json['button_status'] = $setting['button']['product']['status'];
                    $json['button_insert_tag'] = html_entity_decode($setting['button']['product']['insert_tag']);
                    $json['button_insert_type'] = $setting['button']['product']['insert_type'];
                    $json['button_align'] = $setting['button']['product']['align'];
                    $json['button_size'] = $setting['button']['product']['size'];
                    $json['button_width'] = $setting['button_width'][$json['button_size']];
                    $json['button_color'] = $setting['button']['product']['color'];
                    $json['button_shape'] = $setting['button']['product']['shape'];
                    $json['button_label'] = $setting['button']['product']['label'];
                    $json['button_tagline'] = $setting['button']['product']['tagline'];
                }

                if ($setting['message']['product']['status'] && ($json['currency_code'] == $setting['general']['currency_code'])) {
                    $json['components'][] = 'messages';
                    $json['message_status'] = $setting['message']['product']['status'];
                    $json['message_insert_tag'] = html_entity_decode($setting['message']['product']['insert_tag']);
                    $json['message_insert_type'] = $setting['message']['product']['insert_type'];
                    $json['message_align'] = $setting['message']['product']['align'];
                    $json['message_size'] = $setting['message']['product']['size'];
                    $json['message_width'] = $setting['message_width'][$json['message_size']];
                    $json['message_layout'] = $setting['message']['product']['layout'];
                    $json['message_text_color'] = $setting['message']['product']['text_color'];
                    $json['message_text_size'] = $setting['message']['product']['text_size'];
                    $json['message_flex_color'] = $setting['message']['product']['flex_color'];
                    $json['message_flex_ratio'] = $setting['message']['product']['flex_ratio'];

                    $product_id = (int)$this->request->post['product_id'];

                    $this->load->model('catalog/product');

                    $product_info = $this->model_catalog_product->getProduct($product_id);

                    if ($product_info) {
                        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                            if ((float)$product_info['special']) {
                                $product_price = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], true);
                            } else {
                                $product_price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], true);
                            }

                            $json['message_amount'] = number_format($product_price * $json['currency_value'], $json['decimal_place'], '.', '');
                        }
                    }
                }
            }

            if (($this->request->post['page_code'] == 'cart') && ($this->cart->hasProducts() || !empty($this->session->data['vouchers']))) {
                if ($setting['button']['cart']['status']) {
                    $json['components'][] = 'buttons';
                    $json['button_status'] = $setting['button']['cart']['status'];
                    $json['button_insert_tag'] = html_entity_decode($setting['button']['cart']['insert_tag']);
                    $json['button_insert_type'] = $setting['button']['cart']['insert_type'];
                    $json['button_align'] = $setting['button']['cart']['align'];
                    $json['button_size'] = $setting['button']['cart']['size'];
                    $json['button_width'] = $setting['button_width'][$json['button_size']];
                    $json['button_color'] = $setting['button']['cart']['color'];
                    $json['button_shape'] = $setting['button']['cart']['shape'];
                    $json['button_label'] = $setting['button']['cart']['label'];
                    $json['button_tagline'] = $setting['button']['cart']['tagline'];
                }

                if ($setting['message']['cart']['status'] && ($json['currency_code'] == $setting['general']['currency_code'])) {
                    $json['components'][] = 'messages';
                    $json['message_status'] = $setting['message']['cart']['status'];
                    $json['message_insert_tag'] = html_entity_decode($setting['message']['cart']['insert_tag']);
                    $json['message_insert_type'] = $setting['message']['cart']['insert_type'];
                    $json['message_align'] = $setting['message']['cart']['align'];
                    $json['message_size'] = $setting['message']['cart']['size'];
                    $json['message_width'] = $setting['message_width'][$json['message_size']];
                    $json['message_layout'] = $setting['message']['cart']['layout'];
                    $json['message_text_color'] = $setting['message']['cart']['text_color'];
                    $json['message_text_size'] = $setting['message']['cart']['text_size'];
                    $json['message_flex_color'] = $setting['message']['cart']['flex_color'];
                    $json['message_flex_ratio'] = $setting['message']['cart']['flex_ratio'];

                    $item_total = 0;

                    foreach ($this->cart->getProducts() as $product) {
                        $product_price = $this->tax->calculate($product['price'], $product['tax_class_id'], true);

                        $item_total += $product_price * $product['quantity'];
                    }

                    if (!empty($this->session->data['vouchers'])) {
                        foreach ($this->session->data['vouchers'] as $voucher) {
                            $item_total += $voucher['amount'];
                        }
                    }

                    $json['message_amount'] = number_format($item_total * $json['currency_value'], $json['decimal_place'], '.', '');
                }
            }

            if (($this->request->post['page_code'] == 'checkout') && ($this->cart->hasProducts() || !empty($this->session->data['vouchers']))) {
                if (!empty($this->session->data['order_id'])) {
                    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                }

                if ($setting['button']['checkout']['status']) {
                    $json['components'][] = 'buttons';
                    $json['components'][] = 'funding-eligibility';
                    $json['button_status'] = $setting['button']['checkout']['status'];
                    $json['button_align'] = $setting['button']['checkout']['align'];
                    $json['button_size'] = $setting['button']['checkout']['size'];
                    $json['button_width'] = $setting['button_width'][$json['button_size']];
                    $json['button_color'] = $setting['button']['checkout']['color'];
                    $json['button_shape'] = $setting['button']['checkout']['shape'];
                    $json['button_label'] = $setting['button']['checkout']['label'];

                    $json['button_enable_funding'] = [];
                    $json['button_disable_funding'] = [];

                    foreach ($setting['button_funding'] as $button_funding) {
                        if ($setting['button']['checkout']['funding'][$button_funding['code']] == 1) {
                            $json['button_enable_funding'][] = $button_funding['code'];
                        }

                        if ($setting['button']['checkout']['funding'][$button_funding['code']] == 2) {
                            $json['button_disable_funding'][] = $button_funding['code'];
                        }
                    }

                    if (isset($this->session->data['payment_method']['code']) && ($this->session->data['payment_method']['code'] == 'paypal_paylater')) {
                        $json['button_funding_source'] = 'paylater';
                    }
                }

                if ($setting['googlepay_button']['status']) {
                    $json['components'][] = 'googlepay';
                    $json['googlepay_button_status'] = $setting['googlepay_button']['status'];
                    $json['googlepay_button_align'] = $setting['googlepay_button']['align'];
                    $json['googlepay_button_size'] = $setting['googlepay_button']['size'];
                    $json['googlepay_button_width'] = $setting['googlepay_button_width'][$json['googlepay_button_size']];
                    $json['googlepay_button_color'] = $setting['googlepay_button']['color'];
                    $json['googlepay_button_shape'] = $setting['googlepay_button']['shape'];
                    $json['googlepay_button_type'] = $setting['googlepay_button']['type'];

                    if (!empty($order_info)) {
                        $json['googlepay_amount'] = number_format($order_info['total'] * $json['currency_value'], $json['decimal_place'], '.', '');
                    } else {
                        $item_total = 0;

                        foreach ($this->cart->getProducts() as $product) {
                            $product_price = $this->tax->calculate($product['price'], $product['tax_class_id'], true);

                            $item_total += $product_price * $product['quantity'];
                        }

                        if (!empty($this->session->data['vouchers'])) {
                            foreach ($this->session->data['vouchers'] as $voucher) {
                                $item_total += $voucher['amount'];
                            }
                        }

                        $json['googlepay_amount'] = number_format($item_total * $json['currency_value'], $json['decimal_place'], '.', '');
                    }
                }

                if ($setting['applepay_button']['status']) {
                    $json['components'][] = 'applepay';
                    $json['applepay_button_status'] = $setting['applepay_button']['status'];
                    $json['applepay_button_align'] = $setting['applepay_button']['align'];
                    $json['applepay_button_size'] = $setting['applepay_button']['size'];
                    $json['applepay_button_width'] = $setting['applepay_button_width'][$json['applepay_button_size']];
                    $json['applepay_button_color'] = $setting['applepay_button']['color'];
                    $json['applepay_button_shape'] = $setting['applepay_button']['shape'];
                    $json['applepay_button_type'] = $setting['applepay_button']['type'];

                    if (!empty($order_info)) {
                        $json['applepay_amount'] = number_format($order_info['total'] * $json['currency_value'], $json['decimal_place'], '.', '');
                    } else {
                        $item_total = 0;

                        foreach ($this->cart->getProducts() as $product) {
                            $product_price = $this->tax->calculate($product['price'], $product['tax_class_id'], true);

                            $item_total += $product_price * $product['quantity'];
                        }

                        if (!empty($this->session->data['vouchers'])) {
                            foreach ($this->session->data['vouchers'] as $voucher) {
                                $item_total += $voucher['amount'];
                            }
                        }

                        $json['applepay_amount'] = number_format($item_total * $json['currency_value'], $json['decimal_place'], '.', '');
                    }
                }

                if ($setting['card']['status']) {
                    $json['components'][] = 'hosted-fields';
                    $json['card_status'] = $setting['card']['status'];
                    $json['card_align'] = $setting['card']['align'];
                    $json['card_size'] = $setting['card']['size'];
                    $json['card_width'] = $setting['card_width'][$json['card_size']];
                    $json['card_secure_status'] = $setting['card']['secure_status'];
                }

                if ($setting['message']['checkout']['status'] && ($json['currency_code'] == $setting['general']['currency_code'])) {
                    $json['components'][] = 'messages';
                    $json['message_status'] = $setting['message']['checkout']['status'];
                    $json['message_align'] = $setting['message']['checkout']['align'];
                    $json['message_size'] = $setting['message']['checkout']['size'];
                    $json['message_width'] = $setting['message_width'][$json['message_size']];
                    $json['message_layout'] = $setting['message']['checkout']['layout'];
                    $json['message_text_color'] = $setting['message']['checkout']['text_color'];
                    $json['message_text_size'] = $setting['message']['checkout']['text_size'];
                    $json['message_flex_color'] = $setting['message']['checkout']['flex_color'];
                    $json['message_flex_ratio'] = $setting['message']['checkout']['flex_ratio'];

                    if (!empty($order_info)) {
                        $json['message_amount'] = number_format($order_info['total'] * $json['currency_value'], $json['decimal_place'], '.', '');
                    } else {
                        $item_total = 0;

                        foreach ($this->cart->getProducts() as $product) {
                            $product_price = $this->tax->calculate($product['price'], $product['tax_class_id'], true);

                            $item_total += $product_price * $product['quantity'];
                        }

                        if (!empty($this->session->data['vouchers'])) {
                            foreach ($this->session->data['vouchers'] as $voucher) {
                                $item_total += $voucher['amount'];
                            }
                        }

                        $json['message_amount'] = number_format($item_total * $json['currency_value'], $json['decimal_place'], '.', '');
                    }
                }
            }

            require_once DIR_SYSTEM . 'library/paypal/paypal.php';

            $paypal_info = [
                'partner_id'             => $json['partner_id'],
                'client_id'              => $json['client_id'],
                'secret'                 => $json['secret'],
                'environment'            => $json['environment'],
                'partner_attribution_id' => $json['partner_attribution_id']
            ];

            $paypal = new PayPal($paypal_info);

            $token_info = [
                'grant_type' => 'client_credentials'
            ];

            $paypal->setAccessToken($token_info);

            $json['client_token'] = $paypal->getClientToken();

            if ($paypal->hasErrors()) {
                $error_messages = [];

                $errors = $paypal->getErrors();

                foreach ($errors as $error) {
                    if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                        $error['message'] = $this->language->get('error_timeout');
                    }

                    if (isset($error['details'][0]['description'])) {
                        $error_messages[] = $error['details'][0]['description'];
                    } elseif (isset($error['message'])) {
                        $error_messages[] = $error['message'];
                    }

                    $this->model_extension_payment_paypal->log($error, $error['message']);
                }

                $this->error['warning'] = implode(' ', $error_messages);
            }

            if (!empty($this->error['warning'])) {
                $this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
            }
        }

        $json['error'] = $this->error;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Create Order
     *
     * @return void
     */
    public function createOrder(): void
    {
        $this->load->language('extension/payment/paypal');

        $json = [];

        $this->load->model('extension/payment/paypal');

        if (!empty($this->request->post['page_code']) && !empty($this->request->post['payment_type'])) {
            $page_code = $this->request->post['page_code'];
            $payment_type = $this->request->post['payment_type'];

            $errors = [];

            $json['order_id'] = '';

            if (!empty($this->request->post['product'])) {
                $this->request->post['product'] = $this->unserialize($this->request->post['product']);
            }

            if (($page_code == 'product') && (!empty($this->request->post['product']['product_id']))) {
                $product = $this->request->post['product'];

                $product_id = (int)$product['product_id'];

                $this->load->model('catalog/product');

                $product_info = $this->model_catalog_product->getProduct($product_id);

                if ($product_info) {
                    if (isset($product['quantity'])) {
                        $quantity = (int)$product['quantity'];
                    } else {
                        $quantity = 1;
                    }

                    if (isset($product['option'])) {
                        $option = array_filter($product['option']);
                    } else {
                        $option = [];
                    }

                    $product_options = $this->model_catalog_product->getProductOptions($product_id);

                    foreach ($product_options as $product_option) {
                        if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
                            $errors[] = sprintf($this->language->get('error_required'), $product_option['name']);
                        }
                    }

                    if (isset($product['subscription_plan_id'])) {
                        $subscription_plan_id = $product['subscription_plan_id'];
                    } else {
                        $subscription_plan_id = 0;
                    }

                    $this->load->model('catalog/subscription_plan');

                    $filter_data = [
                        'filter_name' => $product_info['name']
                    ];

                    $subscription_plans = $this->model_catalog_subscription_plan->getSubscriptionPlans($filter_data);

                    if ($subscription_plans) {
                        $subscription_plan_ids = [];

                        foreach ($subscription_plans as $subscription_plan) {
                            $subscription_plan_ids[] = $subscription_plan['subscription_plan_id'];
                        }

                        if (!in_array($subscription_plan_id, $subscription_plan_ids)) {
                            $errors[] = $this->language->get('error_subscription_required');
                        }
                    }

                    if (!$errors) {
                        if (!$this->model_extension_payment_paypal->hasProductInCart($product_id, $option, $subscription_plan_id)) {
                            $this->cart->add($product_id, $quantity, $option, $subscription_plan_id);
                        }

                        // Unset all shipping and payment methods
                        unset($this->session->data['shipping_method']);
                        unset($this->session->data['shipping_methods']);
                        unset($this->session->data['payment_method']);
                        unset($this->session->data['payment_methods']);
                    }
                }
            }

            if ($page_code == 'checkout') {
                $this->load->model('checkout/order');

                $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

                $shipping_info = [];

                if ($this->cart->hasShipping()) {
                    $shipping_info['name']['full_name'] = $order_info['shipping_firstname'];
                    $shipping_info['name']['full_name'] .= ($order_info['shipping_lastname'] ? (' ' . $order_info['shipping_lastname']) : '');
                    $shipping_info['address']['address_line_1'] = $order_info['shipping_address_1'];
                    $shipping_info['address']['address_line_2'] = $order_info['shipping_address_2'];
                    $shipping_info['address']['admin_area_1'] = $order_info['shipping_zone'];
                    $shipping_info['address']['admin_area_2'] = $order_info['shipping_city'];
                    $shipping_info['address']['postal_code'] = $order_info['shipping_postcode'];

                    if ($order_info['shipping_country_id']) {
                        $this->load->model('localisation/country');

                        $country_info = $this->model_localisation_country->getCountry($order_info['shipping_country_id']);

                        if ($country_info) {
                            $shipping_info['address']['country_code'] = $country_info['iso_code_2'];
                        }
                    }
                }
            }

            if (!$errors) {
                $_config = new \Config();
                $_config->load('paypal');

                $config_setting = $_config->get('paypal_setting');

                $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

                $client_id = $this->config->get('payment_paypal_client_id');
                $secret = $this->config->get('payment_paypal_secret');
                $merchant_id = $this->config->get('payment_paypal_merchant_id');
                $environment = $this->config->get('payment_paypal_environment');
                $partner_id = $setting['partner'][$environment]['partner_id'];
                $partner_attribution_id = $setting['partner'][$environment]['partner_attribution_id'];
                $transaction_method = $setting['general']['transaction_method'];

                $currency_code = $this->session->data['currency'];
                $currency_value = $this->currency->getValue($this->session->data['currency']);

                if ((($payment_type == 'button') || ($payment_type == 'googlepay_button') || ($payment_type == 'applepay_button')) && empty($setting['currency'][$currency_code]['status'])) {
                    $currency_code = $setting['general']['currency_code'];
                    $currency_value = $setting['general']['currency_value'];
                }

                if (($payment_type == 'card') && empty($setting['currency'][$currency_code]['card_status'])) {
                    $currency_code = $setting['general']['card_currency_code'];
                    $currency_value = $setting['general']['card_currency_value'];
                }

                $decimal_place = $setting['currency'][$currency_code]['decimal_place'];

                require_once DIR_SYSTEM . 'library/paypal/paypal.php';

                $paypal_info = [
                    'partner_id'             => $partner_id,
                    'client_id'              => $client_id,
                    'secret'                 => $secret,
                    'environment'            => $environment,
                    'partner_attribution_id' => $partner_attribution_id
                ];

                $paypal = new PayPal($paypal_info);

                $token_info = [
                    'grant_type' => 'client_credentials'
                ];

                $paypal->setAccessToken($token_info);

                $item_info = [];

                $item_total = 0;
                $tax_total = 0;

                foreach ($this->cart->getProducts() as $product) {
                    $product_price = number_format($product['price'] * $currency_value, $decimal_place, '.', '');

                    $item_info[] = [
                        'name'        => $product['name'],
                        'sku'         => $product['model'],
                        'url'         => $this->url->link('product/product', 'product_id = ' . $product['product_id'], true),
                        'quantity'    => $product['quantity'],
                        'unit_amount' => [
                            'currency_code' => $currency_code,
                            'value'         => $product_price
                        ]
                    ];

                    $item_total += $product_price * $product['quantity'];

                    if ($product['tax_class_id']) {
                        $tax_rates = $this->tax->getRates($product['price'], $product['tax_class_id']);

                        foreach ($tax_rates as $tax_rate) {
                            $tax_total += ($tax_rate['amount'] * $product['quantity']);
                        }
                    }
                }

                if (!empty($this->session->data['vouchers'])) {
                    foreach ($this->session->data['vouchers'] as $voucher) {
                        $item_info[] = [
                            'name'        => $voucher['description'],
                            'quantity'    => 1,
                            'unit_amount' => [
                                'currency_code' => $currency_code,
                                'value'         => $voucher['amount']
                            ]
                        ];

                        $item_total += $voucher['amount'];
                    }
                }

                $item_total = number_format($item_total, $decimal_place, '.', '');
                $tax_total = number_format($tax_total * $currency_value, $decimal_place, '.', '');
                $order_total = number_format($item_total + $tax_total, $decimal_place, '.', '');

                if ($page_code == 'checkout' && isset($order_info)) {
                    $discount_total = 0;
                    $handling_total = 0;
                    $shipping_total = 0;

                    if (isset($this->session->data['shipping_method'])) {
                        $shipping_total = $this->tax->calculate($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id'], true);
                        $shipping_total = number_format($shipping_total * $currency_value, $decimal_place, '.', '');
                    }

                    $order_total = number_format($order_info['total'] * $currency_value, $decimal_place, '.', '');

                    $rebate = number_format($item_total + $tax_total + $shipping_total - $order_total, $decimal_place, '.', '');

                    if ($rebate > 0) {
                        $discount_total = $rebate;
                    } elseif ($rebate < 0) {
                        $handling_total = -$rebate;
                    }
                }

                $amount_info = [];

                $amount_info['currency_code'] = $currency_code;
                $amount_info['value'] = $order_total;

                $amount_info['breakdown']['item_total'] = [
                    'currency_code' => $currency_code,
                    'value'         => $item_total
                ];

                $amount_info['breakdown']['tax_total'] = [
                    'currency_code' => $currency_code,
                    'value'         => $tax_total
                ];

                if ($page_code == 'checkout' && isset($shipping_total) && isset($handling_total) && isset($discount_total) && isset($order_info) && isset($shipping_info)) {
                    $amount_info['breakdown']['shipping'] = [
                        'currency_code' => $currency_code,
                        'value'         => $shipping_total
                    ];

                    $amount_info['breakdown']['handling'] = [
                        'currency_code' => $currency_code,
                        'value'         => $handling_total
                    ];

                    $amount_info['breakdown']['discount'] = [
                        'currency_code' => $currency_code,
                        'value'         => $discount_total
                    ];
                }

                $paypal_order_info = [];

                $paypal_order_info['intent'] = strtoupper($transaction_method);

                $paypal_order_info['purchase_units'][0]['reference_id'] = 'default';
                $paypal_order_info['purchase_units'][0]['items'] = $item_info;
                $paypal_order_info['purchase_units'][0]['amount'] = $amount_info;

                if ($page_code == 'checkout' && isset($order_info)) {
                    $paypal_order_info['purchase_units'][0]['description'] = 'Your order ' . $order_info['order_id'];
                    $paypal_order_info['purchase_units'][0]['invoice_id'] = $order_info['order_id'] . '_' . date('Ymd_His');

                    if ($this->cart->hasShipping() && isset($shipping_info)) {
                        $paypal_order_info['purchase_units'][0]['shipping'] = $shipping_info;
                    }
                }

                if ($this->cart->hasShipping()) {
                    $shipping_preference = 'GET_FROM_FILE';
                } else {
                    $shipping_preference = 'NO_SHIPPING';
                }

                $paypal_order_info['application_context']['shipping_preference'] = $shipping_preference;

                if ($this->cart->hasSubscription()) {
                    $payment_method = '';

                    if ($payment_type == 'button') {
                        $payment_method = 'paypal';
                    }

                    if ($payment_type == 'card') {
                        $payment_method = 'card';
                    }

                    if ($payment_method) {
                        $paypal_order_info['payment_source'][$payment_method]['attributes']['vault'] = [
                            'store_in_vault' => 'ON_SUCCESS',
                            'usage_type'     => 'MERCHANT',
                            'customer_type'  => 'CONSUMER'
                        ];

                        $paypal_order_info['payment_source']['paypal']['experience_context'] = [
                            'return_url' => $this->url->link('checkout/success', '', true),
                            'cancel_url' => $this->url->link('checkout/success', '', true)
                        ];
                    }
                }

                $result = $paypal->createOrder($paypal_order_info);

                if ($paypal->hasErrors()) {
                    $error_messages = [];

                    $errors = $paypal->getErrors();

                    foreach ($errors as $error) {
                        if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                            $error['message'] = $this->language->get('error_timeout');
                        }

                        if (isset($error['details'][0]['description'])) {
                            $error_messages[] = $error['details'][0]['description'];
                        } elseif (isset($error['message'])) {
                            $error_messages[] = $error['message'];
                        }

                        $this->model_extension_payment_paypal->log($error, $error['message']);
                    }

                    $this->error['warning'] = implode(' ', $error_messages);
                }

                if (!empty($this->error['warning'])) {
                    $this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
                }

                $json['paypal_order_id'] = '';

                if (isset($result['id']) && isset($result['status']) && !$this->error) {
                    $this->model_extension_payment_paypal->log($result, 'Create Order');

                    if ($result['status'] == 'VOIDED') {
                        $this->error['warning'] = sprintf($this->language->get('error_order_voided'), $this->url->link('information/contact', '', true));
                    }

                    if ($result['status'] == 'COMPLETED') {
                        $this->error['warning'] = sprintf($this->language->get('error_order_completed'), $this->url->link('information/contact', '', true));
                    }

                    if (!$this->error) {
                        $json['paypal_order_id'] = $result['id'];
                    }
                }
            } else {
                $this->error['warning'] = implode(' ', $errors);
            }
        }

        $json['error'] = $this->error;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Approve Order
     *
     * @return void
     */
    public function approveOrder(): void
    {
        $this->load->language('extension/payment/paypal');

        $json = [];

        $this->load->model('extension/payment/paypal');

        if (!empty($this->request->post['page_code']) && !empty($this->request->post['payment_type'])) {
            $page_code = $this->request->post['page_code'];
            $payment_type = $this->request->post['payment_type'];

            if ($page_code != 'checkout') {
                if (isset($this->request->post['paypal_order_id'])) {
                    $this->session->data['paypal_order_id'] = (int)$this->request->post['paypal_order_id'];
                } else {
                    $json['url'] = $this->url->link('checkout/cart', '', true);

                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                }

                // check checkout can continue due to stock checks or vouchers
                if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
                    $json['url'] = $this->url->link('checkout/cart', '', true);

                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                }

                // if user not logged in check that the guest checkout is allowed
                if (!$this->customer->isLogged() && (!$this->config->get('config_checkout_guest') || $this->config->get('config_customer_price') || $this->cart->hasDownload() || $this->cart->hasSubscription())) {
                    $json['url'] = $this->url->link('checkout/cart', '', true);

                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                }
            }

            $_config = new Config();
            $_config->load('paypal');

            $config_setting = $_config->get('paypal_setting');

            $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

            $client_id = $this->config->get('payment_paypal_client_id');
            $secret = $this->config->get('payment_paypal_secret');
            $environment = $this->config->get('payment_paypal_environment');
            $partner_id = $setting['partner'][$environment]['partner_id'];
            $partner_attribution_id = $setting['partner'][$environment]['partner_attribution_id'];
            $transaction_method = $setting['general']['transaction_method'];

            require_once DIR_SYSTEM . 'library/paypal/paypal.php';

            $paypal_info = [
                'partner_id'             => $partner_id,
                'client_id'              => $client_id,
                'secret'                 => $secret,
                'environment'            => $environment,
                'partner_attribution_id' => $partner_attribution_id
            ];

            $paypal = new PayPal($paypal_info);

            $token_info = [
                'grant_type' => 'client_credentials'
            ];

            $paypal->setAccessToken($token_info);

            if ($page_code != 'checkout') {
                $paypal_order_id = $this->session->data['paypal_order_id'];

                $paypal_order_info = $paypal->getOrder($paypal_order_id);

                if ($paypal->hasErrors()) {
                    $error_messages = [];

                    $errors = $paypal->getErrors();

                    foreach ($errors as $error) {
                        if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                            $error['message'] = $this->language->get('error_timeout');
                        }

                        if (isset($error['details'][0]['description'])) {
                            $error_messages[] = $error['details'][0]['description'];
                        } elseif (isset($error['message'])) {
                            $error_messages[] = $error['message'];
                        }

                        $this->model_extension_payment_paypal->log($error, $error['message']);
                    }

                    $this->error['warning'] = implode(' ', $error_messages);
                }

                if (!empty($this->error['warning'])) {
                    $this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
                }

                if ($paypal_order_info && !$this->error) {
                    $this->load->model('account/customer');
                    $this->load->model('account/address');

                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);

                    if ($this->customer->isLogged()) {
                        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

                        $this->session->data['guest']['customer_id'] = $this->customer->getId();
                        $this->session->data['guest']['customer_group_id'] = $customer_info['customer_group_id'];
                        $this->session->data['guest']['firstname'] = $customer_info['firstname'];
                        $this->session->data['guest']['lastname'] = $customer_info['lastname'];
                        $this->session->data['guest']['email'] = $customer_info['email'];
                        $this->session->data['guest']['telephone'] = $customer_info['telephone'];
                        $this->session->data['guest']['custom_field'] = json_decode($customer_info['custom_field'], true);
                    } else {
                        $this->session->data['guest']['customer_id'] = 0;
                        $this->session->data['guest']['customer_group_id'] = $this->config->get('config_customer_group_id');
                        $this->session->data['guest']['firstname'] = ($paypal_order_info['payer']['name']['given_name'] ?? '');
                        $this->session->data['guest']['lastname'] = ($paypal_order_info['payer']['name']['surname'] ?? '');
                        $this->session->data['guest']['email'] = ($paypal_order_info['payer']['email_address'] ?? '');
                        $this->session->data['guest']['telephone'] = '';
                        $this->session->data['guest']['custom_field'] = [];
                    }

                    if ($this->customer->isLogged() && $this->customer->getAddressId()) {
                        $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    } else {
                        $this->session->data['payment_address']['firstname'] = ($paypal_order_info['payer']['name']['given_name'] ?? '');
                        $this->session->data['payment_address']['lastname'] = ($paypal_order_info['payer']['name']['surname'] ?? '');
                        $this->session->data['payment_address']['company'] = '';
                        $this->session->data['payment_address']['address_1'] = '';
                        $this->session->data['payment_address']['address_2'] = '';
                        $this->session->data['payment_address']['city'] = '';
                        $this->session->data['payment_address']['postcode'] = '';
                        $this->session->data['payment_address']['country'] = '';
                        $this->session->data['payment_address']['country_id'] = 0;
                        $this->session->data['payment_address']['address_format'] = '';
                        $this->session->data['payment_address']['zone'] = '';
                        $this->session->data['payment_address']['zone_id'] = 0;
                        $this->session->data['payment_address']['custom_field'] = [];

                        if (isset($paypal_order_info['payer']['address']['country_code'])) {
                            $country_info = $this->model_extension_payment_paypal->getCountryByCode($paypal_order_info['payer']['address']['country_code']);

                            if ($country_info) {
                                $this->session->data['payment_address']['country'] = $country_info['name'];
                                $this->session->data['payment_address']['country_id'] = $country_info['country_id'];
                            }
                        }
                    }

                    if ($this->cart->hasShipping()) {
                        if ($this->customer->isLogged() && $this->customer->getAddressId()) {
                            $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                        } else {
                            if (isset($paypal_order_info['purchase_units'][0]['shipping']['name']['full_name'])) {
                                $shipping_name = explode(' ', $paypal_order_info['purchase_units'][0]['shipping']['name']['full_name']);
                                $shipping_firstname = $shipping_name[0];
                                unset($shipping_name[0]);
                                $shipping_lastname = implode(' ', $shipping_name);
                            }

                            $this->session->data['shipping_address']['firstname'] = ($shipping_firstname ?? '');
                            $this->session->data['shipping_address']['lastname'] = ($shipping_lastname ?? '');
                            $this->session->data['shipping_address']['company'] = '';
                            $this->session->data['shipping_address']['address_1'] = ($paypal_order_info['purchase_units'][0]['shipping']['address']['address_line_1'] ?? '');
                            $this->session->data['shipping_address']['address_2'] = ($paypal_order_info['purchase_units'][0]['shipping']['address']['address_line_2'] ?? '');
                            $this->session->data['shipping_address']['city'] = ($paypal_order_info['purchase_units'][0]['shipping']['address']['admin_area_2'] ?? '');
                            $this->session->data['shipping_address']['postcode'] = ($paypal_order_info['purchase_units'][0]['shipping']['address']['postal_code'] ?? '');
                            $this->session->data['shipping_address']['country'] = '';
                            $this->session->data['shipping_address']['country_id'] = 0;
                            $this->session->data['shipping_address']['address_format'] = '';
                            $this->session->data['shipping_address']['zone'] = '';
                            $this->session->data['shipping_address']['zone_id'] = 0;
                            $this->session->data['shipping_address']['custom_field'] = [];

                            if (isset($paypal_order_info['purchase_units'][0]['shipping']['address']['country_code'])) {
                                $country_info = $this->model_extension_payment_paypal->getCountryByCode($paypal_order_info['purchase_units'][0]['shipping']['address']['country_code']);

                                if ($country_info) {
                                    $this->session->data['shipping_address']['country_id'] = $country_info['country_id'];
                                    $this->session->data['shipping_address']['country'] = $country_info['name'];
                                    $this->session->data['shipping_address']['address_format'] = $country_info['address_format'];

                                    if (isset($paypal_order_info['purchase_units'][0]['shipping']['address']['admin_area_1'])) {
                                        $zone_info = $this->model_extension_payment_paypal->getZoneByCode($country_info['country_id'], $paypal_order_info['purchase_units'][0]['shipping']['address']['admin_area_1']);

                                        if ($zone_info) {
                                            $this->session->data['shipping_address']['zone_id'] = $zone_info['zone_id'];
                                            $this->session->data['shipping_address']['zone'] = $zone_info['name'];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $json['url'] = $this->url->link('extension/payment/paypal/confirmOrder', '', true);
                }
            } else {
                if ((($payment_type == 'button') || ($payment_type == 'googlepay_button') || ($payment_type == 'applepay_button')) && !empty($this->request->post['paypal_order_id'])) {
                    $paypal_order_id = (int)$this->request->post['paypal_order_id'];
                }

                if (($payment_type == 'card') && !empty($this->request->post['payload'])) {
                    $payload = json_decode(htmlspecialchars_decode($this->request->post['payload']), true);

                    if (isset($payload['orderId'])) {
                        $paypal_order_id = $payload['orderId'];

                        if ($setting['card']['secure_status']) {
                            $paypal_order_info = $paypal->getOrder($paypal_order_id);

                            if ($paypal->hasErrors()) {
                                $error_messages = [];

                                $errors = $paypal->getErrors();

                                foreach ($errors as $error) {
                                    if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                                        $error['message'] = $this->language->get('error_timeout');
                                    }

                                    if (isset($error['details'][0]['description'])) {
                                        $error_messages[] = $error['details'][0]['description'];
                                    } elseif (isset($error['message'])) {
                                        $error_messages[] = $error['message'];
                                    }

                                    $this->model_extension_payment_paypal->log($error, $error['message']);
                                }

                                $this->error['warning'] = implode(' ', $error_messages);
                            }

                            if (isset($paypal_order_info['payment_source']['card']) && !$this->error) {
                                $this->model_extension_payment_paypal->log($paypal_order_info['payment_source']['card'], 'Card');

                                $liability_shift = ($paypal_order_info['payment_source']['card']['authentication_result']['liability_shift'] ?? '');
                                $enrollment_status = ($paypal_order_info['payment_source']['card']['authentication_result']['three_d_secure']['enrollment_status'] ?? '');
                                $authentication_status = ($paypal_order_info['payment_source']['card']['authentication_result']['three_d_secure']['authentication_status'] ?? '');

                                if ($enrollment_status == 'Y') {
                                    if (($authentication_status == 'N') && !$setting['card']['secure_scenario']['failed_authentication']) {
                                        $this->error['warning'] = $this->language->get($setting['card_secure_scenario']['failed_authentication']['error']);
                                    }

                                    if (($authentication_status == 'R') && !$setting['card']['secure_scenario']['rejected_authentication']) {
                                        $this->error['warning'] = $this->language->get($setting['card_secure_scenario']['rejected_authentication']['error']);
                                    }

                                    if (($authentication_status == 'A') && !$setting['card']['secure_scenario']['attempted_authentication']) {
                                        $this->error['warning'] = $this->language->get($setting['card_secure_scenario']['attempted_authentication']['error']);
                                    }

                                    if (($authentication_status == 'U') && !$setting['card']['secure_scenario']['unable_authentication']) {
                                        $this->error['warning'] = $this->language->get($setting['card_secure_scenario']['unable_authentication']['error']);
                                    }

                                    if (($authentication_status == 'C') && !$setting['card']['secure_scenario']['challenge_authentication']) {
                                        $this->error['warning'] = $this->language->get($setting['card_secure_scenario']['challenge_authentication']['error']);
                                    }
                                }

                                if (($enrollment_status == 'N') && !$setting['card']['secure_scenario']['card_ineligible']) {
                                    $this->error['warning'] = $this->language->get($setting['card_secure_scenario']['card_ineligible']['error']);
                                }

                                if (($enrollment_status == 'U') && !$setting['card']['secure_scenario']['system_unavailable']) {
                                    $this->error['warning'] = $this->language->get($setting['card_secure_scenario']['system_unavailable']['error']);
                                }

                                if (($enrollment_status == 'B') && !$setting['card']['secure_scenario']['system_bypassed']) {
                                    $this->error['warning'] = $this->language->get($setting['card_secure_scenario']['system_bypassed']['error']);
                                }
                            }

                            if (!empty($this->error['warning'])) {
                                $this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
                            }
                        }
                    }
                }

                if (isset($paypal_order_id) && !$this->error) {
                    if ($transaction_method == 'authorize') {
                        $result = $paypal->setOrderAuthorize($paypal_order_id);
                    } else {
                        $result = $paypal->setOrderCapture($paypal_order_id);
                    }

                    if ($paypal->hasErrors()) {
                        $error_messages = [];

                        $errors = $paypal->getErrors();

                        foreach ($errors as $error) {
                            if (isset($error['details'][0]['issue']) && ($error['details'][0]['issue'] == 'INSTRUMENT_DECLINED')) {
                                $json['restart'] = true;
                            }

                            if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                                $error['message'] = $this->language->get('error_timeout');
                            }

                            if (isset($error['details'][0]['description'])) {
                                $error_messages[] = $error['details'][0]['description'];
                            } elseif (isset($error['message'])) {
                                $error_messages[] = $error['message'];
                            }

                            $this->model_extension_payment_paypal->log($error, $error['message']);
                        }

                        $this->error['warning'] = implode(' ', $error_messages);
                    }

                    if (!empty($this->error['warning'])) {
                        $this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
                    }

                    if (!$this->error) {
                        $this->load->model('checkout/order');

                        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

                        if ($transaction_method == 'authorize') {
                            $this->model_extension_payment_paypal->log($result, 'Authorize Order');

                            if (isset($result['purchase_units'][0]['payments']['authorizations'][0]['status']) && isset($result['purchase_units'][0]['payments']['authorizations'][0]['seller_protection']['status'])) {
                                $authorization_id = $result['purchase_units'][0]['payments']['authorizations'][0]['id'];
                                $authorization_status = $result['purchase_units'][0]['payments']['authorizations'][0]['status'];
                                $seller_protection_status = $result['purchase_units'][0]['payments']['authorizations'][0]['seller_protection']['status'];
                                $order_status_id = 0;
                                $transaction_status = '';
                                $payment_method = '';
                                $vault_id = '';
                                $vault_customer_id = '';
                                $paypal_order_data = [];

                                if (!$this->cart->hasShipping()) {
                                    $seller_protection_status = 'NOT_ELIGIBLE';
                                }

                                foreach ($result['payment_source'] as $payment_source_key => $payment_source) {
                                    $vault_id = ($payment_source['attributes']['vault']['id'] ?? '');
                                    $vault_customer_id = ($payment_source['attributes']['vault']['customer']['id'] ?? '');
                                    $payment_method = $payment_source_key;

                                    break;
                                }

                                if ($authorization_status == 'CREATED') {
                                    $order_status_id = $setting['order_status']['pending']['id'];
                                    $transaction_status = 'created';
                                }

                                if ($authorization_status == 'CAPTURED') {
                                    $this->error['warning'] = sprintf($this->language->get('error_authorization_captured'), $this->url->link('information/contact', '', true));
                                }

                                if ($authorization_status == 'DENIED') {
                                    $order_status_id = $setting['order_status']['denied']['id'];
                                    $transaction_status = 'denied';

                                    $this->error['warning'] = $this->language->get('error_authorization_denied');
                                }

                                if ($authorization_status == 'EXPIRED') {
                                    $this->error['warning'] = sprintf($this->language->get('error_authorization_expired'), $this->url->link('information/contact', '', true));
                                }

                                if ($authorization_status == 'PENDING') {
                                    $order_status_id = $setting['order_status']['pending']['id'];
                                    $transaction_status = 'pending';
                                }

                                if (($authorization_status == 'CREATED') || ($authorization_status == 'DENIED') || ($authorization_status == 'PENDING')) {
                                    $message = sprintf($this->language->get('text_order_message'), $seller_protection_status);

                                    $this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id, $message);
                                }

                                if (($authorization_status == 'CREATED') || ($authorization_status == 'DENIED') || ($authorization_status == 'PENDING')) {
                                    $this->model_extension_payment_paypal->deletePayPalOrder($this->session->data['order_id']);

                                    $paypal_order_data = [
                                        'order_id'           => $this->session->data['order_id'],
                                        'transaction_id'     => $authorization_id,
                                        'transaction_status' => $transaction_status,
                                        'payment_method'     => $payment_method,
                                        'vault_id'           => $vault_id,
                                        'vault_customer_id'  => $vault_customer_id,
                                        'environment'        => $environment
                                    ];

                                    $this->model_extension_payment_paypal->addPayPalOrder($paypal_order_data);
                                }

                                if (($authorization_status == 'CREATED') || ($authorization_status == 'PENDING')) {
                                    $this->load->model('checkout/subscription');

                                    $subscription_products = $this->cart->getSubscriptions();

                                    $order_products = $this->model_checkout_order->getProducts($this->session->data['order_id']);

                                    if (isset($this->request->server['HTTP_X_REAL_IP'])) {
                                        $ip = $this->request->server['HTTP_X_REAL_IP'];
                                    } elseif (isset($this->request->server['REMOTE_ADDR'])) {
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

                                    if (isset($this->request->server['HTTP_USER_AGENT'])) {
                                        $user_agent = $this->request->server['HTTP_USER_AGENT'];
                                    } else {
                                        $user_agent = '';
                                    }

                                    if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                                        $accept_language = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
                                    } else {
                                        $accept_language = '';
                                    }

                                    foreach ($subscription_products as $item) {
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

                                                $this->model_extension_payment_paypal->subscriptionPayment($item, $order_info, $paypal_order_data);
                                            }
                                        }
                                    }
                                }

                                if (($authorization_status == 'CREATED') || ($authorization_status == 'PARTIALLY_CAPTURED') || ($authorization_status == 'PARTIALLY_CREATED') || ($authorization_status == 'VOIDED') || ($authorization_status == 'PENDING')) {
                                    $json['url'] = $this->url->link('checkout/success', '', true);
                                }
                            }
                        } else {
                            $this->model_extension_payment_paypal->log($result, 'Capture Order');

                            if (isset($result['purchase_units'][0]['payments']['captures'][0]['status']) && isset($result['purchase_units'][0]['payments']['captures'][0]['seller_protection']['status'])) {
                                $capture_id = $result['purchase_units'][0]['payments']['captures'][0]['id'];
                                $capture_status = $result['purchase_units'][0]['payments']['captures'][0]['status'];
                                $seller_protection_status = $result['purchase_units'][0]['payments']['captures'][0]['seller_protection']['status'];
                                $order_status_id = 0;
                                $transaction_status = '';
                                $payment_method = '';
                                $vault_id = '';
                                $vault_customer_id = '';
                                $paypal_order_data = [];

                                if (!$this->cart->hasShipping()) {
                                    $seller_protection_status = 'NOT_ELIGIBLE';
                                }

                                foreach ($result['payment_source'] as $payment_source_key => $payment_source) {
                                    $vault_id = ($payment_source['attributes']['vault']['id'] ?? '');
                                    $vault_customer_id = ($payment_source['attributes']['vault']['customer']['id'] ?? '');
                                    $payment_method = $payment_source_key;

                                    break;
                                }

                                if ($capture_status == 'COMPLETED') {
                                    $order_status_id = $setting['order_status']['completed']['id'];
                                    $transaction_status = 'completed';
                                }

                                if ($capture_status == 'DECLINED') {
                                    $order_status_id = $setting['order_status']['denied']['id'];
                                    $transaction_status = 'denied';

                                    $this->error['warning'] = $this->language->get('error_capture_declined');
                                }

                                if ($capture_status == 'FAILED') {
                                    $this->error['warning'] = sprintf($this->language->get('error_capture_failed'), $this->url->link('information/contact', '', true));
                                }

                                if ($capture_status == 'PENDING') {
                                    $order_status_id = $setting['order_status']['pending']['id'];
                                    $transaction_status = 'pending';
                                }

                                if (($capture_status == 'COMPLETED') || ($capture_status == 'DECLINED') || ($capture_status == 'PENDING')) {
                                    $message = sprintf($this->language->get('text_order_message'), $seller_protection_status);

                                    $this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id, $message);
                                }

                                if (($capture_status == 'COMPLETED') || ($capture_status == 'DECLINED') || ($capture_status == 'PENDING')) {
                                    $this->model_extension_payment_paypal->deletePayPalOrder($this->session->data['order_id']);

                                    $paypal_order_data = [
                                        'order_id'           => $this->session->data['order_id'],
                                        'transaction_id'     => $capture_id,
                                        'transaction_status' => $transaction_status,
                                        'payment_method'     => $payment_method,
                                        'vault_id'           => $vault_id,
                                        'vault_customer_id'  => $vault_customer_id,
                                        'environment'        => $environment
                                    ];

                                    $this->model_extension_payment_paypal->addPayPalOrder($paypal_order_data);
                                }

                                if (($capture_status == 'COMPLETED') || ($capture_status == 'PENDING')) {
                                    // Loop through any products that are subscription items
                                    $subscription_products = $this->cart->getSubscriptions();

                                    $order_products = $this->model_checkout_order->getProducts($this->session->data['order_id']);

                                    if (isset($this->request->server['HTTP_X_REAL_IP'])) {
                                        $ip = $this->request->server['HTTP_X_REAL_IP'];
                                    } elseif (isset($this->request->server['REMOTE_ADDR'])) {
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

                                    if (isset($this->request->server['HTTP_USER_AGENT'])) {
                                        $user_agent = $this->request->server['HTTP_USER_AGENT'];
                                    } else {
                                        $user_agent = '';
                                    }

                                    if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                                        $accept_language = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
                                    } else {
                                        $accept_language = '';
                                    }

                                    foreach ($subscription_products as $item) {
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

                                                $this->model_extension_payment_paypal->subscriptionPayment($item, $order_info, $paypal_order_data);
                                            }
                                        }
                                    }
                                }

                                if (($capture_status == 'COMPLETED') || ($capture_status == 'PARTIALLY_REFUNDED') || ($capture_status == 'REFUNDED') || ($capture_status == 'PENDING')) {
                                    $json['url'] = $this->url->link('checkout/success', '', true);
                                }
                            }
                        }
                    }
                }
            }
        }

        $json['error'] = $this->error;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Confirm Order
     *
     * @return void
     */
    public function confirmOrder(): void
    {
        $this->load->language('extension/payment/paypal');
        $this->load->language('checkout/cart');

        $this->load->model('tool/image');

        if (!isset($this->session->data['paypal_order_id'])) {
            $this->response->redirect($this->url->link('checkout/cart', '', true));
        }

        // Coupon
        if (isset($this->request->post['coupon']) && $this->validateCoupon()) {
            $this->session->data['coupon'] = $this->request->post['coupon'];

            $this->session->data['success'] = $this->language->get('text_coupon');

            $this->response->redirect($this->url->link('extension/payment/paypal/confirmOrder', '', true));
        }

        // Voucher
        if (isset($this->request->post['voucher']) && $this->validateVoucher()) {
            $this->session->data['voucher'] = $this->request->post['voucher'];

            $this->session->data['success'] = $this->language->get('text_voucher');

            $this->response->redirect($this->url->link('extension/payment/paypal/confirmOrder', '', true));
        }

        // Reward
        if (isset($this->request->post['reward']) && $this->validateReward()) {
            $this->session->data['reward'] = abs($this->request->post['reward']);

            $this->session->data['success'] = $this->language->get('text_reward');

            $this->response->redirect($this->url->link('extension/payment/paypal/confirmOrder', '', true));
        }

        $this->document->setTitle($this->language->get('text_paypal'));

        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        $data['heading_title'] = $this->language->get('text_paypal');

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_cart'),
            'href' => $this->url->link('checkout/cart', '', true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_paypal'),
            'href' => $this->url->link('extension/payment/paypal/confirmOrder', '', true)
        ];

        $points_total = 0;

        foreach ($this->cart->getProducts() as $product) {
            if ($product['points']) {
                $points_total += $product['points'];
            }
        }

        if (isset($this->request->post['next'])) {
            $data['next'] = $this->request->post['next'];
        } else {
            $data['next'] = '';
        }

        $this->load->model('tool/upload');

        if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
            $this->response->redirect($this->url->link('checkout/cart', '', true));
        }

        $data['products'] = [];

        $products = $this->cart->getProducts();

        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
            }

            if ($product['image']) {
                $image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
            } else {
                $image = '';
            }

            $option_data = [];

            foreach ($product['option'] as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['value'];
                } else {
                    $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                    if ($upload_info) {
                        $value = $upload_info['name'];
                    } else {
                        $value = '';
                    }
                }

                $option_data[] = [
                    'name'  => $option['name'],
                    'value' => (oc_strlen($value) > 20 ? oc_substr($value, 0, 20) . '..' : $value)
                ];
            }

            // Display prices
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                $price = $this->currency->format($unit_price, $this->session->data['currency']);
                $total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
            } else {
                $price = false;
                $total = false;
            }

            $recurring = '';

            if ($product['recurring']) {
                $frequencies = [
                    'day'        => $this->language->get('text_day'),
                    'week'       => $this->language->get('text_week'),
                    'semi_month' => $this->language->get('text_semi_month'),
                    'month'      => $this->language->get('text_month'),
                    'year'       => $this->language->get('text_year'),
                ];

                if ($product['recurring']['trial']) {
                    $recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
                }

                if ($product['recurring']['duration']) {
                    $recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                } else {
                    $recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                }
            }

            $data['products'][] = [
                'cart_id'   => $product['cart_id'],
                'thumb'     => $image,
                'name'      => $product['name'],
                'model'     => $product['model'],
                'option'    => $option_data,
                'recurring' => $recurring,
                'quantity'  => $product['quantity'],
                'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
                'price'     => $price,
                'total'     => $total,
                'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'], true)
            ];
        }

        // Gift Voucher
        $data['vouchers'] = [];

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {
                $data['vouchers'][] = [
                    'key'         => $key,
                    'description' => $voucher['description'],
                    'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
                    'remove'      => $this->url->link('checkout/cart', 'remove=' . $key, true)
                ];
            }
        }

        $this->load->model('setting/extension');

        if ($this->cart->hasShipping()) {
            $data['has_shipping'] = true;

            $data['shipping_address'] = $this->session->data['shipping_address'] ?? [];

            if (!empty($data['shipping_address'])) {
                // Shipping Methods
                $quote_data = [];

                $results = $this->model_setting_extension->getExtensions('shipping');

                if (!empty($results)) {
                    foreach ($results as $result) {
                        if ($this->config->get('shipping_' . $result['code'] . '_status')) {
                            $this->load->model('extension/shipping/' . $result['code']);

                            if (is_callable([$this->{'model_extension_shipping_' . $result['code']}, 'getQuote'])) {
                                $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($data['shipping_address']);
                            }

                            if ($quote) {
                                $quote_data[$result['code']] = [
                                    'title'      => $quote['title'],
                                    'quote'      => $quote['quote'],
                                    'sort_order' => $quote['sort_order'],
                                    'error'      => $quote['error']
                                ];
                            }
                        }
                    }

                    if (!empty($quote_data)) {
                        $sort_order = [];

                        foreach ($quote_data as $key => $value) {
                            $sort_order[$key] = $value['sort_order'];
                        }

                        array_multisort($sort_order, SORT_ASC, $quote_data);

                        $this->session->data['shipping_methods'] = $quote_data;
                        $data['shipping_methods'] = $quote_data;

                        if (!isset($this->session->data['shipping_method'])) {
                            //default the shipping to the very first option.
                            $key1 = key($quote_data);
                            $key2 = key($quote_data[$key1]['quote']);
                            $this->session->data['shipping_method'] = $quote_data[$key1]['quote'][$key2];
                        }

                        $data['code'] = $this->session->data['shipping_method']['code'];
                        $data['action_shipping'] = $this->url->link('extension/payment/paypal/confirmShipping', '', true);
                    } else {
                        unset($this->session->data['shipping_methods']);
                        unset($this->session->data['shipping_method']);

                        $data['error_no_shipping'] = $this->language->get('error_no_shipping');
                    }
                } else {
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['shipping_method']);

                    $data['error_no_shipping'] = $this->language->get('error_no_shipping');
                }
            }
        } else {
            $data['has_shipping'] = false;
        }

        $data['guest'] = $this->session->data['guest'] ?? [];
        $data['payment_address'] = $this->session->data['payment_address'] ?? [];

        // Totals
        $totals = [];
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = [
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        ];

        // Display prices
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $sort_order = [];

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    if (is_callable([$this->{'model_extension_total_' . $result['code']}, 'getTotal'])) {
                        $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                    }
                }
            }

            $sort_order = [];

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $totals);
        }

        /**
         * Payment methods
         */
        $method_data = [];

        $results = $this->model_setting_extension->getExtensions('payment');

        foreach ($results as $result) {
            if ($this->config->get('payment_' . $result['code'] . '_status')) {
                $this->load->model('extension/payment/' . $result['code']);

                if (is_callable([$this->{'model_extension_payment_' . $result['code']}, 'getMethod'])) {
                    $method = $this->{'model_extension_payment_' . $result['code']}->getMethod($data['payment_address'], $total);
                }

                if ($method) {
                    $method_data[$result['code']] = $method;
                }
            }
        }

        $sort_order = [];

        foreach ($method_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $method_data);

        $this->session->data['payment_methods'] = $method_data;
        $data['payment_methods'] = $method_data;

        if (!isset($method_data['paypal'])) {
            $this->session->data['error_warning'] = $this->language->get('error_unavailable');

            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

        $this->session->data['payment_method'] = $method_data['paypal'];

        // Custom Fields
        $this->load->model('account/custom_field');

        $data['custom_fields'] = $this->model_account_custom_field->getCustomFields();

        // Totals
        $totals = [];
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = [
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        ];

        // Display prices
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $sort_order = [];

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    if (is_callable([$this->{'model_extension_total_' . $result['code']}, 'getTotal'])) {
                        $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                    }
                }
            }

            $sort_order = [];

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $totals);
        }

        $data['totals'] = [];

        foreach ($totals as $total) {
            $data['totals'][] = [
                'title' => $total['title'],
                'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
            ];
        }

        $data['action_confirm'] = $this->url->link('extension/payment/paypal/completeOrder', '', true);

        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->session->data['attention'])) {
            $data['attention'] = $this->session->data['attention'];
            unset($this->session->data['attention']);
        } else {
            $data['attention'] = '';
        }

        $data['total_coupon'] = $this->load->controller('extension/total/coupon');
        $data['total_voucher'] = $this->load->controller('extension/total/voucher');
        $data['total_reward'] = $this->load->controller('extension/total/reward');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/payment/paypal/confirm', $data));
    }

    public function completeOrder(): void
    {
        $this->load->language('extension/payment/paypal');

        $this->load->model('extension/payment/paypal');

        // Validate if payment address has been set.
        if (empty($this->session->data['payment_address'])) {
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

        // Validate if payment method has been set.
        if (!isset($this->session->data['payment_method'])) {
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

        if ($this->cart->hasShipping()) {
            // Validate if shipping address has been set.
            if (empty($this->session->data['shipping_address'])) {
                $this->response->redirect($this->url->link('checkout/checkout', '', true));
            }

            // Validate if shipping method has been set.
            if (!isset($this->session->data['shipping_method'])) {
                $this->response->redirect($this->url->link('checkout/checkout', '', true));
            }
        } else {
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
        }

        // Validate cart has products and has stock.
        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
            $this->response->redirect($this->url->link('checkout/cart', '', true));
        }

        if (isset($this->session->data['paypal_order_id'])) {
            $order_data = [];

            $totals = [];
            $taxes = $this->cart->getTaxes();
            $total = 0;

            // Because __call can not keep var references so we put them into an array.
            $total_data = [
                'totals' => &$totals,
                'taxes'  => &$taxes,
                'total'  => &$total
            ];

            $this->load->model('setting/extension');

            $sort_order = [];

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    if (is_callable([$this->{'model_extension_total_' . $result['code']}, 'getTotal'])) {
                        $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                    }
                }
            }

            $sort_order = [];

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $totals);

            $order_data['totals'] = $totals;

            $order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
            $order_data['store_id'] = $this->config->get('config_store_id');
            $order_data['store_name'] = $this->config->get('config_name');

            if ($order_data['store_id']) {
                $order_data['store_url'] = $this->config->get('config_url');
            } else {
                if ($this->request->server['HTTPS']) {
                    $order_data['store_url'] = HTTPS_SERVER;
                } else {
                    $order_data['store_url'] = HTTP_SERVER;
                }
            }

            $order_data['customer_id'] = $this->session->data['guest']['customer_id'];
            $order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
            $order_data['firstname'] = $this->session->data['guest']['firstname'];
            $order_data['lastname'] = $this->session->data['guest']['lastname'];
            $order_data['email'] = $this->session->data['guest']['email'];
            $order_data['telephone'] = $this->session->data['guest']['telephone'];
            $order_data['custom_field'] = $this->session->data['guest']['custom_field'];

            $order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
            $order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
            $order_data['payment_company'] = $this->session->data['payment_address']['company'];
            $order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
            $order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
            $order_data['payment_city'] = $this->session->data['payment_address']['city'];
            $order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
            $order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
            $order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
            $order_data['payment_country'] = $this->session->data['payment_address']['country'];
            $order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
            $order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
            $order_data['payment_custom_field'] = ($this->session->data['payment_address']['custom_field'] ?? []);

            if (isset($this->session->data['payment_method']['title'])) {
                $order_data['payment_method'] = $this->session->data['payment_method']['title'];
            } else {
                $order_data['payment_method'] = '';
            }

            if (isset($this->session->data['payment_method']['code'])) {
                $order_data['payment_code'] = $this->session->data['payment_method']['code'];
            } else {
                $order_data['payment_code'] = '';
            }

            if ($this->cart->hasShipping()) {
                $order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
                $order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
                $order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
                $order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
                $order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
                $order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
                $order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
                $order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
                $order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
                $order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
                $order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
                $order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
                $order_data['shipping_custom_field'] = ($this->session->data['shipping_address']['custom_field'] ?? []);

                if (isset($this->session->data['shipping_method']['title'])) {
                    $order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
                } else {
                    $order_data['shipping_method'] = '';
                }

                if (isset($this->session->data['shipping_method']['code'])) {
                    $order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
                } else {
                    $order_data['shipping_code'] = '';
                }
            } else {
                $order_data['shipping_firstname'] = '';
                $order_data['shipping_lastname'] = '';
                $order_data['shipping_company'] = '';
                $order_data['shipping_address_1'] = '';
                $order_data['shipping_address_2'] = '';
                $order_data['shipping_city'] = '';
                $order_data['shipping_postcode'] = '';
                $order_data['shipping_zone'] = '';
                $order_data['shipping_zone_id'] = 0;
                $order_data['shipping_country'] = '';
                $order_data['shipping_country_id'] = 0;
                $order_data['shipping_address_format'] = '';
                $order_data['shipping_custom_field'] = [];
                $order_data['shipping_method'] = '';
                $order_data['shipping_code'] = '';
            }

            $order_data['products'] = [];

            foreach ($this->cart->getProducts() as $product) {
                $option_data = [];

                foreach ($product['option'] as $option) {
                    $option_data[] = [
                        'product_option_id'       => $option['product_option_id'],
                        'product_option_value_id' => $option['product_option_value_id'],
                        'option_id'               => $option['option_id'],
                        'option_value_id'         => $option['option_value_id'],
                        'name'                    => $option['name'],
                        'value'                   => $option['value'],
                        'type'                    => $option['type']
                    ];
                }

                $order_data['products'][] = [
                    'product_id' => $product['product_id'],
                    'name'       => $product['name'],
                    'model'      => $product['model'],
                    'option'     => $option_data,
                    'download'   => $product['download'],
                    'quantity'   => $product['quantity'],
                    'subtract'   => $product['subtract'],
                    'price'      => $product['price'],
                    'total'      => $product['total'],
                    'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                    'reward'     => $product['reward']
                ];
            }

            // Gift Voucher
            $order_data['vouchers'] = [];

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $voucher) {
                    $order_data['vouchers'][] = [
                        'description'      => $voucher['description'],
                        'code'             => oc_token(10),
                        'to_name'          => $voucher['to_name'],
                        'to_email'         => $voucher['to_email'],
                        'from_name'        => $voucher['from_name'],
                        'from_email'       => $voucher['from_email'],
                        'voucher_theme_id' => $voucher['voucher_theme_id'],
                        'message'          => $voucher['message'],
                        'amount'           => $voucher['amount']
                    ];
                }
            }

            $order_data['comment'] = ($this->session->data['comment'] ?? '');
            $order_data['total'] = $total_data['total'];

            if (isset($this->request->cookie['tracking'])) {
                $order_data['tracking'] = $this->request->cookie['tracking'];

                $sub_total = $this->cart->getSubTotal();

                // Affiliate
                $this->load->model('account/customer');

                $affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

                if ($affiliate_info) {
                    $order_data['affiliate_id'] = $affiliate_info['customer_id'];
                    $order_data['commission'] = ($sub_total / 100) * $affiliate_info['commission'];
                } else {
                    $order_data['affiliate_id'] = 0;
                    $order_data['commission'] = 0;
                }

                // Marketing
                $this->load->model('checkout/marketing');

                $marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

                if ($marketing_info) {
                    $order_data['marketing_id'] = $marketing_info['marketing_id'];
                } else {
                    $order_data['marketing_id'] = 0;
                }
            } else {
                $order_data['affiliate_id'] = 0;
                $order_data['commission'] = 0;
                $order_data['marketing_id'] = 0;
                $order_data['tracking'] = '';
            }

            $order_data['language_id'] = $this->config->get('config_language_id');
            $order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
            $order_data['currency_code'] = $this->session->data['currency'];
            $order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
            $order_data['ip'] = $this->request->server['REMOTE_ADDR'];

            if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
                $order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
                $order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
            } else {
                $order_data['forwarded_ip'] = '';
            }

            if (isset($this->request->server['HTTP_USER_AGENT'])) {
                $order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
            } else {
                $order_data['user_agent'] = '';
            }

            if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                $order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
            } else {
                $order_data['accept_language'] = '';
            }

            $this->load->model('checkout/order');

            $this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);

            $order_data['order_id'] = $this->session->data['order_id'];

            $_config = new \Config();
            $_config->load('paypal');

            $config_setting = $_config->get('paypal_setting');

            $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

            $client_id = $this->config->get('payment_paypal_client_id');
            $secret = $this->config->get('payment_paypal_secret');
            $environment = $this->config->get('payment_paypal_environment');
            $partner_id = $setting['partner'][$environment]['partner_id'];
            $partner_attribution_id = $setting['partner'][$environment]['partner_attribution_id'];
            $transaction_method = $setting['general']['transaction_method'];

            $currency_code = $this->session->data['currency'];
            $currency_value = $this->currency->getValue($this->session->data['currency']);

            if (empty($setting['currency'][$currency_code]['status'])) {
                $currency_code = $setting['general']['currency_code'];
                $currency_value = $setting['general']['currency_value'];
            }

            $decimal_place = $setting['currency'][$currency_code]['decimal_place'];

            require_once DIR_SYSTEM . 'library/paypal/paypal.php';

            $paypal_info = [
                'partner_id'             => $partner_id,
                'client_id'              => $client_id,
                'secret'                 => $secret,
                'environment'            => $environment,
                'partner_attribution_id' => $partner_attribution_id
            ];

            $paypal = new PayPal($paypal_info);

            $token_info = [
                'grant_type' => 'client_credentials'
            ];

            $paypal->setAccessToken($token_info);

            $paypal_order_id = $this->session->data['paypal_order_id'];

            $paypal_order_info = [];

            $paypal_order_info[] = [
                'op'    => 'add',
                'path'  => '/purchase_units/@reference_id == \'default\'/description',
                'value' => 'Your order ' . $this->session->data['order_id']
            ];

            $paypal_order_info[] = [
                'op'    => 'add',
                'path'  => '/purchase_units/@reference_id == \'default\'/invoice_id',
                'value' => $this->session->data['order_id'] . '_' . date('Ymd_His')
            ];

            $shipping_info = [];

            if ($this->cart->hasShipping()) {
                $shipping_info['name']['full_name'] = ($this->session->data['shipping_address']['firstname'] ?? '');
                $shipping_info['name']['full_name'] .= (isset($this->session->data['shipping_address']['lastname']) ? (' ' . $this->session->data['shipping_address']['lastname']) : '');
                $shipping_info['address']['address_line_1'] = ($this->session->data['shipping_address']['address_1'] ?? '');
                $shipping_info['address']['address_line_2'] = ($this->session->data['shipping_address']['address_2'] ?? '');
                $shipping_info['address']['admin_area_1'] = ($this->session->data['shipping_address']['zone'] ?? '');
                $shipping_info['address']['admin_area_2'] = ($this->session->data['shipping_address']['city'] ?? '');
                $shipping_info['address']['postal_code'] = ($this->session->data['shipping_address']['postcode'] ?? '');

                if (isset($this->session->data['shipping_address']['country_id'])) {
                    $this->load->model('localisation/country');

                    $country_info = $this->model_localisation_country->getCountry($this->session->data['shipping_address']['country_id']);

                    if ($country_info) {
                        $shipping_info['address']['country_code'] = $country_info['iso_code_2'];
                    }
                }

                $paypal_order_info[] = [
                    'op'    => 'replace',
                    'path'  => '/purchase_units/@reference_id == \'default\'/shipping/name',
                    'value' => $shipping_info['name']
                ];

                $paypal_order_info[] = [
                    'op'    => 'replace',
                    'path'  => '/purchase_units/@reference_id == \'default\'/shipping/address',
                    'value' => $shipping_info['address']
                ];
            }

            $item_total = 0;
            $tax_total = 0;

            foreach ($this->cart->getProducts() as $product) {
                $product_price = number_format($product['price'] * $currency_value, $decimal_place, '.', '');

                $item_total += $product_price * $product['quantity'];

                if ($product['tax_class_id']) {
                    $tax_rates = $this->tax->getRates($product['price'], $product['tax_class_id']);

                    foreach ($tax_rates as $tax_rate) {
                        $tax_total += ($tax_rate['amount'] * $product['quantity']);
                    }
                }
            }

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $voucher) {
                    $item_total += $voucher['amount'];
                }
            }

            $item_total = number_format($item_total, $decimal_place, '.', '');
            $tax_total = number_format($tax_total * $currency_value, $decimal_place, '.', '');

            $discount_total = 0;
            $handling_total = 0;
            $shipping_total = 0;

            if (isset($this->session->data['shipping_method'])) {
                $shipping_total = $this->tax->calculate($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id'], true);
                $shipping_total = number_format($shipping_total * $currency_value, $decimal_place, '.', '');
            }

            $order_total = number_format($order_data['total'] * $currency_value, $decimal_place, '.', '');

            $rebate = number_format($item_total + $tax_total + $shipping_total - $order_total, $decimal_place, '.', '');

            if ($rebate > 0) {
                $discount_total = $rebate;
            } elseif ($rebate < 0) {
                $handling_total = -$rebate;
            }

            $amount_info = [];

            $amount_info['currency_code'] = $currency_code;
            $amount_info['value'] = $order_total;

            $amount_info['breakdown']['item_total'] = [
                'currency_code' => $currency_code,
                'value'         => $item_total
            ];

            $amount_info['breakdown']['tax_total'] = [
                'currency_code' => $currency_code,
                'value'         => $tax_total
            ];

            $amount_info['breakdown']['shipping'] = [
                'currency_code' => $currency_code,
                'value'         => $shipping_total
            ];

            $amount_info['breakdown']['handling'] = [
                'currency_code' => $currency_code,
                'value'         => $handling_total
            ];

            $amount_info['breakdown']['discount'] = [
                'currency_code' => $currency_code,
                'value'         => $discount_total
            ];

            $paypal_order_info[] = [
                'op'    => 'replace',
                'path'  => '/purchase_units/@reference_id == \'default\'/amount',
                'value' => $amount_info
            ];

            $result = $paypal->updateOrder($paypal_order_id, $paypal_order_info);

            if ($paypal->hasErrors()) {
                $error_messages = [];

                $errors = $paypal->getErrors();

                foreach ($errors as $error) {
                    if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                        $error['message'] = $this->language->get('error_timeout');
                    }

                    if (isset($error['details'][0]['description'])) {
                        $error_messages[] = $error['details'][0]['description'];
                    } elseif (isset($error['message'])) {
                        $error_messages[] = $error['message'];
                    }

                    $this->model_extension_payment_paypal->log($error, $error['message']);
                }

                $this->error['warning'] = implode(' ', $error_messages);
            }

            if (!empty($this->error['warning'])) {
                $this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
            }

            if ($paypal_order_id && !$this->error) {
                if ($transaction_method == 'authorize') {
                    $result = $paypal->setOrderAuthorize($paypal_order_id);
                } else {
                    $result = $paypal->setOrderCapture($paypal_order_id);
                }

                if ($paypal->hasErrors()) {
                    $error_messages = [];

                    $errors = $paypal->getErrors();

                    foreach ($errors as $error) {
                        if (isset($error['details'][0]['issue']) && ($error['details'][0]['issue'] == 'INSTRUMENT_DECLINED')) {
                            $data['restart'] = true;
                        }

                        if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                            $error['message'] = $this->language->get('error_timeout');
                        }

                        if (isset($error['details'][0]['description'])) {
                            $error_messages[] = $error['details'][0]['description'];
                        } elseif (isset($error['message'])) {
                            $error_messages[] = $error['message'];
                        }

                        $this->model_extension_payment_paypal->log($error, $error['message']);
                    }

                    $this->error['warning'] = implode(' ', $error_messages);
                }

                if (!empty($this->error['warning'])) {
                    $this->error['warning'] .= ' ' . sprintf($this->language->get('error_payment'), $this->url->link('information/contact', '', true));
                }

                if (!$this->error) {
                    if ($transaction_method == 'authorize') {
                        $this->model_extension_payment_paypal->log($result, 'Authorize Order');

                        if (isset($result['purchase_units'][0]['payments']['authorizations'][0]['status']) && isset($result['purchase_units'][0]['payments']['authorizations'][0]['seller_protection']['status'])) {
                            $authorization_id = $result['purchase_units'][0]['payments']['authorizations'][0]['id'];
                            $authorization_status = $result['purchase_units'][0]['payments']['authorizations'][0]['status'];
                            $seller_protection_status = $result['purchase_units'][0]['payments']['authorizations'][0]['seller_protection']['status'];
                            $order_status_id = 0;
                            $transaction_status = '';
                            $payment_method = '';
                            $vault_id = '';
                            $vault_customer_id = '';
                            $paypal_order_data = [];

                            if (!$this->cart->hasShipping()) {
                                $seller_protection_status = 'NOT_ELIGIBLE';
                            }

                            foreach ($result['payment_source'] as $payment_source_key => $payment_source) {
                                $vault_id = ($payment_source['attributes']['vault']['id'] ?? '');
                                $vault_customer_id = ($payment_source['attributes']['vault']['customer']['id'] ?? '');
                                $payment_method = $payment_source_key;

                                break;
                            }

                            if ($authorization_status == 'CREATED') {
                                $order_status_id = $setting['order_status']['pending']['id'];
                                $transaction_status = 'created';
                            }

                            if ($authorization_status == 'CAPTURED') {
                                $this->error['warning'] = sprintf($this->language->get('error_authorization_captured'), $this->url->link('information/contact', '', true));
                            }

                            if ($authorization_status == 'DENIED') {
                                $order_status_id = $setting['order_status']['denied']['id'];
                                $transaction_status = 'denied';

                                $this->error['warning'] = $this->language->get('error_authorization_denied');
                            }

                            if ($authorization_status == 'EXPIRED') {
                                $this->error['warning'] = sprintf($this->language->get('error_authorization_expired'), $this->url->link('information/contact', '', true));
                            }

                            if ($authorization_status == 'PENDING') {
                                $order_status_id = $setting['order_status']['pending']['id'];
                                $transaction_status = 'pending';
                            }

                            if (($authorization_status == 'CREATED') || ($authorization_status == 'DENIED') || ($authorization_status == 'PENDING')) {
                                $message = sprintf($this->language->get('text_order_message'), $seller_protection_status);

                                $this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id, $message);
                            }

                            if (($authorization_status == 'CREATED') || ($authorization_status == 'DENIED') || ($authorization_status == 'PENDING')) {
                                $this->model_extension_payment_paypal->deletePayPalOrder($this->session->data['order_id']);

                                $paypal_order_data = [
                                    'order_id'           => $this->session->data['order_id'],
                                    'transaction_id'     => $authorization_id,
                                    'transaction_status' => $transaction_status,
                                    'payment_method'     => $payment_method,
                                    'vault_id'           => $vault_id,
                                    'vault_customer_id'  => $vault_customer_id,
                                    'environment'        => $environment
                                ];

                                $this->model_extension_payment_paypal->addPayPalOrder($paypal_order_data);
                            }

                            if (($authorization_status == 'CREATED') || ($authorization_status == 'PENDING')) {
                                $this->load->model('checkout/subscription');

                                $subscription_products = $this->cart->getSubscriptions();

                                $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

                                $order_products = $this->model_checkout_order->getProducts($this->session->data['order_id']);

                                if (isset($this->request->server['HTTP_X_REAL_IP'])) {
                                    $ip = $this->request->server['HTTP_X_REAL_IP'];
                                } elseif (isset($this->request->server['REMOTE_ADDR'])) {
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

                                if (isset($this->request->server['HTTP_USER_AGENT'])) {
                                    $user_agent = $this->request->server['HTTP_USER_AGENT'];
                                } else {
                                    $user_agent = '';
                                }

                                if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                                    $accept_language = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
                                } else {
                                    $accept_language = '';
                                }

                                foreach ($subscription_products as $item) {
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

                                            $this->model_extension_payment_paypal->subscriptionPayment($item, $order_info, $paypal_order_data);
                                        }
                                    }
                                }
                            }

                            if (($authorization_status == 'CREATED') || ($authorization_status == 'PARTIALLY_CAPTURED') || ($authorization_status == 'PARTIALLY_CREATED') || ($authorization_status == 'VOIDED') || ($authorization_status == 'PENDING')) {
                                $this->response->redirect($this->url->link('checkout/success', '', true));
                            }
                        }
                    } else {
                        $this->model_extension_payment_paypal->log($result, 'Capture Order');

                        if (isset($result['purchase_units'][0]['payments']['captures'][0]['status']) && isset($result['purchase_units'][0]['payments']['captures'][0]['seller_protection']['status'])) {
                            $capture_id = $result['purchase_units'][0]['payments']['captures'][0]['id'];
                            $capture_status = $result['purchase_units'][0]['payments']['captures'][0]['status'];
                            $seller_protection_status = $result['purchase_units'][0]['payments']['captures'][0]['seller_protection']['status'];
                            $order_status_id = 0;
                            $transaction_status = '';
                            $payment_method = '';
                            $vault_id = '';
                            $vault_customer_id = '';
                            $paypal_order_data = [];

                            if (!$this->cart->hasShipping()) {
                                $seller_protection_status = 'NOT_ELIGIBLE';
                            }

                            foreach ($result['payment_source'] as $payment_source_key => $payment_source) {
                                $vault_id = ($payment_source['attributes']['vault']['id'] ?? '');
                                $vault_customer_id = ($payment_source['attributes']['vault']['customer']['id'] ?? '');
                                $payment_method = $payment_source_key;

                                break;
                            }

                            if ($capture_status == 'COMPLETED') {
                                $order_status_id = $setting['order_status']['completed']['id'];
                                $transaction_status = 'completed';
                            }

                            if ($capture_status == 'DECLINED') {
                                $order_status_id = $setting['order_status']['denied']['id'];
                                $transaction_status = 'denied';

                                $this->error['warning'] = $this->language->get('error_capture_declined');
                            }

                            if ($capture_status == 'FAILED') {
                                $this->error['warning'] = sprintf($this->language->get('error_capture_failed'), $this->url->link('information/contact', '', true));
                            }

                            if ($capture_status == 'PENDING') {
                                $order_status_id = $setting['order_status']['pending']['id'];
                                $transaction_status = 'pending';
                            }

                            if (($capture_status == 'COMPLETED') || ($capture_status == 'DECLINED') || ($capture_status == 'PENDING')) {
                                $message = sprintf($this->language->get('text_order_message'), $seller_protection_status);

                                $this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id, $message);
                            }

                            if (($capture_status == 'COMPLETED') || ($capture_status == 'DECLINED') || ($capture_status == 'PENDING')) {
                                $this->model_extension_payment_paypal->deletePayPalOrder($this->session->data['order_id']);

                                $paypal_order_data = [
                                    'order_id'           => $this->session->data['order_id'],
                                    'transaction_id'     => $capture_id,
                                    'transaction_status' => $transaction_status,
                                    'payment_method'     => $payment_method,
                                    'vault_id'           => $vault_id,
                                    'vault_customer_id'  => $vault_customer_id,
                                    'environment'        => $environment
                                ];

                                $this->model_extension_payment_paypal->addPayPalOrder($paypal_order_data);
                            }

                            if (($capture_status == 'COMPLETED') || ($capture_status == 'PENDING')) {
                                $this->load->model('checkout/subscription');

                                // Loop through any products that are subscription items
                                $subscription_products = $this->cart->getSubscriptions();

                                $order_products = $this->model_checkout_order->getProducts($this->session->data['order_id']);

                                if (isset($this->request->server['HTTP_X_REAL_IP'])) {
                                    $ip = $this->request->server['HTTP_X_REAL_IP'];
                                } elseif (isset($this->request->server['REMOTE_ADDR'])) {
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

                                if (isset($this->request->server['HTTP_USER_AGENT'])) {
                                    $user_agent = $this->request->server['HTTP_USER_AGENT'];
                                } else {
                                    $user_agent = '';
                                }

                                if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                                    $accept_language = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
                                } else {
                                    $accept_language = '';
                                }

                                foreach ($subscription_products as $item) {
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

                                            $this->model_extension_payment_paypal->subscriptionPayment($item, $order_info, $paypal_order_data);
                                        }
                                    }
                                }
                            }

                            if (($capture_status == 'COMPLETED') || ($capture_status == 'PARTIALLY_REFUNDED') || ($capture_status == 'REFUNDED') || ($capture_status == 'PENDING')) {
                                $this->response->redirect($this->url->link('checkout/success', '', true));
                            }
                        }
                    }
                }
            }

            unset($this->session->data['paypal_order_id']);

            if ($this->error) {
                $this->session->data['error'] = $this->error['warning'];

                $this->response->redirect($this->url->link('checkout/checkout', '', true));
            }
        }

        $this->response->redirect($this->url->link('checkout/cart', '', true));
    }

    /**
     * Payment Address
     *
     * @return void
     */
    public function paymentAddress(): void
    {
        $this->load->language('extension/payment/paypal');

        $data['guest'] = $this->session->data['guest'] ?? [];
        $data['payment_address'] = $this->session->data['payment_address'] ?? [];

        $this->load->model('localisation/country');

        $data['countries'] = $this->model_localisation_country->getCountries();

        $this->load->model('account/custom_field');

        $data['custom_fields'] = $this->model_account_custom_field->getCustomFields();

        $this->response->setOutput($this->load->view('extension/payment/paypal/payment_address', $data));
    }

    /**
     * Shipping Address
     *
     * @return void
     */
    public function shippingAddress(): void
    {
        $this->load->language('extension/payment/paypal');

        $data['shipping_address'] = $this->session->data['shipping_address'] ?? [];

        $this->load->model('localisation/country');

        $data['countries'] = $this->model_localisation_country->getCountries();

        $this->load->model('account/custom_field');

        $data['custom_fields'] = $this->model_account_custom_field->getCustomFields();

        $this->response->setOutput($this->load->view('extension/payment/paypal/shipping_address', $data));
    }

    /**
     * Confirm Shipping
     *
     * @return void
     */
    public function confirmShipping(): void
    {
        $this->validateShipping($this->request->post['shipping_method']);

        $this->response->redirect($this->url->link('extension/payment/paypal/confirmOrder', '', true));
    }

    /**
     * Confirm Payment Address
     *
     * @return void
     */
    public function confirmPaymentAddress(): void
    {
        $this->load->language('extension/payment/paypal');

        $json = [];

        $json['url'] = '';

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validatePaymentAddress()) {
            $this->session->data['guest']['firstname'] = $this->request->post['firstname'];
            $this->session->data['guest']['lastname'] = $this->request->post['lastname'];
            $this->session->data['guest']['email'] = $this->request->post['email'];
            $this->session->data['guest']['telephone'] = $this->request->post['telephone'];

            if (isset($this->request->post['custom_field']['account'])) {
                $this->session->data['guest']['custom_field'] = $this->request->post['custom_field']['account'];
            } else {
                $this->session->data['guest']['custom_field'] = [];
            }

            $this->session->data['payment_address']['firstname'] = $this->request->post['firstname'];
            $this->session->data['payment_address']['lastname'] = $this->request->post['lastname'];
            $this->session->data['payment_address']['company'] = $this->request->post['company'];
            $this->session->data['payment_address']['address_1'] = $this->request->post['address_1'];
            $this->session->data['payment_address']['address_2'] = $this->request->post['address_2'];
            $this->session->data['payment_address']['postcode'] = $this->request->post['postcode'];
            $this->session->data['payment_address']['city'] = $this->request->post['city'];
            $this->session->data['payment_address']['country_id'] = (int)$this->request->post['country_id'];
            $this->session->data['payment_address']['zone_id'] = (int)$this->request->post['zone_id'];

            $this->load->model('localisation/country');

            $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

            if ($country_info) {
                $this->session->data['payment_address']['country'] = $country_info['name'];
                $this->session->data['payment_address']['iso_code_2'] = $country_info['iso_code_2'];
                $this->session->data['payment_address']['iso_code_3'] = $country_info['iso_code_3'];
                $this->session->data['payment_address']['address_format'] = $country_info['address_format'];
            } else {
                $this->session->data['payment_address']['country'] = '';
                $this->session->data['payment_address']['iso_code_2'] = '';
                $this->session->data['payment_address']['iso_code_3'] = '';
                $this->session->data['payment_address']['address_format'] = '';
            }

            if (isset($this->request->post['custom_field']['address'])) {
                $this->session->data['payment_address']['custom_field'] = $this->request->post['custom_field']['address'];
            } else {
                $this->session->data['payment_address']['custom_field'] = [];
            }

            $this->load->model('localisation/zone');

            $zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

            if ($zone_info) {
                $this->session->data['payment_address']['zone'] = $zone_info['name'];
                $this->session->data['payment_address']['zone_code'] = $zone_info['code'];
            } else {
                $this->session->data['payment_address']['zone'] = '';
                $this->session->data['payment_address']['zone_code'] = '';
            }

            $json['url'] = $this->url->link('extension/payment/paypal/confirmOrder', '', true);
        }

        $json['error'] = $this->error;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Confirm Shipping Address
     *
     * @return void
     */
    public function confirmShippingAddress(): void
    {
        $this->load->language('extension/payment/paypal');

        $json = [];

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateShippingAddress()) {
            $this->session->data['shipping_address']['firstname'] = $this->request->post['firstname'];
            $this->session->data['shipping_address']['lastname'] = $this->request->post['lastname'];
            $this->session->data['shipping_address']['company'] = $this->request->post['company'];
            $this->session->data['shipping_address']['address_1'] = $this->request->post['address_1'];
            $this->session->data['shipping_address']['address_2'] = $this->request->post['address_2'];
            $this->session->data['shipping_address']['postcode'] = $this->request->post['postcode'];
            $this->session->data['shipping_address']['city'] = $this->request->post['city'];
            $this->session->data['shipping_address']['country_id'] = (int)$this->request->post['country_id'];
            $this->session->data['shipping_address']['zone_id'] = (int)$this->request->post['zone_id'];

            $this->load->model('localisation/country');

            $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

            if ($country_info) {
                $this->session->data['shipping_address']['country'] = $country_info['name'];
                $this->session->data['shipping_address']['iso_code_2'] = $country_info['iso_code_2'];
                $this->session->data['shipping_address']['iso_code_3'] = $country_info['iso_code_3'];
                $this->session->data['shipping_address']['address_format'] = $country_info['address_format'];
            } else {
                $this->session->data['shipping_address']['country'] = '';
                $this->session->data['shipping_address']['iso_code_2'] = '';
                $this->session->data['shipping_address']['iso_code_3'] = '';
                $this->session->data['shipping_address']['address_format'] = '';
            }

            $this->load->model('localisation/zone');

            $zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

            if ($zone_info) {
                $this->session->data['shipping_address']['zone'] = $zone_info['name'];
                $this->session->data['shipping_address']['zone_code'] = $zone_info['code'];
            } else {
                $this->session->data['shipping_address']['zone'] = '';
                $this->session->data['shipping_address']['zone_code'] = '';
            }

            if (isset($this->request->post['custom_field'])) {
                $this->session->data['shipping_address']['custom_field'] = $this->request->post['custom_field']['address'];
            } else {
                $this->session->data['shipping_address']['custom_field'] = [];
            }

            $json['url'] = $this->url->link('extension/payment/paypal/confirmOrder', '', true);
        }

        $json['error'] = $this->error;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Webhook
     *
     * @return bool
     */
    public function webhook(): bool
    {
        if (!empty($this->request->get['webhook_token'])) {
            $_config = new \Config();
            $_config->load('paypal');

            $config_setting = $_config->get('paypal_setting');

            $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

            $webhook_info = json_decode(html_entity_decode(file_get_contents('php://input')), true);

            if (hash_equals($setting['general']['webhook_token'], $this->request->get['webhook_token']) && !empty($webhook_info['id']) && !empty($webhook_info['event_type'])) {
                $this->load->model('extension/payment/paypal');

                $this->model_extension_payment_paypal->log($webhook_info, 'Webhook');

                $webhook_event_id = $webhook_info['id'];

                $client_id = $this->config->get('payment_paypal_client_id');
                $secret = $this->config->get('payment_paypal_secret');
                $environment = $this->config->get('payment_paypal_environment');
                $partner_id = $setting['partner'][$environment]['partner_id'];
                $partner_attribution_id = $setting['partner'][$environment]['partner_attribution_id'];
                $transaction_method = $setting['general']['transaction_method'];

                require_once DIR_SYSTEM . 'library/paypal/paypal.php';

                $paypal_info = [
                    'partner_id'             => $partner_id,
                    'client_id'              => $client_id,
                    'secret'                 => $secret,
                    'environment'            => $environment,
                    'partner_attribution_id' => $partner_attribution_id
                ];

                $paypal = new PayPal($paypal_info);

                $token_info = [
                    'grant_type' => 'client_credentials'
                ];

                $paypal->setAccessToken($token_info);

                $webhook_repeat = 1;

                while ($webhook_repeat) {
                    $webhook_event = $paypal->getWebhookEvent($webhook_event_id);

                    $errors = [];

                    $webhook_repeat = 0;

                    if ($paypal->hasErrors()) {
                        $error_messages = [];

                        $errors = $paypal->getErrors();

                        foreach ($errors as $error) {
                            if (isset($error['name']) && ($error['name'] == 'CURLE_OPERATION_TIMEOUTED')) {
                                $webhook_repeat = 1;
                            }
                        }
                    }
                }

                if (isset($webhook_event['resource']['invoice_id']) && (str_contains($webhook_event['resource']['invoice_id'], '_')) && !$errors) {
                    $invoice_id = explode('_', $webhook_event['resource']['invoice_id']);
                    $order_id = reset($invoice_id);

                    $order_status_id = 0;
                    $transaction_status = '';

                    if ($webhook_event['event_type'] == 'PAYMENT.AUTHORIZATION.CREATED') {
                        $order_status_id = $setting['order_status']['pending']['id'];
                        $transaction_status = 'created';
                    }

                    if ($webhook_event['event_type'] == 'PAYMENT.AUTHORIZATION.VOIDED') {
                        $order_status_id = $setting['order_status']['voided']['id'];
                        $transaction_status = 'voided';
                    }

                    if ($webhook_event['event_type'] == 'PAYMENT.CAPTURE.COMPLETED') {
                        $order_status_id = $setting['order_status']['completed']['id'];
                        $transaction_status = 'completed';
                    }

                    if ($webhook_event['event_type'] == 'PAYMENT.CAPTURE.DENIED') {
                        $order_status_id = $setting['order_status']['denied']['id'];
                        $transaction_status = 'denied';
                    }

                    if ($webhook_event['event_type'] == 'PAYMENT.CAPTURE.PENDING') {
                        $order_status_id = $setting['order_status']['pending']['id'];
                        $transaction_status = 'pending';
                    }

                    if ($webhook_event['event_type'] == 'PAYMENT.CAPTURE.REFUNDED') {
                        $order_status_id = $setting['order_status']['refunded']['id'];
                        $transaction_status = 'refunded';
                    }

                    if ($webhook_event['event_type'] == 'PAYMENT.CAPTURE.REVERSED') {
                        $order_status_id = $setting['order_status']['reversed']['id'];
                        $transaction_status = 'reversed';
                    }

                    if ($webhook_event['event_type'] == 'CHECKOUT.ORDER.COMPLETED') {
                        $order_status_id = $setting['order_status']['completed']['id'];
                    }

                    if ($order_status_id) {
                        $this->load->model('checkout/order');

                        $this->model_checkout_order->addHistory($order_id, $order_status_id, '', true);
                    }

                    if (isset($webhook_event['resource']['id']) && $transaction_status) {
                        $transaction_id = $webhook_event['resource']['id'];

                        $paypal_order_data = [
                            'order_id'           => $order_id,
                            'transaction_status' => $transaction_status
                        ];

                        if (($transaction_status != 'refunded') && ($transaction_status != 'reversed')) {
                            $paypal_order_data['transaction_id'] = $transaction_id;
                        }

                        $this->model_extension_payment_paypal->editPayPalOrder($paypal_order_data);
                    }
                }

                header('HTTP/1.1 200 OK');

                return true;
            }
        }

        return false;
    }

    /**
     * Cron
     *
     * @return bool
     */
    public function cron(): bool
    {
        if (!empty($this->request->get['cron_token'])) {
            $_config = new \Config();
            $_config->load('paypal');

            $config_setting = $_config->get('paypal_setting');

            $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

            if (hash_equals($setting['general']['cron_token'], $this->request->get['cron_token'])) {
                $this->load->model('extension/payment/paypal');

                $this->model_extension_payment_paypal->cronPayment();

                return true;
            }
        }

        return false;
    }

    /**
     * Header Before
     *
     * @param mixed $route
     * @param mixed $data
     *
     * @return void
     */
    public function header_before($route, &$data): void
    {
        $this->load->model('extension/payment/paypal');

        $agree_status = $this->model_extension_payment_paypal->getAgreeStatus();

        if ($this->config->get('payment_paypal_status') && $this->config->get('payment_paypal_client_id') && $this->config->get('payment_paypal_secret') && $agree_status) {
            $_config = new \Config();
            $_config->load('paypal');

            $config_setting = $_config->get('paypal_setting');

            $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

            if (isset($this->request->get['route'])) {
                $route = $this->request->get['route'];
            } else {
                $route = 'common/home';
            }

            $params = [];

            if (($route == 'common/home') && $setting['message']['home']['status']) {
                $params['page_code'] = 'home';
            }

            if (($route == 'product/product') && !empty($this->request->get['product_id']) && ($setting['button']['product']['status'] || $setting['message']['product']['status'])) {
                $params['page_code'] = 'product';
                $params['product_id'] = $this->request->get['product_id'];
            }

            if (($route == 'checkout/cart') && ($setting['button']['cart']['status'] || $setting['message']['cart']['status'])) {
                $params['page_code'] = 'cart';
            }

            if (($route == 'checkout/checkout') && ($setting['button']['checkout']['status'] || $setting['googlepay_button']['status'] || $setting['applepay_button']['status'] || $setting['card']['status'] || $setting['message']['checkout']['status'])) {
                $params['page_code'] = 'checkout';
            }

            if ($params) {
                $theme = $this->config->get('theme_' . $this->config->get('config_theme') . '_directory');

                if (file_exists(DIR_TEMPLATE . $theme . '/stylesheet/paypal/paypal.css')) {
                    $this->document->addStyle('catalog/view/theme/' . $theme . '/stylesheet/paypal/paypal.css');
                } else {
                    $this->document->addStyle('catalog/view/theme/default/stylesheet/paypal/paypal.css');
                }

                if ($params['page_code'] == 'checkout') {
                    if ($setting['card']['status']) {
                        if (file_exists(DIR_TEMPLATE . $theme . '/stylesheet/paypal/card.css')) {
                            $this->document->addStyle('catalog/view/theme/' . $theme . '/stylesheet/paypal/card.css');
                        } else {
                            $this->document->addStyle('catalog/view/theme/default/stylesheet/paypal/card.css');
                        }
                    }

                    if ($setting['googlepay_button']['status']) {
                        $this->document->addScript('https://pay.google.com/gp/p/js/pay.js');
                    }

                    if ($setting['applepay_button']['status']) {
                        $this->document->addScript('https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js');
                    }
                }

                $this->document->addScript('catalog/view/javascript/paypal/paypal.js?' . http_build_query($params));
            }
        }
    }

    /**
     * Extension Get Extensions After
     *
     * @param mixed $route
     * @param mixed $data
     * @param mixed $output
     */
    public function extension_get_extensions_after($route, $data, &$output): void
    {
        if ($this->config->get('payment_paypal_status') && $this->config->get('payment_paypal_client_id') && $this->config->get('payment_paypal_secret')) {
            $type = $data[0];

            if ($type == 'payment') {
                $_config = new \Config();
                $_config->load('paypal');

                $config_setting = $_config->get('paypal_setting');

                $setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

                if (!empty($setting['paylater_country'][$setting['general']['country_code']]) && ($setting['button']['checkout']['funding']['paylater'] != 2)) {
                    $this->config->set('payment_paypal_paylater_status', 1);

                    $output[] = [
                        'extension_id' => 0,
                        'type'         => 'payment',
                        'code'         => 'paypal_paylater'
                    ];
                }

                if ($setting['googlepay_button']['status']) {
                    $this->config->set('payment_paypal_googlepay_status', 1);

                    $output[] = [
                        'extension_id' => 0,
                        'type'         => 'payment',
                        'code'         => 'paypal_googlepay'
                    ];
                }

                if ($setting['applepay_button']['status'] && $this->isApple()) {
                    $this->config->set('payment_paypal_applepay_status', 1);

                    $output[] = [
                        'extension_id' => 0,
                        'type'         => 'payment',
                        'code'         => 'paypal_applepay'
                    ];
                }
            }
        }
    }

    /**
     * Order Delete Order Before
     *
     * @param mixed $route
     * @param mixed $data
     *
     * @return void
     */
    public function order_delete_order_before(&$route, &$data): void
    {
        $this->load->model('extension/payment/paypal');

        $order_id = $data[0];

        $this->model_extension_payment_paypal->deleteOrderSubscription($order_id);
        $this->model_extension_payment_paypal->deletePayPalOrder($order_id);
        $this->model_extension_payment_paypal->deletePayPalOrderSubscription($order_id);
    }

    private function validateShipping($code)
    {
        $this->load->language('checkout/cart');
        $this->load->language('extension/payment/paypal');

        if (empty($code)) {
            $this->session->data['error_warning'] = $this->language->get('error_shipping');

            return false;
        } else {
            $shipping = explode('.', $code);

            if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
                $this->session->data['error_warning'] = $this->language->get('error_shipping');

                return false;
            } else {
                $this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
                $this->session->data['success'] = $this->language->get('text_shipping_updated');

                return true;
            }
        }
    }

    private function validatePaymentAddress()
    {
        if ((oc_strlen(trim($this->request->post['firstname'])) < 1) || (oc_strlen(trim($this->request->post['firstname'])) > 32)) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if ((oc_strlen(trim($this->request->post['lastname'])) < 1) || (oc_strlen(trim($this->request->post['lastname'])) > 32)) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if ((oc_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = $this->language->get('error_email');
        }

        if ((oc_strlen($this->request->post['telephone']) < 3) || (oc_strlen($this->request->post['telephone']) > 32)) {
            $this->error['telephone'] = $this->language->get('error_telephone');
        }

        if ((oc_strlen(trim($this->request->post['address_1'])) < 3) || (oc_strlen(trim($this->request->post['address_1'])) > 128)) {
            $this->error['address_1'] = $this->language->get('error_address_1');
        }

        if ((oc_strlen(trim($this->request->post['city'])) < 2) || (oc_strlen(trim($this->request->post['city'])) > 128)) {
            $this->error['city'] = $this->language->get('error_city');
        }

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

        if ($country_info && $country_info['postcode_required'] && (oc_strlen(trim($this->request->post['postcode'])) < 2 || oc_strlen(trim($this->request->post['postcode'])) > 10)) {
            $this->error['postcode'] = $this->language->get('error_postcode');
        }

        if ($this->request->post['country_id'] == '') {
            $this->error['country'] = $this->language->get('error_country');
        }

        if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
            $this->error['zone'] = $this->language->get('error_zone');
        }

        // Customer Group
        if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], (array)$this->config->get('config_customer_group_display'))) {
            $customer_group_id = (int)$this->request->post['customer_group_id'];
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        // Custom field validation
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                $this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
            } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $custom_field['validation']]])) {
                $this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
            }
        }

        return !$this->error;
    }

    private function validateShippingAddress()
    {
        if ((oc_strlen(trim($this->request->post['firstname'])) < 1) || (oc_strlen(trim($this->request->post['firstname'])) > 32)) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if ((oc_strlen(trim($this->request->post['lastname'])) < 1) || (oc_strlen(trim($this->request->post['lastname'])) > 32)) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if ((oc_strlen(trim($this->request->post['address_1'])) < 3) || (oc_strlen(trim($this->request->post['address_1'])) > 128)) {
            $this->error['address_1'] = $this->language->get('error_address_1');
        }

        if ((oc_strlen(trim($this->request->post['city'])) < 2) || (oc_strlen(trim($this->request->post['city'])) > 128)) {
            $this->error['city'] = $this->language->get('error_city');
        }

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

        if ($country_info && $country_info['postcode_required'] && (oc_strlen(trim($this->request->post['postcode'])) < 2 || oc_strlen(trim($this->request->post['postcode'])) > 10)) {
            $this->error['postcode'] = $this->language->get('error_postcode');
        }

        if ($this->request->post['country_id'] == '') {
            $this->error['country'] = $this->language->get('error_country');
        }

        if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
            $this->error['zone'] = $this->language->get('error_zone');
        }

        // Customer Group
        if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
            $customer_group_id = (int)$this->request->post['customer_group_id'];
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        // Custom field validation
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'address') {
                if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                    $this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $custom_field['validation']]])) {
                    $this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                }
            }
        }

        return !$this->error;
    }

    private function validateCoupon()
    {
        $this->load->model('extension/total/coupon');

        $coupon_info = $this->model_extension_total_coupon->getCoupon($this->request->post['coupon']);

        if ($coupon_info) {
            return true;
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_coupon');

            return false;
        }
    }

    private function validateVoucher()
    {
        $this->load->model('extension/total/voucher');

        $voucher_info = $this->model_extension_total_voucher->getVoucher($this->request->post['voucher']);

        if ($voucher_info) {
            return true;
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_voucher');

            return false;
        }
    }

    private function validateReward()
    {
        $points = $this->customer->getRewardPoints();

        $points_total = 0;

        foreach ($this->cart->getProducts() as $product) {
            if ($product['points']) {
                $points_total += $product['points'];
            }
        }

        $error = '';

        if (empty($this->request->post['reward'])) {
            $error = $this->language->get('error_reward');
        }

        if ($this->request->post['reward'] > $points) {
            $error = sprintf($this->language->get('error_points'), $this->request->post['reward']);
        }

        if ($this->request->post['reward'] > $points_total) {
            $error = sprintf($this->language->get('error_maximum'), $points_total);
        }

        if (!$error) {
            return true;
        } else {
            $this->session->data['error_warning'] = $error;

            return false;
        }
    }

    private function isApple()
    {
        if (!empty($this->request->server['HTTP_USER_AGENT'])) {
            $user_agent = $this->request->server['HTTP_USER_AGENT'];

            $apple_agents = ['ipod', 'iphone', 'ipad'];

            foreach ($apple_agents as $apple_agent) {
                if (stripos($user_agent, $apple_agent)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function unserialize($str)
    {
        $data = [];

        $str = str_replace('&amp;', '&', $str);

        parse_str($str, $data);

        return $data;
    }
}
