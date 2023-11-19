<?php
/**
 * Class Newsletter
 *
 * @package Catalog\Controller\Account
 */
class ControllerAccountNewsletter extends Controller {
    public function index(): void {
        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/newsletter', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/newsletter');

        $this->document->setTitle($this->language->get('heading_title'));

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // Customers
            $this->load->model('account/customer');

            $this->model_account_customer->editNewsletter($this->request->post['newsletter']);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true));
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
            'text' => $this->language->get('text_newsletter'),
            'href' => $this->url->link('account/newsletter', 'customer_token=' . $this->session->data['customer_token'], true)
        ];

        $data['action'] = $this->url->link('account/newsletter', 'customer_token=' . $this->session->data['customer_token'], true);
        $data['back'] = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);
        $data['newsletter'] = $this->customer->getNewsletter();

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/newsletter', $data));
    }
}
