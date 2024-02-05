<?php
/**
 * Class Divido
 *
 * @package Catalog\Controller\Extension\Payment
 */
class ControllerExtensionPaymentDivido extends Controller {
	public const STATUS_ACCEPTED = 'ACCEPTED', STATUS_ACTION_LENDER = 'ACTION-LENDER', STATUS_CANCELED = 'CANCELED', STATUS_COMPLETED = 'COMPLETED', STATUS_DEPOSIT_PAID = 'DEPOSIT-PAID', STATUS_DECLINED = 'DECLINED', STATUS_DEFERRED = 'DEFERRED', STATUS_REFERRED = 'REFERRED', STATUS_FULFILLED = 'FULFILLED', STATUS_SIGNED = 'SIGNED';

	/**
	 * @var array
	 */
	private array $status_id = [
		self::STATUS_ACCEPTED      => 1,
		self::STATUS_ACTION_LENDER => 2,
		self::STATUS_CANCELED      => 0,
		self::STATUS_COMPLETED     => 2,
		self::STATUS_DECLINED      => 8,
		self::STATUS_DEFERRED      => 1,
		self::STATUS_REFERRED      => 1,
		self::STATUS_DEPOSIT_PAID  => 1,
		self::STATUS_FULFILLED     => 1,
		self::STATUS_SIGNED        => 2,
	];
	/**
	 * @var array
	 */
	private array $history_messages = [
		self::STATUS_ACCEPTED      => 'Credit request accepted',
		self::STATUS_ACTION_LENDER => 'Lender notified',
		self::STATUS_CANCELED      => 'Credit request canceled',
		self::STATUS_COMPLETED     => 'Credit application completed',
		self::STATUS_DECLINED      => 'Credit request declined',
		self::STATUS_DEFERRED      => 'Credit request deferred',
		self::STATUS_REFERRED      => 'Credit request referred',
		self::STATUS_DEPOSIT_PAID  => 'Deposit paid',
		self::STATUS_FULFILLED     => 'Credit request fulfilled',
		self::STATUS_SIGNED        => 'Contract signed',
	];

	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$this->load->language('extension/payment/divido');

		// Orders
		$this->load->model('checkout/order');

		// Divido
		$this->load->model('extension/payment/divido');

		$api_key = $this->config->get('payment_divido_api_key');

		$key_parts = explode('.', $api_key);
		$js_key = strtolower(array_shift($key_parts));

		[$total, $totals] = $this->model_extension_payment_divido->getTotals();

		$this->model_extension_payment_divido->setMerchant($this->config->get('payment_divido_api_key'));

		$plans = $this->model_extension_payment_divido->getCartPlans($this->cart);

		foreach ($plans as $key => $plan) {
			$planMinTotal = $total - ($total * ($plan->min_deposit / 100));

			if ($plan->min_amount > $planMinTotal) {
				unset($plans[$key]);
			}
		}

		$plans_ids = array_map(fn ($plan) => $plan->id, $plans);

		$plans_ids = array_unique($plans_ids);
		$plans_list = implode(',', $plans_ids);

		$data = [
			'button_confirm'           => $this->language->get('divido_checkout'),
			'merchant_script'          => "//cdn.divido.com/calculator/{$js_key}.js",
			'grand_total'              => $total,
			'plan_list'                => $plans_list,
			'generic_credit_req_error' => 'Credit request could not be initiated',
		];

		return $this->load->view('extension/payment/divido', $data);
	}

	/**
	 * Update
	 *
	 * @return string
	 */
	public function update(): string {
		$this->load->language('extension/payment/divido');

		// Orders
		$this->load->model('checkout/order');

		// Divido
		$this->load->model('extension/payment/divido');

		$post_data = json_decode(file_get_contents('php://input'), true);

		if (!isset($post_data['status'])) {
			$this->response->setOutput('');

			return '';
		}

		$order_id = (int)$post_data['metadata']['order_id'];

		$lookup = $this->model_extension_payment_divido->getLookupByOrderId($order_id);

		if (!$lookup) {
			$this->response->setOutput('');

			return '';
		}

		$hash = $this->model_extension_payment_divido->hashOrderId($order_id, $lookup['salt']);

		$order_hash = (string)$post_data['metadata']['order_hash'];

		if ($hash !== $order_hash) {
			$this->response->setOutput('');

			return '';
		}

		$order_info = $this->model_checkout_order->getOrder($order_id);
		$order_status_id = $order_info['order_status_id'];

		$status = (string)$post_data['status'];

		$message = 'Status: {$status}';

		if (isset($this->history_messages[$status])) {
			$message = $this->history_messages[$status];
		}

		if ($status == self::STATUS_SIGNED) {
			$status_override = $this->config->get('payment_divido_order_status_id');

			if (!empty($status_override)) {
				$this->status_id[self::STATUS_SIGNED] = $status_override;
			}
		}

		if (isset($this->status_id[$status]) && $this->status_id[$status] > $order_status_id) {
			$order_status_id = $this->status_id[$status];
		}

		if ($status == self::STATUS_DECLINED && $order_info['order_status_id'] == 0) {
			$order_status_id = 0;
		}

		$application = (string)$post_data['application'];

		$this->model_extension_payment_divido->saveLookup($order_id, $lookup['salt'], null, $application);

		$this->model_checkout_order->addHistory($order_id, $order_status_id, $message, false);

		return 'OK';
	}

	/**
	 * Confirm
	 *
	 * @return void
	 */
	public function confirm(): void {
		$this->load->language('extension/payment/divido');

		// Divido
		$this->load->model('extension/payment/divido');

		ini_set('html_errors', 0);

		if (!$this->session->data['payment_method']['code'] == 'divido') {
			return;
		}

		$this->model_extension_payment_divido->setMerchant($this->config->get('payment_divido_api_key'));

		$api_key = $this->config->get('payment_divido_api_key');

		$deposit = $this->request->post['deposit'];
		$finance = $this->request->post['finance'];

		$address = $this->session->data['payment_address'];

		if (isset($this->session->data['shipping_address'])) {
			$address = $this->session->data['shipping_address'];
		}

		$country = $address['iso_code_2'];

		$language = strtoupper($this->language->get('code'));

		$currency = strtoupper($this->session->data['currency']);
		$order_id = (int)$this->session->data['order_id'];

		$firstname = '';
		$lastname = '';
		$email = '';
		$telephone = '';

		if ($this->customer->isLogged()) {
			// Customers
			$this->load->model('account/customer');

			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

			$firstname = $customer_info['firstname'];
			$lastname = $customer_info['lastname'];
			$email = $customer_info['email'];
			$telephone = $customer_info['telephone'];
		} elseif (isset($this->session->data['guest'])) {
			$firstname = $this->session->data['guest']['firstname'];
			$lastname = $this->session->data['guest']['lastname'];
			$email = $this->session->data['guest']['email'];
			$telephone = $this->session->data['guest']['telephone'];
		}

		$postcode = $address['postcode'];

		$products = [];

		foreach ($this->cart->getProducts() as $product) {
			$products[] = [
				'type'     => 'product',
				'text'     => $product['name'],
				'quantity' => $product['quantity'],
				'value'    => $product['price'],
			];
		}

		[
			$total,
			$totals
		] = $this->model_extension_payment_divido->getTotals();

		$sub_total = $total;

		$cart_total = $this->cart->getSubTotal();

		$shiphandle = $sub_total - $cart_total;

		$products[] = [
			'type'     => 'product',
			'text'     => 'Shipping & Handling',
			'quantity' => 1,
			'value'    => $shiphandle,
		];

		$deposit_amount = round(($deposit / 100) * $total, 2, PHP_ROUND_HALF_UP);
		$shop_url = $this->config->get('config_url');

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$shop_url = $this->config->get('config_ssl');
		}

		$callback_url = $this->url->link('extension/payment/divido/update', '', true);
		$return_url = $this->url->link('checkout/success', '', true);
		$checkout_url = $this->url->link('checkout/checkout', '', true);
		$salt = uniqid('', true);

		$hash = $this->model_extension_payment_divido->hashOrderId($order_id, $salt);

		$request_data = [
			'merchant' => $api_key,
			'deposit'  => $deposit_amount,
			'finance'  => $finance,
			'country'  => $country,
			'language' => $language,
			'currency' => $currency,
			'metadata' => [
				'order_id'   => $order_id,
				'order_hash' => $hash,
			],
			'customer' => [
				'title'         => '',
				'first_name'    => $firstname,
				'middle_name'   => '',
				'last_name'     => $lastname,
				'country'       => $country,
				'postcode'      => $postcode,
				'email'         => $email,
				'mobile_number' => '',
				'phone_number'  => $telephone,
			],
			'products'     => $products,
			'response_url' => $callback_url,
			'redirect_url' => $return_url,
			'checkout_url' => $checkout_url,
		];

		$response = Divido_CreditRequest::create($request_data);
		$response = (array)$response;

		$status = (string)$response['status'];
		$proposal_id = (string)$response['id'];
		$response_url = $response['url'];

		if ($status == 'ok') {
			$this->model_extension_payment_divido->saveLookup($order_id, $salt, $proposal_id, null, $deposit_amount);

			$data = [
				'status' => 'ok',
				'url'    => $response_url,
			];
		} else {
			$error = (string)$response['error'];

			$data = [
				'status'  => 'error',
				'message' => $this->language->get($error),
			];
		}

		$this->response->setOutput(json_encode($data));
	}

	/**
	 * Calculator
	 *
	 * @param array<string, mixed> $args
	 *
	 * @return string
	 */
	public function calculator(array $args): string {
		$this->load->language('extension/payment/divido');

		// Divido
		$this->load->model('extension/payment/divido');

		if (!$this->model_extension_payment_divido->isEnabled()) {
			return '';
		}

		$this->model_extension_payment_divido->setMerchant($this->config->get('payment_divido_api_key'));

		$product_selection = $this->config->get('payment_divido_productselection');
		$price_threshold = $this->config->get('payment_divido_price_threshold');

		$product_id = $args['product_id'];
		$product_price = $args['price'];
		$type = $args['type'];

		if ($product_selection == 'threshold' && $product_price < $price_threshold) {
			return '';
		}

		$plans = $this->model_extension_payment_divido->getProductPlans($product_id);

		if (!$plans) {
			return '';
		}

		$plans_ids = array_map(fn ($plan) => $plan->id, $plans);
		$plan_list = implode(',', $plans_ids);

		$data = [
			'planList'     => $plan_list,
			'productPrice' => $product_price
		];

		$filename = ($type == 'full') ? 'extension/payment/divido_calculator' : 'extension/payment/divido_widget';

		return $this->load->view($filename, $data);
	}
}
