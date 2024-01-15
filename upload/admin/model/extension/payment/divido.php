<?php
/**
 * Class Divido
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentDivido extends Model {
	public const CACHE_KEY_PLANS = 'divido_plans';

	/**
	 * setMerchant (Deprecated)
	 *
	 * @param mixed $api_key
	 */
	public function setMerchant($api_key): void {}

	/**
	 * getAllPlans
	 *
	 * @return array
	 */
	public function getAllPlans(): array {
		if ($plans = $this->cache->get(self::CACHE_KEY_PLANS)) {
			// OpenCart 2.1 decodes json objects to associative arrays so we
			// need to make sure we're getting a list of simple objects back.
			return array_map(fn ($plan) => (object)$plan, $plans);
		}

		$api_key = $this->config->get('payment_divido_api_key');

		if (!$api_key) {
			throw new \Exception('No Divido api-key defined');
		}

		$response = Divido_Finances::all();
		$response = (array)$response;

		if ($response['status'] != 'ok') {
			throw new \Exception('Can\'t get list of finance plans from Divido!');
		}

		$plans = $response['finances'];

		// OpenCart 2.1 switched to json for their file storage cache, so
		// we need to convert to a simple object.
		$plans_plain = [];

		foreach ($plans as $plan) {
			$plan_copy = new \stdClass();
			$plan_copy->id = $plan->id;
			$plan_copy->text = $plan->text;
			$plan_copy->country = $plan->country;
			$plan_copy->min_amount = $plan->min_amount;
			$plan_copy->min_deposit = $plan->min_deposit;
			$plan_copy->max_deposit = $plan->max_deposit;
			$plan_copy->interest_rate = $plan->interest_rate;
			$plan_copy->deferral_period = $plan->deferral_period;
			$plan_copy->agreement_duration = $plan->agreement_duration;
			$plans_plain[] = $plan_copy;
		}

		$this->cache->set(self::CACHE_KEY_PLANS, $plans_plain);

		return $plans_plain;
	}

	/**
	 * getLookupByOrderId
	 *
	 * @param int $order_id
	 *
	 * @return int
	 */
	public function getLookupByOrderId(int $order_id): int {
		return $this->db->query("SELECT * FROM `" . DB_PREFIX . "divido_lookup` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "divido_product` (
				`product_id` int(11) NOT NULL,
				`display` varchar(7) NOT NULL,
				`plans` text,
				PRIMARY KEY (`product_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "divido_lookup` (
				`order_id` int(11) NOT NULL,
				`salt` varchar(64) NOT NULL,
				`proposal_id` varchar(40),
				`application_id` varchar(40),
				`deposit_amount` DECIMAL(15,4),
			  PRIMARY KEY (`order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "divido_product`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "divido_lookup`");
	}
}
