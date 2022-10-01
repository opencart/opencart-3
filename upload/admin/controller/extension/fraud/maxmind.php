<?php
class ControllerExtensionFraudMaxMind extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/fraud/maxmind');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('fraud_maxmind', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=fraud', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['key'])) {
            $data['error_key'] = $this->error['key'];
        } else {
            $data['error_key'] = '';
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=fraud', true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/fraud/maxmind', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['action'] = $this->url->link('extension/fraud/maxmind', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=fraud', true);

        if (isset($this->request->post['fraud_maxmind_key'])) {
            $data['fraud_maxmind_key'] = $this->request->post['fraud_maxmind_key'];
        } else {
            $data['fraud_maxmind_key'] = $this->config->get('fraud_maxmind_key');
        }

        if (isset($this->request->post['fraud_maxmind_score'])) {
            $data['fraud_maxmind_score'] = $this->request->post['fraud_maxmind_score'];
        } else {
            $data['fraud_maxmind_score'] = $this->config->get('fraud_maxmind_score');
        }

        if (isset($this->request->post['fraud_maxmind_order_status_id'])) {
            $data['fraud_maxmind_order_status_id'] = $this->request->post['fraud_maxmind_order_status_id'];
        } else {
            $data['fraud_maxmind_order_status_id'] = $this->config->get('fraud_maxmind_order_status_id');
        }

        // Order Statuses
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['fraud_maxmind_status'])) {
            $data['fraud_maxmind_status'] = $this->request->post['fraud_maxmind_status'];
        } else {
            $data['fraud_maxmind_status'] = $this->config->get('fraud_maxmind_status');
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/fraud/maxmind', $data));
    }

    public function install(): void {
        $this->load->model('extension/fraud/maxmind');

        $this->model_extension_fraud_maxmind->install();
    }

    public function uninstall(): void {
        $this->load->model('extension/fraud/maxmind');

        $this->model_extension_fraud_maxmind->uninstall();
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/fraud/maxmind')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['fraud_maxmind_key']) {
            $this->error['key'] = $this->language->get('error_key');
        }

        return !$this->error;
    }

    public function order(): string {
        $this->load->language('extension/fraud/maxmind');

        $this->load->model('extension/fraud/maxmind');

        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        $fraud_info = $this->model_extension_fraud_maxmind->getOrder($order_id);

        if ($fraud_info) {
            $data['country_match'] = $fraud_info['country_match'];

            if ($fraud_info['country_code']) {
                $data['country_code'] = $fraud_info['country_code'];
            } else {
                $data['country_code'] = '';
            }

            $data['high_risk_country'] = $fraud_info['high_risk_country'];
            $data['distance']          = $fraud_info['distance'];

            if ($fraud_info['ip_region']) {
                $data['ip_region'] = $fraud_info['ip_region'];
            } else {
                $data['ip_region'] = '';
            }

            if ($fraud_info['ip_city']) {
                $data['ip_city'] = $fraud_info['ip_city'];
            } else {
                $data['ip_city'] = '';
            }

            $data['ip_latitude']  = $fraud_info['ip_latitude'];
            $data['ip_longitude'] = $fraud_info['ip_longitude'];

            if ($fraud_info['ip_isp']) {
                $data['ip_isp'] = $fraud_info['ip_isp'];
            } else {
                $data['ip_isp'] = '';
            }

            if ($fraud_info['ip_org']) {
                $data['ip_org'] = $fraud_info['ip_org'];
            } else {
                $data['ip_org'] = '';
            }

            $data['ip_asnum'] = $fraud_info['ip_asnum'];

            if ($fraud_info['ip_user_type']) {
                $data['ip_user_type'] = $fraud_info['ip_user_type'];
            } else {
                $data['ip_user_type'] = '';
            }

            if ($fraud_info['ip_country_confidence']) {
                $data['ip_country_confidence'] = $fraud_info['ip_country_confidence'];
            } else {
                $data['ip_country_confidence'] = '';
            }

            if ($fraud_info['ip_region_confidence']) {
                $data['ip_region_confidence'] = $fraud_info['ip_region_confidence'];
            } else {
                $data['ip_region_confidence'] = '';
            }

            if ($fraud_info['ip_city_confidence']) {
                $data['ip_city_confidence'] = $fraud_info['ip_city_confidence'];
            } else {
                $data['ip_city_confidence'] = '';
            }

            if ($fraud_info['ip_postal_confidence']) {
                $data['ip_postal_confidence'] = $fraud_info['ip_postal_confidence'];
            } else {
                $data['ip_postal_confidence'] = '';
            }

            if ($fraud_info['ip_postal_code']) {
                $data['ip_postal_code'] = $fraud_info['ip_postal_code'];
            } else {
                $data['ip_postal_code'] = '';
            }

            $data['ip_accuracy_radius'] = $fraud_info['ip_accuracy_radius'];

            if ($fraud_info['ip_net_speed_cell']) {
                $data['ip_net_speed_cell'] = $fraud_info['ip_net_speed_cell'];
            } else {
                $data['ip_net_speed_cell'] = '';
            }

            $data['ip_metro_code'] = $fraud_info['ip_metro_code'];
            $data['ip_area_code']  = $fraud_info['ip_area_code'];

            if ($fraud_info['ip_time_zone']) {
                $data['ip_time_zone'] = $fraud_info['ip_time_zone'];
            } else {
                $data['ip_time_zone'] = '';
            }

            if ($fraud_info['ip_region_name']) {
                $data['ip_region_name'] = $fraud_info['ip_region_name'];
            } else {
                $data['ip_region_name'] = '';
            }

            if ($fraud_info['ip_domain']) {
                $data['ip_domain'] = $fraud_info['ip_domain'];
            } else {
                $data['ip_domain'] = '';
            }

            if ($fraud_info['ip_country_name']) {
                $data['ip_country_name'] = $fraud_info['ip_country_name'];
            } else {
                $data['ip_country_name'] = '';
            }

            if ($fraud_info['ip_continent_code']) {
                $data['ip_continent_code'] = $fraud_info['ip_continent_code'];
            } else {
                $data['ip_continent_code'] = '';
            }

            if ($fraud_info['ip_corporate_proxy']) {
                $data['ip_corporate_proxy'] = $fraud_info['ip_corporate_proxy'];
            } else {
                $data['ip_corporate_proxy'] = '';
            }

            $data['anonymous_proxy'] = $fraud_info['anonymous_proxy'];
            $data['proxy_score']     = $fraud_info['proxy_score'];

            if ($fraud_info['is_trans_proxy']) {
                $data['is_trans_proxy'] = $fraud_info['is_trans_proxy'];
            } else {
                $data['is_trans_proxy'] = '';
            }

            $data['free_mail']    = $fraud_info['free_mail'];
            $data['carder_email'] = $fraud_info['carder_email'];

            if ($fraud_info['high_risk_username']) {
                $data['high_risk_username'] = $fraud_info['high_risk_username'];
            } else {
                $data['high_risk_username'] = '';
            }

            if ($fraud_info['high_risk_password']) {
                $data['high_risk_password'] = $fraud_info['high_risk_password'];
            } else {
                $data['high_risk_password'] = '';
            }

            $data['bin_match'] = $fraud_info['bin_match'];

            if ($fraud_info['bin_country']) {
                $data['bin_country'] = $fraud_info['bin_country'];
            } else {
                $data['bin_country'] = '';
            }

            $data['bin_name_match'] = $fraud_info['bin_name_match'];

            if ($fraud_info['bin_name']) {
                $data['bin_name'] = $fraud_info['bin_name'];
            } else {
                $data['bin_name'] = '';
            }

            $data['bin_phone_match'] = $fraud_info['bin_phone_match'];

            if ($fraud_info['bin_phone']) {
                $data['bin_phone'] = $fraud_info['bin_phone'];
            } else {
                $data['bin_phone'] = '';
            }

            if ($fraud_info['customer_phone_in_billing_location']) {
                $data['customer_phone_in_billing_location'] = $fraud_info['customer_phone_in_billing_location'];
            } else {
                $data['customer_phone_in_billing_location'] = '';
            }

            $data['ship_forward'] = $fraud_info['ship_forward'];

            if ($fraud_info['city_postal_match']) {
                $data['city_postal_match'] = $fraud_info['city_postal_match'];
            } else {
                $data['city_postal_match'] = '';
            }

            if ($fraud_info['ship_city_postal_match']) {
                $data['ship_city_postal_match'] = $fraud_info['ship_city_postal_match'];
            } else {
                $data['ship_city_postal_match'] = '';
            }

            $data['score']             = $fraud_info['score'];
            $data['explanation']       = $fraud_info['explanation'];
            $data['risk_score']        = $fraud_info['risk_score'];
            $data['queries_remaining'] = $fraud_info['queries_remaining'];
            $data['maxmind_id']        = $fraud_info['maxmind_id'];
            $data['error']             = $fraud_info['error'];

            return $this->load->view('extension/fraud/maxmind_info', $data);
        }
    }
}