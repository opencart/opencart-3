<?php
namespace Cart;
class Customer {
	private int $customer_id = 0;
	private string $firstname = '';
	private string $lastname = '';
	private int $customer_group_id = 0;
	private string $email = '';
	private string $telephone = '';
	private bool $newsletter = false;

	public function __construct(object $registry) {
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

		if (isset($this->session->data['customer_id'])) {
			$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$this->session->data['customer_id'] . "' AND status = '1'");

			if ($customer_query->num_rows) {
				$this->customer_id = $customer_query->row['customer_id'];
				$this->firstname = $customer_query->row['firstname'];
				$this->lastname = $customer_query->row['lastname'];
				$this->customer_group_id = $customer_query->row['customer_group_id'];
				$this->email = $customer_query->row['email'];
				$this->telephone = $customer_query->row['telephone'];
				$this->newsletter = $customer_query->row['newsletter'];
				$this->address_id = $customer_query->row['address_id'];

				$this->db->query("UPDATE " . DB_PREFIX . "customer SET language_id = '" . (int)$this->config->get('config_language_id') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$this->session->data['customer_id'] . "' AND ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "customer_ip SET customer_id = '" . (int)$this->session->data['customer_id'] . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', date_added = NOW()");
				}
			} else {
				$this->logout();
			}
		}
	}

  public function login(string $email, string $password, bool $override = false): bool {
		if ($override) {
			$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND status = '1'");
		} else {
			$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1'");
		}

		if ($customer_query->num_rows) {
			$this->session->data['customer_id'] = $customer_query->row['customer_id'];

			$this->customer_id = $customer_query->row['customer_id'];
			$this->firstname = $customer_query->row['firstname'];
			$this->lastname = $customer_query->row['lastname'];
			$this->customer_group_id = $customer_query->row['customer_group_id'];
			$this->email = $customer_query->row['email'];
			$this->telephone = $customer_query->row['telephone'];
			$this->newsletter = $customer_query->row['newsletter'];
			$this->address_id = $customer_query->row['address_id'];
		
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET language_id = '" . (int)$this->config->get('config_language_id') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

			return true;
		} else {
			return false;
		}
	}

	public function logout(): void {
		unset($this->session->data['customer_id']);

		$this->customer_id = 0;
		$this->firstname = '';
		$this->lastname = '';
		$this->customer_group_id = 0;
		$this->email = '';
		$this->telephone = '';
		$this->newsletter = false;
	}

	public function isLogged(): bool {
		return $this->customer_id ? true : false;
	}

	public function getId(): int {
		return $this->customer_id;
	}

	public function getFirstName(): string {
		return $this->firstname;
	}

	public function getLastName(): string {
		return $this->lastname;
	}

	public function getGroupId(): int {
		return $this->customer_group_id;
	}

	public function getEmail(): string {
		return $this->email;
	}

	public function getTelephone(): string {
		return $this->telephone;
	}

	public function getNewsletter(): bool {
		return $this->newsletter;
	}

	public function getAddressId(): int {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "address` WHERE `customer_id` = '" . (int)$this->customer_id . "' AND `default` = '1'");

		if ($query->num_rows) {
			return (int)$query->row['address_id'];
		} else {
			return 0;
		}
	}

	public function getBalance(): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "customer_transaction` WHERE `customer_id` = '" . (int)$this->customer_id . "'");

		return (float)$query->row['total'];
	}

	public function getRewardPoints(): float {
		$query = $this->db->query("SELECT SUM(`points`) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE `customer_id` = '" . (int)$this->customer_id . "'");

		return (float)$query->row['total'];
	}
}
