<?php
class Squareup {
	/**
	 * @var object
	 */
	private object $session;
	/**
	 * @var object
	 */
	private object $url;
	/**
	 * @var object
	 */
	private object $config;
	/**
	 * @var object
	 */
	private object $log;
	/**
	 * @var object
	 */
	private object $customer;
	/**
	 * @var object
	 */
	private object $currency;
	/**
	 * @var object
	 */
	private object $registry;
	/**
	 * @var string
	 */
	public const API_URL = 'https://connect.squareup.com';
	/**
	 * @var string
	 */
	public const API_VERSION = 'v2';
	/**
	 * @var string
	 */
	public const ENDPOINT_ADD_CARD = 'customers/%s/cards';
	/**
	 * @var string
	 */
	public const ENDPOINT_AUTH = 'oauth2/authorize';
	/**
	 * @var string
	 */
	public const ENDPOINT_CAPTURE_TRANSACTION = 'locations/%s/transactions/%s/capture';
	/**
	 * @var string
	 */
	public const ENDPOINT_CUSTOMERS = 'customers';
	/**
	 * @var string
	 */
	public const ENDPOINT_DELETE_CARD = 'customers/%s/cards/%s';
	/**
	 * @var string
	 */
	public const ENDPOINT_GET_TRANSACTION = 'locations/%s/transactions/%s';
	/**
	 * @var string
	 */
	public const ENDPOINT_LOCATIONS = 'locations';
	/**
	 * @var string
	 */
	public const ENDPOINT_REFRESH_TOKEN = 'oauth2/clients/%s/access-token/renew';
	/**
	 * @var string
	 */
	public const ENDPOINT_REFUND_TRANSACTION = 'locations/%s/transactions/%s/refund';
	/**
	 * @var string
	 */
	public const ENDPOINT_TOKEN = 'oauth2/token';
	/**
	 * @var string
	 */
	public const ENDPOINT_TRANSACTIONS = 'locations/%s/transactions';
	/**
	 * @var string
	 */
	public const ENDPOINT_VOID_TRANSACTION = 'locations/%s/transactions/%s/void';
	/**
	 * @var string
	 */
	public const PAYMENT_FORM_URL = 'https://js.squareup.com/v2/paymentform';
	/**
	 * @var string
	 */
	public const SCOPE = 'MERCHANT_PROFILE_READ PAYMENTS_READ SETTLEMENTS_READ CUSTOMERS_READ CUSTOMERS_WRITE';
	/**
	 * @var string
	 */
	public const VIEW_TRANSACTION_URL = 'https://squareup.com/dashboard/sales/transactions/%s/by-unit/%s';
	/**
	 * @var string
	 */
	public const SQUARE_INTEGRATION_ID = 'sqi_65a5ac54459940e3600a8561829fd970';

	/**
	 * Constructor
	 *
	 * @property Registry $registry
	 *
	 * @param object $registry
	 */
	public function __construct(object $registry) {
		$this->session = $registry->get('session');
		$this->url = $registry->get('url');
		$this->config = $registry->get('config');
		$this->log = $registry->get('log');
		$this->customer = $registry->get('customer');
		$this->currency = $registry->get('currency');
		$this->registry = $registry;
	}

	/**
	 * Api
	 *
	 * @param array<string, mixed> $request_data
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return mixed
	 */
	public function api(array $request_data) {
		$url = self::API_URL;

		if (empty($request_data['no_version'])) {
			$url .= '/' . self::API_VERSION;
		}

		$url .= '/' . $request_data['endpoint'];

		$curl_options = [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true
		];

		if (!empty($request_data['content_type'])) {
			$content_type = $request_data['content_type'];
		} else {
			$content_type = 'application/json';
		}

		// Handle method and parameters
		if (!empty($request_data['parameters']) && is_array($request_data['parameters']) && $request_data['parameters']) {
			$params = $this->encodeParameters($request_data['parameters'], $content_type);
		} else {
			$params = null;
		}

		switch ($request_data['method']) {
			case 'GET':
				$curl_options[CURLOPT_POST] = false;

				if (is_string($params)) {
					$curl_options[CURLOPT_URL] .= ((!str_contains($url, '?')) ? '?' : '&') . $params;
				}
				break;
			case 'POST':
				$curl_options[CURLOPT_POST] = true;

				if ($params !== null) {
					$curl_options[CURLOPT_POSTFIELDS] = $params;
				}
				break;
			default:
				$curl_options[CURLOPT_CUSTOMREQUEST] = $request_data['method'];

				if ($params !== null) {
					$curl_options[CURLOPT_POSTFIELDS] = $params;
				}
				break;
		}

		// handle headers
		$added_headers = [];

		if (!empty($request_data['auth_type'])) {
			if (empty($request_data['token'])) {
				if ($this->config->get('payment_squareup_enable_sandbox')) {
					$token = $this->config->get('payment_squareup_sandbox_token');
				} else {
					$token = $this->config->get('payment_squareup_access_token');
				}
			} else {
				// custom token trumps sandbox/regular one
				$token = $request_data['token'];
			}

			$added_headers[] = 'Authorization: ' . $request_data['auth_type'] . ' ' . $token;
		}

		if (!is_array($params)) {
			// curl automatically adds Content-Type: multipart/form-data when we provide an array
			$added_headers[] = 'Content-Type: ' . $content_type;
		}

		if (!empty($request_data['headers']) && is_array($request_data['headers'])) {
			$curl_options[CURLOPT_HTTPHEADER] = array_merge($added_headers, $request_data['headers']);
		} else {
			$curl_options[CURLOPT_HTTPHEADER] = $added_headers;
		}

		$this->debug("SQUAREUP DEBUG START...");
		$this->debug("SQUAREUP ENDPOINT: " . $curl_options[CURLOPT_URL]);
		$this->debug("SQUAREUP HEADERS: " . print_r($curl_options[CURLOPT_HTTPHEADER], true));
		$this->debug("SQUAREUP PARAMS: " . $params);

		// Fire off the request
		$ch = curl_init();

		curl_setopt_array($ch, $curl_options);

		$result = curl_exec($ch);

		if ($result) {
			$this->debug("SQUAREUP RESULT: " . $result);

			curl_close($ch);

			$return = json_decode($result, true);

			if (!empty($return['errors'])) {
				throw new \Squareup\Exception($this->registry, $return['errors']);
			} else {
				return $return;
			}
		} else {
			$info = curl_getinfo($ch);

			curl_close($ch);

			throw new \Squareup\Exception($this->registry, "CURL error. Info: " . print_r($info, true), true);
		}
	}

	/**
	 * verifyToken
	 *
	 * @param $access_token
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return object|null
	 */
	public function verifyToken($access_token): ?object {
		try {
			$request_data = [
				'method'    => 'GET',
				'endpoint'  => self::ENDPOINT_LOCATIONS,
				'auth_type' => 'Bearer',
				'token'     => $access_token
			];

			$this->api($request_data);
		} catch (\Squareup\Exception $e) {
			if ($e->isAccessTokenRevoked() || $e->isAccessTokenExpired()) {
				return null;
			}

			// In case some other error occurred
			throw $e;
		}

		return null;
	}

	/**
	 * authLink
	 *
	 * @param $client_id
	 *
	 * @return string
	 */
	public function authLink($client_id): string {
		$state = $this->authState();

		$redirect_uri = str_replace('&amp;', '&', $this->url->link('extension/payment/squareup/oauth_callback', 'user_token=' . $this->session->data['user_token'], true));

		$this->session->data['payment_squareup_oauth_redirect'] = $redirect_uri;

		$params = [
			'client_id'     => $client_id,
			'response_type' => 'code',
			'scope'         => self::SCOPE,
			'locale'        => 'en-US',
			'session'       => 'false',
			'state'         => $state,
			'redirect_uri'  => $redirect_uri
		];

		return self::API_URL . '/' . self::ENDPOINT_AUTH . '?' . http_build_query($params);
	}

	/**
	 * fetchLocations
	 *
	 * @param $access_token
	 * @param $first_location_id
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return array<string, mixed>
	 */
	public function fetchLocations($access_token, &$first_location_id): array {
		$request_data = [
			'method'    => 'GET',
			'endpoint'  => self::ENDPOINT_LOCATIONS,
			'auth_type' => 'Bearer',
			'token'     => $access_token
		];

		$api_result = $this->api($request_data);

		$locations = array_filter($api_result['locations'], [
			$this,
			'filterLocation'
		]);

		if (!empty($locations)) {
			$first_location = current($locations);
			$first_location_id = $first_location['id'];
		} else {
			$first_location_id = null;
		}

		return $locations;
	}

	/**
	 * exchangeCodeForAccessToken
	 *
	 * @param $code
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return mixed
	 */
	public function exchangeCodeForAccessToken($code) {
		$request_data = [
			'method'     => 'POST',
			'endpoint'   => self::ENDPOINT_TOKEN,
			'no_version' => true,
			'parameters' => [
				'client_id'     => $this->config->get('payment_squareup_client_id'),
				'client_secret' => $this->config->get('payment_squareup_client_secret'),
				'redirect_uri'  => $this->session->data['payment_squareup_oauth_redirect'],
				'code'          => $code
			]
		];

		return $this->api($request_data);
	}

	/**
	 * Debug
	 *
	 * @param $text
	 *
	 * @return void
	 */
	public function debug($text): void {
		if ($this->config->get('payment_squareup_debug')) {
			$this->log->write($text);
		}
	}

	/**
	 * refreshToken
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return mixed
	 */
	public function refreshToken() {
		$request_data = [
			'method'     => 'POST',
			'endpoint'   => sprintf(self::ENDPOINT_REFRESH_TOKEN, $this->config->get('payment_squareup_client_id')),
			'no_version' => true,
			'auth_type'  => 'Client',
			'token'      => $this->config->get('payment_squareup_client_secret'),
			'parameters' => [
				'access_token' => $this->config->get('payment_squareup_access_token')
			]
		];

		return $this->api($request_data);
	}

	/**
	 * addCard
	 *
	 * @param $square_customer_id
	 * @param $card_data
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return array<string, mixed>
	 */
	public function addCard($square_customer_id, $card_data): array {
		$request_data = [
			'method'     => 'POST',
			'endpoint'   => sprintf(self::ENDPOINT_ADD_CARD, $square_customer_id),
			'auth_type'  => 'Bearer',
			'parameters' => $card_data
		];

		$result = $this->api($request_data);

		return [
			'id'         => $result['card']['id'],
			'card_brand' => $result['card']['card_brand'],
			'last_4'     => $result['card']['last_4']
		];
	}

	/**
	 * deleteCard
	 *
	 * @param mixed $square_customer_id
	 * @param mixed $card
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return mixed
	 */
	public function deleteCard($square_customer_id, $card) {
		$request_data = [
			'method'    => 'DELETE',
			'endpoint'  => sprintf(self::ENDPOINT_DELETE_CARD, $square_customer_id, $card),
			'auth_type' => 'Bearer'
		];

		return $this->api($request_data);
	}

	/**
	 * addLoggedInCustomer
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return array<string, mixed>
	 */
	public function addLoggedInCustomer(): array {
		$request_data = [
			'method'     => 'POST',
			'endpoint'   => self::ENDPOINT_CUSTOMERS,
			'auth_type'  => 'Bearer',
			'parameters' => [
				'given_name'    => $this->customer->getFirstName(),
				'family_name'   => $this->customer->getLastName(),
				'email_address' => $this->customer->getEmail(),
				'phone_number'  => $this->customer->getTelephone(),
				'reference_id'  => $this->customer->getId()
			]
		];

		$result = $this->api($request_data);

		return [
			'customer_id'        => $this->customer->getId(),
			'sandbox'            => $this->config->get('payment_squareup_enable_sandbox'),
			'square_customer_id' => $result['customer']['id']
		];
	}

	/**
	 * addTransaction
	 *
	 * @param array<string, mixed> $data
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return mixed
	 */
	public function addTransaction(array $data) {
		if ($this->config->get('payment_squareup_enable_sandbox')) {
			$location_id = $this->config->get('payment_squareup_sandbox_location_id');
		} else {
			$location_id = $this->config->get('payment_squareup_location_id');
		}

		$request_data = [
			'method'     => 'POST',
			'endpoint'   => sprintf(self::ENDPOINT_TRANSACTIONS, $location_id),
			'auth_type'  => 'Bearer',
			'parameters' => $data
		];

		$result = $this->api($request_data);

		return $result['transaction'];
	}

	/**
	 * getTransaction
	 *
	 * @param $location_id
	 * @param $transaction_id
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return array<string, mixed>
	 */
	public function getTransaction($location_id, $transaction_id): array {
		$request_data = [
			'method'    => 'GET',
			'endpoint'  => sprintf(self::ENDPOINT_GET_TRANSACTION, $location_id, $transaction_id),
			'auth_type' => 'Bearer'
		];

		$result = $this->api($request_data);

		return $result['transaction'];
	}

	/**
	 * captureTransaction
	 *
	 * @param $location_id
	 * @param $transaction_id
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return array<string, mixed>
	 */
	public function captureTransaction($location_id, $transaction_id): array {
		$request_data = [
			'method'    => 'POST',
			'endpoint'  => sprintf(self::ENDPOINT_CAPTURE_TRANSACTION, $location_id, $transaction_id),
			'auth_type' => 'Bearer'
		];

		$this->api($request_data);

		return $this->getTransaction($location_id, $transaction_id);
	}

	/**
	 * voidTransaction
	 *
	 * @param $location_id
	 * @param $transaction_id
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return array<string, mixed>
	 */
	public function voidTransaction($location_id, $transaction_id): array {
		$request_data = [
			'method'    => 'POST',
			'endpoint'  => sprintf(self::ENDPOINT_VOID_TRANSACTION, $location_id, $transaction_id),
			'auth_type' => 'Bearer'
		];

		$this->api($request_data);

		return $this->getTransaction($location_id, $transaction_id);
	}

	/**
	 * refundTransaction
	 *
	 * @param $location_id
	 * @param $transaction_id
	 * @param $reason
	 * @param $amount
	 * @param $currency
	 * @param $tender_id
	 *
	 * @throws \Squareup\Exception
	 *
	 * @return array<string, mixed>
	 */
	public function refundTransaction($location_id, $transaction_id, $reason, $amount, $currency, $tender_id): array {
		$request_data = [
			'method'     => 'POST',
			'endpoint'   => sprintf(self::ENDPOINT_REFUND_TRANSACTION, $location_id, $transaction_id),
			'auth_type'  => 'Bearer',
			'parameters' => [
				'idempotency_key' => uniqid(),
				'tender_id'       => $tender_id,
				'reason'          => $reason,
				'amount_money'    => [
					'amount'   => $this->lowestDenomination($amount, $currency),
					'currency' => $currency
				]
			]
		];

		$this->api($request_data);

		return $this->getTransaction($location_id, $transaction_id);
	}

	/**
	 * lowestDenomination
	 *
	 * @param $value
	 * @param $currency
	 *
	 * @return int
	 */
	public function lowestDenomination($value, $currency): int {
		$power = $this->currency->getDecimalPlace($currency);

		$value = (float)$value;

		return (int)($value * 10 ** $power);
	}

	/**
	 * standardDenomination
	 *
	 * @param $value
	 * @param $currency
	 *
	 * @return float
	 */
	public function standardDenomination($value, $currency): float {
		$power = $this->currency->getDecimalPlace($currency);

		$value = (int)$value;

		return (float)($value / 10 ** $power);
	}

	/**
	 * Filter Location
	 *
	 * @param array<string, mixed> $location
	 *
	 * @return bool
	 */
	protected function filterLocation(array $location): bool {
		if (empty($location['capabilities'])) {
			return false;
		}

		return in_array('CREDIT_CARD_PROCESSING', (array)$location['capabilities']);
	}

	/**
	 * Encode Parameters
	 *
	 * @param array<string, mixed> $params
	 * @param string               $content_type
	 *
	 * @return mixed
	 */
	protected function encodeParameters(array $params, string $content_type) {
		switch ($content_type) {
			case 'application/json':
				return json_encode($params);
			case 'application/x-www-form-urlencoded':
				return http_build_query($params);
			default:
			case 'multipart/form-data':
				// curl will handle the params as multipart form data if we just leave it as an array
				return $params;
		}
	}

	/**
	 * Auth State
	 *
	 * @return string
	 */
	protected function authState(): string {
		if (!isset($this->session->data['payment_squareup_oauth_state'])) {
			$this->session->data['payment_squareup_oauth_state'] = bin2hex(openssl_random_pseudo_bytes(32));
		}

		return $this->session->data['payment_squareup_oauth_state'];
	}
}
