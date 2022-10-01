<?php
class ControllerExtensionPaymentRealex extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/payment/realex');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_realex', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['notify_url'] = HTTPS_CATALOG . 'index.php?route=extension/payment/realex/notify';

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['error_merchant_id'])) {
            $data['error_merchant_id'] = $this->error['error_merchant_id'];
        } else {
            $data['error_merchant_id'] = '';
        }

        if (isset($this->error['error_secret'])) {
            $data['error_secret'] = $this->error['error_secret'];
        } else {
            $data['error_secret'] = '';
        }

        if (isset($this->error['error_live_url'])) {
            $data['error_live_url'] = $this->error['error_live_url'];
        } else {
            $data['error_live_url'] = '';
        }

        if (isset($this->error['error_demo_url'])) {
            $data['error_demo_url'] = $this->error['error_demo_url'];
        } else {
            $data['error_demo_url'] = '';
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
            'href' => $this->url->link('extension/payment/realex', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['action'] = $this->url->link('extension/payment/realex', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        if (isset($this->request->post['payment_realex_merchant_id'])) {
            $data['payment_realex_merchant_id'] = $this->request->post['payment_realex_merchant_id'];
        } else {
            $data['payment_realex_merchant_id'] = $this->config->get('payment_realex_merchant_id');
        }

        if (isset($this->request->post['payment_realex_secret'])) {
            $data['payment_realex_secret'] = $this->request->post['payment_realex_secret'];
        } else {
            $data['payment_realex_secret'] = $this->config->get('payment_realex_secret');
        }

        if (isset($this->request->post['payment_realex_rebate_password'])) {
            $data['payment_realex_rebate_password'] = $this->request->post['payment_realex_rebate_password'];
        } else {
            $data['payment_realex_rebate_password'] = $this->config->get('payment_realex_rebate_password');
        }

        if (isset($this->request->post['payment_realex_live_demo'])) {
            $data['payment_realex_live_demo'] = $this->request->post['payment_realex_live_demo'];
        } else {
            $data['payment_realex_live_demo'] = $this->config->get('payment_realex_live_demo');
        }

        if (isset($this->request->post['payment_realex_geo_zone_id'])) {
            $data['payment_realex_geo_zone_id'] = (int)$this->request->post['payment_realex_geo_zone_id'];
        } else {
            $data['payment_realex_geo_zone_id'] = $this->config->get('payment_realex_geo_zone_id');
        }

        // Geo Zones
        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_realex_total'])) {
            $data['payment_realex_total'] = $this->request->post['payment_realex_total'];
        } else {
            $data['payment_realex_total'] = $this->config->get('payment_realex_total');
        }

        if (isset($this->request->post['payment_realex_sort_order'])) {
            $data['payment_realex_sort_order'] = $this->request->post['payment_realex_sort_order'];
        } else {
            $data['payment_realex_sort_order'] = $this->config->get('payment_realex_sort_order');
        }

        if (isset($this->request->post['payment_realex_status'])) {
            $data['payment_realex_status'] = $this->request->post['payment_realex_status'];
        } else {
            $data['payment_realex_status'] = $this->config->get('payment_realex_status');
        }

        if (isset($this->request->post['payment_realex_debug'])) {
            $data['payment_realex_debug'] = $this->request->post['payment_realex_debug'];
        } else {
            $data['payment_realex_debug'] = $this->config->get('payment_realex_debug');
        }

        if (isset($this->request->post['payment_realex_account'])) {
            $data['payment_realex_account'] = $this->request->post['payment_realex_account'];
        } else {
            $data['payment_realex_account'] = $this->config->get('payment_realex_account');
        }

        if (isset($this->request->post['payment_realex_auto_settle'])) {
            $data['payment_realex_auto_settle'] = $this->request->post['payment_realex_auto_settle'];
        } else {
            $data['payment_realex_auto_settle'] = $this->config->get('payment_realex_auto_settle');
        }

        if (isset($this->request->post['payment_realex_card_select'])) {
            $data['payment_realex_card_select'] = $this->request->post['payment_realex_card_select'];
        } else {
            $data['payment_realex_card_select'] = $this->config->get('payment_realex_card_select');
        }

        if (isset($this->request->post['payment_realex_tss_check'])) {
            $data['payment_realex_tss_check'] = $this->request->post['payment_realex_tss_check'];
        } else {
            $data['payment_realex_tss_check'] = $this->config->get('payment_realex_tss_check');
        }

        if (isset($this->request->post['payment_realex_order_status_success_settled_id'])) {
            $data['payment_realex_order_status_success_settled_id'] = (int)$this->request->post['payment_realex_order_status_success_settled_id'];
        } else {
            $data['payment_realex_order_status_success_settled_id'] = $this->config->get('payment_realex_order_status_success_settled_id');
        }

        if (isset($this->request->post['payment_realex_order_status_success_unsettled_id'])) {
            $data['payment_realex_order_status_success_unsettled_id'] = (int)$this->request->post['payment_realex_order_status_success_unsettled_id'];
        } else {
            $data['payment_realex_order_status_success_unsettled_id'] = $this->config->get('payment_realex_order_status_success_unsettled_id');
        }

        if (isset($this->request->post['payment_realex_order_status_decline_id'])) {
            $data['payment_realex_order_status_decline_id'] = (int)$this->request->post['payment_realex_order_status_decline_id'];
        } else {
            $data['payment_realex_order_status_decline_id'] = $this->config->get('payment_realex_order_status_decline_id');
        }

        if (isset($this->request->post['payment_realex_order_status_decline_pending_id'])) {
            $data['payment_realex_order_status_decline_pending_id'] = (int)$this->request->post['payment_realex_order_status_decline_pending_id'];
        } else {
            $data['payment_realex_order_status_decline_pending_id'] = $this->config->get('payment_realex_order_status_decline_pending_id');
        }

        if (isset($this->request->post['payment_realex_order_status_decline_stolen_id'])) {
            $data['payment_realex_order_status_decline_stolen_id'] = (int)$this->request->post['payment_realex_order_status_decline_stolen_id'];
        } else {
            $data['payment_realex_order_status_decline_stolen_id'] = $this->config->get('payment_realex_order_status_decline_stolen_id');
        }

        if (isset($this->request->post['payment_realex_order_status_decline_bank_id'])) {
            $data['payment_realex_order_status_decline_bank_id'] = (int)$this->request->post['payment_realex_order_status_decline_bank_id'];
        } else {
            $data['payment_realex_order_status_decline_bank_id'] = $this->config->get('payment_realex_order_status_decline_bank_id');
        }

        // Order Statuses
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_realex_live_url'])) {
            $data['payment_realex_live_url'] = $this->request->post['payment_realex_live_url'];
        } else {
            $data['payment_realex_live_url'] = $this->config->get('payment_realex_live_url');
        }

        if (empty($data['payment_realex_live_url'])) {
            $data['payment_realex_live_url'] = 'https://hpp.realexpayments.com/pay';
        }

        if (isset($this->request->post['payment_realex_demo_url'])) {
            $data['payment_realex_demo_url'] = $this->request->post['payment_realex_demo_url'];
        } else {
            $data['payment_realex_demo_url'] = $this->config->get('payment_realex_demo_url');
        }

        if (empty($data['payment_realex_demo_url'])) {
            $data['payment_realex_demo_url'] = 'https://hpp.sandbox.realexpayments.com/pay';
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/realex', $data));
    }

    public function install(): void {
        $this->load->model('extension/payment/realex');

        $this->model_extension_payment_realex->install();
    }

    public function order(): string {
        $view = '';

        if ($this->config->get('payment_realex_status')) {
            $this->load->model('extension/payment/realex');

            $payment_realex_order = $this->model_extension_payment_realex->getOrder($this->request->get['order_id']);

            if (!empty($payment_realex_order)) {
                $this->load->language('extension/payment/realex');

                $payment_realex_order['total_captured_formatted'] = $this->currency->format($payment_realex_order['total_captured'], $payment_realex_order['currency_code'], 1, true);
                $payment_realex_order['total_formatted']          = $this->currency->format($payment_realex_order['total'], $payment_realex_order['currency_code'], 1, true);
                $payment_realex_order['total_captured']           = $this->model_extension_payment_realex->getTotalCaptured($payment_realex_order['realex_order_id']);

                $data['payment_realex_order'] = $payment_realex_order;
                $data['auto_settle']          = $payment_realex_order['settle_type'];
                $data['order_id']             = (int)$this->request->get['order_id'];
                $data['user_token']           = $this->session->data['user_token'];

                $view = $this->load->view('extension/payment/realex_order', $data);
            }
        }

        return $view;
    }

    public function void(): void {
        $this->load->language('extension/payment/realex');

        $json = [];

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
            $this->load->model('extension/payment/realex');

            $payment_realex_order = $this->model_extension_payment_realex->getOrder($this->request->post['order_id']);

            $void_response = $this->model_extension_payment_realex->void($this->request->post['order_id']);

            $this->model_extension_payment_realex->logger('Void result:\r\n' . print_r($void_response, 1));

            if (isset($void_response->result) && $void_response->result == '00') {
                $this->model_extension_payment_realex->addTransaction($payment_realex_order['realex_order_id'], 'void', 0.00);
                $this->model_extension_payment_realex->updateVoidStatus($payment_realex_order['realex_order_id'], 1);

                $json['msg'] = $this->language->get('text_void_ok');

                $json['data'] = [];

                $json['data']['date_added'] = date('Y-m-d H:i:s');

                $json['error'] = false;
            } else {
                $json['error'] = true;

                $json['msg'] = (isset($void_response->message) && !empty($void_response->message) ? sprintf($this->language->get('error_status'), (string)$void_response->message) : $this->language->get('error_void'));
            }
        } else {
            $json['error'] = true;

            $json['msg'] = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function capture(): void {
        $this->load->language('extension/payment/realex');

        $json = [];

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '' && isset($this->request->post['amount']) && $this->request->post['amount'] > 0) {
            $this->load->model('extension/payment/realex');

            $payment_realex_order = $this->model_extension_payment_realex->getOrder($this->request->post['order_id']);

            $capture_response = $this->model_extension_payment_realex->capture($this->request->post['order_id'], $this->request->post['amount']);

            $this->model_extension_payment_realex->logger('Settle result:\r\n' . print_r($capture_response, 1));

            if (isset($capture_response->result) && $capture_response->result == '00') {
                $this->model_extension_payment_realex->addTransaction($payment_realex_order['realex_order_id'], 'payment', $this->request->post['amount']);

                $total_captured = $this->model_extension_payment_realex->getTotalCaptured($payment_realex_order['realex_order_id']);

                if ($total_captured >= $payment_realex_order['total'] || $payment_realex_order['settle_type'] == 0) {
                    $this->model_extension_payment_realex->updateCaptureStatus($payment_realex_order['realex_order_id'], 1);

                    $capture_status = 1;

                    $json['msg'] = $this->language->get('text_capture_ok_order');
                } else {
                    $capture_status = 0;

                    $json['msg'] = $this->language->get('text_capture_ok');
                }

                $this->model_extension_payment_realex->updateForRebate($payment_realex_order['realex_order_id'], $capture_response->pasref, $capture_response->orderid);

                $json['data'] = [];

                $json['data']['date_added']     = date('Y-m-d H:i:s');
                $json['data']['amount']         = $this->request->post['amount'];
                $json['data']['capture_status'] = $capture_status;
                $json['data']['total']          = (float)$total_captured;

                $json['error'] = false;
            } else {
                $json['error'] = true;

                $json['msg'] = (isset($capture_response->message) && !empty($capture_response->message) ? sprintf($this->language->get('error_status'), (string)$capture_response->message) : $this->language->get('error_capture'));
            }
        } else {
            $json['error'] = true;

            $json['msg'] = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function rebate(): void {
        $this->load->language('extension/payment/realex');

        $json = [];

        if (isset($this->request->post['order_id'])) {
            $this->load->model('extension/payment/realex');

            $payment_realex_order = $this->model_extension_payment_realex->getOrder($this->request->post['order_id']);

            $rebate_response = $this->model_extension_payment_realex->rebate($this->request->post['order_id'], $this->request->post['amount']);

            $this->model_extension_payment_realex->logger('Rebate result:\r\n' . print_r($rebate_response, 1));

            if (isset($rebate_response->result) && $rebate_response->result == '00') {
                $this->model_extension_payment_realex->addTransaction($payment_realex_order['realex_order_id'], 'rebate', $this->request->post['amount'] * -1);

                $total_rebated  = $this->model_extension_payment_realex->getTotalRebated($payment_realex_order['realex_order_id']);
                $total_captured = $this->model_extension_payment_realex->getTotalCaptured($payment_realex_order['realex_order_id']);

                if ($total_captured <= 0 && $payment_realex_order['capture_status'] == 1) {
                    $this->model_extension_payment_realex->updateRebateStatus($payment_realex_order['realex_order_id'], 1);
                    $rebate_status = 1;

                    $json['msg'] = $this->language->get('text_rebate_ok_order');
                } else {
                    $rebate_status = 0;

                    $json['msg'] = $this->language->get('text_rebate_ok');
                }

                $json['data'] = [];

                $json['data']['date_added']     = date('Y-m-d H:i:s');
                $json['data']['amount']         = $this->request->post['amount'] * -1;
                $json['data']['total_captured'] = (float)$total_captured;
                $json['data']['total_rebated']  = (float)$total_rebated;
                $json['data']['rebate_status']  = $rebate_status;

                $json['error'] = false;
            } else {
                $json['error'] = true;

                $json['msg'] = (isset($rebate_response->message) && !empty($rebate_response->message) ? sprintf($this->language->get('error_status'), (string)$rebate_response->message) : $this->language->get('error_rebate'));
            }
        } else {
            $json['error'] = true;

            $json['msg'] = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/realex')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_realex_merchant_id']) {
            $this->error['error_merchant_id'] = $this->language->get('error_merchant_id');
        }

        if (!$this->request->post['payment_realex_secret']) {
            $this->error['error_secret'] = $this->language->get('error_secret');
        }

        if (!$this->request->post['payment_realex_live_url']) {
            $this->error['error_live_url'] = $this->language->get('error_live_url');
        }

        if (!$this->request->post['payment_realex_demo_url']) {
            $this->error['error_demo_url'] = $this->language->get('error_demo_url');
        }

        return !$this->error;
    }
}