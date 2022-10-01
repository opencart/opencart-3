<?php
class ControllerLocalisationAddressFormat extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('localisation/address_format');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/address_format');

        $this->getList();
    }

    public function add(): void {
        $this->load->language('localisation/address_format');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/address_format');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_localisation_address_format->addAddressFormat($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('localisation/address_format', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('localisation/address_format');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/address_format');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_localisation_address_format->editAddressFormat($this->request->get['address_format_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('localisation/address_format', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('localisation/address_format');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/address_format');
        $this->load->model('localisation/country');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ((array)$this->request->post['selected'] as $address_format_id) {
                $this->model_localisation_address_format->deleteAddressFormat($address_format_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('localisation/address_format', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->error['address_format'])) {
            $data['error_address_format'] = $this->error['address_format'];
        } else {
            $data['error_address_format'] = '';
        }

        if (isset($this->error['country'])) {
            $data['error_country'] = $this->error['country'];
        } else {
            $data['error_country'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

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
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/address_format', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        $data['add']    = $this->url->link('localisation/address_format/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('localisation/address_format/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['address_formats'] = [];

        $filter_data = [
            'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
            'limit' => $this->config->get('config_pagination_admin')
        ];

        $this->load->model('localisation/address_format');

        $address_format_total = $this->model_localisation_address_format->getTotalAddressFormats($filter_data);

        $results = $this->model_localisation_address_format->getAddressFormats($filter_data);

        foreach ($results as $result) {
            $data['address_formats'][] = [
                'address_format_id' => $result['address_format_id'],
                'name'              => $result['name'] . (($result['address_format_id'] == $this->config->get('config_address_format_id')) ? $this->language->get('text_default') : ''),
                'address_format'    => nl2br($result['address_format']),
                'edit'              => $this->url->link('localisation/address_format/edit', 'user_token=' . $this->session->data['user_token'] . '&address_format_id=' . $result['address_format_id'] . $url, true)
            ];
        }

        $pagination        = new \Pagination();
        $pagination->total = $address_format_total;
        $pagination->page  = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url   = $this->url->link('localisation/address_format', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($address_format_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($address_format_total - $this->config->get('config_limit_admin'))) ? $address_format_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $address_format_total, ceil($address_format_total / $this->config->get('config_limit_admin')));

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('localisation/address_format_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['address_format_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/address_format', 'user_token=' . $this->session->data['user_token'], true)
        ];

        if (!isset($this->request->get['address_format_id'])) {
            $data['action'] = $this->url->link('localisation/address_format/add', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('localisation/address_format/edit', 'user_token=' . $this->session->data['user_token'] . '&address_format_id=' . $this->request->get['address_format_id'], true);
        }

        $data['cancel'] = $this->url->link('localisation/address_format', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->request->get['address_format_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $address_format_info = $this->model_localisation_address_format->getAddressFormat($this->request->get['address_format_id']);
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($address_format_info)) {
            $data['name'] = $address_format_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['address_format'])) {
            $data['address_format'] = $this->request->post['address_format'];
        } elseif (!empty($address_format_info)) {
            $data['address_format'] = $address_format_info['address_format'];
        } else {
            $data['address_format'] = '';
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('localisation/address_format_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'localisation/address_format')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 128)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'localisation/address_format')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('localisation/address_format');

        foreach ((array)$this->request->post['selected'] as $address_format_id) {
            if ($this->config->get('config_address_format_id') == $address_format_id) {
                $this->error['address_format'] = $this->language->get('error_default');
            }

            $country_total = $this->model_localisation_country->getTotalCountriesByAddressFormatId($address_format_id);

            if ($country_total) {
                $this->error['country'] = sprintf($this->language->get('error_country'), $country_total);
            }
        }

        return !$this->error;
    }
}