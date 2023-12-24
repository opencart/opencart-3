<?php
/**
 * Class Securetrading Pp
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentSecureTradingPp extends Model {
	/**
	 * getMethod
	 *
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethod(array $address): array {
		$this->load->language('extension/payment/securetrading_pp');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_securetrading_pp_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_securetrading_pp_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'securetrading_pp',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_securetrading_pp_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "securetrading_pp_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		return $query->row;
	}

	/**
	 * editOrder
	 *
	 * @param int   $order_id
	 * @param array $order
	 *
	 * @return void
	 */
	public function editOrder(int $order_id, array $order): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `shipping_firstname` = '" . $this->db->escape($order['shipping_firstname']) . "', `shipping_lastname` = '" . $this->db->escape($order['shipping_lastname']) . "', `shipping_address_1` = '" . $this->db->escape($order['shipping_address_1']) . "', `shipping_address_2` = '" . $this->db->escape($order['shipping_address_2']) . "', `shipping_city` = '" . $this->db->escape($order['shipping_city']) . "', `shipping_zone` = '" . $this->db->escape($order['shipping_zone']) . "', `shipping_zone_id` = '" . (int)$order['shipping_zone_id'] . "', `shipping_country` = '" . $this->db->escape($order['shipping_country']) . "', `shipping_country_id` = '" . (int)$order['shipping_country_id'] . "', `shipping_postcode` = '" . $this->db->escape($order['shipping_postcode']) . "', `payment_firstname` = '" . $this->db->escape($order['payment_firstname']) . "', `payment_lastname` = '" . $this->db->escape($order['payment_lastname']) . "', `payment_address_1` = '" . $this->db->escape($order['payment_address_1']) . "', `payment_address_2` = '" . $this->db->escape($order['payment_address_2']) . "', `payment_city` = '" . $this->db->escape($order['payment_city']) . "', `payment_zone` = '" . $this->db->escape($order['payment_zone']) . "', `payment_zone_id` = '" . (int)$order['payment_zone_id'] . "', `payment_country` = '" . $this->db->escape($order['payment_country']) . "', `payment_country_id` = '" . (int)$order['payment_country_id'] . "', `payment_postcode` = '" . $this->db->escape($order['payment_postcode']) . "' WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * addReference
	 *
	 * @param int    $order_id
	 * @param string $reference
	 *
	 * @return void
	 */
	public function addReference(int $order_id, string $reference): void {
		$this->db->query("REPLACE INTO `" . DB_PREFIX . "securetrading_pp_order` SET `order_id` = '" . (int)$order_id . "', `transaction_reference` = '" . $this->db->escape($reference) . "', `created` = NOW()");
	}

	/**
	 * confirmOrder
	 *
	 * @param int    $order_id
	 * @param int    $order_status_id
	 * @param string $comment
	 * @param bool   $notify
	 *
	 * @return int
	 */
	public function confirmOrder(int $order_id, int $order_status_id, string $comment = '', bool $notify = false): int {
		$this->logger('confirmOrder');

		// Orders
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `order_status_id` = '0' WHERE `order_id` = '" . (int)$order_id . "'");

			$this->model_checkout_order->addHistory($order_id, $order_status_id, $comment, $notify);

			$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);

			$securetrading_pp_order = $this->getOrder($order_id);

			switch ($this->config->get('payment_securetrading_pp_settle_status')) {
				case 0:
					$trans_type = 'auth';
					break;
				case 1:
					$trans_type = 'auth';
					break;
				case 2:
					$trans_type = 'suspended';
					break;
				case 100:
					$trans_type = 'payment';
					break;
				default:
					$trans_type = 'default';
			}

			$this->db->query("UPDATE `" . DB_PREFIX . "securetrading_pp_order` SET `settle_type` = '" . $this->config->get('payment_securetrading_pp_settle_status') . "', `modified` = NOW(), `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `total` = '" . $amount . "' WHERE `order_id` = '" . (int)$order_info['order_id'] . "'");

			$this->db->query("INSERT INTO `" . DB_PREFIX . "securetrading_pp_order_transaction` SET `securetrading_pp_order_id` = '" . (int)$securetrading_pp_order['securetrading_pp_order_id'] . "', `amount` = '" . $amount . "', `type` = '" . $trans_type . "', `created` = NOW()");
		}

		return 0;
	}

	/**
	 * updateOrder
	 *
	 * @param int    $order_id
	 * @param int    $order_status_id
	 * @param string $comment
	 * @param bool   $notify
	 *
	 * @return void
	 */
	public function updateOrder(int $order_id, int $order_status_id, string $comment = '', bool $notify = false): void {
		// Orders
		$this->load->model('checkout/order');

		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `order_status_id` = '" . (int)$order_status_id . "' WHERE `order_id` = '" . (int)$order_id . "'");

		$this->model_checkout_order->addHistory($order_id, $order_status_id, $comment, $notify);
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		// Log
		$log = new \Log('secure.log');
		$log->write($message);
	}
}
