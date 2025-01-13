<?php
/**
 * Class Banner
 *
 * Can be called using $this->load->model('design/banner');
 *
 * @package Admin\Model\Design
 */
class ModelDesignBanner extends Model {
	/**
	 * Add Banner
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new banner record
	 *
	 * @example
	 *
	 * $banner_data = [
	 *     'name'   => 'Banner Name',
	 *     'status' => 0
	 * ];
	 *
	 * $this->load->model('design/banner');
	 *
	 * $banner_id = $this->model_design_banner->addBanner($banner_data);
	 */
	public function addBanner(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "banner` SET `name` = '" . $this->db->escape($data['name']) . "', `status` = '" . (int)$data['status'] . "'");

		$banner_id = $this->db->getLastId();

		if (isset($data['banner_image'])) {
			foreach ($data['banner_image'] as $language_id => $value) {
				foreach ($value as $banner_image) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "banner_image` SET `banner_id` = '" . (int)$banner_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($banner_image['title']) . "', `link` = '" . $this->db->escape($banner_image['link']) . "', `image` = '" . $this->db->escape($banner_image['image']) . "', `sort_order` = '" . (int)$banner_image['sort_order'] . "'");
				}
			}
		}

		return $banner_id;
	}

	/**
	 * Edit Banner
	 *
	 * @param int                  $banner_id primary key of the banner record
	 * @param array<string, mixed> $data      array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $banner_data = [
	 *     'name'   => 'Banner Name',
	 *     'status' => 1
	 * ];
	 *
	 * $this->load->model('design/banner');
	 *
	 * $this->model_design_banner->editBanner($banner_id, $banner_data);
	 */
	public function editBanner(int $banner_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "banner` SET `name` = '" . $this->db->escape($data['name']) . "', `status` = '" . (int)$data['status'] . "' WHERE `banner_id` = '" . (int)$banner_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "banner_image` WHERE `banner_id` = '" . (int)$banner_id . "'");

		if (isset($data['banner_image'])) {
			foreach ($data['banner_image'] as $language_id => $value) {
				foreach ($value as $banner_image) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "banner_image` SET `banner_id` = '" . (int)$banner_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($banner_image['title']) . "', `link` = '" . $this->db->escape($banner_image['link']) . "', `image` = '" . $this->db->escape($banner_image['image']) . "', `sort_order` = '" . (int)$banner_image['sort_order'] . "'");
				}
			}
		}
	}

	/**
	 * Delete Banner
	 *
	 * @param int $banner_id primary key of the banner record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('design/banner');
	 *
	 * $this->model_design_banner->deleteBanner($banner_id);
	 */
	public function deleteBanner(int $banner_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "banner` WHERE `banner_id` = '" . (int)$banner_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "banner_image` WHERE `banner_id` = '" . (int)$banner_id . "'");
	}

	/**
	 * Get Banner
	 *
	 * @param int $banner_id primary key of the banner record
	 *
	 * @return array<string, mixed> banner record that has banner ID
	 *
	 * @example
	 *
	 * $this->load->model('design/banner');
	 *
	 * $banner_info = $this->model_design_banner->getBanner($banner_id);
	 */
	public function getBanner(int $banner_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "banner` WHERE `banner_id` = '" . (int)$banner_id . "'");

		return $query->row;
	}

	/**
	 * Get Banners
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> banner records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'sort'  => 'name',
	 *     'order' => 'DESC',
	 *     'start' => 0,
	 *     'limit' => 10
	 * ];
	 *
	 * $this->load->model('design/banner');
	 *
	 * $results = $this->model_design_banner->getBanners($filter_data);
	 */
	public function getBanners(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "banner`";

		$sort_data = [
			'name',
			'status'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
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
	 * Get Images
	 *
	 * @param int $banner_id primary key of the banner record
	 *
	 * @return array<int, array<int, array<string, mixed>>> image records that have banner ID
	 *
	 * @example
	 *
	 * $this->load->model('design/banner');
	 *
	 * $banner_images = $this->model_design_banner->getImages($banner_id);
	 */
	public function getImages(int $banner_id): array {
		$banner_image_data = [];

		$banner_image_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "banner_image` WHERE `banner_id` = '" . (int)$banner_id . "' ORDER BY `sort_order` ASC");

		foreach ($banner_image_query->rows as $banner_image) {
			$banner_image_data[$banner_image['language_id']][] = [
				'title'      => $banner_image['title'],
				'link'       => $banner_image['link'],
				'image'      => $banner_image['image'],
				'sort_order' => $banner_image['sort_order']
			];
		}

		return $banner_image_data;
	}

	/**
	 * Get Total Banners
	 *
	 * @return int total number of banner records
	 *
	 * @example
	 *
	 * $this->load->model('design/banner');
	 *
	 * $banner_total = $this->model_design_banner->getTotalBanners();
	 */
	public function getTotalBanners(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "banner`");

		return (int)$query->row['total'];
	}
}
