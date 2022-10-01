<?php
class ModelDesignSeoUrl extends Model {
    public function addSeoUrl(array $data): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$data['store_id'] . "', `language_id` = '" . (int)$data['language_id'] . "', `query` = '" . $this->db->escape($data['query']) . "', `keyword` = '" . $this->db->escape($data['keyword']) . "'");
    }

    public function editSeoUrl(int $seo_url_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$data['store_id'] . "', `language_id` = '" . (int)$data['language_id'] . "', `query` = '" . $this->db->escape($data['query']) . "', `keyword` = '" . $this->db->escape($data['keyword']) . "' WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");
    }

    public function deleteSeoUrl(int $seo_url_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");
    }

    public function getSeoUrl(int $seo_url_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");

        return $query->row;
    }

    public function getSeoUrls(array $data = []): array {
        $sql = "SELECT *, (SELECT `name` FROM `" . DB_PREFIX . "store` s WHERE s.`store_id` = su.`store_id`) AS `store`, (SELECT `name` FROM `" . DB_PREFIX . "language` l WHERE l.`language_id` = su.`language_id`) AS `language` FROM `" . DB_PREFIX . "seo_url` su";

        $implode = [];

        if (!empty($data['filter_query'])) {
            $implode[] = "`query` LIKE '" . $this->db->escape($data['filter_query']) . "'";
        }

        if (!empty($data['filter_keyword'])) {
            $implode[] = "`keyword` LIKE '" . $this->db->escape($data['filter_keyword']) . "'";
        }

        if (isset($data['filter_store_id']) && $data['filter_store_id'] !== '') {
            $implode[] = "`store_id` = '" . (int)$data['filter_store_id'] . "'";
        }

        if (!empty($data['filter_language_id']) && $data['filter_language_id'] !== '') {
            $implode[] = "`language_id` = '" . (int)$data['filter_language_id'] . "'";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $sort_data = [
            'query',
            'keyword',
            'language_id',
            'store_id'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY `query`";
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

    public function getTotalSeoUrls(array $data = []): int {
        $sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "seo_url`";

        $implode = [];

        if (!empty($data['filter_query'])) {
            $implode[] = "`query` LIKE '" . $this->db->escape($data['filter_query']) . "'";
        }

        if (!empty($data['filter_keyword'])) {
            $implode[] = "`keyword` LIKE '" . $this->db->escape($data['filter_keyword']) . "'";
        }

        if (!empty($data['filter_store_id']) && $data['filter_store_id'] !== '') {
            $implode[] = "`store_id` = '" . (int)$data['filter_store_id'] . "'";
        }

        if (!empty($data['filter_language_id']) && $data['filter_language_id'] !== '') {
            $implode[] = "`language_id` = '" . (int)$data['filter_language_id'] . "'";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $query = $this->db->query($sql);

        return (int)$query->row['total'];
    }

    public function getSeoUrlsByKeyword(string $keyword): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `keyword` = '" . $this->db->escape($keyword) . "'");

        return $query->rows;
    }

    public function getSeoUrlsByQuery(string $query): array {
        $data = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $this->db->escape($query) . "'");

        return $data->rows;
    }

    public function getSeoUrlsByQueryId(int $seo_url_id, string $query): array {
        $data = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $this->db->escape($query) . "' AND seo_url_id != '" . (int)$seo_url_id . "'");

        return $data->rows;
    }

    public function getSeoUrlsByKeywordId(int $seo_url_id, string $keyword): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `keyword` = '" . $this->db->escape($keyword) . "' AND seo_url_id != '" . (int)$seo_url_id . "'");

        return $query->rows;
    }
}