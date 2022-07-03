<?php
class ControllerExtensionRecurringPPExpress extends Controller {
	public function index(): string {
		$this->load->language('extension/recurring/pp_express');
		
		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}
		
		$this->load->model('account/subscription');

		$subscription_info = $this->model_account_subscription->getSubscription($subscription_id);
		
		if ($subscription_info) {
			$data['continue'] = $this->url->link('account/subscription', '', true);	
			
			if ($subscription_info['status'] == 2 || $subscription_info['status'] == 3) {
				$data['subscription_id'] = $subscription_id;
			} else {
				$data['subscription_id'] = '';
			}

			return $this->load->view('extension/recurring/pp_express', $data);
		}
	}
	
	public function cancel(): void {
		$this->load->language('extension/recurring/pp_express');
		
		$json = array();
		
		// Cancel an active subscription
		$this->load->model('account/subscription');
		
		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}
		
		$subscription_info = $this->model_account_account->getSubscription($subscription_id);

		if ($subscription_info && $subscription_info['reference']) {
			if ($this->config->get('payment_pp_express_test')) {
				$api_url = 'https://api-3t.sandbox.paypal.com/nvp';
				$api_username = $this->config->get('payment_pp_express_sandbox_username');
				$api_password = $this->config->get('payment_pp_express_sandbox_password');
				$api_signature = $this->config->get('payment_pp_express_sandbox_signature');
			} else {
				$api_url = 'https://api-3t.paypal.com/nvp';
				$api_username = $this->config->get('payment_pp_express_username');
				$api_password = $this->config->get('payment_pp_express_password');
				$api_signature = $this->config->get('payment_pp_express_signature');
			}
		
			$request = array(
				'USER'         => $api_username,
				'PWD'          => $api_password,
				'SIGNATURE'    => $api_signature,
				'VERSION'      => '109.0',
				'BUTTONSOURCE' => 'OpenCart_2.0_EC',
				'METHOD'       => 'SetExpressCheckout',
				'METHOD'       => 'ManageRecurringPaymentsProfileStatus',
				'PROFILEID'    => $subscription_info['reference'],
				'ACTION'       => 'Cancel'
			);

			$curl = curl_init($api_url);

			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($curl);
			
			if (!$response) {
				$this->log(sprintf($this->language->get('error_curl'), curl_errno($curl), curl_error($curl)));
			}
			
			curl_close($curl);
			
			$response_info = array();
			
			parse_str($response, $response_info);

			if (isset($response_info['PROFILEID'])) {
				$this->model_account_subscription->editStatus($subscription_id, 4);
				$this->model_account_subscription->addTransaction($subscription_id, $subscription_info['order_id'], 5);

				$json['success'] = $this->language->get('text_cancelled');
			} else {
				$json['error'] = sprintf($this->language->get('error_not_cancelled'), $response_info['L_LONGMESSAGE0']);
			}
		} else {
			$json['error'] = $this->language->get('error_not_found');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
}