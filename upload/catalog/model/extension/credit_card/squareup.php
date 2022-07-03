<?php
class ModelExtensionCreditCardSquareup extends Model {
    public function addCustomer(array $data): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "squareup_customer` SET `customer_id` = '" . (int)$data['customer_id'] . "', `sandbox` = '" . (int)$data['sandbox'] . "', `square_customer_id` = '" . $this->db->escape($data['square_customer_id']) . "'");
    }

    public function getCustomer(int $customer_id, int $sandbox): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_customer` WHERE `customer_id` = '" . (int)$customer_id . "' AND `sandbox` = '" . (int)$sandbox . "'");
		
		return $query->row;
    }

    public function addCard(int $customer_id, int $sandbox, array $data): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "squareup_token` SET `customer_id` = '" . (int)$customer_id . "', `sandbox` = '" . (int)$sandbox . "', `token` = '" . $this->db->escape($data['id']) . "', `brand` = '" . $this->db->escape($data['card_brand']) . "', `ends_in` = '" . (int)$data['last_4'] . "', `date_added` = NOW()");
    }

    public function getCard(int $squareup_token_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_token` WHERE `squareup_token_id` = '" . (int)$squareup_token_id . "'");
		
		return $query->row;
    }

    public function getCards(int $customer_id, int $sandbox): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_token` WHERE `customer_id` = '" . (int)$customer_id . "' AND `sandbox` = '" . (int)$sandbox . "'");
		
		return $query->rows;
    }

    public function cardExists(int $customer_id, array $data): bool {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_token` WHERE `customer_id` = '" . (int)$customer_id . "' AND `brand` = '" . $this->db->escape($data['card_brand']) . "' AND `ends_in` = '" . (int)$data['last_4'] . "'");
		
		if ($query->num_rows) {
			return true;
		} else {
			return false;
		}
    }

    public function verifyCardCustomer(int $squareup_token_id, int $customer_id): bool {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_token` WHERE `squareup_token_id` = '" . (int)$squareup_token_id . "' AND `customer_id` = '" . (int)$customer_id . "'");
		
		if ($query->num_rows) {
			return true;
		} else {
			return false;
		}
    }

    public function deleteCard(int $squareup_token_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "squareup_token` WHERE `squareup_token_id` = '" . (int)$squareup_token_id . "'");
    }
}