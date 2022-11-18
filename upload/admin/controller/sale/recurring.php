<?php
class ControllerSaleRecurring extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('sale/recurring');

        $this->document->setTitle($this->language->get('heading_title'));

        // Recurring
        $this->load->model('sale/recurring');

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['filter_order_recurring_id'])) {
            $filter_order_recurring_id = (int)$this->request->get['filter_order_recurring_id'];
        } else {
            $filter_order_recurring_id = '';
        }

        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = (int)$this->request->get['filter_order_id'];
        } else {
            $filter_order_id = '';
        }

        if (isset($this->request->get['filter_reference'])) {
            $filter_reference = $this->request->get['filter_reference'];
        } else {
            $filter_reference = '';
        }

        if (isset($this->request->get['filter_customer'])) {
            $filter_customer = $this->request->get['filter_customer'];
        } else {
            $filter_customer = '';
        }

        if (isset($this->request->get['filter_subscription_status_id'])) {
            $filter_subscription_status_id = (int)$this->request->get['filter_subscription_status_id'];
        } else {
            $filter_subscription_status_id = 0;
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'order_recurring_id';
        }

        if (isset($this->request->get['filter_date_added'])) {
            $filter_date_added = $this->request->get['filter_date_added'];
        } else {
            $filter_date_added = '';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_order_recurring_id'])) {
            $url .= '&filter_order_recurring_id=' . $this->request->get['filter_order_recurring_id'];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_reference'])) {
            $url .= '&filter_reference=' . $this->request->get['filter_reference'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_subscription_status_id'])) {
            $url .= '&filter_subscription_status_id=' . $this->request->get['filter_subscription_status_id'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        $data['recurrings'] = [];

        // Subscription
        $this->load->model('sale/subscription');

        $filter_data = [
            'filter_order_recurring_id'     => $filter_order_recurring_id,
            'filter_order_id'               => $filter_order_id,
            'filter_reference'              => $filter_reference,
            'filter_customer'               => $filter_customer,
            'filter_subscription_status_id' => $filter_subscription_status_id,
            'filter_date_added'             => $filter_date_added,
            'order'                         => $order,
            'sort'                          => $sort,
            'start'                         => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'                         => $this->config->get('config_limit_admin')
        ];

        $recurrings_total = $this->model_sale_recurring->getTotalRecurrings($filter_data);

        $results = $this->model_sale_recurring->getRecurrings($filter_data);

        foreach ($results as $result) {
            // Status
            if ($result['status']) {
                $status = $this->language->get('text_status_' . $result['status']);
            } else {
                $status = '';
            }

            $data['recurrings'][] = [
                'order_recurring_id' => $result['order_recurring_id'],
                'order_id'           => $result['order_id'],
                'reference'          => $result['reference'],
                'customer'           => $result['customer'],
                'status'             => $status,
                'date_added'         => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'view'               => $this->url->link('sale/recurring/info', 'user_token=' . $this->session->data['user_token'] . '&order_recurring_id=' . $result['order_recurring_id'] . $url, true),
                'order'              => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true)
            ];
        }

        $data['user_token'] = $this->session->data['user_token'];

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

        $url = '';

        if (isset($this->request->get['filter_order_recurring_id'])) {
            $url .= '&filter_order_recurring_id=' . $this->request->get['filter_order_recurring_id'];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_reference'])) {
            $url .= '&filter_reference=' . urlencode(html_entity_decode($this->request->get['filter_reference'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_order_recurring'] = $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . '&sort=or.order_recurring_id' . $url, true);
        $data['sort_order'] = $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . '&sort=or.order_id' . $url, true);
        $data['sort_reference'] = $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . '&sort=or.reference' . $url, true);
        $data['sort_customer'] = $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
        $data['sort_status'] = $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . '&sort=or.status' . $url, true);
        $data['sort_date_added'] = $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . '&sort=or.date_added' . $url, true);

        $url = '';

        if (isset($this->request->get['filter_order_recurring_id'])) {
            $url .= '&filter_order_recurring_id=' . $this->request->get['filter_order_recurring_id'];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_reference'])) {
            $url .= '&filter_reference=' . urlencode(html_entity_decode($this->request->get['filter_reference'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_subscription_status_id'])) {
            $url .= '&filter_subscription_status_id=' . $this->request->get['filter_subscription_status_id'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        // Subscription Status
        $this->load->model('localisation/subscription_status');

        $data['subscription_statuses'] = $this->model_localisation_subscription_status->getSubscriptionStatuses();

        $pagination = new \Pagination();
        $pagination->total = $recurrings_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . '&page={page}' . $url, true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($recurrings_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($recurrings_total - $this->config->get('config_limit_admin'))) ? $recurrings_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $recurrings_total, ceil($recurrings_total / $this->config->get('config_limit_admin')));

        $data['filter_order_recurring_id'] = $filter_order_recurring_id;
        $data['filter_order_id'] = $filter_order_id;
        $data['filter_reference'] = $filter_reference;
        $data['filter_customer'] = $filter_customer;
        $data['filter_subscription_status_id'] = $filter_subscription_status_id;
        $data['filter_date_added'] = $filter_date_added;

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['recurring_statuses'] = [];

        $data['recurring_statuses'][0] = [
            'text'  => '',
            'value' => 0
        ];

        for ($i = 1; $i <= 6; $i++) {
            $data['recurring_statuses'][$i] = [
                'text'  => $this->language->get('text_status_' . $i),
                'value' => $i,
            ];
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('sale/recurring_list', $data));
    }

    public function info(): object|null {
        // Subscription
        $this->load->model('sale/recurring');

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        $order_recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

        if ($order_recurring_info) {
            $this->load->language('sale/recurring');

            $this->document->setTitle($this->language->get('heading_title'));

            $data['user_token'] = $this->session->data['user_token'];

            $url = '';

            if (isset($this->request->get['filter_order_recurring_id'])) {
                $url .= '&filter_order_recurring_id=' . $this->request->get['filter_order_recurring_id'];
            }

            if (isset($this->request->get['filter_order_id'])) {
                $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
            }

            if (isset($this->request->get['filter_reference'])) {
                $url .= '&filter_reference=' . $this->request->get['filter_reference'];
            }

            if (isset($this->request->get['filter_customer'])) {
                $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_subscription_status_id'])) {
                $url .= '&filter_subscription_status_id=' . $this->request->get['filter_subscription_status_id'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . $url, true)
            ];

            $data['cancel'] = $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'] . $url, true);

            // Recurring
            $data['order_recurring_id'] = $order_recurring_info['order_recurring_id'];
            $data['reference'] = $order_recurring_info['reference'];
            $data['recurring_name'] = $order_recurring_info['recurring_name'];

            if ($order_recurring_info['recurring_id']) {
                $data['recurring'] = $this->url->link('catalog/recurring/edit', 'user_token=' . $this->session->data['user_token'] . '&recurring_id=' . $order_recurring_info['recurring_id'], true);
            } else {
                $data['recurring'] = '';
            }

            $data['recurring_description'] = $order_recurring_info['recurring_description'];

            if ($order_recurring_info['status']) {
                $data['recurring_status'] = $this->language->get('text_status_' . $order_recurring_info['status']);
            } else {
                $data['recurring_status'] = '';
            }

            // Orders
            $this->load->model('sale/order');

            $order_info = $this->model_sale_order->getOrder($order_recurring_info['order_id']);

            $data['payment_method'] = $order_info['payment_method'];
            $data['order_id'] = $order_info['order_id'];
            $data['order'] = $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_info['order_id'], true);
            $data['firstname'] = $order_info['firstname'];
            $data['lastname'] = $order_info['lastname'];

            if ($order_info['customer_id']) {
                $data['customer'] = $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $order_info['customer_id'], true);
            } else {
                $data['customer'] = '';
            }

            $data['email'] = $order_info['email'];
            $data['order_status'] = $order_info['order_status'];
            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

            // Products
            $data['product'] = $order_recurring_info['product_name'];
            $data['quantity'] = $order_recurring_info['product_quantity'];

            // Subscription Status
            $this->load->model('localisation/subscription_status');

            $data['subscription_statuses'] = $this->model_localisation_subscription_status->getSubscriptionStatuses();

            if (!empty($order_recurring_info)) {
                $data['subscription_status_id'] = $order_recurring_info['status'];
            } else {
                $data['subscription_status_id'] = '';
            }

            // Buttons
            $data['buttons'] = $this->load->controller('extension/payment/' . $order_info['payment_code'] . '/recurringButtons');

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('sale/recurring_info', $data));
        } else {
            return new \Action('error/not_found');
        }

        return null;
    }

    public function save() {
        $this->load->language('sale/recurring');

        $json = [];

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        if (!$this->user->hasPermission('modify', 'sale/recurring')) {
            $json['error'] = $this->language->get('error_permission');
        } elseif ($this->request->post['customer_payment_id'] == '') {
            $json['error'] = $this->language->get('error_payment_method');
        } elseif ($this->request->post['subscription_plan_id'] == '') {
            $json['error'] = $this->language->get('error_subscription_plan');
        }

        // Recurring
        $this->load->model('sale/recurring');

        $order_recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

        if (!$order_recurring_info) {
            $json['error'] = $this->language->get('error_not_found');
        } else {
            // Orders
            $this->load->model('sale/order');

            $order_info = $this->model_sale_order->getOrder($order_recurring_info['order_id']);

            if (!$order_info) {
                $json['error'] = $this->language->get('error_payment_method');
            } else {
                // Customers
                $this->load->model('customer/customer');

                $payment_method_info = $this->model_customer_customer->getPaymentMethod($order_info['customer_id'], $this->request->post['customer_payment_id']);

                if (!$payment_method_info) {
                    $json['error'] = $this->language->get('error_payment_method');
                } else {
                    // Subscription Plans
                    $this->load->model('catalog/subscription_plan');

                    $subscription_plan_info = $this->model_catalog_subscription_plan->getSubscriptionPlan($this->request->post['subscription_plan_id']);

                    if (!$subscription_plan_info) {
                        $json['error'] = $this->language->get('error_subscription_plan');
                    }
                }
            }
        }

        if (!$json) {
            $json['success'] = $this->language->get('text_plan_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // admin/view/sale/recurring_info/after
    public function notification(string &$route, array &$args, mixed &$output): void {
        // Recurring
        $this->load->model('sale/recurring');

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        $order_recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

        if ($order_recurring_info) {
            $this->load->language('sale/recurring');

            // Subscription
            $this->load->model('sale/subscription');

            $filter_data = [
                'filter_order_id' => $order_recurring_info['order_id']
            ];

            $data['subscription_total'] = $this->model_sale_subscription->getTotalSubscriptions($filter_data);

            $search = '<?php if ($warning) { ?>';

            $replace = '<?php if ($subscription_total) { ?>' . "\n";
            $replace .= '<div class="alert alert-info"><?php echo $text_subscription; ?></div>' . "\n";
            $replace .= '<?php } ?>' . "\n";

            $output = str_replace($replace, $replace . $search, $output);
        }
    }

    public function history(): void {
        $this->load->language('sale/recurring');

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['histories'] = [];

        // Recurring
        $this->load->model('sale/recurring');

        $results = $this->model_sale_recurring->getHistories($order_recurring_id, ($page - 1) * 10, 10);

        foreach ($results as $result) {
            $data['histories'][] = [
                'status'     => $result['status'],
                'comment'    => nl2br($result['comment']),
                'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            ];
        }

        $history_total = $this->model_sale_recurring->getTotalHistories($order_recurring_id);

        $pagination = new \Pagination();
        $pagination->total = $history_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->url->link('sale/recurring/history', 'user_token=' . $this->session->data['user_token'] . '&order_recurring_id=' . $order_recurring_id . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

        $this->response->setOutput($this->load->view('sale/recurring_history', $data));
    }

    public function addHistory(): void {
        $this->load->language('sale/recurring');

        $json = [];

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        if (!$this->user->hasPermission('modify', 'sale/recurring')) {
            $json['error'] = $this->language->get('error_permission');
        } elseif ($this->request->post['subscription_status_id'] == '') {
            $json['error'] = $this->language->get('error_subscription_status');
        } else {
            // Recurring
            $this->load->model('sale/recurring');

            $order_recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

            if (!$order_recurring_info) {
                $json['error'] = $this->language->get('error_not_found');
            }

            // Subscription
            $this->load->model('sale/subscription');

            $filter_data = [
                'filter_order_id' => $order_recurring_info['order_id']
            ];

            $subscription_total = $this->model_sale_subscription->getTotalSubscriptions($filter_data);

            // The same order ID cannot be the case between the recurring orders
            // and the new subscription system. Therefore, we need to ensure the
            // order ID only exists in the recurring orders prior to change the
            // subscription status in the recurring history.
            if ($subscription_total) {
                $json['error'] = $this->language->get('error_status');
            }
        }

        if (!$json) {
            $this->model_sale_recurring->addHistory($order_recurring_id, $this->request->post['subscription_status_id'], $this->request->post['comment'], $this->request->post['notify']);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function transaction(): void {
        $this->load->language('sale/recurring');

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        // Recurring
        $this->load->model('sale/recurring');

        $transaction_total = $this->model_sale_recurring->getTotalTransactions($order_recurring_id);

        // Recurring
        $this->load->model('sale/recurring');

        $order_recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

        $order_info = $this->model_sale_order->getOrder($order_recurring_info['order_id']);

        // Transactions
        $data['transactions'] = [];

        $transactions = $this->model_sale_recurring->getTransactions($order_recurring_id);

        foreach ($transactions as $transaction) {
            $data['transactions'][] = [
                'date_added' => $transaction['date_added'],
                'type'       => $transaction['type'],
                'amount'     => $this->currency->format($transaction['amount'], $order_info['currency_code'], $order_info['currency_value'])
            ];
        }

        $pagination = new \Pagination();
        $pagination->total = $transaction_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->url->link('sale/recurring/transaction', 'user_token=' . $this->session->data['user_token'] . '&order_recurring_id=' . $order_recurring_id . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($transaction_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($transaction_total - 10)) ? $transaction_total : ((($page - 1) * 10) + 10), $transaction_total, ceil($transaction_total / 10));

        $this->response->setOutput($this->load->view('sale/recurring_transaction', $data));
    }

    public function addTransaction(): void {
        $this->load->language('sale/recurring');

        $json = [];

        if (!$this->user->hasPermission('modify', 'sale/recurring')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        if (isset($this->request->post['description'])) {
            $description = (string)$this->request->post['description'];
        } else {
            $description = '';
        }

        if (isset($this->request->post['amount'])) {
            $amount = (float)$this->request->post['amount'];
        } else {
            $amount = 0;
        }

        if ($this->request->post['type'] == '') {
            $json['error'] = $this->language->get('error_service_type');
        }

        // Recurring
        $this->load->model('sale/recurring');

        $order_recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

        if (!$order_recurring_info) {
            $json['error'] = $this->language->get('error_recurring');
        } else {
            // Orders
            $this->load->model('sale/order');

            $order_info = $this->model_sale_order->getOrder($order_recurring_info['order_id']);

            if (!$order_info) {
                $json['error'] = $this->language->get('error_payment_method');
            }

            // Payment Methods
            $this->load->model('customer/customer');

            $payment_methods_total = $this->model_customer_customer->getTotalPaymentMethods($order_info['customer_id']);

            if (!$payment_methods_total) {
                $json['error'] = $this->language->get('error_payment_method');
            }

            // Subscription
            $this->load->model('sale/subscription');

            // Subscription Plans
            $this->load->model('catalog/subscription_plan');

            $filter_data = [
                'filter_order_id' => $order_recurring_info['order_id']
            ];

            $subscription_total = $this->model_sale_subscription->getTotalSubscriptions($filter_data);

            $subscription_plan_total = $this->model_catalog_subscription_plan->getTotalSubscriptionPlans();

            // Only recurring or new orders are allowed to be migrated into the subscription system.
            // Subscription plans must be created from the store prior to migrate recurring orders.
            if ($subscription_total || !$subscription_plan_total) {
                $json['error'] = $this->language->get('error_transaction');
            } else {
                // The subscription active status ID needs to match the recurring status ID
                $this->load->model('setting/setting');

                $store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

                if (!$store_info) {
                    $json['error'] = $this->language->get('error_status');
                } else {
                    $config_subscription_status_id = $store_info['config_subscription_canceled_status_id'];

                    $subscription_status_id = $this->config->get('config_subscription_canceled_status_id');

                    if ($config_subscription_status_id != $subscription_status_id) {
                        $json['error'] = $this->language->get('error_status');
                    } else {
                        $config_subscription_status_total = $this->model_sale_subscription->getTotalSubscriptionsBySubscriptionStatusId($config_subscription_status_id);

                        $subscription_status_total = $this->model_sale_subscription->getTotalSubscriptionsBySubscriptionStatusId($subscription_status_id);

                        if ((!$config_subscription_status_total) || (!$subscription_total)) {
                            $json['error'] = $this->language->get('error_status');
                        }
                    }
                }

                if ((!$order_recurring_info['status']) || ($subscription_status_id != $order_recurring_info['status'])) {
                    $json['error'] = $this->language->get('error_status');
                }
            }
        }

        if (!$json) {
            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}