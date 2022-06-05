<?php
class ControllerExtensionModuleFraudlabspro extends Controller {
	// catalog/model/checkout/order/addOrderHistory/after
	public function addOrderHistory(&$route, &$args, &$output) {
		if (isset($args[0])) {
			$order_id = $args[0];
		} else {
			$order_id = 0;
		}

		if (isset($args[1])) {
			$order_status_id = $args[1];
		} else {
			$order_status_id = 0;
		}	

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}
		
		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = false;
		}
						
		// We need to grab the old order status ID.
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		if ($order_info) {
			$comment = $this->getStatus($order_id, $order_status_id, $notify);				
			
			if ($comment) {
				$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment);
			}
		}
	}
	
	private function getStatus($order_id, $order_status_id, $notify) {
		// Only pull the comment if we don't notify the customer and when there's an order status ID 
		// for service information security purposes between store owners and the customers.
		if (!$notify && $order_status_id) {
			$this->load->language('extension/module/fraudlabspro');
			
			$this->load->model('extension/fraud/fraudlabspro');
			
			$status = $this->model_extension_fraud_fraudlabspro->getStatus($order_id);
			
			if ($status) {
				return sprintf($this->language->get('text_status'), $status);
			}
		}
	}
}