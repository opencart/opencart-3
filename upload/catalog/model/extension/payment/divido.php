<?php
/**
 * Class Divido
 *
 * @package Catalog\Model\Extension\Payment
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
	 * findOCOrderId
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
	 * getProductSettings
	 *
	 * @param int $product_id
	 *
	 * @return array
	 */
	public function getProductSettings(int $product_id): array {
		$query = $this->db->query("SELECT `display`, `plans` FROM `" . DB_PREFIX . "divido_product` WHERE `product_id` = '" . (int)$product_id . "'");

		return $query->row;
	}

	/**
	 * isEnabled
	 *
	 * @return bool
	 */
	public function isEnabled(): bool {
		$api_key = $this->config->get('payment_divido_api_key');
		$enabled = $this->config->get('payment_divido_status');

		return !empty($api_key) && $enabled == 1;
	}

	/**
	 * hasOrderId
	 *
	 * @param int    $order_id
	 * @param string $salt
	 *
	 * @return string
	 */
	public function hashOrderId(int $order_id, string $salt): string {
		return hash('sha256', $order_id . $salt);
	}

	/**
	 * saveLookup
	 *
	 * @param int    $order_id
	 * @param string $salt
	 * @param string $proposal_id
	 * @param string $application_id
	 * @param float  $deposit_amount
	 *
	 * @return void
	 */
	public function saveLookup(int $order_id, string $salt, ?string $proposal_id = null, ?string $application_id = null, ?float $deposit_amount = null): void {
		$query = $this->db->query("SELECT `application_id` FROM `" . DB_PREFIX . "divido_lookup` WHERE `order_id` = '" . $order_id . "'");

		if (!$query->num_rows) {
			$proposal_id = ($proposal_id) ? "'" . $proposal_id . "'" : 'NULL';
			$application_id = ($application_id) ? "'" . $application_id . "'" : 'NULL';
			$deposit_amount = ($deposit_amount) ?: 'NULL';

			$sql = "INSERT INTO `" . DB_PREFIX . "divido_lookup` SET `order_id` = '" . (int)$order_id . "', `salt` = '" . $this->db->escape($salt) . "', `proposal_id` = '" . $this->db->escape($proposal_id) . "', `application_id` = '" . $application_id . "', `deposit_amount` = '" . (float)$deposit_amount . "'";
		} else {
			$sql = "UPDATE `" . DB_PREFIX . "divido_lookup` SET `salt` = '" . $salt . "'";

			if ($proposal_id) {
				$sql .= ", `proposal_id` = '" . $proposal_id . "'";
			}

			if ($application_id) {
				$sql .= ", `application_id` = '" . $application_id . "'";
			}

			if ($deposit_amount) {
				$sql .= ", `deposit_amount` = '" . $deposit_amount . "'";
			}

			$sql .= " WHERE `order_id` = '" . $order_id . "'";
		}

		$this->db->query($sql);
	}

	/**
	 * getLookupByOrderId
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function getLookupByOrderId(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "divido_lookup` WHERE `order_id` = '" . (int)$order_id . "'");

		return $query->row;
	}

	/**
	 * getGlobalSelectedPlans
	 *
	 * @return array
	 */
	public function getGlobalSelectedPlans(): array {
		$all_plans = $this->getAllPlans();

		$display_plans = $this->config->get('payment_divido_planselection');

		if ($display_plans == 'all' || empty($display_plans)) {
			return $all_plans;
		}

		$selected_plans = $this->config->get('payment_divido_plans_selected');

		if (!$selected_plans) {
			return [];
		}

		$plans = [];

		foreach ($all_plans as $plan) {
			if (in_array($plan->id, $selected_plans)) {
				$plans[] = $plan;
			}
		}

		return $plans;
	}

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

		if (($response->status) && strtolower($response->status) != 'ok') {
			throw new \Exception('Can\'t get list of finance plans from Divido!');
		}

		// OpenCart 2.1 switched to json for their file storage cache, so
		// we need to convert to a simple object.
		$plans_plain = [];

		if (isset($response->plans)) {
			$plans = $response->finances;

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
		}

		$this->cache->set(self::CACHE_KEY_PLANS, $plans_plain);

		return $plans_plain;
	}

	/**
	 * getCartPlans
	 *
	 * @param object $cart
	 *
	 * @return array
	 */
	public function getCartPlans(object $cart): array {
		$plans = [];

		$products = $cart->getProducts();

		foreach ($products as $product) {
			$product_plans = $this->getProductPlans($product['product_id']);

			if ($product_plans) {
				$plans = array_merge($plans, $product_plans);
			}
		}

		return $plans;
	}

	/**
	 * getPlans
	 *
	 * @param mixed $default_plans
	 *
	 * @return array
	 */
	public function getPlans($default_plans): array {
		if ($default_plans) {
			$plans = $this->getGlobalSelectedPlans();
		} else {
			$plans = $this->getAllPlans();
		}

		return $plans;
	}

	/**
	 * getTotals
	 *
	 * @return array
	 */
	public function getTotals(): array {
		$taxes = $this->cart->getTaxes();
		$total = 0;
		$totals = [];

		// Because __call can not keep var references, so we put them into an array.
		$total_data = [
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		];

		// Extensions
		$this->load->model('setting/extension');

		$sort_order = [];

		$results = $this->model_setting_extension->getExtensionsByType('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);

				// We have to put the totals in an array so that they pass by reference.
				$callable = [$this->{'model_extension_total_' . $result['code']}, 'getTotal'];

				if (is_callable($callable)) {
					$callable($total_data);
				}
			}
		}

		$sort_order = [];

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);

		return [
			$total,
			$totals
		];
	}

	/**
	 * getProductPlans
	 *
	 * @param int $product_id
	 *
	 * @return array
	 */
	public function getProductPlans(int $product_id): array {
		// Products
		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		$settings = $this->getProductSettings($product_id);

		$product_selection = $this->config->get('payment_divido_productselection');
		$divido_categories = $this->config->get('payment_divido_categories');
		$price_threshold = $this->config->get('payment_divido_price_threshold');

		if ($divido_categories) {
			$product_categories = $this->model_catalog_product->getCategories($product_id);

			$all_categories = [];

			foreach ($product_categories as $product_category) {
				$all_categories[] = $product_category['category_id'];
			}

			$category_matches = array_intersect($all_categories, $divido_categories);

			if (!$category_matches) {
				return [];
			}
		}

		if (empty($settings)) {
			$settings = [
				'display' => 'default',
				'plans'   => '',
			];
		}

		if ($product_selection == 'selected' && $settings['display'] == 'custom' && empty($settings['plans'])) {
			return [];
		}

		$price = 0;

		if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
			$base_price = !empty($product_info['special']) ? $product_info['special'] : $product_info['price'];
			$price = $this->tax->calculate($base_price, $product_info['tax_class_id'], $this->config->get('config_tax'));
		}

		if ($product_selection == 'threshold' && !empty($price_threshold) && $price < $price_threshold) {
			return [];
		}

		if ($settings['display'] == 'default') {
			return $this->getPlans(true);
		}

		// If the product has non-default plans, fetch all of them.
		$available_plans = $this->getPlans(false);

		$selected_plans = explode(',', $settings['plans']);

		$plans = [];

		foreach ($available_plans as $plan) {
			if (in_array($plan->id, $selected_plans)) {
				$plans[] = $plan;
			}
		}

		if (empty($plans)) {
			return [];
		}

		return $plans;
	}
}
