<?php
/**
 * Class Subscription
 *
 * @package Catalog\Controller\Mail
 */
class ControllerMailSubscription extends Controller {
	/**
	 * Index
	 *
	 * @param string               $route
	 * @param array<string, mixed> $args
	 * @param mixed                $output
	 *
	 * @return void
	 *
	 * catalog/model/checkout/order/addHistory/after
	 */
	public function index(string &$route, array &$args, mixed &$output): void {
		if (isset($args[0])) {
			$subscription_id = $args[0];
		} else {
			$subscription_id = 0;
		}

		if (isset($args[1]['subscription'])) {
			$subscription = $args[1]['subscription'];
		} else {
			$subscription = [];
		}

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}

		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}
		/*
		$subscription['order_product_id']
		$subscription['customer_id']
		$subscription['order_id']
		$subscription['subscription_plan_id']
		$subscription['customer_payment_id'],
		$subscription['name']
		$subscription['description']
		$subscription['trial_price']
		$subscription['trial_frequency']
		$subscription['trial_cycle']
		$subscription['trial_duration']
		$subscription['trial_remaining']
		$subscription['trial_status']
		$subscription['price']
		$subscription['frequency']
		$subscription['cycle']
		$subscription['duration']
		$subscription['remaining']
		$subscription['date_next']
		$subscription['status']
		*/
	}

	/**
	 * Cancel
	 *
	 * @param string               $route
	 * @param array<string, mixed> $args
	 * @param mixed                $output
	 *
	 * @return void
	 *
	 * catalog/model/checkout/order/editOrder/after
	 */
	public function cancel(string &$route, array &$args, mixed &$output): void {
		if (isset($args[0])) {
			$subscription_id = $args[0];
		} else {
			$subscription_id = 0;
		}

		if (isset($args[1]['subscription'])) {
			$subscription = $args[1]['subscription'];
		} else {
			$subscription = [];
		}

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}

		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}
		/*
		$subscription['order_product_id']
		$subscription['customer_id']
		$subscription['order_id']
		$subscription['subscription_plan_id']
		$subscription['customer_payment_id'],
		$subscription['name']
		$subscription['description']
		$subscription['trial_price']
		$subscription['trial_frequency']
		$subscription['trial_cycle']
		$subscription['trial_duration']
		$subscription['trial_remaining']
		$subscription['trial_status']
		$subscription['price']
		$subscription['frequency']
		$subscription['cycle']
		$subscription['duration']
		$subscription['remaining']
		$subscription['date_next']
		$subscription['status']
		*/
	}
}
