<?php
/**
 * Class Subscription
 *
 * @package Catalog\Controller\Account
 */
class ControllerAccountSubscription extends Controller {
	/**
	 * @return void
	 */
    public function index(): void {
        $this->load->language('account/subscription');

        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/subscription', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

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
            'href' => $this->url->link('account/subscription', 'customer_token=' . $this->session->data['customer_token'] . $url, true)
        ];

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['subscriptions'] = [];

        // Subscriptions
        $this->load->model('account/subscription');

        $filter_data = [
            'start'	=> ($page - 1) * 10,
            'limit'	=> 10
        ];

        $subscription_total = $this->model_account_subscription->getTotalSubscriptions($filter_data);

        $results = $this->model_account_subscription->getSubscriptions($filter_data);

        foreach ($results as $result) {
            if ($result['status']) {
                $status = $this->language->get('text_status_' . $result['status']);
            } else {
                $status = '';
            }

            if ($result['duration']) {
                if ($result['frequency'] == 'semi_month') {
                    $period = strtotime("2 weeks");
                } else {
                    $period = strtotime($result['cycle'] . ' ' . $result['frequency']);
                }
            }

            $date_next = strtotime($result['date_next']);

            $data['subscriptions'][] = [
                'subscription_id' => $result['subscription_id'],
                'product'         => $result['product'],
                'status'          => $status,
                'date_added'      => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'is_subscription' => $date_next <= $period ? true : false,
                'view'            => $this->url->link('account/subscription/info', 'customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $result['subscription_id'], true)
            ];
        }

        $pagination = new \Pagination();
        $pagination->total = $subscription_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->url->link('account/subscription', 'customer_token=' . $this->session->data['customer_token'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['continue'] = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/subscription_list', $data));
    }

	/**
	 * Info
	 *
	 * @return void
	 */
    public function info(): void {
        $this->load->language('account/subscription');

        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/subscription', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        if (isset($this->request->get['subscription_id'])) {
            $subscription_id = (int)$this->request->get['subscription_id'];
        } else {
            $subscription_id = 0;
        }

        // Subscriptions
        $this->load->model('account/subscription');

        $subscription_info = $this->model_account_subscription->getSubscription($subscription_id);

        if ($subscription_info) {
            $this->document->setTitle($this->language->get('text_subscription'));

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

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
                'href' => $this->url->link('account/subscription', 'customer_token=' . $this->session->data['customer_token'] . $url, true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_subscription'),
                'href' => $this->url->link('account/subscription/info', 'customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $this->request->get['subscription_id'] . $url, true)
            ];

            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($subscription_info['date_added']));

            $data['subscription_id'] = (int)$this->request->get['subscription_id'];

            if ($subscription_info['status']) {
                $data['status'] = $this->language->get('text_status_' . $subscription_info['status']);
            } else {
                $data['status'] = '';
            }

            if ($subscription_info['duration']) {
                if ($subscription_info['frequency'] == 'semi_month') {
                    $period = strtotime("2 weeks");
                } else {
                    $period = strtotime($subscription_info['cycle'] . ' ' . $subscription_info['frequency']);
                }
            }

            $date_next = strtotime($subscription_info['date_next']);

            $data['is_subscription'] = $date_next <= $period ? true : false;

            // Orders
            $this->load->model('account/order');

            $order_product = $this->model_account_order->getProduct($subscription_info['order_id'], $subscription_info['order_product_id']);

            $data['order_id'] = $subscription_info['order_id'];
            $data['reference'] = $subscription_info['reference'];
            $data['product_name'] = $order_product['name'];
            $data['payment_method'] = $subscription_info['payment_method'];
            $data['product_quantity'] = $order_product['quantity'];
            $data['description'] = $subscription_info['description'];

            // Transactions
            $data['transactions'] = [];

            $results = $this->model_account_subscription->getTransactions($this->request->get['subscription_id']);

            foreach ($results as $result) {
                $data['transactions'][] = [
                    'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'type'       => $result['type'],
                    'amount'     => $this->currency->format($result['amount'], $subscription_info['currency_code'])
                ];
            }

            $data['order'] = $this->url->link('account/order/info', 'customer_token=' . $this->session->data['customer_token'] . '&order_id=' . $subscription_info['order_id'], true);
            $data['product'] = $this->url->link('product/product', 'customer_token=' . $this->session->data['customer_token'] . '&product_id=' . $subscription_info['product_id']);

            // Extensions
            $this->load->model('setting/extension');

            $extension_info = $this->model_setting_extension->getExtensionByCode('payment', $subscription_info['payment_code']);

            if ($extension_info) {
                $data['subscription'] = $this->load->controller('extension/subscription/' . $subscription_info['payment_code']);
            } else {
                $data['subscription'] = '';
            }

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('account/subscription_info', $data));
        } else {
            $this->document->setTitle($this->language->get('text_subscription'));

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
                'href' => $this->url->link('account/subscription', '&customer_token=' . $this->session->data['customer_token'], true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_recurring'),
                'href' => $this->url->link('account/subscription/info', 'customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $subscription_id, true)
            ];

            $data['continue'] = $this->url->link('account/subscription', 'customer_token=' . $this->session->data['customer_token'], true);

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }
}
