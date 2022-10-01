<?php
class ControllerExtensionPaymentPilibaba extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/payment/pilibaba');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('extension/payment/pilibaba');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_pilibaba', $this->request->post);

            if ($this->request->post['payment_pilibaba_status']) {
                $this->model_extension_payment_pilibaba->enablePiliExpress();
            } else {
                $this->model_extension_payment_pilibaba->disablePiliExpress();
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/pilibaba', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['action'] = $this->url->link('extension/payment/pilibaba', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        if (isset($this->request->post['payment_pilibaba_merchant_number'])) {
            $data['payment_pilibaba_merchant_number'] = $this->request->post['payment_pilibaba_merchant_number'];
        } else {
            $data['payment_pilibaba_merchant_number'] = $this->config->get('payment_pilibaba_merchant_number');
        }

        if (isset($this->request->post['payment_pilibaba_secret_key'])) {
            $data['payment_pilibaba_secret_key'] = $this->request->post['payment_pilibaba_secret_key'];
        } else {
            $data['payment_pilibaba_secret_key'] = $this->config->get('payment_pilibaba_secret_key');
        }

        if (isset($this->request->post['payment_pilibaba_environment'])) {
            $data['payment_pilibaba_environment'] = $this->request->post['payment_pilibaba_environment'];
        } else {
            $data['payment_pilibaba_environment'] = $this->config->get('payment_pilibaba_environment');
        }

        if (isset($this->request->post['payment_pilibaba_shipping_fee'])) {
            $data['payment_pilibaba_shipping_fee'] = $this->request->post['payment_pilibaba_shipping_fee'];
        } else {
            $data['payment_pilibaba_shipping_fee'] = $this->config->get('payment_pilibaba_shipping_fee');
        }

        if (isset($this->request->post['payment_pilibaba_order_status_id'])) {
            $data['payment_pilibaba_order_status_id'] = (int)$this->request->post['payment_pilibaba_order_status_id'];
        } elseif ($this->config->has('payment_pilibaba_order_status_id')) {
            $data['payment_pilibaba_order_status_id'] = $this->config->get('payment_pilibaba_order_status_id');
        } else {
            $data['payment_pilibaba_order_status_id'] = '2';
        }

        if (isset($this->request->post['payment_pilibaba_status'])) {
            $data['payment_pilibaba_status'] = $this->request->post['payment_pilibaba_status'];
        } else {
            $data['payment_pilibaba_status'] = $this->config->get('payment_pilibaba_status');
        }

        if (isset($this->request->post['payment_pilibaba_logging'])) {
            $data['payment_pilibaba_logging'] = $this->request->post['payment_pilibaba_logging'];
        } else {
            $data['payment_pilibaba_logging'] = $this->config->get('payment_pilibaba_logging');
        }

        if (isset($this->request->post['payment_pilibaba_sort_order'])) {
            $data['payment_pilibaba_sort_order'] = $this->request->post['payment_pilibaba_sort_order'];
        } else {
            $data['payment_pilibaba_sort_order'] = $this->config->get('payment_pilibaba_sort_order');
        }

        if (isset($this->request->post['payment_pilibaba_email_address'])) {
            $data['payment_pilibaba_email_address'] = $this->request->post['payment_pilibaba_email_address'];
        } elseif ($this->config->has('payment_pilibaba_email_address')) {
            $data['payment_pilibaba_email_address'] = $this->config->get('payment_pilibaba_email_address');
        } else {
            $data['payment_pilibaba_email_address'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['pilibaba_merchant_number'])) {
            $data['error_pilibaba_merchant_number'] = $this->error['pilibaba_merchant_number'];
        } else {
            $data['error_pilibaba_merchant_number'] = '';
        }

        if (isset($this->error['pilibaba_secret_key'])) {
            $data['error_pilibaba_secret_key'] = $this->error['pilibaba_secret_key'];
        } else {
            $data['error_pilibaba_secret_key'] = '';
        }

        if (isset($this->error['pilibaba_shipping_fee'])) {
            $data['error_pilibaba_shipping_fee'] = $this->error['pilibaba_shipping_fee'];
        } else {
            $data['error_pilibaba_shipping_fee'] = '';
        }

        if (isset($data['payment_pilibaba_merchant_number']) && $data['payment_pilibaba_merchant_number'] && isset($data['payment_pilibaba_secret_key']) && $data['payment_pilibaba_secret_key']) {
            $data['show_register'] = false;

            $data['currencies'] = $data['warehouses'] = $data['countries'] = [];
        } else {
            $data['show_register'] = true;

            $data['currencies'] = $this->model_extension_payment_pilibaba->getCurrencies();

            $data['warehouses'] = $this->model_extension_payment_pilibaba->getWarehouses();

            // Countries
            $this->load->model('localisation/country');

            $data['countries'] = $this->model_localisation_country->getCountries();
        }

        // Order Statuses
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if ($this->config->get('config_weight_class_id') != '2') {
            $data['error_weight'] = sprintf($this->language->get('error_weight'), $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true));
        } else {
            $data['error_weight'] = '';
        }

        if ($this->config->has('payment_pilibaba_email_address') && $this->config->get('payment_pilibaba_email_address')) {
            $data['notice_email'] = sprintf($this->language->get('text_email'), $this->config->get('payment_pilibaba_email_address'));
        } else {
            $data['notice_email'] = '';
        }

        $data['user_token'] = $this->session->data['user_token'];

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/pilibaba', $data));
    }

    public function install(): void {
        if ($this->user->hasPermission('modify', 'marketplace/extension')) {
            $this->load->model('extension/payment/pilibaba');

            $this->model_extension_payment_pilibaba->install();
        }
    }

    public function uninstall(): void {
        if ($this->user->hasPermission('modify', 'marketplace/extension')) {
            $this->load->model('extension/payment/pilibaba');

            $this->model_extension_payment_pilibaba->uninstall();
        }
    }

    public function register(): void {
        $this->load->language('extension/payment/pilibaba');

        $json = [];

        if (isset($this->request->post['email_address']) && isset($this->request->post['password']) && isset($this->request->post['currency']) && isset($this->request->post['warehouse']) && isset($this->request->post['country']) && isset($this->request->post['environment'])) {
            if (utf8_strlen($this->request->post['email_address']) < 1) {
                $json['error'] = $this->language->get('error_email_address');
            } elseif (!filter_var($this->request->post['email_address'], FILTER_VALIDATE_EMAIL)) {
                $json['error'] = $this->language->get('error_email_invalid');
            } elseif (utf8_strlen($this->request->post['password']) < 8) {
                $json['error'] = $this->language->get('error_password');
            } elseif (utf8_strlen($this->request->post['currency']) < 1) {
                $json['error'] = $this->language->get('error_currency');
            } elseif (utf8_strlen($this->request->post['warehouse']) < 1) {
                $json['error'] = $this->language->get('error_warehouse');
            } elseif ($this->request->post['warehouse'] == 'other' && utf8_strlen($this->request->post['country']) < 1) {
                $json['error'] = $this->language->get('error_country');
            } else {
                $this->load->model('extension/payment/pilibaba');

                $response = $this->model_extension_payment_pilibaba->register($this->request->post['email_address'], $this->request->post['password'], $this->request->post['currency'], $this->request->post['warehouse'], $this->request->post['country'], $this->request->post['environment']);

                if (isset($response['code']) && isset($response['message'])) {
                    if ($response['code'] == '0') {
                        $this->load->model('setting/setting');

                        $this->model_setting_setting->editSetting('payment_pilibaba', ['pilibaba_merchant_number'     => $response['data']['merchantNo'],
                                                                                       'pilibaba_secret_key'          => $response['data']['privateKey'],
                                                                                       'pilibaba_email_address'       => $this->request->post['email_address'],
                                                                                       'payment_pilibaba_environment' => $this->request->post['environment']
                        ], 0);

                        $this->session->data['success'] = $this->language->get('text_register_success');

                        $json['redirect'] = $this->url->link('extension/payment/pilibaba', 'user_token=' . $this->session->data['user_token'], true);
                    } else {
                        $json['error'] = $response['message'];
                    }
                } else {
                    $json['error'] = $this->language->get('error_bad_response');
                }
            }
        } else {
            $json['error'] = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function order(): string {
        $view = '';

        if ($this->config->get('payment_pilibaba_status')) {
            $this->load->model('extension/payment/pilibaba');

            $order_id = (int)$this->request->get['order_id'];

            $pilibaba_order = $this->model_extension_payment_pilibaba->getOrder($this->request->get['order_id']);

            if ($pilibaba_order) {
                $this->load->language('extension/payment/pilibaba');

                $order_info['order_id']   = $pilibaba_order['order_id'];
                $order_info['amount']     = '&yen;' . $pilibaba_order['amount'];
                $order_info['fee']        = '&yen;' . $pilibaba_order['fee'];
                $order_info['status']     = $this->language->get('text_status');
                $order_info['date_added'] = date($this->language->get('datetime_format'), strtotime($pilibaba_order['date_added']));
                $order_info['tracking']   = $pilibaba_order['tracking'];
                $data['pilibaba_order']   = $order_info;
                $data['barcode']          = $this->url->link('extension/payment/pilibaba/barcode', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $this->request->get['order_id'], true);
                $data['order_id']         = $order_id;
                $data['user_token']       = $this->session->data['user_token'];

                $view = $this->load->view('extension/payment/pilibaba_order', $data);
            }
        }

        return $view;
    }

    public function tracking(): void {
        $this->load->language('extension/payment/pilibaba');

        $json = [];

        if ($this->config->get('payment_pilibaba_status')) {
            if (isset($this->request->post['order_id']) && isset($this->request->post['tracking'])) {
                if (utf8_strlen($this->request->post['tracking']) > 0 && utf8_strlen($this->request->post['tracking']) <= 50) {
                    $this->load->model('extension/payment/pilibaba');

                    $this->model_extension_payment_pilibaba->updateTrackingNumber($this->request->post['order_id'], $this->request->post['tracking'], $this->config->get('payment_pilibaba_merchant_number'));

                    $json['success'] = $this->language->get('text_tracking_success');
                } else {
                    $json['error'] = $this->language->get('error_tracking_length');
                }
            } else {
                $json['error'] = $this->language->get('error_data_missing');
            }
        } else {
            $json['error'] = $this->language->get('error_not_enabled');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function barcode(): void {
        if ($this->config->get('payment_pilibaba_status')) {
            if (isset($this->request->get['order_id'])) {
                if ($this->config->get('payment_pilibaba_environment') == 'live') {
                    $url = 'https://www.pilibaba.com/pilipay/barCode';
                } else {
                    $url = 'http://pre.pilibaba.com/pilipay/barCode';
                }

                echo '<img src="' . $url . '?orderNo=' . $this->request->get['order_id'] . '&merchantNo=' . $this->config->get('payment_pilibaba_merchant_number') . '">';
            }
        }
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/pilibaba')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_pilibaba_merchant_number']) {
            $this->error['pilibaba_merchant_number'] = $this->language->get('error_merchant_number');
        }

        if (!$this->request->post['payment_pilibaba_secret_key']) {
            $this->error['pilibaba_secret_key'] = $this->language->get('error_secret_key');
        }

        if ($this->request->post['payment_pilibaba_shipping_fee'] != '' && strpos($this->request->post['payment_pilibaba_shipping_fee'], '.') === false) {
            $this->error['pilibaba_shipping_fee'] = $this->language->get('error_shipping_fee');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}