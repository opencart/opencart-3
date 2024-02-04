<?php
/**
 * Class Cardinity
 *
 * @package Catalog\Model\Extension\Payment
 */
use Cardinity\Client;
use Cardinity\Exception as CardinityException;
use Cardinity\Method\Payment;

class ModelExtensionPaymentCardinity extends Model {
	/**
	 * addOrder
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function addOrder(array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "cardinity_order` SET `order_id` = '" . (int)$data['order_id'] . "', `payment_id` = '" . $this->db->escape($data['payment_id']) . "'");
	}

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
	 * createPayment
	 *
	 * @param string $key
	 * @param string $secret
	 * @param array  $payment_data
	 *
	 * @return ?object
	 *
	 * @Throws \Exception
	 */
	public function createPayment(string $key, string $secret, array $payment_data): ?object {
		$client = Client::create([
			'consumerKey'    => $key,
			'consumerSecret' => $secret,
		]);

		$method = new Payment\Create($payment_data);

		try {
			return $client->call($method);
		} catch (\Exception $exception) {
			$this->exception($exception);

			throw new $exception();
		}
	}

	/**
	 * finalizePayment
	 *
	 * @param mixed $key
	 * @param mixed $secret
	 * @param mixed $payment_id
	 * @param mixed $pares
	 */
	public function finalizePayment($key, $secret, $payment_id, $pares) {
		$client = Client::create([
			'consumerKey'    => $key,
			'consumerSecret' => $secret,
		]);

		$method = new Payment\Finalize($payment_id, $pares);

		try {
			return $client->call($method);
		} catch (\Exception $exception) {
			$this->exception($exception);

			return false;
		}
	}

	/**
	 * getMethod
	 *
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethod(array $address): array {
		$this->load->language('extension/payment/cardinity');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_cardinity_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('payment_cardinity_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if (!in_array($this->session->data['currency'], $this->getSupportedCurrencies())) {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'cardinity',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_cardinity_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * getSupportedCountries
	 *
	 * @return array
	 */
	public function getSupportedCurrencies(): array {
		return [
			'USD',
			'GBP',
			'EUR'
		];
	}

	/**
	 * Log
	 *
	 * @param string $data
	 * @param int    $class_step
	 * @param int    $function_step
	 *
	 * @return void
	 */
	public function log(string $data, int $class_step = 6, int $function_step = 6): void {
		if ($this->config->get('payment_cardinity_debug')) {
			$backtrace = debug_backtrace();

			// Log
			$log = new \Log('cardinity.log');
			$log->write('(' . $backtrace[$class_step]['class'] . '::' . $backtrace[$function_step]['function'] . ') - ' . print_r($data, true));
		}
	}

	/**
	 * Exception
	 * 
	 * @property Exception $exception
	 * 
	 * @throws Exception
	 * 
	 * @return void
	 */
	private function exception(Exception $exception): void {
		$this->log($exception->getMessage(), 1, 2);

		switch (true) {
			case $exception instanceof CardinityException\Request:
				if ($exception->getErrorsAsString()) {
					$this->log($exception->getErrorsAsString(), 1, 2);
				}
				break;
			case $exception instanceof CardinityException\InvalidAttributeValue:
				foreach ($exception->getViolations() as $violation) {
					$this->log($violation->getMessage(), 1, 2);
				}
				break;
		}
	}
}
