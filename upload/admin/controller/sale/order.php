<?php
/**
 * Class Order
 *
 * @package Admin\Controller\Sale
 */
class ControllerSaleOrder extends Controller {
    private array $error = [];

	/**
	 * @return void
	 */
    public function index(): void {
        $this->load->language('sale/order');

        $this->document->setTitle($this->language->get('heading_title'));

        // Orders
        $this->load->model('sale/order');

        $this->getList();
    }

	/**
	 * Add
	 *
	 * @return void
	 */
    public function add(): void {
        $this->load->language('sale/order');

        $this->document->setTitle($this->language->get('heading_title'));

        // Orders
        $this->load->model('sale/order');

        $this->getForm();
    }

	/**
	 * Edit
	 *
	 * @return void
	 */
    public function edit(): void {
        $this->load->language('sale/order');

        $this->document->setTitle($this->language->get('heading_title'));

        // Orders
        $this->load->model('sale/order');

        $this->getForm();
    }

	/**
	 * Delete
	 *
	 * @return void
	 */
    public function delete(): void {
        $this->load->language('sale/order');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->session->data['success'] = $this->language->get('text_success');

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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

        $this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true));
    }

    protected function getList() {
        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = '';
        }

        if (isset($this->request->get['filter_customer'])) {
            $filter_customer = $this->request->get['filter_customer'];
        } else {
            $filter_customer = '';
        }

        if (isset($this->request->get['filter_order_status'])) {
            $filter_order_status = $this->request->get['filter_order_status'];
        } else {
            $filter_order_status = '';
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $filter_order_status_id = (int)$this->request->get['filter_order_status_id'];
        } else {
            $filter_order_status_id = '';
        }

        if (isset($this->request->get['filter_total'])) {
            $filter_total = $this->request->get['filter_total'];
        } else {
            $filter_total = '';
        }

        if (isset($this->request->get['filter_date_added'])) {
            $filter_date_added = $this->request->get['filter_date_added'];
        } else {
            $filter_date_added = '';
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $filter_date_modified = $this->request->get['filter_date_modified'];
        } else {
            $filter_date_modified = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'o.order_id';
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

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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
            'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        $data['invoice'] = $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'], true);
        $data['shipping'] = $this->url->link('sale/order/shipping', 'user_token=' . $this->session->data['user_token'], true);
        $data['add'] = $this->url->link('sale/order/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = str_replace('&amp;', '&', $this->url->link('sale/order/delete', 'user_token=' . $this->session->data['user_token'] . $url, true));

        $data['orders'] = [];

        $filter_data = [
            'filter_order_id'        => $filter_order_id,
            'filter_customer'        => $filter_customer,
            'filter_order_status'    => $filter_order_status,
            'filter_order_status_id' => $filter_order_status_id,
            'filter_total'           => $filter_total,
            'filter_date_added'      => $filter_date_added,
            'filter_date_modified'   => $filter_date_modified,
            'sort'                   => $sort,
            'order'                  => $order,
            'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'                  => $this->config->get('config_limit_admin')
        ];

        $order_total = $this->model_sale_order->getTotalOrders($filter_data);

        $results = $this->model_sale_order->getOrders($filter_data);

        foreach ($results as $result) {
            $data['orders'][] = [
                'order_id'      => $result['order_id'],
                'customer'      => $result['customer'],
                'order_status'  => $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
                'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
                'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
                'shipping_code' => $result['shipping_code'],
                'view'          => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url, true),
                'edit'          => $this->url->link('sale/order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url, true)
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

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = [];
        }

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_order'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.order_id' . $url, true);
        $data['sort_customer'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
        $data['sort_status'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=order_status' . $url, true);
        $data['sort_total'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.total' . $url, true);
        $data['sort_date_added'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_added' . $url, true);
        $data['sort_date_modified'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_modified' . $url, true);

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new \Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

        $data['filter_order_id'] = $filter_order_id;
        $data['filter_customer'] = $filter_customer;
        $data['filter_order_status'] = $filter_order_status;
        $data['filter_order_status_id'] = $filter_order_status_id;
        $data['filter_total'] = $filter_total;
        $data['filter_date_added'] = $filter_date_added;
        $data['filter_date_modified'] = $filter_date_modified;

        $data['sort'] = $sort;
        $data['order'] = $order;

        // Order Statuses
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // API login
        $this->load->model('user/api');

        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
            // Session
            $session = new \Session($this->config->get('session_engine'), $this->registry);
            $session->start();

            $this->model_user_api->deleteSessionBySessionId($session->getId());
            $this->model_user_api->addSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

            $session->data['api_id'] = $api_info['api_id'];

            $data['api_token'] = $session->getId();
        } else {
            $data['api_token'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('sale/order_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['order_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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
            'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        $data['cancel'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['order_id'])) {
            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
        }

        if (!empty($order_info)) {
            $data['order_id'] = (int)$this->request->get['order_id'];
            $data['store_id'] = $order_info['store_id'];
            $data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

            $data['customer'] = $order_info['customer'];
            $data['customer_id'] = $order_info['customer_id'];
            $data['customer_group_id'] = $order_info['customer_group_id'];
            $data['firstname'] = $order_info['firstname'];
            $data['lastname'] = $order_info['lastname'];
            $data['email'] = $order_info['email'];
            $data['telephone'] = $order_info['telephone'];
            $data['account_custom_field'] = $order_info['custom_field'];

            // Customers
            $this->load->model('customer/customer');

            // Subscriptions
            $this->load->model('sale/subscription');

            // Settings
            $this->load->model('setting/setting');

            $data['addresses'] = $this->model_customer_customer->getAddresses($order_info['customer_id']);

            // Payment Details
            $data['payment_firstname'] = $order_info['payment_firstname'];
            $data['payment_lastname'] = $order_info['payment_lastname'];
            $data['payment_company'] = $order_info['payment_company'];
            $data['payment_address_1'] = $order_info['payment_address_1'];
            $data['payment_address_2'] = $order_info['payment_address_2'];
            $data['payment_city'] = $order_info['payment_city'];
            $data['payment_postcode'] = $order_info['payment_postcode'];
            $data['payment_country_id'] = $order_info['payment_country_id'];
            $data['payment_zone_id'] = $order_info['payment_zone_id'];
            $data['payment_custom_field'] = $order_info['payment_custom_field'];
            $data['payment_method'] = $order_info['payment_method'];
            $data['payment_code'] = $order_info['payment_code'];

            // Shipping Details
            $data['shipping_firstname'] = $order_info['shipping_firstname'];
            $data['shipping_lastname'] = $order_info['shipping_lastname'];
            $data['shipping_company'] = $order_info['shipping_company'];
            $data['shipping_address_1'] = $order_info['shipping_address_1'];
            $data['shipping_address_2'] = $order_info['shipping_address_2'];
            $data['shipping_city'] = $order_info['shipping_city'];
            $data['shipping_postcode'] = $order_info['shipping_postcode'];
            $data['shipping_country_id'] = $order_info['shipping_country_id'];
            $data['shipping_zone_id'] = $order_info['shipping_zone_id'];
            $data['shipping_custom_field'] = $order_info['shipping_custom_field'];
            $data['shipping_method'] = $order_info['shipping_method'];
            $data['shipping_code'] = $order_info['shipping_code'];

            // Subscriptions
            $filter_data = [
                'filter_order_id' => $order_info['order_id']
            ];

            $subscriptions = $this->model_sale_subscription->getSubscriptions($filter_data);

            $data['order_products'] = [];

            $frequencies = [
                'day',
                'week',
                'semi_month',
                'month',
                'year'
            ];

            // Products
            $products = $this->model_sale_order->getProducts($this->request->get['order_id']);

            foreach ($products as $product) {
                // Subscriptions
                $subscription_data = '';

                foreach ($subscriptions as $subscription) {
                    $filter_data = [
                        'filter_subscription_id'        => $subscription['subscription_id'],
                        'filter_order_product_id'       => $product['order_product_id']
                    ];

                    $subscription_info = $this->model_sale_subscription->getSubscriptions($filter_data);

                    if ($subscription_info) {
                        $subscription_data = $subscription_info['name'];
                    }
                }

                $data['order_products'][] = [
                    'product_id'   => $product['product_id'],
                    'name'         => $product['name'],
                    'model'        => $product['model'],
                    'option'       => $this->model_sale_order->getOptions($this->request->get['order_id'], $product['order_product_id']),
                    'subscription' => $subscription_data,
                    'quantity'     => $product['quantity'],
                    'price'        => $product['price'],
                    'total'        => $product['total'],
                    'reward'       => $product['reward']
                ];
            }

            // Vouchers
            $data['order_vouchers'] = $this->model_sale_order->getVouchers($this->request->get['order_id']);

            $data['coupon'] = '';
            $data['voucher'] = '';
            $data['reward'] = '';
            $data['order_totals'] = [];

            $order_totals = $this->model_sale_order->getTotals($this->request->get['order_id']);

            foreach ($order_totals as $order_total) {
                // If coupon, voucher or reward points
                $start = strpos($order_total['title'], '(') + 1;
                $end = strrpos($order_total['title'], ')');

                if ($start && $end) {
                    $data[$order_total['code']] = substr($order_total['title'], $start, $end - $start);
                }
            }

            $data['order_status_id'] = $order_info['order_status_id'];
            $data['comment'] = $order_info['comment'];
            $data['affiliate_id'] = $order_info['affiliate_id'];
            $data['affiliate'] = $order_info['affiliate_firstname'] . ' ' . $order_info['affiliate_lastname'];
            $data['currency_code'] = $order_info['currency_code'];
        } else {
            $data['order_id'] = 0;
            $data['store_id'] = 0;
            $data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
            $data['customer'] = '';
            $data['customer_id'] = '';
            $data['firstname'] = '';
            $data['lastname'] = '';
            $data['email'] = '';
            $data['telephone'] = '';
            $data['customer_group_id'] = $this->config->get('config_customer_group_id');
            $data['customer_custom_field'] = [];
            $data['addresses'] = [];

            // Payment Details
            $data['payment_country_id'] = 0;
            $data['payment_zone_id'] = 0;
            $data['payment_firstname'] = '';
            $data['payment_lastname'] = '';
            $data['payment_company'] = '';
            $data['payment_address_1'] = '';
            $data['payment_address_2'] = '';
            $data['payment_city'] = '';
            $data['payment_postcode'] = '';
            $data['payment_method'] = '';
            $data['payment_code'] = '';
            $data['payment_custom_field'] = [];

            // Shipping Details
            $data['shipping_country_id'] = 0;
            $data['shipping_zone_id'] = 0;
            $data['shipping_firstname'] = '';
            $data['shipping_lastname'] = '';
            $data['shipping_company'] = '';
            $data['shipping_address_1'] = '';
            $data['shipping_address_2'] = '';
            $data['shipping_city'] = '';
            $data['shipping_postcode'] = '';
            $data['shipping_method'] = '';
            $data['shipping_code'] = '';
            $data['shipping_custom_field'] = [];

            // Order Details
            $data['affiliate_id'] = 0;
            $data['order_products'] = [];
            $data['order_vouchers'] = [];
            $data['order_totals'] = [];
            $data['order_status_id'] = $this->config->get('config_order_status_id');
            $data['comment'] = '';
            $data['affiliate'] = '';
            $data['coupon'] = '';
            $data['voucher'] = '';
            $data['reward'] = '';
            $data['currency_code'] = $this->config->get('config_currency');
        }

        // Stores
        $this->load->model('setting/store');

        $data['stores'] = [];

        $data['stores'][] = [
            'store_id' => 0,
            'name'     => $this->language->get('text_default')
        ];

        $results = $this->model_setting_store->getStores();

        foreach ($results as $result) {
            $data['stores'][] = [
                'store_id' => $result['store_id'],
                'name'     => $result['name']
            ];
        }

        // Customer Groups
        $this->load->model('customer/customer_group');

        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        // Upload
        $this->load->model('tool/upload');

        // Custom Fields
        $this->load->model('customer/custom_field');

        $data['custom_fields'] = [];

        $custom_field_locations = [
            'account_custom_field',
            'payment_custom_field',
            'shipping_custom_field'
        ];

        $filter_data = [
            'sort'  => 'cf.sort_order',
            'order' => 'ASC'
        ];

        $custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);

        foreach ($custom_fields as $custom_field) {
            $data['custom_fields'][] = [
                'custom_field_id'    => $custom_field['custom_field_id'],
                'custom_field_value' => $this->model_customer_custom_field->getValues($custom_field['custom_field_id']),
                'name'               => $custom_field['name'],
                'value'              => $custom_field['value'],
                'type'               => $custom_field['type'],
                'location'           => $custom_field['location'],
                'sort_order'         => $custom_field['sort_order']
            ];

            if ($custom_field['type'] == 'file') {
                foreach ($custom_field_locations as $location) {
                    if (isset($data[$location][$custom_field['custom_field_id']])) {
                        $code = $data[$location][$custom_field['custom_field_id']];

                        $upload_info = $this->model_tool_upload->getUploadByCode($code);

                        $data[$location][$custom_field['custom_field_id']] = [];

                        if ($upload_info) {
                            $data[$location][$custom_field['custom_field_id']]['name'] = $upload_info['name'];
                            $data[$location][$custom_field['custom_field_id']]['code'] = $upload_info['code'];
                        } else {
                            $data[$location][$custom_field['custom_field_id']]['name'] = '';
                            $data[$location][$custom_field['custom_field_id']]['code'] = $code;
                        }
                    }
                }
            }
        }

        // Order Statuses
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Countries
        $this->load->model('localisation/country');

        $data['countries'] = $this->model_localisation_country->getCountries();

        // Currencies
        $this->load->model('localisation/currency');

        $data['currencies'] = $this->model_localisation_currency->getCurrencies();

        // Voucher
        $data['voucher_min'] = $this->config->get('config_voucher_min');

        // Voucher Themes
        $this->load->model('sale/voucher_theme');

        $data['voucher_themes'] = $this->model_sale_voucher_theme->getVoucherThemes();

        // API login
        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

		// API login
        $this->load->model('user/api');

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
            // Session
            $session = new \Session($this->config->get('session_engine'), $this->registry);
            $session->start();

            $this->model_user_api->deleteSessionBySessionId($session->getId());
            $this->model_user_api->addSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

            $session->data['api_id'] = $api_info['api_id'];

            $data['api_token'] = $session->getId();
        } else {
            $data['api_token'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('sale/order_form', $data));
    }
	/**
	 * Info
	 *
	 * @return object|\Action|null
	 */
    public function info(): ?object {
        // Orders
        $this->load->model('sale/order');

        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        $order_info = $this->model_sale_order->getOrder($order_id);

        if ($order_info) {
            $this->load->language('sale/order');

            $this->document->setTitle($this->language->get('heading_title'));

            $data['text_ip_add'] = sprintf($this->language->get('text_ip_add'), $this->request->server['REMOTE_ADDR']);

            $data['text_order'] = sprintf($this->language->get('text_order'), $this->request->get['order_id']);

            $url = '';

            if (isset($this->request->get['filter_order_id'])) {
                $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
            }

            if (isset($this->request->get['filter_customer'])) {
                $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_order_status'])) {
                $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
            }

            if (isset($this->request->get['filter_order_status_id'])) {
                $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
            }

            if (isset($this->request->get['filter_total'])) {
                $url .= '&filter_total=' . $this->request->get['filter_total'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['filter_date_modified'])) {
                $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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
                'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true)
            ];

            $data['shipping'] = $this->url->link('sale/order/shipping', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
            $data['invoice'] = $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
            $data['edit'] = $this->url->link('sale/order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
            $data['cancel'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true);
            $data['user_token'] = $this->session->data['user_token'];
            $data['order_id'] = (int)$this->request->get['order_id'];
            $data['store_id'] = $order_info['store_id'];
            $data['store_name'] = $order_info['store_name'];

            if ($order_info['store_id'] == 0) {
                $data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
            } else {
                $data['store_url'] = $order_info['store_url'];
            }

            if ($order_info['invoice_no']) {
                $data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
            } else {
                $data['invoice_no'] = '';
            }

            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
            $data['firstname'] = $order_info['firstname'];
            $data['lastname'] = $order_info['lastname'];

            if ($order_info['customer_id']) {
                $data['customer'] = $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $order_info['customer_id'], true);
            } else {
                $data['customer'] = '';
            }

            // Subscriptions
            $this->load->model('sale/subscription');

            // Customer Groups
            $this->load->model('customer/customer_group');

            // Settings
            $this->load->model('setting/setting');

            $customer_group_info = $this->model_customer_customer_group->getCustomerGroup($order_info['customer_group_id']);

            if ($customer_group_info) {
                $data['customer_group'] = $customer_group_info['name'];
            } else {
                $data['customer_group'] = '';
            }

            $data['email'] = $order_info['email'];
            $data['telephone'] = $order_info['telephone'];
            $data['shipping_method'] = $order_info['shipping_method'];
            $data['payment_method'] = $order_info['payment_method'];

            // Payment Address
            if ($order_info['payment_address_format']) {
                $format = $order_info['payment_address_format'];
            } else {
                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
            }

            $find = [
                '{firstname}',
                '{lastname}',
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}'
            ];

            $replace = [
                'firstname' => $order_info['payment_firstname'],
                'lastname'  => $order_info['payment_lastname'],
                'company'   => $order_info['payment_company'],
                'address_1' => $order_info['payment_address_1'],
                'address_2' => $order_info['payment_address_2'],
                'city'      => $order_info['payment_city'],
                'postcode'  => $order_info['payment_postcode'],
                'zone'      => $order_info['payment_zone'],
                'zone_code' => $order_info['payment_zone_code'],
                'country'   => $order_info['payment_country']
            ];

            $data['payment_address'] = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));

            // Shipping Address
            if ($order_info['shipping_address_format']) {
                $format = $order_info['shipping_address_format'];
            } else {
                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
            }

            $find = [
                '{firstname}',
                '{lastname}',
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}'
            ];

            $replace = [
                'firstname' => $order_info['shipping_firstname'],
                'lastname'  => $order_info['shipping_lastname'],
                'company'   => $order_info['shipping_company'],
                'address_1' => $order_info['shipping_address_1'],
                'address_2' => $order_info['shipping_address_2'],
                'city'      => $order_info['shipping_city'],
                'postcode'  => $order_info['shipping_postcode'],
                'zone'      => $order_info['shipping_zone'],
                'zone_code' => $order_info['shipping_zone_code'],
                'country'   => $order_info['shipping_country']
            ];

            $data['shipping_address'] = str_replace(["\r\n", "\r", "\n" ], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));

            // Subscriptions
            $filter_data = [
                'filter_order_id' => $this->request->get['order_id']
            ];

            $subscriptions = $this->model_sale_subscription->getSubscriptions($filter_data);

            // Uploaded Files
            $this->load->model('tool/upload');

            $data['products'] = [];

            $frequencies = [
                'day',
                'week',
                'semi_month',
                'month',
                'year'
            ];

            $products = $this->model_sale_order->getProducts($this->request->get['order_id']);

            foreach ($products as $product) {
                $option_data = [];

                $options = $this->model_sale_order->getOptions($this->request->get['order_id'], $product['order_product_id']);

                foreach ($options as $option) {
                    if ($option['type'] != 'file') {
                        $option_data[] = [
                            'name'  => $option['name'],
                            'value' => $option['value'],
                            'type'  => $option['type']
                        ];
                    } else {
                        $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($upload_info) {
                            $option_data[] = [
                                'name'  => $option['name'],
                                'value' => $upload_info['name'],
                                'type'  => $option['type'],
                                'href'  => $this->url->link('tool/upload/download', 'user_token=' . $this->session->data['user_token'] . '&code=' . $upload_info['code'], true)
                            ];
                        }
                    }
                }

                // Subscriptions
                $subscription_data = '';

                foreach ($subscriptions as $subscription) {
                    $filter_data = [
                        'filter_subscription_id'        => $subscription['subscription_id'],
                        'filter_order_product_id'       => $product['order_product_id']
                    ];

                    $subscription_info = $this->model_sale_subscription->getSubscriptions($filter_data);

                    if ($subscription_info) {
                        $subscription_data = $subscription_info['name'];
                    }
                }

                $data['products'][] = [
                    'order_product_id' => $product['order_product_id'],
                    'product_id'       => $product['product_id'],
                    'name'             => $product['name'],
                    'model'            => $product['model'],
                    'option'           => $option_data,
                    'subscription'     => $subscription_data,
                    'quantity'         => $product['quantity'],
                    'price'            => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                    'total'            => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
                    'href'             => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product['product_id'], true)
                ];
            }

            $data['vouchers'] = [];

            // Vouchers
            $vouchers = $this->model_sale_order->getVouchers($this->request->get['order_id']);

            foreach ($vouchers as $voucher) {
                $data['vouchers'][] = [
                    'description' => $voucher['description'],
                    'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
                    'href'        => $this->url->link('sale/voucher/edit', 'user_token=' . $this->session->data['user_token'] . '&voucher_id=' . $voucher['voucher_id'], true)
                ];
            }

            // Totals
            $data['totals'] = [];

            $totals = $this->model_sale_order->getTotals($this->request->get['order_id']);

            foreach ($totals as $total) {
                $data['totals'][] = [
                    'title' => $total['title'],
                    'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
                ];
            }

            // Customers
            $this->load->model('customer/customer');

            $data['comment'] = nl2br($order_info['comment']);
            $data['reward'] = $order_info['reward'];
            $data['affiliate_firstname'] = $order_info['affiliate_firstname'];
            $data['affiliate_lastname'] = $order_info['affiliate_lastname'];
            $data['reward_total'] = $this->model_customer_customer->getTotalCustomerRewardsByOrderId($this->request->get['order_id']);

            if ($order_info['affiliate_id']) {
                $data['affiliate'] = $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $order_info['affiliate_id'], true);
            } else {
                $data['affiliate'] = '';
            }

            $data['commission'] = $this->currency->format($order_info['commission'], $order_info['currency_code'], $order_info['currency_value']);
            $data['commission_total'] = $this->model_customer_customer->getTotalTransactionsByOrderId($this->request->get['order_id']);

            // Order Statuses
            $this->load->model('localisation/order_status');

            $order_status_info = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id']);

            if ($order_status_info) {
                $data['order_status'] = $order_status_info['name'];
            } else {
                $data['order_status'] = '';
            }

            $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

            $data['order_status_id'] = $order_info['order_status_id'];
            $data['account_custom_field'] = $order_info['custom_field'];

            // Account Custom Fields
            $this->load->model('customer/custom_field');

            $data['account_custom_fields'] = [];

            $filter_data = [
                'sort'  => 'cf.sort_order',
                'order' => 'ASC'
            ];

            $custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);

            foreach ($custom_fields as $custom_field) {
                if ($custom_field['location'] == 'account' && isset($order_info['custom_field'][$custom_field['custom_field_id']])) {
                    if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
                        $custom_field_value_info = $this->model_customer_custom_field->getValue($order_info['custom_field'][$custom_field['custom_field_id']]);

                        if ($custom_field_value_info) {
                            $data['account_custom_fields'][] = [
                                'name'  => $custom_field['name'],
                                'value' => $custom_field_value_info['name']
                            ];
                        }
                    }

                    if ($custom_field['type'] == 'checkbox' && !empty($order_info['custom_field'][$custom_field['custom_field_id']]) && is_array($order_info['custom_field'][$custom_field['custom_field_id']])) {
                        foreach ($order_info['custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
                            $custom_field_value_info = $this->model_customer_custom_field->getValue($custom_field_value_id);

                            if ($custom_field_value_info) {
                                $data['account_custom_fields'][] = [
                                    'name'  => $custom_field['name'],
                                    'value' => $custom_field_value_info['name']
                                ];
                            }
                        }
                    }

                    if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
                        $data['account_custom_fields'][] = [
                            'name'  => $custom_field['name'],
                            'value' => $order_info['custom_field'][$custom_field['custom_field_id']]
                        ];
                    }

                    if ($custom_field['type'] == 'file') {
                        $upload_info = $this->model_tool_upload->getUploadByCode($order_info['custom_field'][$custom_field['custom_field_id']]);

                        if ($upload_info) {
                            $data['account_custom_fields'][] = [
                                'name'  => $custom_field['name'],
                                'value' => $upload_info['name']
                            ];
                        }
                    }
                }
            }

            // Payment Custom fields
            $data['payment_custom_fields'] = [];

            foreach ($custom_fields as $custom_field) {
                if ($custom_field['location'] == 'address' && isset($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
                    if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
                        $custom_field_value_info = $this->model_customer_custom_field->getValue($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

                        if ($custom_field_value_info) {
                            $data['payment_custom_fields'][] = [
                                'name'       => $custom_field['name'],
                                'value'      => $custom_field_value_info['name'],
                                'sort_order' => $custom_field['sort_order']
                            ];
                        }
                    }

                    if ($custom_field['type'] == 'checkbox' && !empty($order_info['payment_custom_field'][$custom_field['custom_field_id']]) && is_array($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
                        foreach ($order_info['payment_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
                            $custom_field_value_info = $this->model_customer_custom_field->getValue($custom_field_value_id);

                            if ($custom_field_value_info) {
                                $data['payment_custom_fields'][] = [
                                    'name'       => $custom_field['name'],
                                    'value'      => $custom_field_value_info['name'],
                                    'sort_order' => $custom_field['sort_order']
                                ];
                            }
                        }
                    }

                    if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
                        $data['payment_custom_fields'][] = [
                            'name'       => $custom_field['name'],
                            'value'      => $order_info['payment_custom_field'][$custom_field['custom_field_id']],
                            'sort_order' => $custom_field['sort_order']
                        ];
                    }

                    if ($custom_field['type'] == 'file') {
                        $upload_info = $this->model_tool_upload->getUploadByCode($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

                        if ($upload_info) {
                            $data['payment_custom_fields'][] = [
                                'name'       => $custom_field['name'],
                                'value'      => $upload_info['name'],
                                'sort_order' => $custom_field['sort_order']
                            ];
                        }
                    }
                }
            }

            // Shipping Custom Fields
            $data['shipping_custom_fields'] = [];

            foreach ($custom_fields as $custom_field) {
                if ($custom_field['location'] == 'address' && isset($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
                    if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
                        $custom_field_value_info = $this->model_customer_custom_field->getValue($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

                        if ($custom_field_value_info) {
                            $data['shipping_custom_fields'][] = [
                                'name'       => $custom_field['name'],
                                'value'      => $custom_field_value_info['name'],
                                'sort_order' => $custom_field['sort_order']
                            ];
                        }
                    }

                    if ($custom_field['type'] == 'checkbox' && !empty($order_info['shipping_custom_field'][$custom_field['custom_field_id']]) && is_array($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
                        foreach ($order_info['shipping_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
                            $custom_field_value_info = $this->model_customer_custom_field->getValue($custom_field_value_id);

                            if ($custom_field_value_info) {
                                $data['shipping_custom_fields'][] = [
                                    'name'       => $custom_field['name'],
                                    'value'      => $custom_field_value_info['name'],
                                    'sort_order' => $custom_field['sort_order']
                                ];
                            }
                        }
                    }

                    if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
                        $data['shipping_custom_fields'][] = [
                            'name'       => $custom_field['name'],
                            'value'      => $order_info['shipping_custom_field'][$custom_field['custom_field_id']],
                            'sort_order' => $custom_field['sort_order']
                        ];
                    }

                    if ($custom_field['type'] == 'file') {
                        $upload_info = $this->model_tool_upload->getUploadByCode($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

                        if ($upload_info) {
                            $data['shipping_custom_fields'][] = [
                                'name'       => $custom_field['name'],
                                'value'      => $upload_info['name'],
                                'sort_order' => $custom_field['sort_order']
                            ];
                        }
                    }
                }
            }

            $data['ip'] = $order_info['ip'];
            $data['forwarded_ip'] = $order_info['forwarded_ip'];
            $data['user_agent'] = $order_info['user_agent'];
            $data['accept_language'] = $order_info['accept_language'];

            // Additional Tabs
            $data['tabs'] = [];

            if ($this->user->hasPermission('access', 'extension/payment/' . $order_info['payment_code'])) {
                if (is_file(DIR_CATALOG . 'controller/extension/payment/' . $order_info['payment_code'] . '.php')) {
                    $content = $this->load->controller('extension/payment/' . $order_info['payment_code'] . '/order');
                } else {
                    $content = '';
                }

                if ($content) {
                    $this->load->language('extension/payment/' . $order_info['payment_code']);

                    $data['tabs'][] = [
                        'code'    => $order_info['payment_code'],
                        'title'   => $this->language->get('heading_title'),
                        'content' => $content
                    ];
                }
            }

            // Extemsions
            $this->load->model('setting/extension');

            $extensions = $this->model_setting_extension->getExtensionsByType('fraud');

            foreach ($extensions as $extension) {
                if ($this->config->get('fraud_' . $extension . '_status')) {
                    $this->load->language('extension/fraud/' . $extension, 'extension');

                    $content = $this->load->controller('extension/fraud/' . $extension . '/order');

                    if ($content) {
                        $data['tabs'][] = [
                            'code'    => $extension,
                            'title'   => $this->language->get('extension')->get('heading_title'),
                            'content' => $content
                        ];
                    }
                }
            }

            // The URL we send API requests to
            $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

            // API login
            $this->load->model('user/api');

            $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

            if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
                // Session
                $session = new \Session($this->config->get('session_engine'), $this->registry);
                $session->start();

                $this->model_user_api->deleteSessionBySessionId($session->getId());
                $this->model_user_api->addSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

                $session->data['api_id'] = $api_info['api_id'];

                $data['api_token'] = $session->getId();
            } else {
                $data['api_token'] = '';
            }

            $data['config_telephone_required'] = $this->config->get('config_telephone_required');

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('sale/order_info', $data));
        } else {
            return new \Action('error/not_found');
        }

        return null;
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
	/**
	 * createInvoiceNo
	 *
	 * @return void
	 */
    public function createInvoiceNo(): void {
        $this->load->language('sale/order');

        $json = [];

        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $json['error'] = $this->language->get('error_permission');
        } elseif (isset($this->request->get['order_id'])) {
            if (isset($this->request->get['order_id'])) {
                $order_id = (int)$this->request->get['order_id'];
            } else {
                $order_id = 0;
            }

            // Orders
            $this->load->model('sale/order');

            $invoice_no = $this->model_sale_order->createInvoiceNo($order_id);

            if ($invoice_no) {
                $json['invoice_no'] = $invoice_no;
            } else {
                $json['error'] = $this->language->get('error_action');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	/**
	 * addReward
	 *
	 * @return void
	 */
    public function addReward(): void {
        $this->load->language('sale/order');

        $json = [];

        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->get['order_id'])) {
                $order_id = (int)$this->request->get['order_id'];
            } else {
                $order_id = 0;
            }

            // Orders
            $this->load->model('sale/order');

            $order_info = $this->model_sale_order->getOrder($order_id);

            if ($order_info && $order_info['customer_id'] && ($order_info['reward'] > 0)) {
                // Customers
                $this->load->model('customer/customer');

                $reward_total = $this->model_customer_customer->getTotalCustomerRewardsByOrderId($order_id);

                if (!$reward_total) {
                    $this->model_customer_customer->addReward($order_info['customer_id'], $this->language->get('text_orders_id') . ' #' . $order_id, $order_info['reward'], $order_id);
                }
            }

            $json['success'] = $this->language->get('text_reward_added');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	/**
	 * removeReward
	 *
	 * @return void
	 */
    public function removeReward(): void {
        $this->load->language('sale/order');

        $json = [];

        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->get['order_id'])) {
                $order_id = (int)$this->request->get['order_id'];
            } else {
                $order_id = 0;
            }

            // Orders
            $this->load->model('sale/order');

            $order_info = $this->model_sale_order->getOrder($order_id);

            if ($order_info) {
                // Customer
                $this->load->model('customer/customer');

                $this->model_customer_customer->deleteReward($order_id);
            }

            $json['success'] = $this->language->get('text_reward_removed');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	/**
	 * addCommission
	 *
	 * @return void
	 */
    public function addCommission(): void {
        $this->load->language('sale/order');

        $json = [];

        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->get['order_id'])) {
                $order_id = (int)$this->request->get['order_id'];
            } else {
                $order_id = 0;
            }

            // Orders
            $this->load->model('sale/order');

            $order_info = $this->model_sale_order->getOrder($order_id);

            if ($order_info) {
                // Customers
                $this->load->model('customer/customer');

                $affiliate_total = $this->model_customer_customer->getTotalTransactionsByOrderId($order_id);

                if (!$affiliate_total) {
                    $this->model_customer_customer->addTransaction($order_info['affiliate_id'], $this->language->get('text_orders_id') . ' #' . $order_id, $order_info['commission'], $order_id);
                }
            }

            $json['success'] = $this->language->get('text_commission_added');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	/**
	 * removeCommission
	 *
	 * @return void
	 */
    public function removeCommission(): void {
        $this->load->language('sale/order');

        $json = [];

        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->get['order_id'])) {
                $order_id = (int)$this->request->get['order_id'];
            } else {
                $order_id = 0;
            }

            // Orders
            $this->load->model('sale/order');

            $order_info = $this->model_sale_order->getOrder($order_id);

            if ($order_info) {
                // Customer
                $this->load->model('customer/customer');

                $this->model_customer_customer->deleteTransactionByOrderId($order_id);
            }

            $json['success'] = $this->language->get('text_commission_removed');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	/**
	 * History
	 *
	 * @return void
	 */
    public function history(): void {
        $this->load->language('sale/order');

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['histories'] = [];

        // Orders
        $this->load->model('sale/order');

        $results = $this->model_sale_order->getHistories($this->request->get['order_id'], ($page - 1) * 10, 10);

        foreach ($results as $result) {
            $data['histories'][] = [
                'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
                'status'     => $result['status'],
                'comment'    => nl2br($result['comment']),
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            ];
        }

        $history_total = $this->model_sale_order->getTotalOrderHistories($this->request->get['order_id']);

        $pagination = new \Pagination();
        $pagination->total = $history_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->url->link('sale/order/history', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $this->request->get['order_id'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

        $this->response->setOutput($this->load->view('sale/order_history', $data));
    }

	/**
	 * Invoice
	 *
	 * @return void
	 */
    public function invoice(): void {
        $this->load->language('sale/order');

        $data['title'] = $this->language->get('text_invoice');

        if ($this->request->server['HTTPS']) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }

        $data['direction'] = $this->language->get('direction');
        $data['lang'] = $this->language->get('code');

        // Orders
        $this->load->model('sale/order');

        // Settings
        $this->load->model('setting/setting');

        // Subscriptions
        $this->load->model('sale/subscription');

        // Subscription Status
        $this->load->model('localisation/subscription_status');

        // Uploaded Files
        $this->load->model('tool/upload');

        $data['orders'] = [];

        $orders = [];

        if (isset($this->request->post['selected'])) {
            $orders = (array)$this->request->post['selected'];
        } elseif (isset($this->request->get['order_id'])) {
            $orders[] = (int)$this->request->get['order_id'];
        }

        $frequencies = [
            'day',
            'week',
            'semi_month',
            'month',
            'year'
        ];

        foreach ($orders as $order_id) {
            $order_info = $this->model_sale_order->getOrder($order_id);

            $text_order = sprintf($this->language->get('text_order'), $order_id);

            if ($order_info) {
                $store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

                if ($store_info) {
                    $store_address = $store_info['config_address'];
                    $store_email = $store_info['config_email'];
                    $store_telephone = $store_info['config_telephone'];
                    $store_fax = $store_info['config_fax'];
                } else {
                    $store_address = $this->config->get('config_address');
                    $store_email = $this->config->get('config_email');
                    $store_telephone = $this->config->get('config_telephone');
                    $store_fax = $this->config->get('config_fax');
                }

                if ($order_info['invoice_no']) {
                    $invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
                } else {
                    $invoice_no = '';
                }

                // Payment Address
                if ($order_info['payment_address_format']) {
                    $format = $order_info['payment_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = [
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                ];

                $replace = [
                    'firstname' => $order_info['payment_firstname'],
                    'lastname'  => $order_info['payment_lastname'],
                    'company'   => $order_info['payment_company'],
                    'address_1' => $order_info['payment_address_1'],
                    'address_2' => $order_info['payment_address_2'],
                    'city'      => $order_info['payment_city'],
                    'postcode'  => $order_info['payment_postcode'],
                    'zone'      => $order_info['payment_zone'],
                    'zone_code' => $order_info['payment_zone_code'],
                    'country'   => $order_info['payment_country']
                ];

                $payment_address = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));

                // Shipping Address
                if ($order_info['shipping_address_format']) {
                    $format = $order_info['shipping_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = [
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                ];

                $replace = [
                    'firstname' => $order_info['shipping_firstname'],
                    'lastname'  => $order_info['shipping_lastname'],
                    'company'   => $order_info['shipping_company'],
                    'address_1' => $order_info['shipping_address_1'],
                    'address_2' => $order_info['shipping_address_2'],
                    'city'      => $order_info['shipping_city'],
                    'postcode'  => $order_info['shipping_postcode'],
                    'zone'      => $order_info['shipping_zone'],
                    'zone_code' => $order_info['shipping_zone_code'],
                    'country'   => $order_info['shipping_country']
                ];

                $shipping_address = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));

                // Subscriptions
                $filter_data = [
                    'filter_order_id' => $order_id
                ];

                $subscriptions = $this->model_sale_subscription->getSubscriptions($filter_data);

                $product_data = [];

                // Products
                $products = $this->model_sale_order->getProducts($order_id);

                foreach ($products as $product) {
                    $option_data = [];

                    $options = $this->model_sale_order->getOptions($order_id, $product['order_product_id']);

                    foreach ($options as $option) {
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
                            'value' => $value
                        ];
                    }

                    // Subscriptions
                    $subscription_data = '';

                    foreach ($subscriptions as $subscription) {
                        $filter_data = [
                            'filter_subscription_id'        => $subscription['subscription_id'],
                            'filter_order_product_id'       => $product['order_product_id']
                        ];

                        $subscription_info = $this->model_sale_subscription->getSubscriptions($filter_data);

                        if ($subscription_info) {
                            $subscription_data = $subscription_info['name'];
                        }
                    }

                    $product_data[] = [
                        'name'         => $product['name'],
                        'model'        => $product['model'],
                        'option'       => $option_data,
                        'subscription' => $subscription_data,
                        'quantity'     => $product['quantity'],
                        'price'        => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                        'total'        => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
                    ];
                }

                $voucher_data = [];

                $vouchers = $this->model_sale_order->getVouchers($order_id);

                foreach ($vouchers as $voucher) {
                    $voucher_data[] = [
                        'description' => $voucher['description'],
                        'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
                    ];
                }

                $total_data = [];

                $totals = $this->model_sale_order->getTotals($order_id);

                foreach ($totals as $total) {
                    $total_data[] = [
                        'title' => $total['title'],
                        'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
                    ];
                }

                $data['orders'][] = [
                    'order_id'         => $order_id,
                    'invoice_no'       => $invoice_no,
                    'text_order'       => $text_order,
                    'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                    'store_name'       => $order_info['store_name'],
                    'store_url'        => rtrim($order_info['store_url'], '/'),
                    'store_address'    => nl2br($store_address),
                    'store_email'      => $store_email,
                    'store_telephone'  => $store_telephone,
                    'store_fax'        => $store_fax,
                    'email'            => $order_info['email'],
                    'telephone'        => $order_info['telephone'],
                    'shipping_address' => $shipping_address,
                    'shipping_method'  => $order_info['shipping_method'],
                    'payment_address'  => $payment_address,
                    'payment_method'   => $order_info['payment_method'],
                    'product'          => $product_data,
                    'voucher'          => $voucher_data,
                    'total'            => $total_data,
                    'comment'          => nl2br($order_info['comment'])
                ];
            }
        }

        $this->response->setOutput($this->load->view('sale/order_invoice', $data));
    }

	/**
	 * Shipping
	 *
	 * @return void
	 */
    public function shipping(): void {
        $this->load->language('sale/order');

        $data['title'] = $this->language->get('text_shipping');

        if ($this->request->server['HTTPS']) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }

        $data['direction'] = $this->language->get('direction');
        $data['lang'] = $this->language->get('code');

        // Orders
        $this->load->model('sale/order');

        // Products
        $this->load->model('catalog/product');

        // Settings
        $this->load->model('setting/setting');

        // Subscriptions
        $this->load->model('sale/subscription');

        // Subscription Status
        $this->load->model('localisation/subscription_status');

        $data['orders'] = [];

        $orders = [];

        if (isset($this->request->post['selected'])) {
            $orders = (array)$this->request->post['selected'];
        } elseif (isset($this->request->get['order_id'])) {
            $orders[] = (int)$this->request->get['order_id'];
        }

        $frequencies = [
            'day',
            'week',
            'semi_month',
            'month',
            'year'
        ];

        foreach ($orders as $order_id) {
            $order_info = $this->model_sale_order->getOrder($order_id);

            // Make sure there is a shipping method
            if ($order_info && $order_info['shipping_code']) {
                $store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

                if ($store_info) {
                    $store_address = $store_info['config_address'];
                    $store_email = $store_info['config_email'];
                    $store_telephone = $store_info['config_telephone'];
                } else {
                    $store_address = $this->config->get('config_address');
                    $store_email = $this->config->get('config_email');
                    $store_telephone = $this->config->get('config_telephone');
                }

                if ($order_info['invoice_no']) {
                    $invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
                } else {
                    $invoice_no = '';
                }

                // Shipping Address
                if ($order_info['shipping_address_format']) {
                    $format = $order_info['shipping_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = [
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                ];

                $replace = [
                    'firstname' => $order_info['shipping_firstname'],
                    'lastname'  => $order_info['shipping_lastname'],
                    'company'   => $order_info['shipping_company'],
                    'address_1' => $order_info['shipping_address_1'],
                    'address_2' => $order_info['shipping_address_2'],
                    'city'      => $order_info['shipping_city'],
                    'postcode'  => $order_info['shipping_postcode'],
                    'zone'      => $order_info['shipping_zone'],
                    'zone_code' => $order_info['shipping_zone_code'],
                    'country'   => $order_info['shipping_country']
                ];

                $shipping_address = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));

                $product_data = [];

                // Subscriptions
                $filter_data = [
                    'filter_order_id' => $order_id
                ];

                $subscriptions = $this->model_sale_subscription->getSubscriptions($filter_data);

                // Uploaded Files
                $this->load->model('tool/upload');

                // Products
                $products = $this->model_sale_order->getProducts($order_id);

                foreach ($products as $product) {
                    $option_weight = 0;

                    $product_info = $this->model_catalog_product->getProduct($product['product_id']);

                    if ($product_info) {
                        $option_data = [];

                        $options = $this->model_sale_order->getOptions($order_id, $product['order_product_id']);

                        foreach ($options as $option) {
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
                                'value' => $value
                            ];

                            $product_option_value_info = $this->model_catalog_product->getOptionValue($product['product_id'], $option['product_option_value_id']);

                            if (!empty($product_option_value_info['weight'])) {
                                if ($product_option_value_info['weight_prefix'] == '+') {
                                    $option_weight += $product_option_value_info['weight'];
                                } elseif ($product_option_value_info['weight_prefix'] == '-') {
                                    $option_weight -= $product_option_value_info['weight'];
                                }
                            }
                        }

                        // Subscriptions
                        $subscription_data = '';

                        foreach ($subscriptions as $subscription) {
                            $filter_data = [
                                'filter_subscription_id'        => $subscription['subscription_id'],
                                'filter_order_product_id'       => $product['order_product_id']
                            ];

                            $subscription_info = $this->model_sale_subscription->getSubscriptions($filter_data);

                            if ($subscription_info) {
                                $subscription_data = $subscription_info['name'];
                            }
                        }

                        $product_data[] = [
                            'name'         => $product_info['name'],
                            'model'        => $product_info['model'],
                            'option'       => $option_data,
                            'subscription' => $subscription_data,
                            'quantity'     => $product['quantity'],
                            'location'     => $product_info['location'],
                            'sku'          => $product_info['sku'],
                            'upc'          => $product_info['upc'],
                            'ean'          => $product_info['ean'],
                            'jan'          => $product_info['jan'],
                            'isbn'         => $product_info['isbn'],
                            'mpn'          => $product_info['mpn'],
                            'weight'       => $this->weight->format(($product_info['weight'] + (float)$option_weight) * $product['quantity'], $product_info['weight_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point'))
                        ];
                    }
                }

                $data['orders'][] = [
                    'order_id'         => $order_id,
                    'invoice_no'       => $invoice_no,
                    'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                    'store_name'       => $order_info['store_name'],
                    'store_url'        => rtrim($order_info['store_url'], '/'),
                    'store_address'    => nl2br($store_address),
                    'store_email'      => $store_email,
                    'store_telephone'  => $store_telephone,
                    'email'            => $order_info['email'],
                    'telephone'        => $order_info['telephone'],
                    'shipping_address' => $shipping_address,
                    'shipping_method'  => $order_info['shipping_method'],
                    'product'          => $product_data,
                    'comment'          => nl2br($order_info['comment'])
                ];
            }
        }

        $this->response->setOutput($this->load->view('sale/order_shipping', $data));
    }
}
