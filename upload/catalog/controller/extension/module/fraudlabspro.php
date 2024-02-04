<?php
/**
 * Class Fraudlabspro
 *
 * @package Catalog\Controller\Extension\Module
 */
class ControllerExtensionModuleFraudlabspro extends Controller {
	/**
	 * addHistory
	 *
	 * @param string $route
	 * @param array  $args
	 * @param mixed  $output
	 *
	 * @return void
	 *
	 * catalog/model/checkout/order/addHistory/after
	 */
	public function addHistory(string &$route, array &$args, mixed &$output): void {
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

		if ($this->config->get('fraud_fraudlabspro_status')) {
			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$this->getStatus($order_id, $order_status_id, $notify);
			}
		}
	}

	/**
	 * Get Status
	 * 
	 * @param int  $order_id
	 * @param int  $order_status_id
	 * @param bool $notify
	 * 
	 * @return void
	 */
	protected function getStatus(int $order_id, int $order_status_id, bool $notify): void {
		// Only pull the comment if we don't notify the customer and when there's an order status ID
		// for service information security purposes between store owners and the customers.
		if ($this->config->get('fraud_fraudlabspro_status')) {
			if (!$notify && $order_status_id) {
				$this->load->language('extension/module/fraudlabspro');

				// Fraud Labs Pro
				$this->load->model('extension/fraud/fraudlabspro');

				$status = $this->model_extension_fraud_fraudlabspro->getStatus($order_id);

				if ($status == 'REVIEW') {
					$comment = $this->language->get('text_review');

					$this->model_checkout_order->addHistory($order_id, $this->config->get('fraud_fraudlabspro_review_status_id'), $comment);
				} elseif ($status == 'REJECT') {
					$comment = $this->language->get('text_reject');

					$this->model_checkout_order->addHistory($order_id, $this->config->get('fraud_fraudlabspro_reject_status_id'), $comment);
				} elseif ($status == 'APPROVE') {
					$comment = $this->language->get('text_approve');

					$this->model_checkout_order->addHistory($order_id, $order_status_id, $comment);
				}
			}
		}
	}
}
