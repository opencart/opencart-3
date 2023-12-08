<?php
/**
 * Class Customer Payment
 *
 * @package Admin\Controller\Customer
 */
class ControllerCustomerCustomerPayment extends Controller {
	/**
	 * @return void
	 */
    public function index(): void {
        if (isset($this->request->get['customer_id'])) {
            $customer_id = (int)$this->request->get['customer_id'];
        } else {
            $customer_id = 0;
        }

        // Customers
        $this->load->model('customer/customer');

        $customer_info = $this->model_customer_customer->getCustomer($customer_id);

        if ($customer_info) {
            $this->load->language('customer/customer_payment');

            $this->document->setTitle($this->language->get('heading_title'));

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_customer'),
                'href' => $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'], true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_customer_back'),
                'href' => $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $customer_id, true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('customer/customer_payment', 'user_token=' . $this->session->data['user_token'] . $url, true)
            ];

            $data['user_token'] = $this->session->data['user_token'];

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('customer/customer_payment', $data));
        } else {
            $this->load->language('error/not_found');

            $this->document->setTitle($this->language->get('heading_title'));

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'], true)
            ];

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }

	/**
	 * getPayment
	 *
	 * @return void
	 */
    public function getPayment(): void {
        if (isset($this->request->get['customer_id'])) {
            $customer_id = (int)$this->request->get['customer_id'];
        } else {
            $customer_id = 0;
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['payment_methods'] = [];

        // Customers
        $this->load->model('customer/customer');

        $payment_total = $this->model_customer_customer->getTotalPaymentMethods($customer_id);

        $results = $this->model_customer_customer->getPaymentMethods($customer_id, ($page - 1) * 10, 10);

        foreach ($results as $result) {
            if (isset($result['image'])) {
                $image = DIR_IMAGE . 'payment/' . $result['image'];
            } else {
                $image = '';
            }

            $data['payment_methods'][] = [
                'customer_payment_id' => $result['customer_payment_id'],
                'name'                => $result['name'],
                'image'               => $image,
                'type'                => $result['type'],
                'status'              => $result['status'],
                'date_expire'         => date($this->language->get('date_format_short'), strtotime($result['date_expire'])),
                'delete'              => $this->url->link('customer/customer_payment/deletePayment', 'user_token=' . $this->session->data['user_token'] . '&customer_payment_id=' . $result['customer_payment_id'])
            ];
        }

        $pagination = new \Pagination();
        $pagination->total = $payment_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('customer/customer_payment/getPayment', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($payment_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($payment_total - $this->config->get('config_limit_admin'))) ? $payment_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $payment_total, ceil($payment_total / $this->config->get('config_limit_admin')));

        $this->response->setOutput($this->load->view('customer/customer_payment_list', $data));
    }

	/**
	 * deletePayment
	 *
	 * @return void
	 */
    public function deletePayment(): void {
        $this->load->language('customer/customer');

        $json = [];

        if (isset($this->request->get['customer_payment_id'])) {
            $customer_payment_id = (int)$this->request->get['customer_payment_id'];
        } else {
            $customer_payment_id = 0;
        }

        if (!$this->user->hasPermission('modify', 'customer/customer')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            // Customers
            $this->load->model('customer/customer');

            $this->model_customer_customer->deletePaymentMethod($customer_payment_id);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
