<?php
/**
 * Class Klarna Account
 *
 * @package Catalog\Model\Extension\Payment
 */
class ModelExtensionPaymentKlarnaAccount extends Model {
	/**
	 * getMethod
	 *
	 * @param array $address
	 *
	 * @return array
	 *
	 * Requires $total
	 */
	public function getMethod(array $address): array {
		return [];
	}

	/**
	 * Get Lowest Payment Account
	 *
	 * @param string $country
	 *
	 * @return float
	 */
	private function getLowestPaymentAccount(string $country): float {
		switch ($country) {
			case 'SWE':
				$amount = 50.0;
				break;
			case 'NOR':
				$amount = 95.0;
				break;
			case 'FIN':
				$amount = 8.95;
				break;
			case 'DNK':
				$amount = 89.0;
				break;
			case 'DEU':
			case 'NLD':
				$amount = 6.95;
				break;
			default:
				// Log
				$log = new \Log('klarna_account.log');
				$log->write('Unknown country ' . $country);

				$amount = null;
				break;
		}

		return $amount;
	}
}
