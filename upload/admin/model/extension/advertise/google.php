<?php
/**
 * Class Google
 *
 * @package Admin\Model\Extension\Advertise
 */
use googleshopping\Googleshopping;

class ModelExtensionAdvertiseGoogle extends Model {
	/**
	 * @param array $events
	 */
	private array $events = [
		'admin/view/common/column_left/before' => [
			'extension/advertise/google/admin_link',
		],
		'admin/model/catalog/product/addProduct/after' => [
			'extension/advertise/google/addProduct',
		],
		'admin/model/catalog/product/copyProduct/after' => [
			'extension/advertise/google/copyProduct',
		],
		'admin/model/catalog/product/deleteProduct/after' => [
			'extension/advertise/google/deleteProduct',
		],
		'catalog/controller/checkout/success/before' => [
			'extension/advertise/google/before_checkout_success'
		],
		'catalog/view/common/header/after' => [
			'extension/advertise/google/google_global_site_tag'
		],
		'catalog/view/common/success/after' => [
			'extension/advertise/google/google_dynamic_remarketing_purchase'
		],
		'catalog/view/product/product/after' => [
			'extension/advertise/google/google_dynamic_remarketing_product'
		],
		'catalog/view/product/search/after' => [
			'extension/advertise/google/google_dynamic_remarketing_searchresults'
		],
		'catalog/view/product/category/after' => [
			'extension/advertise/google/google_dynamic_remarketing_category'
		],
		'catalog/view/common/home/after' => [
			'extension/advertise/google/google_dynamic_remarketing_home'
		],
		'catalog/view/checkout/cart/after' => [
			'extension/advertise/google/google_dynamic_remarketing_cart'
		]
	];

	/**
	 * @param array $rename_tables
	 */
	private array $rename_tables = [
		'advertise_google_target'             => 'googleshopping_target',
		'category_to_google_product_category' => 'googleshopping_category',
		'product_advertise_google_status'     => 'googleshopping_product_status',
		'product_advertise_google_target'     => 'googleshopping_product_target',
		'product_advertise_google'            => 'googleshopping_product'
	];

	/**
	 * @param array $table_columns
	 */
	private array $table_columns = [
		'googleshopping_target' => [
			'advertise_google_target_id',
			'store_id',
			'campaign_name',
			'country',
			'budget',
			'feeds',
			'status'
		],
		'googleshopping_category' => [
			'google_product_category',
			'store_id',
			'category_id'
		],
		'googleshopping_product_status' => [
			'product_id',
			'store_id',
			'product_variation_id',
			'destination_statuses',
			'data_quality_issues',
			'item_level_issues',
			'google_expiration_date'
		],
		'googleshopping_product_target' => [
			'product_id',
			'store_id',
			'advertise_google_target_id'
		],
		'googleshopping_product' => [
			'product_advertise_google_id',
			'product_id',
			'store_id',
			'has_issues',
			'destination_status',
			'impressions',
			'clicks',
			'conversions',
			'cost',
			'conversion_value',
			'google_product_category',
			'condition',
			'adult',
			'multipack',
			'is_bundle',
			'age_group',
			'color',
			'gender',
			'size_type',
			'size_system',
			'size',
			'is_modified'
		]
	];

	/**
	 * isAppIdUsed
	 *
	 * @param string $app_id
	 * @param int    $store_id
	 *
	 * @return bool
	 */
	public function isAppIdUsed(string $app_id, int $store_id): bool {
		$sql = "SELECT `store_id` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'advertise_google_app_id' AND `value` = '" . $this->db->escape($app_id) . "' AND `store_id` != '" . (int)$store_id . "' LIMIT 1";

		$result = $this->db->query($sql);

		if ($result->num_rows) {
			try {
				$googleshopping = new Googleshopping($this->registry, (int)$result->row['store_id']);

				return $googleshopping->isConnected();
			} catch (\RuntimeException $e) {
				return false;
			}
		}

		return false;
	}

	/**
	 * getFinalProductId
	 *
	 * @return int
	 */
	public function getFinalProductId(): int {
		$query = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "product` ORDER BY `product_id` DESC LIMIT 1");

		if ($query->num_rows) {
			return (int)$query->row['product_id'];
		} else {
			return 0;
		}
	}

	/**
	 * isAnyProductCategoryModified
	 *
	 * @param int $store_id
	 *
	 * @return int
	 */
	public function isAnyProductCategoryModified(int $store_id): int {
		$query = $this->db->query("SELECT `pag`.`is_modified` FROM `" . DB_PREFIX . "googleshopping_product` `pag` WHERE `pag`.`google_product_category` IS NOT NULL AND `pag`.`store_id` = '" . (int)$store_id . "' LIMIT 0,1");

		return $query->num_rows;
	}

	/**
	 * getAdvertisedCount
	 *
	 * @param int $store_id
	 *
	 * @return int
	 */
	public function getAdvertisedCount(int $store_id): int {
		$query = $this->db->query("SELECT COUNT(`product_id`) AS `total` FROM `" . DB_PREFIX . "googleshopping_product_target` WHERE `store_id` = '" . (int)$store_id . "' GROUP BY `product_id`");

		return (int)$query->row['total'];
	}

	/**
	 * getMapping
	 *
	 * @param int $store_id
	 *
	 * @return int
	 */
	public function getMapping(int $store_id): int {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "googleshopping_category` WHERE `store_id` = '" . (int)$store_id . "'");

		return $query->rows;
	}

	/**
	 * setCategoryMapping
	 *
	 * @param string $google_product_category
	 * @param int    $store_id
	 * @param int    $category_id
	 *
	 * @return void
	 */
	public function setCategoryMapping(string $google_product_category, int $store_id, int $category_id): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "googleshopping_category` SET `google_product_category` = '" . $this->db->escape($google_product_category) . "', `store_id` = '" . (int)$store_id . "', `category_id` = '" . (int)$category_id . "' ON DUPLICATE KEY UPDATE `category_id` = '" . (int)$category_id . "'");
	}

	/**
	 * getMappedCategory
	 *
	 * @param string $google_product_category
	 * @param int    $store_id
	 *
	 * @return array
	 */
	public function getMappedCategory(string $google_product_category, int $store_id): array {
		$query = $this->db->query("SELECT GROUP_CONCAT(`cd`.`name` ORDER BY `cp`.`level` SEPARATOR ' > ') AS `name`, `cp`.`category_id` FROM `" . DB_PREFIX . "category_path` `cp` LEFT JOIN `" . DB_PREFIX . "category_description` `cd` ON (`cp`.`path_id` = `cd`.`category_id`) LEFT JOIN `" . DB_PREFIX . "googleshopping_category` `c2gpc` ON (`c2gpc`.`category_id` = `cp`.`category_id`) WHERE `cd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `c2gpc`.`google_product_category` = '" . $this->db->escape($google_product_category) . "' AND `c2gpc`.`store_id` = '" . (int)$store_id . "'");

		return $query->row;
	}

	/**
	 * getProductByProductAdvertiseGoogleId
	 *
	 * @param int $product_advertise_google_id
	 *
	 * @return array
	 */
	public function getProductByProductAdvertiseGoogleId(int $product_advertise_google_id): array {
		$sql = "SELECT `pag`.`product_id` FROM `" . DB_PREFIX . "googleshopping_product` `pag` WHERE `pag`.`product_advertise_google_id` = '" . (int)$product_advertise_google_id . "'";

		$result = $this->db->query($sql);

		if ($result->num_rows) {
			// Products
			$this->load->model('catalog/product');

			return $this->model_catalog_product->getProduct($result->row['product_id']);
		} else {
			return [];
		}
	}

	/**
	 * getProductAdvertiseGoogle
	 *
	 * @param int $product_advertise_google_id
	 *
	 * @return array
	 */
	public function getProductAdvertiseGoogle(int $product_advertise_google_id): array {
		$query = $this->db->query("SELECT `pag`.* FROM `" . DB_PREFIX . "googleshopping_product` `pag` WHERE `pag`.`product_advertise_google_id` = '" . (int)$product_advertise_google_id . "'");

		return $query->row;
	}

	/**
	 * hasActiveTarget
	 *
	 * @param int $store_id
	 *
	 * @return int
	 */
	public function hasActiveTarget(int $store_id): int {
		$query = $this->db->query("SELECT COUNT(`agt`.`advertise_google_target_id`) AS `total` FROM `" . DB_PREFIX . "googleshopping_target` `agt` WHERE `agt`.`store_id` = '" . (int)$store_id . "' AND `agt`.`status` = 'active' LIMIT 1");

		return (int)$query->row['total'];
	}

	/**
	 * getRequiredFieldsByProductIds
	 *
	 * @param array $product_ids
	 * @param int   $store_id
	 *
	 * @return array
	 */
	public function getRequiredFieldsByProductIds(array $product_ids, int $store_id): array {
		$this->load->config('googleshopping/googleshopping');

		$result = [];

		$countries = $this->getTargetCountriesByProductIds($product_ids, $store_id);

		foreach ($countries as $country) {
			foreach ((array)$this->config->get('advertise_google_country_required_fields') as $field => $requirements) {
				if ((!empty($requirements['countries']) && in_array($country, (array)$requirements['countries'])) || (empty($requirements['countries']) && is_array($requirements['countries']))) {
					$result[$field] = $requirements;
				}
			}
		}

		return $result;
	}

	/**
	 * getRequiredFieldsByFilter
	 *
	 * @param array<string, mixed> $data
	 * @param int                  $store_id
	 *
	 * @return array
	 */
	public function getRequiredFieldsByFilter(array $data, int $store_id): array {
		$this->load->config('googleshopping/googleshopping');

		$result = [];

		$countries = $this->getTargetCountriesByFilter($data, $store_id);

		foreach ($countries as $country) {
			foreach ((array)$this->config->get('advertise_google_country_required_fields') as $field => $requirements) {
				if ((!empty($requirements['countries']) && in_array($country, (array)$requirements['countries'])) || (is_array($requirements['countries']) && empty($requirements['countries']))) {
					$result[$field] = $requirements;
				}
			}
		}

		return $result;
	}

	/**
	 * getTargetCountriesByProductIds
	 *
	 * @param array $product_ids
	 * @param int   $store_id
	 *
	 * @return array
	 */
	public function getTargetCountriesByProductIds(array $product_ids, int $store_id): array {
		$sql = "SELECT DISTINCT `agt`.`country` FROM `" . DB_PREFIX . "googleshopping_product_target` `pagt` LEFT JOIN `" . DB_PREFIX . "googleshopping_target` `agt` ON (`agt`.`advertise_google_target_id` = `pagt`.`advertise_google_target_id` AND `agt`.`store_id` = `pagt`.`store_id`) WHERE `pagt`.`product_id` IN(" . $this->googleshopping->productIdsToIntegerExpression($product_ids) . ") AND `pagt`.`store_id` = '" . (int)$store_id . "'";

		return array_map([$this, 'country'], $this->db->query($sql)->rows);
	}

	/**
	 * getTargetCountriesByFilter
	 *
	 * @param array<string, mixed> $data
	 * @param int                  $store_id
	 *
	 * @return array
	 */
	public function getTargetCountriesByFilter(array $data, int $store_id): array {
		$sql = "SELECT DISTINCT `agt`.`country` FROM `" . DB_PREFIX . "googleshopping_product_target` `pagt` LEFT JOIN `" . DB_PREFIX . "googleshopping_target` `agt` ON (`agt`.`advertise_google_target_id` = `pagt`.`advertise_google_target_id` AND `agt`.`store_id` = `pagt`.`store_id`) LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`pagt`.`product_id` = `p`.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`pd`.`product_id` = `pagt`.`product_id`) WHERE `pagt`.`store_id` = '" . (int)$store_id . "' AND `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		$this->googleshopping->applyFilter($sql, $data);

		return array_map([$this, 'country'], $this->db->query($sql)->rows);
	}

	/**
	 * getProductOptionsByProductIds
	 *
	 * @param array $product_ids
	 *
	 * @return array
	 */
	public function getProductOptionsByProductIds(array $product_ids): array {
		$query = $this->db->query("SELECT `po`.`option_id`, `od`.`name` FROM `" . DB_PREFIX . "product_option` `po` LEFT JOIN `" . DB_PREFIX . "option_description` `od` ON (`od`.`option_id` = `po`.`option_id` AND `od`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN `" . DB_PREFIX . "option` `o` ON (`o`.`option_id` = `po`.`option_id`) WHERE `o`.`type` IN('select', 'radio') AND `po`.`product_id` IN(" . $this->googleshopping->productIdsToIntegerExpression($product_ids) . ")");

		return $query->rows;
	}

	/**
	 * getProductOptionsByFilter
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array
	 */
	public function getProductOptionsByFilter(array $data): array {
		$sql = "SELECT DISTINCT `po`.`option_id`, `od`.`name` FROM `" . DB_PREFIX . "product_option` `po` LEFT JOIN `" . DB_PREFIX . "option_description` `od` ON (`od`.`option_id` = `po`.`option_id` AND `od`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN `" . DB_PREFIX . "option` `o` ON (`o`.`option_id` = `po`.`option_id`) LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`po`.`product_id` = `p`.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`pd`.`product_id` = `po`.`product_id`) WHERE `o`.`type` IN('select', 'radio') AND `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		$this->googleshopping->applyFilter($sql, $data);

		return $this->db->query($sql)->rows;
	}

	/**
	 * addTarget
	 *
	 * @param array $target
	 * @param int   $store_id
	 *
	 * @return int
	 */
	public function addTarget(array $target, int $store_id): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "googleshopping_target` SET `store_id` = '" . (int)$store_id . "', `campaign_name` = '" . $this->db->escape($target['campaign_name']) . "', `country` = '" . $this->db->escape($target['country']) . "', `budget` = '" . (float)$target['budget'] . "', `feeds` = '" . $this->db->escape(json_encode($target['feeds'])) . "', `date_added` = NOW(), `roas` = '" . (int)$target['roas'] . "', `status` = '" . $this->db->escape($target['status']) . "'");

		return $this->db->getLastId();
	}

	/**
	 * deleteProducts
	 *
	 * @param array $product_ids
	 *
	 * @return void
	 */
	public function deleteProducts(array $product_ids): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "googleshopping_product` WHERE `product_id` IN(" . $this->googleshopping->productIdsToIntegerExpression($product_ids) . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "googleshopping_product_target` WHERE `product_id` IN(" . $this->googleshopping->productIdsToIntegerExpression($product_ids) . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "googleshopping_product_status` WHERE `product_id` IN(" . $this->googleshopping->productIdsToIntegerExpression($product_ids) . ")");
	}

	/**
	 * setAdvertisingBySelect
	 *
	 * @param array $post_product_ids
	 * @param array $post_target_ids
	 * @param int   $store_id
	 *
	 * @return void
	 */
	public function setAdvertisingBySelect(array $post_product_ids, array $post_target_ids, int $store_id): void {
		if ($post_product_ids) {
			$product_ids = array_map([$this->googleshopping, 'integer'], $post_product_ids);

			$product_ids_expression = implode(',', $product_ids);

			$this->db->query("DELETE FROM `" . DB_PREFIX . "googleshopping_product_target` WHERE `product_id` IN(" . $product_ids_expression . ") AND `store_id` = '" . (int)$store_id . "'");

			if ($post_target_ids) {
				$target_ids = array_map([$this->googleshopping, 'integer'], $post_target_ids);

				$values = [];

				foreach ($product_ids as $product_id) {
					foreach ($target_ids as $target_id) {
						$values[] = '(' . $product_id . ',' . $store_id . ',' . $target_id . ')';
					}
				}

				$this->db->query("INSERT INTO `" . DB_PREFIX . "googleshopping_product_target` (`product_id`, `store_id`, `advertise_google_target_id`) VALUES " . implode(',', $values));
			}
		}
	}

	/**
	 * setAdvertisingByFilter
	 *
	 * @param array<string, mixed> $data
	 * @param array                $post_target_ids
	 * @param int                  $store_id
	 *
	 * @return void
	 */
	public function setAdvertisingByFilter(array $data, array $post_target_ids, int $store_id): void {
		$sql = "DELETE `pagt` FROM `" . DB_PREFIX . "googleshopping_product_target` `pagt` LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`pagt`.`product_id` = `p`.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`pd`.`product_id` = `p`.`product_id`) WHERE `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		$this->googleshopping->applyFilter($sql, $data);

		$this->db->query($sql);

		if ($post_target_ids) {
			$target_ids = array_map([$this->googleshopping, 'integer'], $post_target_ids);

			$insert_sql = "SELECT `p`.`product_id`, " . (int)$store_id . " AS `store_id`, '{TARGET_ID}' AS `advertise_google_target_id` FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`pd`.`product_id` = `p`.`product_id`) WHERE `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

			$this->googleshopping->applyFilter($insert_sql, $data);

			foreach ($target_ids as $target_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "googleshopping_product_target` (`product_id`, `store_id`, `advertise_google_target_id`) " . str_replace('{TARGET_ID}', (string)$target_id, $insert_sql));
			}
		}
	}

	/**
	 * insertNewProducts
	 *
	 * @param array $product_ids
	 * @param int   $store_id
	 *
	 * @return void
	 */
	public function insertNewProducts(array $product_ids, int $store_id): void {
		$sql = "INSERT INTO `" . DB_PREFIX . "googleshopping_product` (`product_id`, `store_id`, `google_product_category`) SELECT `p`.`product_id`, `p2s`.`store_id`, (SELECT `c2gpc`.`google_product_category` FROM `" . DB_PREFIX . "product_to_category` `p2c` LEFT JOIN `" . DB_PREFIX . "category_path` `cp` ON (`p2c`.`category_id` = `cp`.`category_id`) LEFT JOIN `" . DB_PREFIX . "googleshopping_category` `c2gpc` ON (`c2gpc`.`category_id` = `cp`.`path_id` AND `c2gpc`.`store_id` = '" . (int)$store_id . "') WHERE `p2c`.`product_id` = `p`.`product_id` AND `c2gpc`.`google_product_category` IS NOT NULL ORDER BY `cp`.`level` DESC LIMIT 0,1) AS `google_product_category` FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_to_store` `p2s` ON (`p2s`.`product_id` = `p`.`product_id` AND `p2s`.`store_id` = '" . (int)$store_id . "') LEFT JOIN `" . DB_PREFIX . "googleshopping_product` `pag` ON (`pag`.`product_id` = `p`.`product_id` AND `pag`.`store_id` = `p2s`.`store_id`) WHERE `pag`.`product_id` IS NULL AND `p2s`.`store_id` IS NOT NULL";

		if ($product_ids) {
			$sql .= " AND `p`.`product_id` IN(" . $this->googleshopping->productIdsToIntegerExpression($product_ids) . ")";
		}

		$this->db->query($sql);
	}

	/**
	 * updateGoogleProductCategoryMapping
	 *
	 * @param int $store_id
	 *
	 * @return void
	 */
	public function updateGoogleProductCategoryMapping(int $store_id): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "googleshopping_product` (`product_id`, `store_id`, `google_product_category`) SELECT `p`.`product_id`, " . (int)$store_id . " AS `store_id`, (SELECT `c2gpc`.`google_product_category` FROM `" . DB_PREFIX . "product_to_category` `p2c` LEFT JOIN `" . DB_PREFIX . "category_path` `cp` ON (`p2c`.`category_id` = `cp`.`category_id`) LEFT JOIN `" . DB_PREFIX . "googleshopping_category` `c2gpc` ON (`c2gpc`.`category_id` = `cp`.`path_id` AND `c2gpc`.`store_id` = '" . (int)$store_id . "') WHERE `p2c`.`product_id` = `p`.`product_id` AND `c2gpc`.`google_product_category` IS NOT NULL ORDER BY `cp`.`level` DESC LIMIT 0,1) AS `google_product_category` FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "googleshopping_product` `pag` ON (`pag`.`product_id` = `p`.`product_id`) WHERE `pag`.`product_id` IS NOT NULL ON DUPLICATE KEY UPDATE `google_product_category` = VALUES(`google_product_category`)");
	}

	/**
	 * updateSingleProductFields
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function updateSingleProductFields(array $data): void {
		$values = [];
		$entry = [];

		$entry['product_id'] = (int)$data['product_id'];
		$entry = array_merge($entry, $this->makeInsertData($data));

		$values[] = "(" . implode(",", $entry) . ")";

		$this->db->query("INSERT INTO `" . DB_PREFIX . "googleshopping_product` (`product_id`, `store_id`, `google_product_category`, `condition`, `adult`, `multipack`, `is_bundle`, `age_group`, `color`, `gender`, `size_type`, `size_system`, `size`, `is_modified`) VALUES " . implode(',', $values) . " ON DUPLICATE KEY UPDATE " . $this->makeOnDuplicateKeyData());
	}

	/**
	 * updateMultipleProductFields
	 *
	 * @param array                $filter_data
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function updateMultipleProductFields(array $filter_data, array $data): void {
		$insert_sql = "SELECT `p`.`product_id`, {INSERT_DATA} FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`pd`.`product_id` = `p`.`product_id`) WHERE `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		$this->googleshopping->applyFilter($insert_sql, $filter_data);

		$insert_data = [];

		$keys[] = "`product_id`";

		foreach ($this->makeInsertData($data) as $key => $value) {
			$insert_data[] = $value . " as `" . $key . "`";
			$keys[] = "`" . $key . "`";
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "googleshopping_product` (" . implode(", ", $keys) . ") " . str_replace('{INSERT_DATA}', implode(", ", $insert_data), $insert_sql) . " ON DUPLICATE KEY UPDATE " . $this->makeOnDuplicateKeyData());
	}

	protected function makeInsertData($data) {
		$insert_data = [];

		$insert_data['store_id'] = "'" . (int)$data['store_id'] . "'";
		$insert_data['google_product_category'] = "'" . $this->db->escape($data['google_product_category']) . "'";
		$insert_data['condition'] = "'" . $this->db->escape($data['condition']) . "'";
		$insert_data['adult'] = "'" . (int)$data['adult'] . "'";
		$insert_data['multipack'] = "'" . (int)$data['multipack'] . "'";
		$insert_data['is_bundle'] = "'" . (int)$data['is_bundle'] . "'";
		$insert_data['age_group'] = "'" . $this->db->escape($data['age_group']) . "'";
		$insert_data['color'] = "'" . (int)$data['color'] . "'";
		$insert_data['gender'] = "'" . $this->db->escape($data['gender']) . "'";
		$insert_data['size_type'] = "'" . $this->db->escape($data['size_type']) . "'";
		$insert_data['size_system'] = "'" . $this->db->escape($data['size_system']) . "'";
		$insert_data['size'] = "'" . (int)$data['size'] . "'";
		$insert_data['is_modified'] = '1';

		return $insert_data;
	}

	protected function makeOnDuplicateKeyData() {
		return "`google_product_category`=VALUES(`google_product_category`), `condition`=VALUES(`condition`), `adult`=VALUES(`adult`), `multipack`=VALUES(`multipack`), `is_bundle`=VALUES(`is_bundle`), `age_group`=VALUES(`age_group`), `color`=VALUES(`color`), `gender`=VALUES(`gender`), `size_type`=VALUES(`size_type`), `size_system`=VALUES(`size_system`), `size`=VALUES(`size`), `is_modified`=VALUES(`is_modified`)";
	}

	/**
	 * getCategories
	 *
	 * @param array<string, mixed> $data
	 * @param int                  $store_id
	 *
	 * @return array
	 */
	public function getCategories(array $data, int $store_id): array {
		$sql = "SELECT `cp`.`category_id` AS `category_id`, GROUP_CONCAT(`cd1`.`name` ORDER BY `cp`.`level` SEPARATOR ' > ') AS `name`, `c1`.`parent_id`, `c1`.`sort_order` FROM `" . DB_PREFIX . "category_path` `cp` LEFT JOIN `" . DB_PREFIX . "category_to_store` `c2s` ON (`c2s`.`category_id` = `cp`.`category_id` AND `c2s`.`store_id` = '" . (int)$store_id . "') LEFT JOIN `" . DB_PREFIX . "category` `c1` ON (`cp`.`category_id` = `c1`.`category_id`) LEFT JOIN `" . DB_PREFIX . "category` `c2` ON (`cp`.`path_id` = `c2`.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` `cd1` ON (`cp`.`path_id` = `cd1`.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` `cd2` ON (`cp`.`category_id` = `cd2`.`category_id`) WHERE `c2s`.`store_id` IS NOT NULL AND `cd1`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `cd2`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `cd2`.`name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY `cp`.`category_id`";

		$sort_data = [
			'name',
			'sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `sort_order`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * getProductCampaigns
	 *
	 * @param int $product_id
	 * @param int $store_id
	 *
	 * @return array
	 */
	public function getProductCampaigns(int $product_id, int $store_id): array {
		$query = $this->db->query("SELECT `agt`.`advertise_google_target_id`, `agt`.`campaign_name` FROM `" . DB_PREFIX . "googleshopping_product_target` `pagt` LEFT JOIN `" . DB_PREFIX . "googleshopping_target` `agt` ON (`pagt`.`advertise_google_target_id` = `agt`.`advertise_google_target_id`) WHERE `pagt`.`product_id` = '" . (int)$product_id . "' AND `pagt`.`store_id` = '" . (int)$store_id . "'");

		return $query->rows;
	}

	/**
	 * getProductIssues
	 *
	 * @param int $product_id
	 * @param int $store_id
	 *
	 * @return array
	 */
	public function getProductIssues(int $product_id, int $store_id): array {
		$this->load->language('extension/advertise/google');

		// Languages
		$this->load->model('localisation/language');

		$query = $this->db->query("SELECT `pag`.`color`, `pag`.`size`, `pd`.`name`, `p`.`model` FROM `" . DB_PREFIX . "googleshopping_product` `pag` LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`p`.`product_id` = `pag`.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`pd`.`product_id` = `pag`.`product_id` AND `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') WHERE `pag`.`product_id` = '" . (int)$product_id . "' AND `pag`.`store_id` = '" . (int)$store_id . "'");

		$product_info = $query->row;

		if ($product_info) {
			$result = [];

			$result['name'] = $product_info['name'];
			$result['model'] = $product_info['model'];

			$result['entries'] = [];

			foreach ($this->model_localisation_language->getLanguages() as $language) {
				$language_id = $language['language_id'];

				$groups = $this->googleshopping->getGroups($product_id, $language_id, $product_info['color'], $product_info['size']);

				$result['entries'][$language_id] = [
					'language_name' => $language['name'],
					'issues'        => []
				];

				foreach ($groups as $id => $group) {
					$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "googleshopping_product_status` WHERE `product_id` = '" . (int)$product_id . "' AND `store_id` = '" . (int)$store_id . "' AND `product_variation_id` = '" . $this->db->escape($id) . "'");

					$issues = $query->row;

					$destination_statuses = !empty($issues['destination_statuses']) ? json_decode($issues['destination_statuses'], true) : [];
					$data_quality_issues = !empty($issues['data_quality_issues']) ? json_decode($issues['data_quality_issues'], true) : [];
					$item_level_issues = !empty($issues['item_level_issues']) ? json_decode($issues['item_level_issues'], true) : [];
					$google_expiration_date = !empty($issues['google_expiration_date']) ? date($this->language->get('datetime_format'), $issues['google_expiration_date']) : $this->language->get('text_na');

					$result['entries'][$language_id]['issues'][] = [
						'color'                  => $group['color'] != '' ? $group['color'] : $this->language->get('text_na'),
						'size'                   => $group['size'] != '' ? $group['size'] : $this->language->get('text_na'),
						'destination_statuses'   => $destination_statuses,
						'data_quality_issues'    => $data_quality_issues,
						'item_level_issues'      => $item_level_issues,
						'google_expiration_date' => $google_expiration_date
					];
				}
			}

			return $result;
		}

		return [];
	}

	/**
	 * renameTables
	 *
	 * @return void
	 *
	 * Shortly after releasing the extension,
	 * we learned that the table names are actually
	 * clashing with third-party extensions.
	 * Hence, this renaming script was created.
	 */
	public function renameTables(): void {
		foreach ($this->rename_tables as $old_table => $new_table) {
			$new_table_name = DB_PREFIX . $new_table;
			$old_table_name = DB_PREFIX . $old_table;

			if ($this->tableExists($old_table_name) && !$this->tableExists($new_table_name) && $this->tableColumnsMatch($old_table_name, $this->table_columns[$new_table])) {
				$this->db->query("RENAME TABLE `" . $old_table_name . "` TO `" . $new_table_name . "`");
			}
		}
	}

	/**
	 * tableExists
	 *
	 * @param string $table
	 *
	 * @return int
	 */
	private function tableExists($table): int {
		return $this->db->query("SHOW TABLES LIKE '" . $table . "'")->num_rows;
	}

	/**
	 * tableColunsMatch
	 *
	 * @param string $table
	 * @param string $columns
	 *
	 * @return bool
	 */
	private function tableColumnsMatch($table, $columns): bool {
		$num_columns = $this->db->query("SHOW COLUMNS FROM `" . $table . "` WHERE Field IN(" . implode(',', $this->wrap($columns, '"')) . ")")->num_rows;

		return $num_columns == count($columns);
	}

	/**
	 * Wrap
	 *
	 * @param mixed $text
	 * @param mixed $char
	 */
	private function wrap($text, $char) {
		if (is_array($text)) {
			foreach ($text as &$string) {
				$string = $char . $string . $char;
			}

			return $text;
		} else {
			return $char . $text . $char;
		}
	}

	/**
	 * createTables
	 *
	 * @return void
	 */
	public function createTables(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_product` (
            `product_advertise_google_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `product_id` int(11),
            `store_id` int(11) NOT NULL DEFAULT '0',
            `has_issues` tinyint(1),
            `destination_status` enum(\'pending\',\'approved\',\'disapproved\') NOT NULL DEFAULT 'pending',
            `impressions` int(11) NOT NULL DEFAULT '0',
            `clicks` int(11) NOT NULL DEFAULT '0',
            `conversions` int(11) NOT NULL DEFAULT '0',
            `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
            `conversion_value` decimal(15,4) NOT NULL DEFAULT '0.0000',
            `google_product_category` VARCHAR(10),
            `condition` enum(\'new\',\'refurbished\',\'used\'),
            `adult` tinyint(1),
            `multipack` int(11),
            `is_bundle` tinyint(1),
            `age_group` enum(\'newborn\',\'infant\',\'toddler\',\'kids\',\'adult\'),
            `color` int(11),
            `gender` enum(\'male\',\'female\',\'unisex\'),
            `size_type` enum(\'regular\',\'petite\',\'plus\',\'big and tall\',\'maternity\'),
            `size_system` enum(\'AU\',\'BR\',\'CN\',\'DE\',\'EU\',\'FR\',\'IT\',\'JP\',\'MEX\',\'UK\',\'US\'),
            `size` int(11),
            `is_modified` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`product_advertise_google_id`),
            UNIQUE `product_id_store_id` (`product_id`, `store_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_product_status` (
            `product_id` int(11),
            `store_id` int(11) NOT NULL DEFAULT '0',
            `product_variation_id` varchar(64),
            `destination_statuses` text NOT NULL,
            `data_quality_issues` text NOT NULL,
            `item_level_issues` text NOT NULL,
            `google_expiration_date` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`product_id`, `store_id`, `product_variation_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_product_target` (
            `product_id` int(11) NOT NULL,
            `store_id` int(11) NOT NULL DEFAULT '0',
            `advertise_google_target_id` int(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`product_id`, `advertise_google_target_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_category` (
            `google_product_category` varchar(10) NOT NULL,
            `store_id` int(11) NOT NULL DEFAULT '0',
            `category_id` int(11) NOT NULL,
            INDEX `category_id_store_id` (`category_id`, `store_id`),
            PRIMARY KEY (`google_product_category`, `store_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "googleshopping_target` (
            `advertise_google_target_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `store_id` int(11) NOT NULL DEFAULT '0',
            `campaign_name` varchar(255) NOT NULL DEFAULT '',
            `country` varchar(2) NOT NULL DEFAULT '',
            `budget` decimal(15,4) NOT NULL DEFAULT '0.0000',
            `feeds` text NOT NULL,
            `date_added` date,
            `roas` int(11) NOT NULL DEFAULT '0',
            `status` enum(\'paused\',\'active\') NOT NULL DEFAULT 'paused',
            INDEX `store_id` (`store_id`),
            PRIMARY KEY (`advertise_google_target_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
	}

	/**
	 * fixColumns
	 *
	 * @return void
	 */
	public function fixColumns(): void {
		$has_auto_increment = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "googleshopping_product` WHERE Field = 'product_advertise_google_id' AND Extra LIKE '%auto_increment%'")->num_rows;

		if (!$has_auto_increment) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "googleshopping_product` MODIFY COLUMN `product_advertise_google_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT");
		}

		$has_unique_key = $this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "googleshopping_product` WHERE Key_name = 'product_id_store_id' AND Non_unique = 0")->num_rows == 2;

		if (!$has_unique_key) {
			$index_exists = $this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "googleshopping_product` WHERE Key_name = 'product_id_store_id'")->num_rows;

			if ($index_exists) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "googleshopping_product` DROP INDEX product_id_store_id;");
			}

			$this->db->query("CREATE UNIQUE INDEX product_id_store_id ON `" . DB_PREFIX . "googleshopping_product` (product_id, store_id)");
		}

		$has_date_added_column = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "googleshopping_target` WHERE Field = 'date_added'")->num_rows;

		if (!$has_date_added_column) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "googleshopping_target` ADD COLUMN `date_added` DATE");

			$this->db->query("UPDATE `" . DB_PREFIX . "googleshopping_target` SET `date_added` = NOW() WHERE `date_added` IS NULL");
		}

		$has_roas_column = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "googleshopping_target` WHERE Field = 'roas'")->num_rows;

		if (!$has_roas_column) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "googleshopping_target` ADD COLUMN `roas` int(11) NOT NULL DEFAULT '0'");
		}
	}

	/**
	 * dropTables
	 *
	 * @return void
	 */
	public function dropTables(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_target`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_category`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_product_status`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_product_target`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "googleshopping_product`");
	}

	/**
	 * deleteEvents
	 *
	 * @return void
	 */
	public function deleteEvents(): void {
		// Events
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('advertise_google');
	}

	/**
	 * createEvents
	 *
	 * @return void
	 */
	public function createEvents(): void {
		// Events
		$this->load->model('setting/event');

		foreach ($this->events as $trigger => $actions) {
			foreach ($actions as $action) {
				$this->model_setting_event->addEvent('advertise_google', $trigger, $action, 1, 0);
			}
		}
	}

	/**
	 * getAllowedTargets
	 *
	 * @return array
	 */
	public function getAllowedTargets(): array {
		$this->load->config('googleshopping/googleshopping');

		$result = [];

		foreach ((array)$this->config->get('advertise_google_targets') as $target) {
			$result[] = [
				'country' => [
					'code' => $target['country'],
					'name' => $this->googleshopping->getCountryName($target['country'])
				],
				'languages'  => $this->googleshopping->getLanguages($target['languages']),
				'currencies' => $this->googleshopping->getCurrencies($target['currencies'])
			];
		}

		return $result;
	}

	protected function country($row) {
		return $row['country'];
	}
}
