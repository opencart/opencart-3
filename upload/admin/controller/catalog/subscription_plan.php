<?php
class ControllerCatalogSubscriptionPlan extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('catalog/subscription_plan');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/subscription_plan');

        $this->getList();
    }

    public function add(): void {
        $this->load->language('catalog/subscription_plan');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/subscription_plan');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_subscription_plan->addSubscription($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('catalog/subscription_plan');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/subscription_plan');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_subscription_plan->editSubscriptionPlan($this->request->get['subscription_plan_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('catalog/subscription_plan');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/subscription_plan');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ((array)$this->request->post['selected'] as $subscription_plan_id) {
                $this->model_catalog_subscription_plan->deleteSubscriptionPlan($subscription_plan_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    public function copy(): void {
        $this->load->language('catalog/subscription_plan');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/subscription_plan');

        if (isset($this->request->post['selected']) && $this->validateCopy()) {
            foreach ((array)$this->request->post['selected'] as $subscription_plan_id) {
                $this->model_catalog_subscription_plan->copySubscriptionPlan($subscription_plan_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'rd.name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

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
            'href' => $this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        $data['add']    = $this->url->link('catalog/subscription_plan/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['copy']   = $this->url->link('catalog/subscription_plan/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('catalog/subscription_plan/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['subscription_plans'] = [];

        $filter_data = [
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        ];

        $subscription_plan_total = $this->model_catalog_subscription_plan->getTotalsubscription_plans();

        $results = $this->model_catalog_subscription_plan->getsubscription_plans($filter_data);

        foreach ($results as $result) {
            $data['subscription_plans'][] = [
                'subscription_plan_id' => $result['subscription_plan_id'],
                'name'                 => $result['name'],
                'sort_order'           => $result['sort_order'],
                'edit'                 => $this->url->link('catalog/subscription_plan/edit', 'user_token=' . $this->session->data['user_token'] . '&subscription_plan_id=' . $result['subscription_plan_id'] . $url, true)
            ];
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

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = [];
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name']       = $this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
        $data['sort_sort_order'] = $this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $url, true);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination        = new \Pagination();
        $pagination->total = $subscription_plan_total;
        $pagination->page  = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url   = $this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($subscription_plan_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($subscription_plan_total - $this->config->get('config_limit_admin'))) ? $subscription_plan_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $subscription_plan_total, ceil($subscription_plan_total / $this->config->get('config_limit_admin')));

        $data['sort']  = $sort;
        $data['order'] = $order;

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('catalog/subscription_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['subscription_plan_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = [];
        }

        $url = '';

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
            'href' => $this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        if (!isset($this->request->get['subscription_plan_id'])) {
            $data['action'] = $this->url->link('catalog/subscription_plan/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('catalog/subscription_plan/edit', 'user_token=' . $this->session->data['user_token'] . '&subscription_plan_id=' . $this->request->get['subscription_plan_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['subscription_plan_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $subscription_plan_info = $this->model_catalog_subscription_plan->getSubscriptionPlan($this->request->get['subscription_plan_id']);
        }

        $data['user_token'] = $this->session->data['user_token'];

        // Languages
        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['subscription_plan_description'])) {
            $data['subscription_plan_description'] = $this->request->post['subscription_plan_description'];
        } elseif (!empty($subscription_plan_info)) {
            $data['subscription_plan_description'] = $this->model_catalog_subscription_plan->getSubscriptionPlanDescription($subscription_plan_info['subscription_plan_id']);
        } else {
            $data['subscription_plan_description'] = [];
        }

        if (isset($this->request->post['price'])) {
            $data['price'] = $this->request->post['price'];
        } elseif (!empty($subscription_plan_info)) {
            $data['price'] = $subscription_plan_info['price'];
        } else {
            $data['price'] = 0;
        }

        $data['frequencies'] = [];

        $data['frequencies'][] = [
            'text'  => $this->language->get('text_day'),
            'value' => 'day'
        ];

        $data['frequencies'][] = [
            'text'  => $this->language->get('text_week'),
            'value' => 'week'
        ];

        $data['frequencies'][] = [
            'text'  => $this->language->get('text_semi_month'),
            'value' => 'semi_month'
        ];

        $data['frequencies'][] = [
            'text'  => $this->language->get('text_month'),
            'value' => 'month'
        ];

        $data['frequencies'][] = [
            'text'  => $this->language->get('text_year'),
            'value' => 'year'
        ];

        if (isset($this->request->post['frequency'])) {
            $data['frequency'] = $this->request->post['frequency'];
        } elseif (!empty($subscription_plan_info)) {
            $data['frequency'] = $subscription_plan_info['frequency'];
        } else {
            $data['frequency'] = '';
        }

        if (isset($this->request->post['duration'])) {
            $data['duration'] = $this->request->post['duration'];
        } elseif (!empty($subscription_plan_info)) {
            $data['duration'] = $subscription_plan_info['duration'];
        } else {
            $data['duration'] = 0;
        }

        if (isset($this->request->post['cycle'])) {
            $data['cycle'] = $this->request->post['cycle'];
        } elseif (!empty($subscription_plan_info)) {
            $data['cycle'] = $subscription_plan_info['cycle'];
        } else {
            $data['cycle'] = 1;
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($subscription_plan_info)) {
            $data['status'] = $subscription_plan_info['status'];
        } else {
            $data['status'] = 0;
        }

        if (isset($this->request->post['trial_price'])) {
            $data['trial_price'] = $this->request->post['trial_price'];
        } elseif (!empty($subscription_plan_info)) {
            $data['trial_price'] = $subscription_plan_info['trial_price'];
        } else {
            $data['trial_price'] = 0.00;
        }

        if (isset($this->request->post['trial_frequency'])) {
            $data['trial_frequency'] = $this->request->post['trial_frequency'];
        } elseif (!empty($subscription_plan_info)) {
            $data['trial_frequency'] = $subscription_plan_info['trial_frequency'];
        } else {
            $data['trial_frequency'] = '';
        }

        if (isset($this->request->post['trial_duration'])) {
            $data['trial_duration'] = $this->request->post['trial_duration'];
        } elseif (!empty($subscription_plan_info)) {
            $data['trial_duration'] = $subscription_plan_info['trial_duration'];
        } else {
            $data['trial_duration'] = '0';
        }

        if (isset($this->request->post['trial_cycle'])) {
            $data['trial_cycle'] = $this->request->post['trial_cycle'];
        } elseif (!empty($subscription_plan_info)) {
            $data['trial_cycle'] = $subscription_plan_info['trial_cycle'];
        } else {
            $data['trial_cycle'] = '1';
        }

        if (isset($this->request->post['trial_status'])) {
            $data['trial_status'] = $this->request->post['trial_status'];
        } elseif (!empty($subscription_plan_info)) {
            $data['trial_status'] = $subscription_plan_info['trial_status'];
        } else {
            $data['trial_status'] = 0;
        }

        if (isset($this->request->post['sort_order'])) {
            $data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($subscription_plan_info)) {
            $data['sort_order'] = $subscription_plan_info['sort_order'];
        } else {
            $data['sort_order'] = 0;
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('catalog/subscription_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'catalog/subscription')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->request->post['subscription_plan_description'] as $language_id => $value) {
            if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
                $this->error['name'][$language_id] = $this->language->get('error_name');
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'catalog/subscription')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('catalog/product');

        foreach ((array)$this->request->post['selected'] as $subscription_plan_id) {
            $product_total = $this->model_catalog_product->getTotalProductsBySubscriptionPlanId($subscription_plan_id);

            if ($product_total) {
                $this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
            }
        }

        return !$this->error;
    }

    protected function validateCopy() {
        if (!$this->user->hasPermission('modify', 'catalog/subscription')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}