<?php
/**
 * Class Address
 *
 * @example $address_model = $this->model_account_address;
 *
 * Can be called from $this->load->model('account/address');
 *
 * @package Catalog\Model\Account
 */
class ModelAccountAddress extends Model {
	/**
	 * Add Address
	 *
	 * @param int                  $customer_id primary key of the customer record
	 * @param array<string, mixed> $data        array of data
	 *
	 * @return int returns the primary key of the new address record
	 */
	public function addAddress(int $customer_id, array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "address` SET `customer_id` = '" . (int)$customer_id . "', `firstname` = '" . $this->db->escape($data['firstname']) . "', `lastname` = '" . $this->db->escape($data['lastname']) . "', `company` = '" . $this->db->escape($data['company']) . "', `address_1` = '" . $this->db->escape($data['address_1']) . "', `address_2` = '" . $this->db->escape($data['address_2']) . "', `postcode` = '" . $this->db->escape($data['postcode']) . "', `city` = '" . $this->db->escape($data['city']) . "', `zone_id` = '" . (int)$data['zone_id'] . "', `country_id` = '" . (int)$data['country_id'] . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', `default` = '" . (isset($data['default']) ? (int)$data['default'] : 0) . "'");

		$address_id = $this->db->getLastId();

		if (!empty($data['default'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "address` SET `default` = '0' WHERE `address_id` != '" . (int)$address_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "'");
		}

		return $address_id;
	}

	/**
	 * Edit Address
	 *
	 * @param int                  $address_id primary key of the address record
	 * @param array<string, mixed> $data       array of data
	 *
	 * @return void
	 */
	public function editAddress(int $address_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "address` SET `firstname` = '" . $this->db->escape($data['firstname']) . "', `lastname` = '" . $this->db->escape($data['lastname']) . "', `company` = '" . $this->db->escape($data['company']) . "', `address_1` = '" . $this->db->escape($data['address_1']) . "', `address_2` = '" . $this->db->escape($data['address_2']) . "', `postcode` = '" . $this->db->escape($data['postcode']) . "', `city` = '" . $this->db->escape($data['city']) . "', `zone_id` = '" . (int)$data['zone_id'] . "', `country_id` = '" . (int)$data['country_id'] . "', `custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', `default` = '" . (isset($data['default']) ? (int)$data['default'] : 0) . "' WHERE `address_id` = '" . (int)$address_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "'");

		if (!empty($data['default'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "address` SET `default` = '0' WHERE `address_id` != '" . (int)$address_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "'");
		}
	}

	/**
	 * Delete Address
	 *
	 * @param int $address_id primary key of the address record
	 *
	 * @return void
	 */
	public function deleteAddress(int $address_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "address` WHERE `address_id` = '" . (int)$address_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "'");

		$default_query = $this->db->query("SELECT `address_id` FROM `" . DB_PREFIX . "customer` WHERE `address_id` = '" . (int)$address_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "'");

		if ($default_query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `address_id` = '0' WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");
		}
	}

	/**
	 * Get Address
	 *
	 * @param int $address_id primary key of the address record
	 *
	 * @return array<string, mixed> address record that has address ID
	 */
	public function getAddress(int $address_id): array {
		$address_query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "address` WHERE `address_id` = '" . (int)$address_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "'");

		if ($address_query->num_rows) {
			// Countries
			$this->load->model('localisation/country');

			$country_info = $this->model_localisation_country->getCountry($address_query->row['country_id']);

			if ($country_info) {
				$country = $country_info['name'];
				$iso_code_2 = $country_info['iso_code_2'];
				$iso_code_3 = $country_info['iso_code_3'];
				$address_format = $country_info['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			// Zones
			$this->load->model('localisation/zone');

			$zone_info = $this->model_localisation_zone->getZone($address_query->row['zone_id']);

			if ($zone_info) {
				$zone = $zone_info['name'];
				$zone_code = $zone_info['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			$find = [
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			];

			$replace = [
				'firstname' => $address_query->row['firstname'],
				'lastname'  => $address_query->row['lastname'],
				'company'   => $address_query->row['company'],
				'address_1' => $address_query->row['address_1'],
				'address_2' => $address_query->row['address_2'],
				'city'      => $address_query->row['city'],
				'postcode'  => $address_query->row['postcode'],
				'zone'      => $zone,
				'zone_code' => $zone_code,
				'country'   => $country
			];

			$address_format = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\\s\\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $address_format))));

			return [
				'address_id'     => $address_query->row['address_id'],
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'custom_field'   => json_decode($address_query->row['custom_field'], true),
				'default'        => $address_query->row['default']
			];
		} else {
			return [];
		}
	}

	/**
	 * Get Addresses
	 *
	 * @return array<int, array<string, mixed>> address records
	 */
	public function getAddresses(): array {
		$address_data = [];

		$query = $this->db->query("SELECT `address_id` FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");

		foreach ($query->rows as $result) {
			$address_info = $this->getAddress($result['address_id']);

			if ($address_info) {
				$address_data[$result['address_id']] = $address_info;
			}
		}

		return $address_data;
	}

	/**
	 * Get Total Addresses
	 *
	 * @return int total number of address records
	 */
	public function getTotalAddresses(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");

		return (int)$query->row['total'];
	}
}
