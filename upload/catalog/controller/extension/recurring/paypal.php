<?php
class ControllerExtensionRecurringPayPal extends Controller {
	private $error = array();
			
	public function index() {
		$content = '';
		
		if ($this->config->get('payment_paypal_status') && !empty($this->request->get['subscription_id'])) {
			$this->load->language('extension/recurring/paypal');
		
			$this->load->model('account/recurring');
			
			$data['subscription_id'] = (int)$this->request->get['subscription_id'];

			$order_recurring_info = $this->model_account_recurring->getOrderRecurring($data['subscription_id']);
			
			if ($order_recurring_info) {
				$data['recurring_status'] = $order_recurring_info['status'];
				
				$data['info_url'] =  str_replace('&amp;', '&', $this->url->link('extension/recurring/paypal/getRecurringInfo', 'subscription_id=' . $data['subscription_id'], true));
				$data['enable_url'] =  str_replace('&amp;', '&', $this->url->link('extension/recurring/paypal/enableRecurring', '', true));
				$data['disable_url'] =  str_replace('&amp;', '&', $this->url->link('extension/recurring/paypal/disableRecurring', '', true));
				
				$content = $this->load->view('extension/recurring/paypal', $data);
			}
		}
		
		return $content;
	}
		
	public function getRecurringInfo() {
		$this->response->setOutput($this->index());
	}
	
	public function enableRecurring() {
		if ($this->config->get('payment_paypal_status') && !empty($this->request->post['subscription_id'])) {
			$this->load->language('extension/recurring/paypal');
			
			$data['success'] = $this->language->get('success_enable_recurring');	
		}
						
		$data['error'] = $this->error;
				
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
	
	public function disableRecurring() {
		if ($this->config->get('payment_paypal_status') && !empty($this->request->post['subscription_id'])) {
			$this->load->language('extension/recurring/paypal');
			
			$data['success'] = $this->language->get('success_disable_recurring');	
		}
						
		$data['error'] = $this->error;
				
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}