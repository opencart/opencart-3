<?php
/**
 * Class Product
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogProduct extends Model {
	/**
	 * Add Product
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function addProduct(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "product` SET `model` = '" . $this->db->escape($data['model']) . "', `sku` = '" . $this->db->escape($data['sku']) . "', `upc` = '" . $this->db->escape($data['upc']) . "', `ean` = '" . $this->db->escape($data['ean']) . "', `jan` = '" . $this->db->escape($data['jan']) . "', `isbn` = '" . $this->db->escape($data['isbn']) . "', `mpn` = '" . $this->db->escape($data['mpn']) . "', `location` = '" . $this->db->escape($data['location']) . "', `quantity` = '" . (int)$data['quantity'] . "', `minimum` = '" . (int)$data['minimum'] . "', `subtract` = '" . (int)$data['subtract'] . "', `stock_status_id` = '" . (int)$data['stock_status_id'] . "', `date_available` = '" . $this->db->escape($data['date_available']) . "', `manufacturer_id` = '" . (int)$data['manufacturer_id'] . "', `shipping` = '" . (int)$data['shipping'] . "', `price` = '" . (float)$data['price'] . "', `points` = '" . (int)$data['points'] . "', `weight` = '" . (float)$data['weight'] . "', `weight_class_id` = '" . (int)$data['weight_class_id'] . "', `length` = '" . (float)$data['length'] . "', `width` = '" . (float)$data['width'] . "', `height` = '" . (float)$data['height'] . "', `length_class_id` = '" . (int)$data['length_class_id'] . "', `status` = '" . (int)$data['status'] . "', `tax_class_id` = '" . (int)$data['tax_class_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `date_added` = NOW(), `date_modified` = NOW()");

		$product_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `product_id` = '" . (int)$product_id . "'");
		}

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "product_description` SET `product_id` = '" . (int)$product_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `tag` = '" . $this->db->escape($value['tag']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_store` SET `product_id` = '" . (int)$product_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = '" . (int)$product_id . "' AND `attribute_id` = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = '" . (int)$product_id . "' AND `attribute_id` = '" . (int)$product_attribute['attribute_id'] . "' AND `language_id` = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO `" . DB_PREFIX . "product_attribute` SET `product_id` = '" . (int)$product_id . "', `attribute_id` = '" . (int)$product_attribute['attribute_id'] . "', `language_id` = '" . (int)$language_id . "', `text` = '" . $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "product_option` SET `product_id` = '" . (int)$product_id . "', `option_id` = '" . (int)$product_option['option_id'] . "', `required` = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO `" . DB_PREFIX . "product_option_value` SET `product_option_id` = '" . (int)$product_option_id . "', `product_id` = '" . (int)$product_id . "', `option_id` = '" . (int)$product_option['option_id'] . "', `option_value_id` = '" . (int)$product_option_value['option_value_id'] . "', `quantity` = '" . (int)$product_option_value['quantity'] . "', `subtract` = '" . (int)$product_option_value['subtract'] . "', `price` = '" . (float)$product_option_value['price'] . "', `price_prefix` = '" . $this->db->escape($product_option_value['price_prefix']) . "', `points` = '" . (int)$product_option_value['points'] . "', `points_prefix` = '" . $this->db->escape($product_option_value['points_prefix']) . "', `weight` = '" . (float)$product_option_value['weight'] . "', `weight_prefix` = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_option` SET `product_id` = '" . (int)$product_id . "', `option_id` = '" . (int)$product_option['option_id'] . "', `value` = '" . $this->db->escape($product_option['value']) . "', `required` = '" . (int)$product_option['required'] . "'");
				}
			}
		}

		// Subscriptions
		if (isset($data['product_subscription'])) {
			foreach ($data['product_subscription'] as $product_subscription) {
				$query = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "product_subscription` WHERE `product_id` = '" . (int)$product_id . "' AND `customer_group_id` = '" . (int)$product_subscription['customer_group_id'] . "' AND `subscription_plan_id` = '" . (int)$product_subscription['subscription_plan_id'] . "'");

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_subscription` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$product_subscription['customer_group_id'] . "', `subscription_plan_id` = '" . (int)$product_subscription['subscription_plan_id'] . "'");
				}
			}
		}

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_discount` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$product_discount['customer_group_id'] . "', `quantity` = '" . (int)$product_discount['quantity'] . "', `priority` = '" . (int)$product_discount['priority'] . "', `price` = '" . (float)$product_discount['price'] . "', `date_start` = '" . $this->db->escape($product_discount['date_start']) . "', `date_end` = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_special` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$product_special['customer_group_id'] . "', `priority` = '" . (int)$product_special['priority'] . "', `price` = '" . (float)$product_special['price'] . "', `date_start` = '" . $this->db->escape($product_special['date_start']) . "', `date_end` = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_image` SET `product_id` = '" . (int)$product_id . "', `image` = '" . $this->db->escape($product_image['image']) . "', `sort_order` = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_download` SET `product_id` = '" . (int)$product_id . "', `download_id` = '" . (int)$download_id . "'");
			}
		}

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_category` SET `product_id` = '" . (int)$product_id . "', `category_id` = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_filter` SET `product_id` = '" . (int)$product_id . "', `filter_id` = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `product_id` = '" . (int)$product_id . "' AND `related_id` = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `product_id` = '" . (int)$related_id . "' AND `related_id` = '" . (int)$product_id . "'");

				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_related` SET `product_id` = '" . (int)$product_id . "', `related_id` = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_related` SET `product_id` = '" . (int)$related_id . "', `related_id` = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				if ((int)$product_reward['points'] > 0) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_reward` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$customer_group_id . "', `points` = '" . (int)$product_reward['points'] . "'");
				}
			}
		}

		// SEO URL
		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `query` = 'product_id=" . (int)$product_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_layout` SET `product_id` = '" . (int)$product_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('product');

		return $product_id;
	}

	/**
	 * Edit Product
	 *
	 * @param int                  $product_id
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function editProduct(int $product_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `model` = '" . $this->db->escape($data['model']) . "', `sku` = '" . $this->db->escape($data['sku']) . "', `upc` = '" . $this->db->escape($data['upc']) . "', `ean` = '" . $this->db->escape($data['ean']) . "', `jan` = '" . $this->db->escape($data['jan']) . "', `isbn` = '" . $this->db->escape($data['isbn']) . "', `mpn` = '" . $this->db->escape($data['mpn']) . "', `location` = '" . $this->db->escape($data['location']) . "', `quantity` = '" . (int)$data['quantity'] . "', `minimum` = '" . (int)$data['minimum'] . "', `subtract` = '" . (int)$data['subtract'] . "', `stock_status_id` = '" . (int)$data['stock_status_id'] . "', `date_available` = '" . $this->db->escape($data['date_available']) . "', `manufacturer_id` = '" . (int)$data['manufacturer_id'] . "', `shipping` = '" . (int)$data['shipping'] . "', `price` = '" . (float)$data['price'] . "', `points` = '" . (int)$data['points'] . "', `weight` = '" . (float)$data['weight'] . "', `weight_class_id` = '" . (int)$data['weight_class_id'] . "', `length` = '" . (float)$data['length'] . "', `width` = '" . (float)$data['width'] . "', `height` = '" . (float)$data['height'] . "', `length_class_id` = '" . (int)$data['length_class_id'] . "', `status` = '" . (int)$data['status'] . "', `tax_class_id` = '" . (int)$data['tax_class_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `date_modified` = NOW() WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `product_id` = '" . (int)$product_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_description` WHERE `product_id` = '" . (int)$product_id . "'");

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "product_description` SET `product_id` = '" . (int)$product_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `tag` = '" . $this->db->escape($value['tag']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_store` SET `product_id` = '" . (int)$product_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = '" . (int)$product_id . "'");

		if (!empty($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = '" . (int)$product_id . "' AND `attribute_id` = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "product_attribute` SET `product_id` = '" . (int)$product_id . "', `attribute_id` = '" . (int)$product_attribute['attribute_id'] . "', `language_id` = '" . (int)$language_id . "', `text` = '" . $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option_value` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "product_option` SET `product_option_id` = '" . (int)$product_option['product_option_id'] . "', `product_id` = '" . (int)$product_id . "', `option_id` = '" . (int)$product_option['option_id'] . "', `required` = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO `" . DB_PREFIX . "product_option_value` SET `product_option_value_id` = '" . (int)$product_option_value['product_option_value_id'] . "', `product_option_id` = '" . (int)$product_option_id . "', `product_id` = '" . (int)$product_id . "', `option_id` = '" . (int)$product_option['option_id'] . "', `option_value_id` = '" . (int)$product_option_value['option_value_id'] . "', `quantity` = '" . (int)$product_option_value['quantity'] . "', `subtract` = '" . (int)$product_option_value['subtract'] . "', `price` = '" . (float)$product_option_value['price'] . "', `price_prefix` = '" . $this->db->escape($product_option_value['price_prefix']) . "', `points` = '" . (int)$product_option_value['points'] . "', `points_prefix` = '" . $this->db->escape($product_option_value['points_prefix']) . "', `weight` = '" . (float)$product_option_value['weight'] . "', `weight_prefix` = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_option` SET `product_option_id` = '" . (int)$product_option['product_option_id'] . "', `product_id` = '" . (int)$product_id . "', `option_id` = '" . (int)$product_option['option_id'] . "', `value` = '" . $this->db->escape($product_option['value']) . "', `required` = '" . (int)$product_option['required'] . "'");
				}
			}
		}

		// Subscriptions
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_subscription` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_subscription'])) {
			foreach ($data['product_subscription'] as $product_subscription) {
				$query = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "product_subscription` WHERE `product_id` = '" . (int)$product_id . "' AND `customer_group_id` = '" . (int)$product_subscription['customer_group_id'] . "' AND `subscription_plan_id` = '" . (int)$product_subscription['subscription_plan_id'] . "'");

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_subscription` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$product_subscription['customer_group_id'] . "', `subscription_plan_id` = '" . (int)$product_subscription['subscription_plan_id'] . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_discount` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_discount` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$product_discount['customer_group_id'] . "', `quantity` = '" . (int)$product_discount['quantity'] . "', `priority` = '" . (int)$product_discount['priority'] . "', `price` = '" . (float)$product_discount['price'] . "', `date_start` = '" . $this->db->escape($product_discount['date_start']) . "', `date_end` = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_special` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$product_special['customer_group_id'] . "', `priority` = '" . (int)$product_special['priority'] . "', `price` = '" . (float)$product_special['price'] . "', `date_start` = '" . $this->db->escape($product_special['date_start']) . "', `date_end` = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_image` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_image` SET `product_id` = '" . (int)$product_id . "', `image` = '" . $this->db->escape($product_image['image']) . "', `sort_order` = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_download` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_download` SET `product_id` = '" . (int)$product_id . "', `download_id` = '" . (int)$download_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_category` SET `product_id` = '" . (int)$product_id . "', `category_id` = '" . (int)$category_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_filter` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_filter` SET `product_id` = '" . (int)$product_id . "', `filter_id` = '" . (int)$filter_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `related_id` = '" . (int)$product_id . "'");

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `product_id` = '" . (int)$product_id . "' AND `related_id` = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `product_id` = '" . (int)$related_id . "' AND `related_id` = '" . (int)$product_id . "'");

				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_related` SET `product_id` = '" . (int)$product_id . "', `related_id` = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_related` SET `product_id` = '" . (int)$related_id . "', `related_id` = '" . (int)$product_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_reward` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $value) {
				if ((int)$value['points'] > 0) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_reward` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$customer_group_id . "', `points` = '" . (int)$value['points'] . "'");
				}
			}
		}

		// SEO URL
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'product_id=" . (int)$product_id . "'");

		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `query` = 'product_id=" . (int)$product_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_layout` WHERE `product_id` = '" . (int)$product_id . "'");

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_layout` SET `product_id` = '" . (int)$product_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('product');
	}

	/**
	 * Copy Product
	 *
	 * @param int $product_id
	 *
	 * @return void
	 */
	public function copyProduct(int $product_id): void {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "product` `p` WHERE `p`.`product_id` = '" . (int)$product_id . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['viewed'] = '0';
			$data['status'] = '0';
			$data['sku'] = '';
			$data['upc'] = '';
			$data['keyword'] = '';
			$data['product_attribute'] = $this->getAttributes($product_id);
			$data['product_description'] = $this->getDescriptions($product_id);
			$data['product_discount'] = $this->getDiscounts($product_id);
			$data['product_filter'] = $this->getFilters($product_id);
			$data['product_image'] = $this->getImages($product_id);
			$data['product_option'] = $this->getOptions($product_id);
			$data['product_related'] = $this->getRelated($product_id);
			$data['product_reward'] = $this->getRewards($product_id);
			$data['product_special'] = $this->getSpecials($product_id);
			$data['product_category'] = $this->getCategories($product_id);
			$data['product_download'] = $this->getDownloads($product_id);
			$data['product_layout'] = $this->getLayouts($product_id);
			$data['product_store'] = $this->getStores($product_id);
			$data['product_subscriptions'] = $this->getSubscriptions($product_id);

			$this->addProduct($data);
		}
	}

	/**
	 * Delete Product
	 *
	 * @param int $product_id
	 *
	 * @return void
	 */
	public function deleteProduct(int $product_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_description` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_discount` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_filter` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_image` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option_value` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE `related_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_reward` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_download` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_layout` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_subscription` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "review` WHERE `product_id` = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'product_id=" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "coupon_product` WHERE `product_id` = '" . (int)$product_id . "'");

		$this->cache->delete('product');
	}

	/**
	 * Get Product
	 *
	 * @param int $product_id
	 *
	 * @return array<string, mixed>
	 */
	public function getProduct(int $product_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`p`.`product_id` = `pd`.`product_id`) WHERE `p`.`product_id` = '" . (int)$product_id . "' AND `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Products
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getProducts(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`p`.`product_id` = `pd`.`product_id`) WHERE `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `pd`.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND `p`.`model` LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (!empty($data['filter_price'])) {
			$sql .= " AND `p`.`price` LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
			$sql .= " AND `p`.`quantity` = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND `p`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		$sql .= " GROUP BY `p`.`product_id`";

		$sort_data = [
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `pd`.`name`";
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
	 * Get Products By Category ID
	 *
	 * @param int $category_id
	 *
	 * @return array
	 */
	public function getProductsByCategoryId(int $category_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`p`.`product_id` = `pd`.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_to_category` `p2c` ON (`p`.`product_id` = `p2c`.`product_id`) WHERE `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `p2c`.`category_id` = '" . (int)$category_id . "' ORDER BY `pd`.`name` ASC");

		return $query->rows;
	}

	/**
	 * Get Descriptions
	 *
	 * @param int $product_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getDescriptions(int $product_id): array {
		$product_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_description` WHERE `product_id` = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = [
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			];
		}

		return $product_description_data;
	}

	/**
	 * Get Categories
	 *
	 * @param int $product_id
	 *
	 * @return array<int, int>
	 */
	public function getCategories(int $product_id): array {
		$product_category_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	/**
	 * Get Filters
	 *
	 * @param int $product_id
	 *
	 * @return array<int, int>
	 */
	public function getFilters(int $product_id): array {
		$product_filter_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_filter` WHERE `product_id` = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_filter_data[] = $result['filter_id'];
		}

		return $product_filter_data;
	}

	/**
	 * Get Attributes
	 *
	 * @param int $product_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getAttributes(int $product_id): array {
		$product_attribute_data = [];

		$product_attribute_query = $this->db->query("SELECT `attribute_id` FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = '" . (int)$product_id . "' GROUP BY `attribute_id`");

		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = [];

			$product_attribute_description_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = '" . (int)$product_id . "' AND `attribute_id` = '" . (int)$product_attribute['attribute_id'] . "'");

			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = ['text' => $product_attribute_description['text']];
			}

			$product_attribute_data[] = [
				'attribute_id'                  => $product_attribute['attribute_id'],
				'product_attribute_description' => $product_attribute_description_data
			];
		}

		return $product_attribute_data;
	}

	/**
	 * Get Options
	 *
	 * @param int $product_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getOptions(int $product_id): array {
		$product_option_data = [];

		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` `po` LEFT JOIN `" . DB_PREFIX . "option` `o` ON (`po`.`option_id` = `o`.`option_id`) LEFT JOIN `" . DB_PREFIX . "option_description` `od` ON (`o`.`option_id` = `od`.`option_id`) WHERE `po`.`product_id` = '" . (int)$product_id . "' AND `od`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `o`.`sort_order` ASC");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = [];

			$product_option_value_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option_value` `pov` LEFT JOIN `" . DB_PREFIX . "option_value` `ov` ON(`pov`.`option_value_id` = `ov`.`option_value_id`) WHERE `pov`.`product_option_id` = '" . (int)$product_option['product_option_id'] . "' ORDER BY `ov`.`sort_order` ASC");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = [
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'points'                  => $product_option_value['points'],
					'points_prefix'           => $product_option_value['points_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix']
				];
			}

			$product_option_data[] = [
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			];
		}

		return $product_option_data;
	}

	/**
	 * Get Option Value
	 *
	 * @param int $product_id
	 * @param int $product_option_value_id
	 *
	 * @return array<string, mixed>
	 */
	public function getOptionValue(int $product_id, int $product_option_value_id): array {
		$query = $this->db->query("SELECT `pov`.`option_value_id`, `ovd`.`name`, `pov`.`quantity`, `pov`.`subtract`, `pov`.`price`, `pov`.`price_prefix`, `pov`.`points`, `pov`.`points_prefix`, `pov`.`weight`, `pov`.`weight_prefix` FROM `" . DB_PREFIX . "product_option_value` `pov` LEFT JOIN `" . DB_PREFIX . "option_value` `ov` ON (`pov`.`option_value_id` = `ov`.`option_value_id`) LEFT JOIN `" . DB_PREFIX . "option_value_description` `ovd` ON (`ov`.`option_value_id` = `ovd`.`option_value_id`) WHERE `pov`.`product_id` = '" . (int)$product_id . "' AND `pov`.`product_option_value_id` = '" . (int)$product_option_value_id . "' AND `ovd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Images
	 *
	 * @param int $product_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getImages(int $product_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_image` WHERE `product_id` = '" . (int)$product_id . "' ORDER BY `sort_order` ASC");

		return $query->rows;
	}

	/**
	 * Get Discounts
	 *
	 * @param int $product_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getDiscounts(int $product_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_discount` WHERE `product_id` = '" . (int)$product_id . "' ORDER BY `quantity`, `priority`, `price`");

		return $query->rows;
	}

	/**
	 * Get Specials
	 *
	 * @param int $product_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSpecials(int $product_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_special` WHERE `product_id` = '" . (int)$product_id . "' ORDER BY `priority`, `price`");

		return $query->rows;
	}

	/**
	 * Get Rewards
	 *
	 * @param int $product_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getRewards(int $product_id): array {
		$product_reward_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_reward` WHERE `product_id` = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = ['points' => $result['points']];
		}

		return $product_reward_data;
	}

	/**
	 * Get Downloads
	 *
	 * @param int $product_id
	 *
	 * @return array<int, int>
	 */
	public function getDownloads(int $product_id): array {
		$product_download_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_download` WHERE `product_id` = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}

		return $product_download_data;
	}

	/**
	 * Get Stores
	 *
	 * @param int $product_id
	 *
	 * @return array<int, int>
	 */
	public function getStores(int $product_id): array {
		$product_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_store` WHERE `product_id` = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}

		return $product_store_data;
	}

	/**
	 * getSeoUrls
	 *
	 * @param int $product_id
	 *
	 * @return array<int, array<string, string>>
	 */
	public function getSeoUrls(int $product_id): array {
		$product_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'product_id=" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $product_seo_url_data;
	}

	/**
	 * Get Layouts
	 *
	 * @param int $product_id
	 *
	 * @return array<int, int>
	 */
	public function getLayouts(int $product_id): array {
		$product_layout_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_layout` WHERE `product_id` = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $product_layout_data;
	}

	/**
	 * Get Related
	 *
	 * @param int $product_id
	 *
	 * @return array<int, int>
	 */
	public function getRelated(int $product_id): array {
		$product_related_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_related` WHERE `product_id` = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}

	/**
	 * Get Subscriptions
	 *
	 * @param int $product_id
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSubscriptions(int $product_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_subscription` WHERE `product_id` = '" . (int)$product_id . "'");

		return $query->rows;
	}

	/**
	 * Get Total Products
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function getTotalProducts(array $data = []): int {
		$implode = [];

		$sql = "SELECT COUNT(DISTINCT `p`.`product_id`) AS `total` FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`p`.`product_id` = `pd`.`product_id`)";

		$implode[] = "`pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$implode[] = "`pd`.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$implode[] = "`p`.`model` LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && $data['filter_price'] != '') {
			$implode[] = "`p`.`price` LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
			$implode[] = "`p`.`quantity` = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "`p`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Tax Class ID
	 *
	 * @param int $tax_class_id
	 *
	 * @return int
	 */
	public function getTotalProductsByTaxClassId(int $tax_class_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product` WHERE `tax_class_id` = '" . (int)$tax_class_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Stock Status ID
	 *
	 * @param int $stock_status_id
	 *
	 * @return int
	 */
	public function getTotalProductsByStockStatusId(int $stock_status_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product` WHERE `stock_status_id` = '" . (int)$stock_status_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Weight Class ID
	 *
	 * @param int $weight_class_id
	 *
	 * @return int
	 */
	public function getTotalProductsByWeightClassId(int $weight_class_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product` WHERE `weight_class_id` = '" . (int)$weight_class_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Length Class ID
	 *
	 * @param int $length_class_id
	 *
	 * @return int
	 */
	public function getTotalProductsByLengthClassId(int $length_class_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product` WHERE `length_class_id` = '" . (int)$length_class_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Download ID
	 *
	 * @param int $download_id
	 *
	 * @return int
	 */
	public function getTotalProductsByDownloadId(int $download_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product_to_download` WHERE `download_id` = '" . (int)$download_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Manufacturer ID
	 *
	 * @param int $manufacturer_id
	 *
	 * @return int
	 */
	public function getTotalProductsByManufacturerId(int $manufacturer_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Attribute ID
	 *
	 * @param int $attribute_id
	 *
	 * @return int
	 */
	public function getTotalProductsByAttributeId(int $attribute_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product_attribute` WHERE `attribute_id` = '" . (int)$attribute_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Subscription Plan ID
	 *
	 * @param int $subscription_plan_id
	 *
	 * @return int
	 */
	public function getTotalProductsBySubscriptionPlanId(int $subscription_plan_id): int {
		$query = $this->db->query("SELECT COUNT(DISTINCT `product_id`) AS `total` FROM `" . DB_PREFIX . "product_subscription` WHERE `subscription_plan_id` = '" . (int)$subscription_plan_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Option ID
	 *
	 * @param int $option_id
	 *
	 * @return int
	 */
	public function getTotalProductsByOptionId(int $option_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product_option` WHERE `option_id` = '" . (int)$option_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Products By Layout ID
	 *
	 * @param int $layout_id
	 *
	 * @return int
	 */
	public function getTotalProductsByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product_to_layout` WHERE `layout_id` = '" . (int)$layout_id . "'");

		return (int)$query->row['total'];
	}
}
