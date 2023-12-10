<?php
/**
 * Class Payment Method
 *
 * @package Catalog\Model\Account
 */
class ModelAccountPaymentMethod extends Model {
	/**
	 * addPaymentMethod
	 *
	 * @param array $data
	 *
	 * @return void
	 */
    public function addPaymentMethod(array $data): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_payment` SET `customer_id` = '" . (int)$this->customer->getId() . "', `name` = '" . (int)$this->customer->getId() . "', `image` = '" . $this->db->escape($data['image']) . "', `type` = '" . $this->db->escape($data['type']) . "', `code` = '" . $this->db->escape($data['code']) . "', `token` = '" . $this->db->escape($data['token']) . "', `date_expire` = '" . $this->db->escape($data['date_expire']) . "', `default` = '" . (bool)$data['default'] . "', `status` = '1', `date_added` = NOW()");
    }

	/**
	 * deletePaymentMethod
	 *
	 * @param int $customer_payment_id
	 *
	 * @return void
	 */
    public function deletePaymentMethod(int $customer_payment_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_payment` WHERE `customer_id` = '" . (int)$this->customer->getId() . "' AND `customer_payment_id` = '" . (int)$customer_payment_id . "'");
    }

	/**
	 * getPaymentMethod
	 *
	 * @param int $customer_id
	 * @param int $customer_payment_id
	 *
	 * @return array
	 */
    public function getPaymentMethod(int $customer_id, int $customer_payment_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_payment` WHERE `customer_id` = '" . (int)$customer_id . "' AND `customer_payment_id` = '" . (int)$customer_payment_id . "'");

        return $query->row;
    }

	/**
	 * getPaymentMethods
	 *
	 * @param int $customer_id
	 *
	 * @return array
	 */
    public function getPaymentMethods(int $customer_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_payment` WHERE `customer_id` = '" . (int)$customer_id . "'");

        return $query->rows;
    }

	/**
	 * getTotalPaymentMethods
	 *
	 * @return int
	 */
    public function getTotalPaymentMethods(): int {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_payment` WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");

        return (int)$query->row['total'];
    }
}
