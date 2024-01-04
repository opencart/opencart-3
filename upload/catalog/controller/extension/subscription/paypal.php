<?php
class ControllerExtensionSubscriptionPayPal extends Controller {
	private array $error = [];
			
	public function index(): string {
		$content = '';
		
		if ($this->config->get('payment_paypal_status') && !empty($this->request->get['order_subscription_id'])) {
			$this->load->language('extension/subscription/paypal');
		
			$this->load->model('account/subscription');

			if (isset($this->request->post['order_subscription_id'])) {
				$order_subscription_id = (int)$this->request->get['order_subscription_id'];
			} else {
				$order_subscription_id = 0;
			}
			
			$order_subscription_info = $this->model_account_subscription->getOrderRecurring($order_subscription_id);
			
			if ($order_subscription_info) {
				$data['order_subscription_id'] = $order_subscription_id;

				$data['subscription_status'] = $order_subscription_info['status'];
				
				$data['info_url'] =  str_replace('&amp;', '&', $this->url->link('extension/subscription/paypal/getRecurringInfo', 'order_subscription_id=' . $data['order_subscription_id'], true));
				$data['enable_url'] =  str_replace('&amp;', '&', $this->url->link('extension/subscription/paypal/enableRecurring', '', true));
				$data['disable_url'] =  str_replace('&amp;', '&', $this->url->link('extension/subscription/paypal/disableRecurring', '', true));
				
				$content = $this->load->view('extension/subscription/paypal', $data);
			}
		}
		
		return $content;
	}
		
	public function getRecurringInfo(): void {
		$this->response->setOutput($this->index());
	}
	
	public function enableRecurring(): void {
		if ($this->config->get('payment_paypal_status') && !empty($this->request->post['order_subscription_id'])) {
			$this->load->language('extension/subscription/paypal');
			
			$this->load->model('extension/payment/paypal');
			
			if (isset($this->request->post['order_subscription_id'])) {
				$order_subscription_id = (int)$this->request->get['order_subscription_id'];
			} else {
				$order_subscription_id = 0;
			}
			
			$this->model_extension_payment_paypal->editOrderRecurringStatus($order_subscription_id, 1);
			
			$data['success'] = $this->language->get('success_enable_subscription');	
		}
						
		$data['error'] = $this->error;
				
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
	
	public function disableRecurring(): void {
		if ($this->config->get('payment_paypal_status') && !empty($this->request->post['order_subscription_id'])) {
			$this->load->language('extension/subscription/paypal');
			
			$this->load->model('extension/payment/paypal');
			
			if (isset($this->request->post['order_subscription_id'])) {
				$order_subscription_id = (int)$this->request->get['order_subscription_id'];
			} else {
				$order_subscription_id = 0;
			}
			
			$this->model_extension_payment_paypal->editOrderRecurringStatus($order_subscription_id, 2);
			
			$data['success'] = $this->language->get('success_disable_subscription');	
		}
						
		$data['error'] = $this->error;
				
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}