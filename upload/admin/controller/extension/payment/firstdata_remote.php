<?php
class ControllerExtensionPaymentFirstdataRemote extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/payment/firstdata_remote');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_firstdata_remote', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        // Order Statuses
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Geo Zones
        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

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

        if (isset($this->error['error_user_id'])) {
            $data['error_user_id'] = $this->error['error_user_id'];
        } else {
            $data['error_user_id'] = '';
        }

        if (isset($this->error['error_password'])) {
            $data['error_password'] = $this->error['error_password'];
        } else {
            $data['error_password'] = '';
        }

        if (isset($this->error['error_certificate'])) {
            $data['error_certificate'] = $this->error['error_certificate'];
        } else {
            $data['error_certificate'] = '';
        }

        if (isset($this->error['error_key'])) {
            $data['error_key'] = $this->error['error_key'];
        } else {
            $data['error_key'] = '';
        }

        if (isset($this->error['error_key_pw'])) {
            $data['error_key_pw'] = $this->error['error_key_pw'];
        } else {
            $data['error_key_pw'] = '';
        }

        if (isset($this->error['error_ca'])) {
            $data['error_ca'] = $this->error['error_ca'];
        } else {
            $data['error_ca'] = '';
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/firstdata_remote', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        ];

        $data['action'] = $this->url->link('extension/payment/firstdata_remote', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        if (isset($this->request->post['payment_firstdata_remote_merchant_id'])) {
            $data['payment_firstdata_remote_merchant_id'] = $this->request->post['payment_firstdata_remote_merchant_id'];
        } else {
            $data['payment_firstdata_remote_merchant_id'] = $this->config->get('payment_firstdata_remote_merchant_id');
        }

        if (isset($this->request->post['payment_firstdata_remote_user_id'])) {
            $data['payment_firstdata_remote_user_id'] = $this->request->post['payment_firstdata_remote_user_id'];
        } else {
            $data['payment_firstdata_remote_user_id'] = $this->config->get('payment_firstdata_remote_user_id');
        }

        if (isset($this->request->post['payment_firstdata_remote_password'])) {
            $data['payment_firstdata_remote_password'] = $this->request->post['payment_firstdata_remote_password'];
        } else {
            $data['payment_firstdata_remote_password'] = $this->config->get('payment_firstdata_remote_password');
        }

        if (isset($this->request->post['payment_firstdata_remote_certificate'])) {
            $data['payment_firstdata_remote_certificate'] = $this->request->post['payment_firstdata_remote_certificate'];
        } else {
            $data['payment_firstdata_remote_certificate'] = $this->config->get('payment_firstdata_remote_certificate');
        }

        if (isset($this->request->post['payment_firstdata_remote_key'])) {
            $data['payment_firstdata_remote_key'] = $this->request->post['payment_firstdata_remote_key'];
        } else {
            $data['payment_firstdata_remote_key'] = $this->config->get('payment_firstdata_remote_key');
        }

        if (isset($this->request->post['payment_firstdata_remote_key_pw'])) {
            $data['payment_firstdata_remote_key_pw'] = $this->request->post['payment_firstdata_remote_key_pw'];
        } else {
            $data['payment_firstdata_remote_key_pw'] = $this->config->get('payment_firstdata_remote_key_pw');
        }

        if (isset($this->request->post['payment_firstdata_remote_ca'])) {
            $data['payment_firstdata_remote_ca'] = $this->request->post['payment_firstdata_remote_ca'];
        } else {
            $data['payment_firstdata_remote_ca'] = $this->config->get('payment_firstdata_remote_ca');
        }

        if (isset($this->request->post['payment_firstdata_remote_geo_zone_id'])) {
            $data['payment_firstdata_remote_geo_zone_id'] = (int)$this->request->post['payment_firstdata_remote_geo_zone_id'];
        } else {
            $data['payment_firstdata_remote_geo_zone_id'] = $this->config->get('payment_firstdata_remote_geo_zone_id');
        }

        if (isset($this->request->post['payment_firstdata_remote_total'])) {
            $data['payment_firstdata_remote_total'] = $this->request->post['payment_firstdata_remote_total'];
        } else {
            $data['payment_firstdata_remote_total'] = $this->config->get('payment_firstdata_remote_total');
        }

        if (isset($this->request->post['payment_firstdata_remote_sort_order'])) {
            $data['payment_firstdata_remote_sort_order'] = $this->request->post['payment_firstdata_remote_sort_order'];
        } else {
            $data['payment_firstdata_remote_sort_order'] = $this->config->get('payment_firstdata_remote_sort_order');
        }

        if (isset($this->request->post['payment_firstdata_remote_status'])) {
            $data['payment_firstdata_remote_status'] = $this->request->post['payment_firstdata_remote_status'];
        } else {
            $data['payment_firstdata_remote_status'] = $this->config->get('payment_firstdata_remote_status');
        }

        if (isset($this->request->post['payment_firstdata_remote_debug'])) {
            $data['payment_firstdata_remote_debug'] = $this->request->post['payment_firstdata_remote_debug'];
        } else {
            $data['payment_firstdata_remote_debug'] = $this->config->get('payment_firstdata_remote_debug');
        }

        if (isset($this->request->post['payment_firstdata_remote_auto_settle'])) {
            $data['payment_firstdata_remote_auto_settle'] = $this->request->post['payment_firstdata_remote_auto_settle'];
        } elseif (!isset($this->request->post['payment_firstdata_auto_settle']) && $this->config->get('payment_firstdata_remote_auto_settle') != '') {
            $data['payment_firstdata_remote_auto_settle'] = $this->config->get('payment_firstdata_remote_auto_settle');
        } else {
            $data['payment_firstdata_remote_auto_settle'] = 1;
        }

        if (isset($this->request->post['payment_firstdata_remote_order_status_success_settled_id'])) {
            $data['payment_firstdata_remote_order_status_success_settled_id'] = (int)$this->request->post['payment_firstdata_remote_order_status_success_settled_id'];
        } else {
            $data['payment_firstdata_remote_order_status_success_settled_id'] = $this->config->get('payment_firstdata_remote_order_status_success_settled_id');
        }

        if (isset($this->request->post['payment_firstdata_remote_order_status_success_unsettled_id'])) {
            $data['payment_firstdata_remote_order_status_success_unsettled_id'] = (int)$this->request->post['payment_firstdata_remote_order_status_success_unsettled_id'];
        } else {
            $data['payment_firstdata_remote_order_status_success_unsettled_id'] = $this->config->get('payment_firstdata_remote_order_status_success_unsettled_id');
        }

        if (isset($this->request->post['payment_firstdata_remote_order_status_decline_id'])) {
            $data['payment_firstdata_remote_order_status_decline_id'] = (int)$this->request->post['payment_firstdata_remote_order_status_decline_id'];
        } else {
            $data['payment_firstdata_remote_order_status_decline_id'] = $this->config->get('payment_firstdata_remote_order_status_decline_id');
        }

        if (isset($this->request->post['payment_firstdata_remote_card_storage'])) {
            $data['payment_firstdata_remote_card_storage'] = $this->request->post['payment_firstdata_remote_card_storage'];
        } else {
            $data['payment_firstdata_remote_card_storage'] = $this->config->get('payment_firstdata_remote_card_storage');
        }

        $data['cards'] = [];

        $data['cards'][] = [
            'text'  => $this->language->get('text_mastercard'),
            'value' => 'mastercard'
        ];

        $data['cards'][] = [
            'text'  => $this->language->get('text_visa'),
            'value' => 'visa'
        ];

        $data['cards'][] = [
            'text'  => $this->language->get('text_diners'),
            'value' => 'diners'
        ];

        $data['cards'][] = [
            'text'  => $this->language->get('text_amex'),
            'value' => 'amex'
        ];

        $data['cards'][] = [
            'text'  => $this->language->get('text_maestro'),
            'value' => 'maestro'
        ];

        if (isset($this->request->post['payment_firstdata_remote_cards_accepted'])) {
            $data['payment_firstdata_remote_cards_accepted'] = $this->request->post['payment_firstdata_remote_cards_accepted'];
        } elseif ($this->config->get('payment_firstdata_remote_cards_accepted')) {
            $data['payment_firstdata_remote_cards_accepted'] = $this->config->get('payment_firstdata_remote_cards_accepted');
        } else {
            $data['payment_firstdata_remote_cards_accepted'] = [];
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/firstdata_remote', $data));
    }

    public function install(): void {
        $this->load->model('extension/payment/firstdata_remote');

        $this->model_extension_payment_firstdata_remote->install();
    }

    public function uninstall(): void {
        $this->load->model('extension/payment/firstdata_remote');

        $this->model_extension_payment_firstdata_remote->uninstall();
    }

    public function order(): string {
        $view = '';

        if ($this->config->get('payment_firstdata_remote_status')) {
            $this->load->model('extension/payment/firstdata_remote');

            $firstdata_order = $this->model_extension_payment_firstdata_remote->getOrder($this->request->get['order_id']);

            if (!empty($firstdata_order)) {
                $this->load->language('extension/payment/firstdata_remote');

                $firstdata_order['total_captured'] = $this->model_extension_payment_firstdata_remote->getTotalCaptured($firstdata_order['firstdata_remote_order_id']);

                $firstdata_order['total_formatted']          = $this->currency->format($firstdata_order['total'], $firstdata_order['currency_code'], 1, true);
                $firstdata_order['total_captured_formatted'] = $this->currency->format($firstdata_order['total_captured'], $firstdata_order['currency_code'], 1, true);

                $data['firstdata_order'] = $firstdata_order;

                $data['order_id']   = (int)$this->request->get['order_id'];
                $data['user_token'] = $this->session->data['user_token'];

                $view = $this->load->view('extension/payment/firstdata_remote_order', $data);
            }
        }

        return $view;
    }

    public function void(): void {
        $this->load->language('extension/payment/firstdata_remote');

        $json = [];

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
            $this->load->model('extension/payment/firstdata_remote');

            $firstdata_order = $this->model_extension_payment_firstdata_remote->getOrder($this->request->post['order_id']);

            $void_response = $this->model_extension_payment_firstdata_remote->void($firstdata_order['order_ref'], $firstdata_order['tdate']);

            $this->model_extension_payment_firstdata_remote->logger('Void result:\r\n' . print_r($void_response, 1));

            if (strtoupper($void_response['transaction_result']) == 'APPROVED') {
                $this->model_extension_payment_firstdata_remote->addTransaction($firstdata_order['firstdata_remote_order_id'], 'void', 0.00);

                $this->model_extension_payment_firstdata_remote->updateVoidStatus($firstdata_order['firstdata_remote_order_id'], 1);

                $json['msg'] = $this->language->get('text_void_ok');

                $json['data'] = [];

                $json['data']['column_date_added'] = date('Y-m-d H:i:s');

                $json['error'] = false;
            } else {
                $json['error'] = true;

                $json['msg'] = (isset($void_response['error']) && $void_response['error'] != '' ? sprintf($this->language->get('error_status'), (string)$void_response['error']) : $this->language->get('error_void'));
            }
        } else {
            $json['error'] = true;

            $json['msg'] = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function capture(): void {
        $this->load->language('extension/payment/firstdata');

        $json = [];

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
            $this->load->model('extension/payment/firstdata_remote');

            $firstdata_order = $this->model_extension_payment_firstdata_remote->getOrder($this->request->post['order_id']);

            $capture_response = $this->model_extension_payment_firstdata_remote->capture($firstdata_order['order_ref'], $firstdata_order['total'], $firstdata_order['currency_code']);

            $this->model_extension_payment_firstdata_remote->logger('Settle result:\r\n' . print_r($capture_response, 1));

            if (strtoupper($capture_response['transaction_result']) == 'APPROVED') {
                $this->model_extension_payment_firstdata_remote->addTransaction($firstdata_order['firstdata_remote_order_id'], 'payment', $firstdata_order['total']);

                $total_captured = $this->model_extension_payment_firstdata_remote->getTotalCaptured($firstdata_order['firstdata_remote_order_id']);

                $this->model_extension_payment_firstdata_remote->updateCaptureStatus($firstdata_order['firstdata_remote_order_id'], 1);

                $capture_status = 1;

                $json['msg'] = $this->language->get('text_capture_ok_order');

                $json['data'] = [];

                $json['data']['column_date_added'] = date('Y-m-d H:i:s');
                $json['data']['amount']            = (float)$firstdata_order['total'];
                $json['data']['capture_status']    = $capture_status;
                $json['data']['total']             = (float)$total_captured;
                $json['data']['total_formatted']   = $this->currency->format($total_captured, $firstdata_order['currency_code'], 1, true);

                $json['error'] = false;
            } else {
                $json['error'] = true;

                $json['msg'] = (isset($capture_response['error']) && $capture_response['error'] != '' ? sprintf($this->language->get('error_status'), (string)$capture_response['error']) : $this->language->get('error_capture'));

            }
        } else {
            $json['error'] = true;

            $json['msg'] = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function refund(): void {
        $this->load->language('extension/payment/firstdata_remote');

        $json = [];

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
            $this->load->model('extension/payment/firstdata_remote');

            $firstdata_order = $this->model_extension_payment_firstdata_remote->getOrder($this->request->post['order_id']);

            $refund_response = $this->model_extension_payment_firstdata_remote->refund($firstdata_order['order_ref'], $firstdata_order['total'], $firstdata_order['currency_code']);

            $this->model_extension_payment_firstdata_remote->logger('Refund result:\r\n' . print_r($refund_response, 1));

            if (strtoupper($refund_response['transaction_result']) == 'APPROVED') {
                $this->model_extension_payment_firstdata_remote->addTransaction($firstdata_order['firstdata_remote_order_id'], 'refund', $firstdata_order['total'] * -1);

                $total_refunded = $this->model_extension_payment_firstdata_remote->getTotalRefunded($firstdata_order['firstdata_remote_order_id']);
                $total_captured = $this->model_extension_payment_firstdata_remote->getTotalCaptured($firstdata_order['firstdata_remote_order_id']);

                if ($total_captured <= 0 && $firstdata_order['capture_status'] == 1) {
                    $this->model_extension_payment_firstdata_remote->updateRefundStatus($firstdata_order['firstdata_remote_order_id'], 1);

                    $refund_status = 1;

                    $json['msg'] = $this->language->get('text_refund_ok_order');
                } else {
                    $refund_status = 0;

                    $json['msg'] = $this->language->get('text_refund_ok');
                }

                $json['data'] = [];

                $json['data']['column_date_added'] = date('Y-m-d H:i:s');
                $json['data']['amount']            = $firstdata_order['total'] * -1;
                $json['data']['total_captured']    = (float)$total_captured;
                $json['data']['total_refunded']    = (float)$total_refunded;
                $json['data']['refund_status']     = $refund_status;

                $json['error'] = false;
            } else {
                $json['error'] = true;

                $json['msg'] = (isset($refund_response['error']) && $refund_response['error'] != '' ? sprintf($this->language->get('error_status'), (string)$refund_response['error']) : $this->language->get('error_refund'));
            }
        } else {
            $json['error'] = true;

            $json['msg'] = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/firstdata_remote')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_firstdata_remote_merchant_id']) {
            $this->error['error_merchant_id'] = $this->language->get('error_merchant_id');
        }

        if (!$this->request->post['payment_firstdata_remote_user_id']) {
            $this->error['error_user_id'] = $this->language->get('error_user_id');
        }

        if (!$this->request->post['payment_firstdata_remote_password']) {
            $this->error['error_password'] = $this->language->get('error_password');
        }

        if (!$this->request->post['payment_firstdata_remote_certificate']) {
            $this->error['error_certificate'] = $this->language->get('error_certificate');
        }

        if (!$this->request->post['payment_firstdata_remote_key']) {
            $this->error['error_key'] = $this->language->get('error_key');
        }

        if (!$this->request->post['payment_firstdata_remote_key_pw']) {
            $this->error['error_key_pw'] = $this->language->get('error_key_pw');
        }

        if (!$this->request->post['payment_firstdata_remote_ca']) {
            $this->error['error_ca'] = $this->language->get('error_ca');
        }

        return !$this->error;
    }
}