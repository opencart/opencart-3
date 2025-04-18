<?php
/**
 * Class Category
 *
 * Can be called using $this->load->model('catalog/category');
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogCategory extends Model {
	/**
	 * Add Category
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new category record
	 *
	 * @example
	 *
	 * $category_data = [
	 *     'image'         => 'category_image',
	 *     'parent_id'     => 0,
	 *     'sort_order'    => 0,
	 *     'status'        => 0,
	 *     'date_added'    => '2021-01-01',
	 *     'date_modified' => '2021-01-31'
	 * ];
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $category_id = $this->model_catalog_category->addCategory($category_data);
	 */
	public function addCategory(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET `parent_id` = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW(), `date_added` = NOW()");

		$category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "category` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `category_id` = '" . (int)$category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET `category_id` = '" . (int)$category_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_filter` SET `category_id` = '" . (int)$category_id . "', `filter_id` = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_store` SET `category_id` = '" . (int)$category_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `query` = 'category_id=" . (int)$category_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		// Set which layout to use with this category
		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_layout` SET `category_id` = '" . (int)$category_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('category');

		return $category_id;
	}

	/**
	 * Edit Category
	 *
	 * @param int                  $category_id primary key of the category record
	 * @param array<string, mixed> $data        array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $category_data = [
	 *     'image'         => 'category_image',
	 *     'parent_id'     => 0,
	 *     'sort_order'    => 0,
	 *     'status'        => 1,
	 *     'date_modified' => '2021-01-01'
	 * ];
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $this->model_catalog_category->editCategory($category_id, $category_data);
	 */
	public function editCategory(int $category_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "category` SET `parent_id` = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW() WHERE `category_id` = '" . (int)$category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "category` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `category_id` = '" . (int)$category_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_description` WHERE `category_id` = '" . (int)$category_id . "'");

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET `category_id` = '" . (int)$category_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE `path_id` = '" . (int)$category_id . "' ORDER BY `level` ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$category_path['category_id'] . "' AND `level` < '" . (int)$category_path['level'] . "'");

				$path = [];

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$category_path['category_id'] . "' ORDER BY `level` ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', `level` = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_filter` WHERE `category_id` = '" . (int)$category_id . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_filter` SET `category_id` = '" . (int)$category_id . "', `filter_id` = '" . (int)$filter_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE `category_id` = '" . (int)$category_id . "'");

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_store` SET `category_id` = '" . (int)$category_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'category_id=" . (int)$category_id . "'");

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `query` = 'category_id=" . (int)$category_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_layout` WHERE `category_id` = '" . (int)$category_id . "'");

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_layout` SET `category_id` = '" . (int)$category_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('category');
	}

	/**
	 * Delete Category
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $this->model_catalog_category->deleteCategory($category_id);
	 */
	public function deleteCategory(int $category_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$category_id . "'");

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE `path_id` = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['category_id']);
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "category` WHERE `category_id` = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_description` WHERE `category_id` = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_filter` WHERE `category_id` = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE `category_id` = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_layout` WHERE `category_id` = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `category_id` = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'category_id=" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "coupon_category` WHERE `category_id` = '" . (int)$category_id . "'");

		$this->cache->delete('category');
	}

	/**
	 * Repair Categories
	 *
	 * @param int $parent_id primary key of the parent category record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $this->model_catalog_category->repairCategories();
	 */
	public function repairCategories(int $parent_id = 0): void {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category` WHERE `parent_id` = '" . (int)$parent_id . "'");

		foreach ($query->rows as $category) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$category['category_id'] . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$parent_id . "' ORDER BY `level` ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$category['category_id'] . "', `level` = '" . (int)$level . "'");

			$this->repairCategories($category['category_id']);
		}
	}

	/**
	 * Get Category
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return array<string, mixed> category record that has category ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $category_info = $this->model_catalog_category->getCategory($category_id);
	 */
	public function getCategory(int $category_id): array {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(`cd1`.`name` ORDER BY `level` SEPARATOR ' > ') FROM `" . DB_PREFIX . "category_path` `cp` LEFT JOIN `" . DB_PREFIX . "category_description` `cd1` ON (`cp`.`path_id` = `cd1`.`category_id` AND `cp`.`category_id` != `cp`.`path_id`) WHERE `cp`.`category_id` = `c`.`category_id` AND `cd1`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' GROUP BY `cp`.`category_id`) AS `path` FROM `" . DB_PREFIX . "category` `c` LEFT JOIN `" . DB_PREFIX . "category_description` `cd2` ON (`c`.`category_id` = `cd2`.`category_id`) WHERE `c`.`category_id` = '" . (int)$category_id . "' AND `cd2`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Categories
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> category records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $results = $this->model_catalog_category->getCategories();
	 */
	public function getCategories(array $data = []): array {
		$sql = "SELECT `cp`.`category_id` AS `category_id`, GROUP_CONCAT(`cd1`.`name` ORDER BY `cp`.`level` SEPARATOR ' > ') AS `name`, `c1`.`parent_id`, `c1`.`sort_order` FROM `" . DB_PREFIX . "category_path` `cp` LEFT JOIN `" . DB_PREFIX . "category` `c1` ON (`cp`.`category_id` = `c1`.`category_id`) LEFT JOIN `" . DB_PREFIX . "category` `c2` ON (`cp`.`path_id` = `c2`.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` `cd1` ON (`cp`.`path_id` = `cd1`.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` `cd2` ON (`cp`.`category_id` = `cd2`.`category_id`) WHERE `cd1`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `cd2`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `cd2`.`name` LIKE '" . $this->db->escape('%' . $data['filter_name'] . '%') . "'";
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
	 * Get Descriptions
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return array<int, array<string, string>> description records that have category ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $category_description = $this->model_catalog_category->getDescriptions($category_id);
	 */
	public function getDescriptions(int $category_id): array {
		$category_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_description` WHERE `category_id` = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = [
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			];
		}

		return $category_description_data;
	}

	/**
	 * Get Path
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $path = $this->model_catalog_category->getPath($category_id);
	 */
	public function getPath(int $category_id): array {
		$query = $this->db->query("SELECT `category_id`, `path_id`, `level` FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$category_id . "'");

		return $query->rows;
	}

	/**
	 * Get Filters
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return array<int, int> filter records that have category ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $filters = $this->model_catalog_category->getFilters($category_id);
	 */
	public function getFilters(int $category_id): array {
		$category_filter_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_filter` WHERE `category_id` = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_filter_data[] = $result['filter_id'];
		}

		return $category_filter_data;
	}

	/**
	 * Get Category Stores
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return array<int, int> store records that have category ID
	 *
	 * @example
	 *
	 * $results = $this->model_catalog_category->getCategoryStores($category_id);
	 */
	public function getCategoryStores(int $category_id): array {
		$category_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_to_store` WHERE `category_id` = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_store_data[] = $result['store_id'];
		}

		return $category_store_data;
	}

	/**
	 * Get Category Seo Urls
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return array<int, array<string, string>> SEO URL records that have category ID
	 *
	 * @example
	 *
	 * $results = $this->model_catalog_category->getCategorySeoUrls($category_id);
	 */
	public function getCategorySeoUrls(int $category_id): array {
		$category_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'category_id=" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $category_seo_url_data;
	}

	/**
	 * Get Category Layouts
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return array<int, int> layout records that have category ID
	 *
	 * @example
	 *
	 * $category_layout = $this->model_catalog_category->getCategoryLayouts($category_id);
	 */
	public function getCategoryLayouts(int $category_id): array {
		$category_layout_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_to_layout` WHERE `category_id` = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $category_layout_data;
	}

	/**
	 * Get Total Categories
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of category records
	 *
	 * @example
	 *
	 * $category_total = $this->model_catalog_category->getTotalCategories($filter_data);
	 */
	public function getTotalCategories(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "category`");

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Categories By LayoutId
	 *
	 * @param int $layout_id primary key of the layout record
	 *
	 * @return int total number of layout records that have layout ID
	 *
	 * @example
	 *
	 * $category_total = $this->model_catalog_category->getTotalCategoriesByLayoutId($layout_id);
	 */
	public function getTotalCategoriesByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "category_to_layout` WHERE `layout_id` = '" . (int)$layout_id . "'");

		return (int)$query->row['total'];
	}
}
