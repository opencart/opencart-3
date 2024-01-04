<?php
/**
 * Class Cardinity
 *
 * @package Admin\Model\Extension\Payment
 */
use Cardinity\Client;

class ModelExtensionPaymentCardinity extends Model {
	/**
	 * getOrder
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function getOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cardinity_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

		return $query->row;
	}

	/**
	 * getOrder
	 *
	 * @param array $credentials
	 *
	 * @return object|null
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
	 * verifyCredentials
	 *
	 * @param object $client
	 *
	 * @return bool
	 */
	public function verifyCredentials(object $client): bool {
		$method = new \Payment\GetAll(10);

		try {
			$client->call($method);

			return true;
		} catch (\Exception $e) {
			$this->log($e->getMessage());

			return false;
		}
	}

	/**
	 * getPayment
	 *
	 * @param object $client
	 * @param string $payment_id
	 *
	 * @return object|null
	 */
	public function getPayment(object $client, string $payment_id): ?object {
		$method = new \Payment\Get($payment_id);

		try {
			return $client->call($method);
		} catch (\Exception $e) {
			$this->log($e->getMessage());

			return null;
		}
	}

	/**
	 * getRefunds
	 *
	 * @param object $client
	 * @param string $payment_id
	 *
	 * @return object|null
	 */
	public function getRefunds(object $client, string $payment_id): ?object {
		$method = new \Refund\GetAll($payment_id);

		try {
			return $client->call($method);
		} catch (\Exception $e) {
			$this->log($e->getMessage());

			return null;
		}
	}

	/**
	 * refundPayment
	 *
	 * @param object $client
	 * @param string $payment_id
	 * @param float  $amount
	 * @param string $description
	 *
	 * @return object|null
	 */
	public function refundPayment(object $client, string $payment_id, float $amount, string $description): ?object {
		$method = new \Refund\Create($payment_id, $amount, $description);

		try {
			return $client->call($method);
		} catch (\Exception $e) {
			$this->log($e->getMessage());

			return null;
		}
	}

	/**
	 * Log
	 *
	 * @param string $data
	 *
	 * @return void
	 */
	public function log(string $data): void {
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
			  `cardinity_order_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `order_id` INT(11) NOT NULL,
			  `payment_id` VARCHAR(255),
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
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "cardinity_order`;");
	}
}
