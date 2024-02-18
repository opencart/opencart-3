<?php
/**
 * Class Cardinity
 *
 * @package Admin\Model\Extension\Payment
 */
use Cardinity\Client;
use Cardinity\Method\Payment;
use Cardinity\Method\Refund;

class ModelExtensionPaymentCardinity extends Model {
	/**
	 * Get Order
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cardinity_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		return $query->row;
	}

	/**
	 * Create Client
	 *
	 * @param array<string, mixed> $credentials
	 *
	 * @return ?object
	 */
	public function createClient(array $credentials): ?object {
		if ($credentials) {
			return Client::create([
				'consumerKey'    => $credentials['key'],
				'consumerSecret' => $credentials['secret']
			]);
		} else {
			return null;
		}
	}

	/**
	 * Verify Credentials
	 *
	 * @param mixed $client
	 *
	 * @return Exception|object|null
	 */
	public function verifyCredentials($client): ?object {
		$method = new Payment\GetAll(10);

		try {
			$client->call($method);

			return true;
		} catch (\Exception $e) {
			$this->log($e->getMessage());
		}

		return null;
	}

	/**
	 * Get Payment
	 *
	 * @param mixed  $client
	 * @param string $payment_id
	 *
	 * @return Exception|object|null
	 */
	public function getPayment($client, string $payment_id): ?object {
		$method = new Payment\Get($payment_id);

		try {
			return $client->call($method);
		} catch (\Exception $e) {
			$this->log($e->getMessage());
		}

		return null;
	}

	/**
	 * Get Refunds
	 *
	 * @param mixed  $client
	 * @param string $payment_id
	 *
	 * @return Exception|object|null
	 */
	public function getRefunds($client, string $payment_id): ?object {
		$method = new Refund\GetAll($payment_id);

		try {
			return $client->call($method);
		} catch (\Exception $e) {
			$this->log($e->getMessage());
		}

		return null;
	}

	/**
	 * Refund Payment
	 *
	 * @param mixed  $client
	 * @param string $payment_id
	 * @param float  $amount
	 * @param string $description
	 *
	 * @return Exception|object|null
	 */
	public function refundPayment($client, string $payment_id, float $amount, string $description): ?object {
		$method = new Refund\Create($payment_id, $amount, $description);

		try {
			return $client->call($method);
		} catch (\Exception $e) {
			$this->log($e->getMessage());
		}

		return null;
	}

	/**
	 * Log
	 *
	 * @param ?string $data
	 *
	 * @return void
	 */
	public function log(?string $data): void {
		if ($this->config->get('payment_cardinity_debug')) {
			$backtrace = debug_backtrace();

			$log = new \Log('cardinity.log');
			$log->write('(' . $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . ') - ' . print_r($data, true));
		}
	}

	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cardinity_order` (
			  `cardinity_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `payment_id` varchar(255),
			  PRIMARY KEY (`cardinity_order_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
		");
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "cardinity_order`");
	}
}
