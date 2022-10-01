<?php
class ControllerExtensionPaymentEway extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/payment/eway');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->model_setting_setting->editSetting('payment_eway', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
        }

        // Geo Zones
        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        // Order Statuses
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['username'])) {
            $data['error_username'] = $this->error['username'];
        } else {
            $data['error_username'] = '';
        }

        if (isset($this->error['password'])) {
            $data['error_password'] = $this->error['password'];
        } else {
            $data['error_password'] = '';
        }

        if (isset($this->error['payment_type'])) {
            $data['error_payment_type'] = $this->error['payment_type'];
        } else {
            $data['error_payment_type'] = '';
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
            'href' => $this->url->link('extension/payment/eway', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['action'] = $this->url->link('extension/payment/eway', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->request->post['payment_eway_paymode'])) {
            $data['payment_eway_paymode'] = $this->request->post['payment_eway_paymode'];
        } else {
            $data['payment_eway_paymode'] = $this->config->get('payment_eway_paymode');
        }

        if (isset($this->request->post['payment_eway_test'])) {
            $data['payment_eway_test'] = $this->request->post['payment_eway_test'];
        } else {
            $data['payment_eway_test'] = $this->config->get('payment_eway_test');
        }

        if (isset($this->request->post['payment_eway_payment_type'])) {
            $data['payment_eway_payment_type'] = $this->request->post['payment_eway_payment_type'];
        } else {
            $data['payment_eway_payment_type'] = $this->config->get('payment_eway_payment_type');
        }

        if (isset($this->request->post['payment_eway_transaction'])) {
            $data['payment_eway_transaction'] = $this->request->post['payment_eway_transaction'];
        } else {
            $data['payment_eway_transaction'] = $this->config->get('payment_eway_transaction');
        }

        if (isset($this->request->post['payment_eway_standard_geo_zone_id'])) {
            $data['payment_eway_standard_geo_zone_id'] = (int)$this->request->post['payment_eway_standard_geo_zone_id'];
        } else {
            $data['payment_eway_standard_geo_zone_id'] = $this->config->get('payment_eway_standard_geo_zone_id');
        }

        if (isset($this->request->post['payment_eway_order_status_id'])) {
            $data['payment_eway_order_status_id'] = (int)$this->request->post['payment_eway_order_status_id'];
        } else {
            $data['payment_eway_order_status_id'] = $this->config->get('payment_eway_order_status_id');
        }

        if (isset($this->request->post['payment_eway_order_status_refunded_id'])) {
            $data['payment_eway_order_status_refunded_id'] = (int)$this->request->post['payment_eway_order_status_refunded_id'];
        } else {
            $data['payment_eway_order_status_refunded_id'] = $this->config->get('payment_eway_order_status_refunded_id');
        }

        if (isset($this->request->post['payment_eway_order_status_auth_id'])) {
            $data['payment_eway_order_status_auth_id'] = (int)$this->request->post['payment_eway_order_status_auth_id'];
        } else {
            $data['payment_eway_order_status_auth_id'] = $this->config->get('payment_eway_order_status_auth_id');
        }

        if (isset($this->request->post['payment_eway_order_status_fraud_id'])) {
            $data['payment_eway_order_status_fraud_id'] = (int)$this->request->post['payment_eway_order_status_fraud_id'];
        } else {
            $data['payment_eway_order_status_fraud_id'] = $this->config->get('payment_eway_order_status_fraud_id');
        }

        if (isset($this->request->post['payment_eway_transaction_method'])) {
            $data['payment_eway_transaction_method'] = $this->request->post['payment_eway_transaction_method'];
        } else {
            $data['payment_eway_transaction_method'] = $this->config->get('payment_eway_transaction_method');
        }

        if (isset($this->request->post['payment_eway_username'])) {
            $data['payment_eway_username'] = $this->request->post['payment_eway_username'];
        } else {
            $data['payment_eway_username'] = $this->config->get('payment_eway_username');
        }

        if (isset($this->request->post['payment_eway_password'])) {
            $data['payment_eway_password'] = $this->request->post['payment_eway_password'];
        } else {
            $data['payment_eway_password'] = $this->config->get('payment_eway_password');
        }

        if (isset($this->request->post['payment_eway_status'])) {
            $data['payment_eway_status'] = $this->request->post['payment_eway_status'];
        } else {
            $data['payment_eway_status'] = $this->config->get('payment_eway_status');
        }

        if (isset($this->request->post['payment_eway_sort_order'])) {
            $data['payment_eway_sort_order'] = $this->request->post['payment_eway_sort_order'];
        } else {
            $data['payment_eway_sort_order'] = $this->config->get('payment_eway_sort_order');
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/eway', $data));
    }

    public function install(): void {
        $this->load->model('extension/payment/eway');

        $this->model_extension_payment_eway->install();
    }

    public function uninstall(): void {
        $this->load->model('extension/payment/eway');

        $this->model_extension_payment_eway->uninstall();
    }

    // Legacy 2.0.0
    public function orderAction() {
        return $this->order();
    }

    // Legacy 2.0.3
    public function action() {
        return $this->order();
    }

    public function order(): string {
        $view = '';

        if ($this->config->get('payment_eway_status')) {
            $this->load->model('extension/payment/eway');

            $eway_order = $this->model_extension_payment_eway->getOrder($this->request->get['order_id']);

            if (!empty($eway_order)) {
                $this->load->language('extension/payment/eway');

                $eway_order['total']           = $eway_order['amount'];
                $eway_order['total_formatted'] = $this->currency->format($eway_order['amount'], $eway_order['currency_code'], 1, true);

                $eway_order['total_captured']           = $this->model_extension_payment_eway->getTotalCaptured($eway_order['eway_order_id']);
                $eway_order['total_captured_formatted'] = $this->currency->format($eway_order['total_captured'], $eway_order['currency_code'], 1, true);

                $eway_order['uncaptured'] = $eway_order['total'] - $eway_order['total_captured'];

                $eway_order['total_refunded']           = $this->model_extension_payment_eway->getTotalRefunded($eway_order['eway_order_id']);
                $eway_order['total_refunded_formatted'] = $this->currency->format($eway_order['total_refunded'], $eway_order['currency_code'], 1, true);

                $eway_order['unrefunded'] = $eway_order['total_captured'] - $eway_order['total_refunded'];

                $data['eway_order'] = $eway_order;
                $data['user_token'] = $this->session->data['user_token'];
                $data['order_id']   = (int)$this->request->get['order_id'];

                $view = $this->load->view('extension/payment/eway_order', $data);
            }
        }

        return $view;
    }

    public function refund(): void {
        $this->load->language('extension/payment/eway');

        $json = [];

        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        $refund_amount = (double)$this->request->post['refund_amount'];

        if ($order_id && $refund_amount > 0) {
            $this->load->model('extension/payment/eway');

            $result = $this->model_extension_payment_eway->refund($order_id, $refund_amount);

            // Check if any error returns
            if (isset($result->Errors) || $result === false) {
                $json['error'] = true;

                $reason = '';

                if ($result === false) {
                    $reason = $this->language->get('text_unknown_failure');
                } else {
                    $errors = explode(',', $result->Errors);

                    foreach ($errors as $error) {
                        $reason .= $this->language->get('text_card_message_' . $result->Errors);
                    }
                }

                $json['message'] = $this->language->get('text_refund_failed') . $reason;
            } else {
                $eway_order = $this->model_extension_payment_eway->getOrder($order_id);

                $this->model_extension_payment_eway->addTransaction($eway_order['eway_order_id'], $result->Refund->TransactionID, 'refund', $result->Refund->TotalAmount / 100, $eway_order['currency_code']);

                $total_captured = $this->model_extension_payment_eway->getTotalCaptured($eway_order['eway_order_id']);
                $total_refunded = $this->model_extension_payment_eway->getTotalRefunded($eway_order['eway_order_id']);

                $refund_status = 0;

                if ($total_captured == $total_refunded) {
                    $refund_status = 1;

                    $this->model_extension_payment_eway->updateRefundStatus($eway_order['eway_order_id'], $refund_status);
                }

                $json['data'] = [];

                $json['data']['transactionid']            = $result->TransactionID;
                $json['data']['created']                  = date('Y-m-d H:i:s');
                $json['data']['amount']                   = number_format($refund_amount, 2, '.', '');
                $json['data']['total_refunded_formatted'] = $this->currency->format($total_refunded, $eway_order['currency_code'], 1, true);
                $json['data']['refund_status']            = $refund_status;
                $json['data']['remaining']                = $total_captured - $total_refunded;

                $json['message'] = $this->language->get('text_refund_success');

                $json['error'] = false;
            }
        } else {
            $json['error'] = true;

            $json['message'] = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function capture(): void {
        $this->load->language('extension/payment/eway');

        $json = [];

        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        $capture_amount = (double)$this->request->post['capture_amount'];

        if ($order_id && $capture_amount > 0) {
            $this->load->model('extension/payment/eway');

            $eway_order = $this->model_extension_payment_eway->getOrder($order_id);

            $result = $this->model_extension_payment_eway->capture($order_id, $capture_amount, $eway_order['currency_code']);

            // Check if any error returns
            if (isset($result->Errors) || $result === false) {
                $json['error'] = true;

                $reason = '';

                if ($result === false) {
                    $reason = $this->language->get('text_unknown_failure');
                } else {
                    $errors = explode(',', $result->Errors);

                    foreach ($errors as $error) {
                        $reason .= $this->language->get('text_card_message_' . $result->Errors);
                    }
                }

                $json['message'] = $this->language->get('text_capture_failed') . $reason;
            } else {
                $this->model_extension_payment_eway->addTransaction($eway_order['eway_order_id'], $result->TransactionID, 'payment', $capture_amount, $eway_order['currency_code']);

                $total_captured = $this->model_extension_payment_eway->getTotalCaptured($eway_order['eway_order_id']);
                $total_refunded = $this->model_extension_payment_eway->getTotalRefunded($eway_order['eway_order_id']);

                $remaining = $eway_order['amount'] - $capture_amount;

                if ($remaining <= 0) {
                    $remaining = 0;
                }

                $this->model_extension_payment_eway->updateCaptureStatus($eway_order['eway_order_id'], 1);
                $this->model_extension_payment_eway->updateTransactionId($eway_order['eway_order_id'], $result->TransactionID);

                $json['data'] = [];

                $json['data']['transactionid']            = $result->TransactionID;
                $json['data']['created']                  = date('Y-m-d H:i:s');
                $json['data']['amount']                   = number_format($capture_amount, 2, '.', '');
                $json['data']['total_captured_formatted'] = $this->currency->format($total_captured, $eway_order['currency_code'], 1, true);
                $json['data']['capture_status']           = 1;
                $json['data']['remaining']                = $remaining;

                $json['message'] = $this->language->get('text_capture_success');

                $json['error'] = false;
            }
        } else {
            $json['error'] = true;

            $json['message'] = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/eway')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_eway_username']) {
            $this->error['username'] = $this->language->get('error_username');
        }

        if (!$this->request->post['payment_eway_password']) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if (!isset($this->request->post['payment_eway_payment_type'])) {
            $this->error['payment_type'] = $this->language->get('error_payment_type');
        }

        return !$this->error;
    }
}