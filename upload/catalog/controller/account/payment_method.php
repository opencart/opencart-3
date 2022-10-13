<?php
class ControllerAccountPaymentMethod extends Controller {
    private array $error = [];

    public function index(): void {
        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/payment_method', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/payment_method');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/payment_method');

        $this->getList();
    }

    public function delete(): void {
        $this->load->language('account/payment_method');

        $json = [];

        if (isset($this->request->get['customer_payment_id'])) {
            $customer_payment_id = (int)$this->request->get['customer_payment_id'];
        } else {
            $customer_payment_id = 0;
        }

        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/payment_method', 'language=' . $this->config->get('config_language'));

            $json['redirect']                = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
        }

        if (!$json) {
            $this->load->model('account/payment_method');

            $payment_method_info = $this->model_account_payment_method->getPaymentMethod($this->customer->getId(), $customer_payment_id);

            if (!$payment_method_info) {
                $json['error'] = $this->language->get('error_payment_method');
            }
        }

        if (!$json) {
            $this->load->model('extension/' . $payment_method_info['extension'] . '/payment/' . $payment_method_info['code']);

            if ($this->{'model_extension_' . $payment_method_info['extension'] . '_payment_' . $payment_method_info['code']}->delete($customer_payment_id)) {

            }

            // Delete payment method from database.
            $this->model_account_payment_method->deletePaymentMethod($customer_payment_id);

            $this->session->data['success'] = $this->language->get('text_delete');

            $json['success']                = str_replace('&amp;', '&', $this->url->link('account/payment_method', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function getList() {
        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/payment_method', 'customer_token=' . $this->session->data['customer_token'], true)
        ];

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['payment_methods'] = [];
        
        $results                 = $this->model_account_payment_method->getPaymentMethods();

        foreach ($results as $result) {
            $data['payment_methods'][] = [
                'customer_payment_id' => $result['customer_payment_id'],
                'name'                => $result['name'],
                'image'               => $result['image'],
                'type'                => $result['type'],
                'date_expire'         => date('m-Y', strtotime($result['date_expire'])),
                'delete'              => $this->url->link('account/payment_method/delete', 'customer_token=' . $this->session->data['customer_token'] . '&customer_payment_id=' . $result['customer_payment_id'], true)
            ];
        }

        $data['customer_token'] = $this->session->data['customer_token'];

        $data['back']           = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);

        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/payment_method_list', $data));
    }
}