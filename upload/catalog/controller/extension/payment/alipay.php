<?php
class ControllerExtensionPaymentAlipay extends Controller {
    public function index(): bool|string {
        if (!isset($this->session->data['order_id'])) {
            return false;
        }

        $data['button_confirm'] = $this->language->get('button_confirm');

        // Orders
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $config = [
            'app_id'               => $this->config->get('payment_alipay_app_id'),
            'merchant_private_key' => $this->config->get('payment_alipay_merchant_private_key'),
            'notify_url'           => HTTPS_SERVER . "payment_callback/alipay",
            'return_url'           => $this->url->link('checkout/success'),
            'charset'              => "UTF-8",
            'sign_type'            => "RSA2",
            'gateway_url'          => $this->config->get('payment_alipay_test') == 'sandbox' ? 'https://openapi.alipaydev.com/gateway.do' : 'https://openapi.alipay.com/gateway.do',
            'alipay_public_key'    => $this->config->get('payment_alipay_alipay_public_key'),
        ];

        $out_trade_no = $order_info['order_id'];
        $subject = trim($this->config->get('config_name'));
        $total_amount = $this->currency->format($order_info['total'], 'CNY', '', false);
        $body = '';//trim($_POST['WIDbody']);

        $payRequestBuilder = [
            'body'         => $body,
            'subject'      => $subject,
            'total_amount' => $total_amount,
            'out_trade_no' => $out_trade_no,
            'product_code' => 'FAST_INSTANT_TRADE_PAY'
        ];

        // Alipay
        $this->load->model('extension/payment/alipay');

        $response = $this->model_extension_payment_alipay->pagePay($payRequestBuilder, $config);

        $data['action'] = $config['gateway_url'] . '?charset=' . $this->model_extension_payment_alipay->getPostCharset();
        $data['form_params'] = $response;

        return $this->load->view('extension/payment/alipay', $data);
    }

    public function callback(): void {
        $this->log->write('alipay pay notify:');

        // Alipay
        $this->load->model('extension/payment/alipay');

        $arr = $_POST;

        $config = [
            'app_id'               => $this->config->get('payment_alipay_app_id'),
            'merchant_private_key' => $this->config->get('payment_alipay_merchant_private_key'),
            'notify_url'           => HTTPS_SERVER . 'payment_callback/alipay',
            'return_url'           => $this->url->link('checkout/success'),
            'charset'              => 'UTF-8',
            'sign_type'            => 'RSA2',
            'gateway_url'          => $this->config->get('payment_alipay_test') == 'sandbox' ? 'https://openapi.alipaydev.com/gateway.do' : 'https://openapi.alipay.com/gateway.do',
            'alipay_public_key'    => $this->config->get('payment_alipay_alipay_public_key'),
        ];

        $this->log->write('POST' . var_export($_POST, true));

        $result = $this->model_extension_payment_alipay->check($arr, $config);

        // Check success
        if ($result) {
            $this->log->write('Alipay check success');

            $order_id = $_POST['out_trade_no'];

            if ($_POST['trade_status'] == 'TRADE_FINISHED') {

            } elseif ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                // Orders
                $this->load->model('checkout/order');

                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_alipay_order_status_id'));
            }

            echo 'success'; // Do not modify or delete
        } else {
            $this->log->write('Alipay check failed');

            // Check failed
            echo 'fail';
        }
    }
}