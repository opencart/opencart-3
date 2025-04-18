<?php
/**
 * Class Globalpay
 *
 * @package Admin\Controller\Extension\Payment
 */
class ControllerExtensionPaymentGlobalpay extends Controller {
	/**
	 * @var array<string, string>
	 */
	private array $error = [];

	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/payment/globalpay');

		$this->document->setTitle($this->language->get('heading_title'));

		// Settings
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_globalpay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['notify_url'] = HTTPS_CATALOG . 'index.php?route=extension/payment/globalpay/notify';

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_merchant_id'])) {
			$data['error_merchant_id'] = $this->error['error_merchant_id'];
		} else {
			$data['error_merchant_id'] = '';
		}

		if (isset($this->error['error_secret'])) {
			$data['error_secret'] = $this->error['error_secret'];
		} else {
			$data['error_secret'] = '';
		}

		if (isset($this->error['error_live_url'])) {
			$data['error_live_url'] = $this->error['error_live_url'];
		} else {
			$data['error_live_url'] = '';
		}

		if (isset($this->error['error_demo_url'])) {
			$data['error_demo_url'] = $this->error['error_demo_url'];
		} else {
			$data['error_demo_url'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/globalpay', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['action'] = $this->url->link('extension/payment/globalpay', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_globalpay_merchant_id'])) {
			$data['payment_globalpay_merchant_id'] = $this->request->post['payment_globalpay_merchant_id'];
		} else {
			$data['payment_globalpay_merchant_id'] = $this->config->get('payment_globalpay_merchant_id');
		}

		if (isset($this->request->post['payment_globalpay_secret'])) {
			$data['payment_globalpay_secret'] = $this->request->post['payment_globalpay_secret'];
		} else {
			$data['payment_globalpay_secret'] = $this->config->get('payment_globalpay_secret');
		}

		if (isset($this->request->post['payment_globalpay_rebate_password'])) {
			$data['payment_globalpay_rebate_password'] = $this->request->post['payment_globalpay_rebate_password'];
		} else {
			$data['payment_globalpay_rebate_password'] = $this->config->get('payment_globalpay_rebate_password');
		}

		if (isset($this->request->post['payment_globalpay_live_demo'])) {
			$data['payment_globalpay_live_demo'] = $this->request->post['payment_globalpay_live_demo'];
		} else {
			$data['payment_globalpay_live_demo'] = $this->config->get('payment_globalpay_live_demo');
		}

		if (isset($this->request->post['payment_globalpay_geo_zone_id'])) {
			$data['payment_globalpay_geo_zone_id'] = (int)$this->request->post['payment_globalpay_geo_zone_id'];
		} else {
			$data['payment_globalpay_geo_zone_id'] = (int)$this->config->get('payment_globalpay_geo_zone_id');
		}

		// Geo Zones
		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_globalpay_total'])) {
			$data['payment_globalpay_total'] = $this->request->post['payment_globalpay_total'];
		} else {
			$data['payment_globalpay_total'] = $this->config->get('payment_globalpay_total');
		}

		if (isset($this->request->post['payment_globalpay_sort_order'])) {
			$data['payment_globalpay_sort_order'] = (int)$this->request->post['payment_globalpay_sort_order'];
		} else {
			$data['payment_globalpay_sort_order'] = $this->config->get('payment_globalpay_sort_order');
		}

		if (isset($this->request->post['payment_globalpay_status'])) {
			$data['payment_globalpay_status'] = (int)$this->request->post['payment_globalpay_status'];
		} else {
			$data['payment_globalpay_status'] = $this->config->get('payment_globalpay_status');
		}

		if (isset($this->request->post['payment_globalpay_debug'])) {
			$data['payment_globalpay_debug'] = (int)$this->request->post['payment_globalpay_debug'];
		} else {
			$data['payment_globalpay_debug'] = $this->config->get('payment_globalpay_debug');
		}

		if (isset($this->request->post['payment_globalpay_account'])) {
			$data['payment_globalpay_account'] = $this->request->post['payment_globalpay_account'];
		} else {
			$data['payment_globalpay_account'] = $this->config->get('payment_globalpay_account');
		}

		if (isset($this->request->post['payment_globalpay_auto_settle'])) {
			$data['payment_globalpay_auto_settle'] = $this->request->post['payment_globalpay_auto_settle'];
		} else {
			$data['payment_globalpay_auto_settle'] = $this->config->get('payment_globalpay_auto_settle');
		}

		if (isset($this->request->post['payment_globalpay_card_select'])) {
			$data['payment_globalpay_card_select'] = $this->request->post['payment_globalpay_card_select'];
		} else {
			$data['payment_globalpay_card_select'] = $this->config->get('payment_globalpay_card_select');
		}

		if (isset($this->request->post['payment_globalpay_tss_check'])) {
			$data['payment_globalpay_tss_check'] = $this->request->post['payment_globalpay_tss_check'];
		} else {
			$data['payment_globalpay_tss_check'] = $this->config->get('payment_globalpay_tss_check');
		}

		if (isset($this->request->post['payment_globalpay_success_settled_status_id'])) {
			$data['payment_globalpay_success_settled_status_id'] = (int)$this->request->post['payment_globalpay_success_settled_status_id'];
		} else {
			$data['payment_globalpay_success_settled_status_id'] = (int)$this->config->get('payment_globalpay_success_settled_status_id');
		}

		if (isset($this->request->post['payment_globalpay_success_unsettled_status_id'])) {
			$data['payment_globalpay_success_unsettled_status_id'] = (int)$this->request->post['payment_globalpay_success_unsettled_status_id'];
		} else {
			$data['payment_globalpay_success_unsettled_status_id'] = (int)$this->config->get('payment_globalpay_success_unsettled_status_id');
		}

		if (isset($this->request->post['payment_globalpay_declined_status_id'])) {
			$data['payment_globalpay_declined_id'] = (int)$this->request->post['payment_globalpay_declined_status_id'];
		} else {
			$data['payment_globalpay_declined_id'] = (int)$this->config->get('payment_globalpay_declined_status_id');
		}

		if (isset($this->request->post['payment_globalpay_declined_pending_status_id'])) {
			$data['payment_globalpay_declined_pending_status_id'] = (int)$this->request->post['payment_globalpay_declined_pending_status_id'];
		} else {
			$data['payment_globalpay_declined_pending_status_id'] = (int)$this->config->get('payment_globalpay_declined_pending_status_id');
		}

		if (isset($this->request->post['payment_globalpay_declined_stolen_status_id'])) {
			$data['payment_globalpay_declined_stolen_status_id'] = (int)$this->request->post['payment_globalpay_declined_stolen_status_id'];
		} else {
			$data['payment_globalpay_declined_stolen_status_id'] = (int)$this->config->get('payment_globalpay_declined_stolen_status_id');
		}

		if (isset($this->request->post['payment_globalpay_declined_bank_status_id'])) {
			$data['payment_globalpay_declined_bank_status_id'] = (int)$this->request->post['payment_globalpay_declined_bank_status_id'];
		} else {
			$data['payment_globalpay_declined_bank_status_id'] = (int)$this->config->get('payment_globalpay_declined_bank_status_id');
		}

		// Order Statuses
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_globalpay_live_url'])) {
			$data['payment_globalpay_live_url'] = $this->request->post['payment_globalpay_live_url'];
		} else {
			$data['payment_globalpay_live_url'] = $this->config->get('payment_globalpay_live_url');
		}

		if (empty($data['payment_globalpay_live_url'])) {
			$data['payment_globalpay_live_url'] = 'https://hpp.globaliris.com/pay';
		}

		if (isset($this->request->post['payment_globalpay_demo_url'])) {
			$data['payment_globalpay_demo_url'] = $this->request->post['payment_globalpay_demo_url'];
		} else {
			$data['payment_globalpay_demo_url'] = $this->config->get('payment_globalpay_demo_url');
		}

		if (empty($data['payment_globalpay_demo_url'])) {
			$data['payment_globalpay_demo_url'] = 'https://hpp.sandbox.globaliris.com/pay';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/globalpay', $data));
	}

	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		// Globalpay
		$this->load->model('extension/payment/globalpay');

		$this->model_extension_payment_globalpay->install();
	}

	/**
	 * Order
	 *
	 * @return string
	 */
	public function order(): string {
		if ($this->config->get('payment_globalpay_status')) {
			// Globalpay
			$this->load->model('extension/payment/globalpay');

			$globalpay_order = $this->model_extension_payment_globalpay->getOrder($this->request->get['order_id']);

			if ($globalpay_order) {
				$this->load->language('extension/payment/globalpay');

				$globalpay_order['total_captured'] = $this->model_extension_payment_globalpay->getTotalCaptured($globalpay_order['globalpay_order_id']);
				$globalpay_order['total_formatted'] = $this->currency->format($globalpay_order['total'], $globalpay_order['currency_code'], 1, true);
				$globalpay_order['total_captured_formatted'] = $this->currency->format($globalpay_order['total_captured'], $globalpay_order['currency_code'], 1, true);

				$data['globalpay_order'] = $globalpay_order;
				$data['auto_settle'] = $globalpay_order['settle_type'];
				$data['order_id'] = (int)$this->request->get['order_id'];
				$data['user_token'] = $this->session->data['user_token'];

				return $this->load->view('extension/payment/globalpay_order', $data);
			} else {
				return '';
			}
		} else {
			return '';
		}
	}

	/**
	 * Void
	 *
	 * @return void
	 */
	public function void(): void {
		$this->load->language('extension/payment/globalpay');

		$json = [];

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			// Globalpay
			$this->load->model('extension/payment/globalpay');

			$globalpay_order = $this->model_extension_payment_globalpay->getOrder($this->request->post['order_id']);

			$void_response = $this->model_extension_payment_globalpay->void($this->request->post['order_id']);

			$this->model_extension_payment_globalpay->logger('Void result:\r\n' . print_r($void_response, 1));

			if (isset($void_response['result']) && $void_response['result'] == '00') {
				$this->model_extension_payment_globalpay->addTransaction($globalpay_order['globalpay_order_id'], 'void', 0.00);

				$this->model_extension_payment_globalpay->updateVoidStatus($globalpay_order['globalpay_order_id'], 1);

				$json['msg'] = $this->language->get('text_void_ok');
				$json['date_added'] = date('Y-m-d H:i:s');

				$json['error'] = false;
			} else {
				$json['msg'] = !empty($void_response['message']) ? sprintf($this->language->get('error_status'), (string)$void_response['message']) : $this->language->get('error_void');

				$json['error'] = true;
			}
		} else {
			$json['msg'] = $this->language->get('error_data_missing');

			$json['error'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Capture
	 *
	 * @return void
	 */
	public function capture(): void {
		$this->load->language('extension/payment/globalpay');

		$json = [];

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '' && isset($this->request->post['amount']) && $this->request->post['amount'] > 0) {
			// Globalpay
			$this->load->model('extension/payment/globalpay');

			$globalpay_order = $this->model_extension_payment_globalpay->getOrder($this->request->post['order_id']);

			$capture_response = $this->model_extension_payment_globalpay->capture($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_extension_payment_globalpay->logger('Settle result:\r\n' . print_r($capture_response, 1));

			if (isset($capture_response['result']) && $capture_response['result'] == '00') {
				$this->model_extension_payment_globalpay->addTransaction($globalpay_order['globalpay_order_id'], 'payment', $this->request->post['amount']);

				$total_captured = $this->model_extension_payment_globalpay->getTotalCaptured($globalpay_order['globalpay_order_id']);

				if ($total_captured >= $globalpay_order['total'] || $globalpay_order['settle_type'] == 0) {
					$this->model_extension_payment_globalpay->updateCaptureStatus($globalpay_order['globalpay_order_id'], 1);

					$capture_status = 1;

					$json['msg'] = $this->language->get('text_capture_ok_order');
				} else {
					$capture_status = 0;

					$json['msg'] = $this->language->get('text_capture_ok');
				}

				$this->model_extension_payment_globalpay->updateForRebate($globalpay_order['globalpay_order_id'], (string)$capture_response['pasref'], (string)$capture_response['orderid']);

				$json['date_added'] = date('Y-m-d H:i:s');
				$json['amount'] = $this->request->post['amount'];
				$json['capture_status'] = $capture_status;
				$json['total'] = (float)$total_captured;

				$json['error'] = false;
			} else {
				$json['msg'] = isset($capture_response['message']) ? sprintf($this->language->get('error_status'), (string)$capture_response['message']) : $this->language->get('error_capture');

				$json['error'] = true;
			}
		} else {
			$json['msg'] = $this->language->get('error_data_missing');

			$json['error'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Rebate
	 *
	 * @return void
	 */
	public function rebate(): void {
		$this->load->language('extension/payment/globalpay');

		$json = [];

		if (isset($this->request->post['order_id'])) {
			// Globalpay
			$this->load->model('extension/payment/globalpay');

			$globalpay_order = $this->model_extension_payment_globalpay->getOrder($this->request->post['order_id']);

			$rebate_response = $this->model_extension_payment_globalpay->rebate($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_extension_payment_globalpay->logger('Rebate result:\r\n' . print_r($rebate_response, 1));

			if (isset($rebate_response['result']) && $rebate_response['result'] == '00') {
				$this->model_extension_payment_globalpay->addTransaction($globalpay_order['globalpay_order_id'], 'rebate', $this->request->post['amount'] * -1);

				$total_rebated = $this->model_extension_payment_globalpay->getTotalRebated($globalpay_order['globalpay_order_id']);
				$total_captured = $this->model_extension_payment_globalpay->getTotalCaptured($globalpay_order['globalpay_order_id']);

				if ($total_captured <= 0 && $globalpay_order['capture_status'] == 1) {
					$this->model_extension_payment_globalpay->updateRebateStatus($globalpay_order['globalpay_order_id'], 1);

					$rebate_status = 1;

					$json['msg'] = $this->language->get('text_rebate_ok_order');
				} else {
					$rebate_status = 0;

					$json['msg'] = $this->language->get('text_rebate_ok');
				}

				$json['date_added'] = date('Y-m-d H:i:s');
				$json['amount'] = $this->request->post['amount'] * -1;
				$json['total_captured'] = (float)$total_captured;
				$json['total_rebated'] = (float)$total_rebated;
				$json['rebate_status'] = $rebate_status;

				$json['error'] = false;
			} else {
				$json['msg'] = !empty($rebate_response['message']) ? sprintf($this->language->get('error_status'), (string)$rebate_response['message']) : $this->language->get('error_rebate');

				$json['error'] = true;
			}
		} else {
			$json['msg'] = $this->language->get('error_data_missing');

			$json['error'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Validate
	 *
	 * @return bool
	 */
	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/payment/globalpay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_globalpay_merchant_id']) {
			$this->error['error_merchant_id'] = $this->language->get('error_merchant_id');
		}

		if (!$this->request->post['payment_globalpay_secret']) {
			$this->error['error_secret'] = $this->language->get('error_secret');
		}

		if (!$this->request->post['payment_globalpay_live_url']) {
			$this->error['error_live_url'] = $this->language->get('error_live_url');
		}

		if (!$this->request->post['payment_globalpay_demo_url']) {
			$this->error['error_demo_url'] = $this->language->get('error_demo_url');
		}

		return !$this->error;
	}
}
