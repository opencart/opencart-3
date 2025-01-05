<?php
/**
 * Class Review
 *
 * Can be called from $this->load->model('catalog/review');
 *
 * @package Admin\Model\Catalog
 */
class ModelCatalogReview extends Model {
	/**
	 * Add Review
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new review record
	 *
	 * @example
	 *
	 * $review_id = $this->model_catalog_review->addReview($data);
	 */
	public function addReview(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "review` SET `author` = '" . $this->db->escape((string)$data['author']) . "', `product_id` = '" . (int)$data['product_id'] . "', `text` = '" . $this->db->escape(strip_tags((string)$data['text'])) . "', `rating` = '" . (int)$data['rating'] . "', `status` = '" . (bool)($data['status'] ?? 0) . "', `date_added` = '" . $this->db->escape((string)$data['date_added']) . "'");

		$review_id = $this->db->getLastId();

		$this->cache->delete('product');

		return $review_id;
	}

	/**
	 * Edit Review
	 *
	 * @param int                  $review_id primary key of the review record
	 * @param array<string, mixed> $data      array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_catalog_review->editReview($review_id, $data);
	 */
	public function editReview(int $review_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "review` SET `author` = '" . $this->db->escape((string)$data['author']) . "', `product_id` = '" . (int)$data['product_id'] . "', `text` = '" . $this->db->escape(strip_tags((string)$data['text'])) . "', `rating` = '" . (int)$data['rating'] . "', `status` = '" . (bool)($data['status'] ?? 0) . "', `date_added` = '" . $this->db->escape((string)$data['date_added']) . "', `date_modified` = NOW() WHERE `review_id` = '" . (int)$review_id . "'");

		$this->cache->delete('product');
	}

	/**
	 * Delete Review
	 *
	 * @param int $review_id primary key of the review record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->model_catalog_review->deleteReview($review_id);
	 */
	public function deleteReview(int $review_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "review` WHERE `review_id` = '" . (int)$review_id . "'");

		$this->cache->delete('product');
	}

	/**
	 * Get Review
	 *
	 * @param int $review_id primary key of the review record
	 *
	 * @return array<string, mixed> review record that has review ID
	 *
	 * @example
	 *
	 * $review_info = $this->model_catalog_review->getReview($review_id);
	 */
	public function getReview(int $review_id): array {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT `pd`.`name` FROM `" . DB_PREFIX . "product_description` `pd` WHERE `pd`.`product_id` = `r`.`product_id` AND `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS `product` FROM `" . DB_PREFIX . "review` `r` WHERE `r`.`review_id` = '" . (int)$review_id . "'");

		return $query->row;
	}

	/**
	 * Get Reviews
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> review records
	 *
	 * @example
	 *
	 * $results = $this->model_catalog_review->getReviews();
	 */
	public function getReviews(array $data = []): array {
		$sql = "SELECT `r`.`review_id`, `pd`.`name`, `r`.`author`, `r`.`rating`, `r`.`status`, `r`.`date_added` FROM `" . DB_PREFIX . "review` `r` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`r`.`product_id` = `pd`.`product_id`) WHERE `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_product'])) {
			$sql .= " AND `pd`.`name` LIKE '" . $this->db->escape((string)$data['filter_product'] . '%') . "'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND `r`.`author` LIKE '" . $this->db->escape((string)$data['filter_author'] . '%') . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND `r`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(`r`.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(`r`.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_to']) . "')";
		}

		$sort_data = [
			'pd.name',
			'r.author',
			'r.rating',
			'r.status',
			'r.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `r`.`date_added`";
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
	 * Get Total Reviews
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of review records
	 *
	 * @example
	 *
	 * $review_total = $this->model_catalog_review->getTotalReviews($filter_data);
	 */
	public function getTotalReviews(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "review` `r` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`r`.`product_id` = `pd`.`product_id`) WHERE `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_product'])) {
			$sql .= " AND `pd`.`name` LIKE '" . $this->db->escape((string)$data['filter_product'] . '%') . "'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND `r`.`author` LIKE '" . $this->db->escape((string)$data['filter_author'] . '%') . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND `r`.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(`r`.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(`r`.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_to']) . "')";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Reviews Awaiting Approval
	 *
	 * @return int total number of reviews awaiting approval records
	 *
	 * @example
	 *
	 * $review_total = $this->model_catalog_review->getTotalReviewsAwaitingApproval());
	 */
	public function getTotalReviewsAwaitingApproval(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "review` WHERE `status` = '0'");

		return (int)$query->row['total'];
	}
}
