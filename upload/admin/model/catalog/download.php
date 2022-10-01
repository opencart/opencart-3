<?php
class ModelCatalogDownload extends Model {
    public function addDownload(array $data): int {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "download` SET `filename` = '" . $this->db->escape($data['filename']) . "', `mask` = '" . $this->db->escape($data['mask']) . "', `date_added` = NOW()");

        $download_id = $this->db->getLastId();

        foreach ($data['download_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "download_description` SET `download_id` = '" . (int)$download_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
        }

        return $download_id;
    }

    public function editDownload(int $download_id, array $data): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "download` SET `filename` = '" . $this->db->escape($data['filename']) . "', `mask` = '" . $this->db->escape($data['mask']) . "' WHERE `download_id` = '" . (int)$download_id . "'");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "download_description` WHERE `download_id` = '" . (int)$download_id . "'");

        foreach ($data['download_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "download_description` SET `download_id` = '" . (int)$download_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'");
        }
    }

    public function deleteDownload(int $download_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "download` WHERE `download_id` = '" . (int)$download_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "download_description` WHERE `download_id` = '" . (int)$download_id . "'");
    }

    public function getDownload(int $download_id): array {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "download` d LEFT JOIN `" . DB_PREFIX . "download_description` dd ON (d.`download_id` = dd.`download_id`) WHERE d.`download_id` = '" . (int)$download_id . "' AND dd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getDownloads(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "download` d LEFT JOIN `" . DB_PREFIX . "download_description` dd ON (d.`download_id` = dd.`download_id`) WHERE dd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND dd.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = [
            'dd.name',
            'd.date_added'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY dd.`name`";
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

    public function getDownloadDescriptions(int $download_id): array {
        $download_description_data = [];

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "download_description` WHERE `download_id` = '" . (int)$download_id . "'");

        foreach ($query->rows as $result) {
            $download_description_data[$result['language_id']] = ['name' => $result['name']];
        }

        return $download_description_data;
    }

    public function getTotalDownloads(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "download`");

        return (int)$query->row['total'];
    }
}