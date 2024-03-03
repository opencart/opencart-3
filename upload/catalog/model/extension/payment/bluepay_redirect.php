<?php
/**
 * Class Bluepay Redirect
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentBluePayRedirect extends Model {
	/**
	 * Get Methods
	 *
	 * @param array<string, mixed> $address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address): array {
		$this->load->language('extension/payment/bluepay_redirect');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_bluepay_redirect_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_bluepay_redirect_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'bluepay_redirect',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_bluepay_redirect_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * Get Cards
	 *
	 * @param int $customer_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getCards(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bluepay_redirect_card` WHERE `customer_id` = '" . (int)$customer_id . "'");

		$card_data = [];

		// Addresses
		$this->load->model('account/address');

		foreach ($query->rows as $row) {
			$card_data[] = [
				'card_id'     => $row['card_id'],
				'customer_id' => $row['customer_id'],
				'token'       => $row['token'],
				'digits'      => '**** ' . $row['digits'],
				'expiry'      => $row['expiry'],
				'type'        => $row['type'],
			];
		}

		return $card_data;
	}

	/**
	 * addCard
	 *
	 * @param array<string, mixed> $card_data
	 *
	 * @return void
	 */
	public function addCard(array $card_data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "bluepay_redirect_card` SET `customer_id` = '" . (int)$card_data['customer_id'] . "', `token` = '" . $this->db->escape($card_data['Token']) . "', `digits` = '" . $this->db->escape($card_data['Last4Digits']) . "', `expiry` = '" . $this->db->escape($card_data['ExpiryDate']) . "', `type` = '" . $this->db->escape($card_data['CardType']) . "'");
	}

	/**
	 * addOrder
	 *
	 * @param array<string, mixed> $order_info
	 * @param array<string, mixed> $response_data
	 *
	 * @return int
	 */
	public function addOrder(array $order_info, array $response_data): int {
		if ($this->config->get('payment_bluepay_redirect_transaction') == 'SALE') {
			$release_status = 1;
		} else {
			$release_status = 0;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "bluepay_redirect_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `transaction_id` = '" . $this->db->escape($response_data['RRNO']) . "', `date_added` = NOW(), `date_modified` = NOW(), `release_status` = '" . (int)$release_status . "', `currency_code` = '" . $this->db->escape($order_info['currency_code']) . "', `total` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "'");

		return $this->db->getLastId();
	}

	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bluepay_redirect_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		if ($query->num_rows) {
			$order = $query->row;
			$order['transactions'] = $this->getTransactions($order['bluepay_redirect_order_id']);

			return $order;
		} else {
			return [];
		}
	}

	/**
	 * addTransaction
	 *
	 * @param int                  $bluepay_redirect_order_id
	 * @param string               $type
	 * @param array<string, mixed> $order_info
	 *
	 * @return void
	 */
	public function addTransaction(int $bluepay_redirect_order_id, string $type, array $order_info): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "bluepay_redirect_order_transaction` SET `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "', `date_added` = NOW(), `type` = '" . $this->db->escape($type) . "', `amount` = '" . $this->currency->format($order_info['total'], $order_info['currency_code'], false, false) . "'");
	}

	/**
	 * Get Transactions
	 *
	 * @param int $bluepay_redirect_order_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getTransactions(int $bluepay_redirect_order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bluepay_redirect_order_transaction` WHERE `bluepay_redirect_order_id` = '" . (int)$bluepay_redirect_order_id . "'");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return [];
		}
	}

	/**
	 * Logger
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function logger(string $message): void {
		if ($this->config->get('payment_bluepay_redirect_debug') == 1) {
			// Log
			$log = new \Log('bluepay_redirect.log');
			$log->write($message);
		}
	}

	/**
	 * sendCurl
	 *
	 * @param string               $url
	 * @param array<string, mixed> $post_data
	 *
	 * @return array<string, mixed>
	 */
	public function sendCurl(string $url, array $post_data) {
		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

		$response_data = curl_exec($curl);

		curl_close($curl);

		return json_decode($response_data, true);
	}
}
