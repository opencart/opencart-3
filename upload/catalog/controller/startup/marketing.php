<?php
class ControllerStartupMarketing extends Controller {
	public function index(): void {
		$tracking = '';

		if (isset($this->request->get['tracking'])) {
			$tracking = $this->request->get['tracking'];
		}

		if (isset($this->request->cookie['tracking'])) {
			$tracking = $this->request->cookie['tracking'];
		}

		// Tracking Code
		if ($tracking) {
			$this->load->model('checkout/marketing');

			$marketing_info = $this->model_checkout_marketing->getMarketingByCode($tracking);

			if ($marketing_info) {
				$this->model_checkout_marketing->addReport($marketing_info['marketing_id'], $this->request->server['REMOTE_ADDR']);
			}

			if ($this->config->get('config_affiliate_status')) {
				$this->load->model('account/customer');

				$affiliate_info = $this->model_account_customer->getAffiliateByTracking($tracking);

				if ($affiliate_info && $affiliate_info['status']) {
					$this->model_account_customer->addReport($affiliate_info['customer_id'], $this->request->server['REMOTE_ADDR']);
				}

				if ($marketing_info || ($affiliate_info && $affiliate_info['status'])) {
					$this->session->data['tracking'] = $tracking;

					if (!isset($this->request->cookie['tracking'])) {
						$option = array(
							'expires'  => $this->config->get('config_affiliate_expire') ? time() + (int)$this->config->get('config_affiliate_expire') : 0,
							'path'     => '/',
							'SameSite' => $this->config->get('session_samesite')
						);

						setcookie('tracking', $tracking, $option);
					}
				}
			}
		}
	}
}
