<?php
/**
 * Class PayPal
 *
 * @package Admin\Model\Extension\Payment
 */
class ModelExtensionPaymentPayPal extends Model {
	/**
	 * Get Total Sales
	 *
	 * @return int
	 */
	public function getTotalSales(): int {
		$implode = [];

		foreach ((array)$this->config->get('config_complete_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}

		$query = $this->db->query("SELECT SUM(`total`) AS `total` FROM `" . DB_PREFIX . "order` WHERE `order_status_id` IN(" . implode(',', $implode) . ") AND `payment_code` = 'paypal'");

		if ($query->num_rows) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	/**
	 * Get Total Sales By Day
	 *
	 * @return array<int, array<string, int>>
	 */
	public function getTotalSalesByDay(): array {
		$implode = [];

		foreach ((array)$this->config->get('config_complete_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}

		$sale_data = [];

		for ($i = 0; $i < 24; $i++) {
			$sale_data[$i] = [
				'hour'         => $i,
				'total'        => 0,
				'paypal_total' => 0
			];
		}

		$query = $this->db->query("SELECT SUM(`total`) AS `total`, SUM(IF (`payment_code` = 'paypal', `total`, 0)) AS `paypal_total`, HOUR(`date_added`) AS `hour` FROM `" . DB_PREFIX . "order` WHERE `order_status_id` IN(" . implode(',', $implode) . ") AND DATE(`date_added`) = DATE(NOW()) GROUP BY HOUR(`date_added`) ORDER BY `date_added` ASC");

		foreach ($query->rows as $result) {
			$sale_data[$result['hour']] = [
				'hour'         => $result['hour'],
				'total'        => $result['total'],
				'paypal_total' => $result['paypal_total']
			];
		}

		return $sale_data;
	}

	/**
	 * Get Total Sales By Week
	 *
	 * @return array<int, array<string, int>>
	 */
	public function getTotalSalesByWeek(): array {
		$implode = [];

		foreach ((array)$this->config->get('config_complete_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}

		$sale_data = [];

		$date_start = strtotime('-' . date('w') . ' days');

		for ($i = 0; $i < 7; $i++) {
			$date = date('Y-m-d', $date_start + ($i * 86400));

			$sale_data[date('w', strtotime($date))] = [
				'day'          => date('D', strtotime($date)),
				'total'        => 0,
				'paypal_total' => 0
			];
		}

		$query = $this->db->query("SELECT SUM(`total`) AS `total`, SUM(IF (`payment_code` = 'paypal', total, 0)) AS `paypal_total`, `date_added` FROM `" . DB_PREFIX . "order` WHERE `order_status_id` IN(" . implode(',', $implode) . ") AND DATE(`date_added`) >= DATE('" . $this->db->escape(date('Y-m-d', $date_start)) . "') GROUP BY DAYNAME(`date_added`)");

		foreach ($query->rows as $result) {
			$sale_data[date('w', strtotime($result['date_added']))] = [
				'day'          => date('D', strtotime($result['date_added'])),
				'total'        => $result['total'],
				'paypal_total' => $result['paypal_total']
			];
		}

		return $sale_data;
	}

	/**
	 * Get Total Sales By Month
	 *
	 * @return array<int, array<string, int>>
	 */
	public function getTotalSalesByMonth(): array {
		$implode = [];

		foreach ((array)$this->config->get('config_complete_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}

		$sale_data = [];

		for ($i = 1; $i <= date('t'); $i++) {
			$date = date('Y') . '-' . date('m') . '-' . $i;

			$sale_data[date('j', strtotime($date))] = [
				'day'          => date('d', strtotime($date)),
				'total'        => 0,
				'paypal_total' => 0
			];
		}

		$query = $this->db->query("SELECT SUM(`total`) AS `total`, SUM(IF (`payment_code` = 'paypal', total, 0)) AS `paypal_total`, `date_added` FROM `" . DB_PREFIX . "order` WHERE `order_status_id` IN(" . implode(',', $implode) . ") AND DATE(`date_added`) >= '" . $this->db->escape(date('Y') . '-' . date('m') . '-1') . "' GROUP BY DATE(`date_added`)");

		foreach ($query->rows as $result) {
			$sale_data[date('j', strtotime($result['date_added']))] = [
				'day'          => date('d', strtotime($result['date_added'])),
				'total'        => $result['total'],
				'paypal_total' => $result['paypal_total']
			];
		}

		return $sale_data;
	}

	/**
	 * Get Total Sales By Year
	 *
	 * @return array<int, array<string, int>>
	 */
	public function getTotalSalesByYear(): array {
		$implode = [];

		foreach ((array)$this->config->get('config_complete_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}

		$sale_data = [];

		for ($i = 1; $i <= 12; $i++) {
			$sale_data[$i] = [
				'month'        => date('M', mktime(0, 0, 0, $i)),
				'total'        => 0,
				'paypal_total' => 0
			];
		}

		$query = $this->db->query("SELECT SUM(`total`) AS `total`, SUM(IF (`payment_code` = 'paypal', `total`, 0)) AS `paypal_total`, `date_added` FROM `" . DB_PREFIX . "order` WHERE `order_status_id` IN(" . implode(',', $implode) . ") AND YEAR(`date_added`) = YEAR(NOW()) GROUP BY MONTH(`date_added`)");

		foreach ($query->rows as $result) {
			$sale_data[date('n', strtotime($result['date_added']))] = [
				'month'        => date('M', strtotime($result['date_added'])),
				'total'        => $result['total'],
				'paypal_total' => $result['paypal_total']
			];
		}

		return $sale_data;
	}

	/**
	 * Get Country By Code
	 *
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 */
	public function getCountryByCode(string $code): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE `iso_code_2` = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	/**
	 * Edit Paypal Order
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function editPayPalOrder(array $data): void {
		$sql = "UPDATE `" . DB_PREFIX . "paypal_checkout_integration_order` SET";

		$implode = [];

		if (!empty($data['transaction_id'])) {
			$implode[] = "`transaction_id` = '" . $this->db->escape($data['transaction_id']) . "'";
		}

		if (!empty($data['transaction_status'])) {
			$implode[] = "`transaction_status` = '" . $this->db->escape($data['transaction_status']) . "'";
		}

		if (!empty($data['payment_method'])) {
			$implode[] = "`payment_method` = '" . $this->db->escape($data['payment_method']) . "'";
		}

		if (!empty($data['vault_id'])) {
			$implode[] = "`vault_id` = '" . $this->db->escape($data['vault_id']) . "'";
		}

		if (!empty($data['vault_customer_id'])) {
			$implode[] = "`vault_customer_id` = '" . $this->db->escape($data['vault_customer_id']) . "'";
		}

		if (!empty($data['environment'])) {
			$implode[] = "`environment` = '" . $this->db->escape($data['environment']) . "'";
		}

		if ($implode) {
			$sql .= implode(", ", $implode);
		}

		$sql .= " WHERE `order_id` = '" . (int)$data['order_id'] . "'";

		$this->db->query($sql);
	}

	/**
	 * Delete PayPal Order
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function deletePayPalOrder(int $order_id): void {
		$query = $this->db->query("DELETE FROM `" . DB_PREFIX . "paypal_checkout_integration_order` WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Get PayPal Order
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getPayPalOrder(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_checkout_integration_order` WHERE `order_id` = '" . (int)$order_id . "'");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return [];
		}
	}

	/**
	 * Get PayPal Order Subscription
	 *
	 * @param int $order_id
	 *
	 * @return array<string, mixed>
	 */
	public function getPayPalOrderSubscription(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_checkout_integration_subscription` WHERE `order_id` = '" . (int)$order_id . "'");

		return $query->row;
	}

	/**
	 * Edit Order Subscription Status
	 *
	 * @param int $order_id
	 * @param int $status
	 *
	 * @return void
	 */
	public function editOrderSubscriptionStatus(int $order_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "paypal_checkout_integration_order` SET `status` = '" . (int)$status . "' WHERE `order_id` = '" . (int)$order_id . "'");
	}

	/**
	 * Set Agree Status
	 *
	 * @return void
	 */
	public function setAgreeStatus(): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "country` SET `status` = '0' WHERE (`iso_code_2` = 'CU' OR `iso_code_2` = 'IR' OR `iso_code_2` = 'SY' OR `iso_code_2` = 'KP')");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `status` = '0' WHERE `country_id` = '220' AND (`code` = '43' OR `code` = '14' OR `code` = '09')");
	}

	/**
	 * Get Agree Status
	 *
	 * @return bool
	 */
	public function getAgreeStatus(): bool {
		$agree_status = true;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE `status` = '1' AND (`iso_code_2` = 'CU' OR `iso_code_2` = 'IR' OR `iso_code_2` = 'SY' OR `iso_code_2` = 'KP')");

		if ($query->rows) {
			$agree_status = false;
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `country_id` = '220' AND `status` = '1' AND (`code` = '43' OR `code` = '14' OR `code` = '09')");

		if ($query->rows) {
			$agree_status = false;
		}

		return $agree_status;
	}

	/**
	 * Check Version
	 *
	 * @param string $opencart_version
	 * @param string $paypal_version
	 *
	 * @return array
	 */
	public function checkVersion(string $opencart_version, string $paypal_version): array {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, 'https://www.opencart.com/index.php?route=api/promotion/paypalCheckoutIntegration&opencart=' . $opencart_version . '&paypal=' . $paypal_version);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

		$response = curl_exec($curl);

		curl_close($curl);

		$result = json_decode($response, true);

		if ($result) {
			return $result;
		} else {
			return [];
		}
	}

	/**
	 * Send Contact
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function sendContact(array $data): void {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8');
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

		$response = curl_exec($curl);

		curl_close($curl);
	}

	/**
	 * Log
	 *
	 * @param array<string, mixed> $data
	 * @param string               $title
	 *
	 * @return void
	 */
	public function log(array $data, ?string $title = null): void {
		$_config = new \Config();
		$_config->load('paypal');

		$config_setting = $_config->get('paypal_setting');

		$setting = array_replace_recursive((array)$config_setting, (array)$this->config->get('payment_paypal_setting'));

		if ($setting['general']['debug']) {
			$log = new \Log('paypal.log');
			$log->write('PayPal debug (' . $title . '): ' . json_encode($data));
		}
	}

	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paypal_checkout_integration_order` (
			`order_id` int(11) NOT NULL,
			`transaction_id` varchar(20) NOT NULL,
			`transaction_status` varchar(20) NULL,
			`payment_method` varchar(20) NULL,
			`vault_id` varchar(50) NULL,
			`vault_customer_id` varchar(50) NULL,
			`environment` varchar(20) NULL,
			`status` tinyint(1) NOT NULL,
			PRIMARY KEY (`order_id`, `transaction_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paypal_checkout_integration_subscription` (
			`paypal_checkout_integration_subscription_id` int(11) NOT NULL AUTO_INCREMENT,
			`subscription_id` int(11) NOT NULL,
			`order_id` int(11) NOT NULL,
			`next_payment` datetime NOT NULL,
			`trial_end` datetime DEFAULT NULL,
			`subscription_end` datetime DEFAULT NULL,
			`currency_code` varchar(3) NOT NULL,
			`total` decimal(15,4) NOT NULL,
			`payment_code` varchar(128) NOT NULL,
			`date_added` datetime NOT NULL,
			`date_modified` datetime NOT NULL,
			PRIMARY KEY (`paypal_checkout_integration_subscription_id`),
			KEY (`order_id`),
			KEY (`subscription_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paypal_checkout_integration_transaction` (
			  `paypal_checkout_integration_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `reference` varchar(255) NOT NULL,
			  `type` tinyint(1) NOT NULL,
			  `amount` decimal(15,4) NOT NULL,
			  `date_added` datetime NOT NULL,
			  PRIMARY KEY (`paypal_checkout_integration_transaction_id`),
			  KEY (`order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

		$this->load->model('setting/setting');

		// Setting
		$_config = new \Config();
		$_config->load('paypal');

		$config_setting = $_config->get('paypal_setting');

		$this->model_setting_setting->deleteSetting('paypal_version');

		$this->model_setting_setting->editSetting($config_setting['version']);

		// Events
		$this->load->model('setting/event');

		$this->model_setting_event->addEvent('paypal_order_info', 'admin/view/sale/order_info/before', 'extension/payment/paypal/order_info_before', 1, 0);
		$this->model_setting_event->addEvent('paypal_header', 'catalog/controller/common/header/before', 'extension/payment/paypal/header_before', 1, 0);
		$this->model_setting_event->addEvent('paypal_extension_get_extensions', 'paypal_extension_extensions', 'catalog/model/setting/extension/getExtensions/after', 'extension/payment/paypal/extension_get_extensions_after', 1, 0);
		$this->model_setting_event->addEvent('paypal_order_delete_order', 'catalog/model/checkout/order/deleteOrder/before', 'extension/payment/paypal/order_delete_order_before', 1, 0);
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		// Events
		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('paypal_order_info');
		$this->model_setting_event->deleteEventByCode('paypal_header');
		$this->model_setting_event->deleteEventByCode('paypal_extension_get_extensions');
		$this->model_setting_event->deleteEventByCode('paypal_order_delete_order');
		$this->model_setting_event->deleteEventByCode('paypal_version');
	}
}
