<?php
/**
 * Class Free
 *
 * @package Admin\Controller\Extension\Shipping
 */
class ControllerExtensionShippingFree extends Controller {
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
		$this->load->language('extension/shipping/free');

		$this->document->setTitle($this->language->get('heading_title'));

		// Settings
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_free', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/free', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['action'] = $this->url->link('extension/shipping/free', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

		if (isset($this->request->post['shipping_free_total'])) {
			$data['shipping_free_total'] = $this->request->post['shipping_free_total'];
		} else {
			$data['shipping_free_total'] = $this->config->get('shipping_free_total');
		}

		if (isset($this->request->post['shipping_free_geo_zone_id'])) {
			$data['shipping_free_geo_zone_id'] = (int)$this->request->post['shipping_free_geo_zone_id'];
		} else {
			$data['shipping_free_geo_zone_id'] = (int)$this->config->get('shipping_free_geo_zone_id');
		}

		// Geo Zones
		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['shipping_free_status'])) {
			$data['shipping_free_status'] = $this->request->post['shipping_free_status'];
		} else {
			$data['shipping_free_status'] = $this->config->get('shipping_free_status');
		}

		if (isset($this->request->post['shipping_free_sort_order'])) {
			$data['shipping_free_sort_order'] = $this->request->post['shipping_free_sort_order'];
		} else {
			$data['shipping_free_sort_order'] = $this->config->get('shipping_free_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/free', $data));
	}

	/**
	 * Validate
	 *
	 * @return bool
	 */
	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/shipping/free')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
