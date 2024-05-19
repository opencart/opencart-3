<?php
/**
 * Class Eway
 *
 * @package Catalog\Controller\Extension\Payment
 */
class ControllerExtensionPaymentEway extends Controller {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$this->load->language('extension/payment/eway');

		$data['months'] = [];

		$data['payment_type'] = $this->config->get('payment_eway_payment_type');

		for ($i = 1; $i <= 12; $i++) {
			$data['months'][] = [
				'text'  => sprintf('%02d', $i),
				'value' => sprintf('%02d', $i)
			];
		}

		$today = getdate();

		$data['year_expire'] = [];

		for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
			$data['year_expire'][] = [
				'text'  => sprintf('%02d', $i % 100),
				'value' => sprintf('%04d', $i)
			];
		}

		// Orders
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		if ($this->config->get('payment_eway_test')) {
			$data['Endpoint'] = 'Sandbox';

			$data['text_testing'] = $this->language->get('text_testing');
		} else {
			$data['Endpoint'] = 'Production';

			$data['text_testing'] = '';
		}

		// Zones
		$this->load->model('localisation/zone');

		$payment_zone_info = $this->model_localisation_zone->getZone($order_info['payment_zone_id']);

		$payment_zone_code = $payment_zone_info['code'] ?? '';

		$shipping_zone_info = $this->model_localisation_zone->getZone($order_info['shipping_zone_id']);

		$shipping_zone_code = $shipping_zone_info['code'] ?? '';

		$request = new \stdClass();
		$request->Customer = new \stdClass();
		$request->Customer->Title = 'Mr.';
		$request->Customer->FirstName = (string)substr($order_info['payment_firstname'], 0, 50);
		$request->Customer->LastName = (string)substr($order_info['payment_lastname'], 0, 50);
		$request->Customer->CompanyName = (string)substr($order_info['payment_company'], 0, 50);
		$request->Customer->Street1 = (string)substr($order_info['payment_address_1'], 0, 50);
		$request->Customer->Street2 = (string)substr($order_info['payment_address_2'], 0, 50);
		$request->Customer->City = (string)substr($order_info['payment_city'], 0, 50);
		$request->Customer->State = (string)substr($payment_zone_code, 0, 50);
		$request->Customer->PostalCode = (string)substr($order_info['payment_postcode'], 0, 30);
		$request->Customer->Country = strtolower($order_info['payment_iso_code_2']);
		$request->Customer->Email = $order_info['email'];
		$request->Customer->Phone = (string)substr($order_info['telephone'], 0, 32);
		$request->ShippingAddress = new \stdClass();
		$request->ShippingAddress->FirstName = (string)substr($order_info['shipping_firstname'], 0, 50);
		$request->ShippingAddress->LastName = (string)substr($order_info['shipping_lastname'], 0, 50);
		$request->ShippingAddress->Street1 = (string)substr($order_info['shipping_address_1'], 0, 50);
		$request->ShippingAddress->Street2 = (string)substr($order_info['shipping_address_2'], 0, 50);
		$request->ShippingAddress->City = (string)substr($order_info['shipping_city'], 0, 50);
		$request->ShippingAddress->State = (string)substr($shipping_zone_code, 0, 50);
		$request->ShippingAddress->PostalCode = (string)substr($order_info['shipping_postcode'], 0, 30);
		$request->ShippingAddress->Country = strtolower($order_info['shipping_iso_code_2']);
		$request->ShippingAddress->Email = $order_info['email'];
		$request->ShippingAddress->Phone = (string)substr($order_info['telephone'], 0, 32);
		$request->ShippingAddress->ShippingMethod = 'Unknown';

		$invoice_desc = '';

		foreach ($this->cart->getProducts() as $product) {
			$item_price = $this->currency->format($product['price'], $order_info['currency_code'], false, false);
			$item_total = $this->currency->format($product['total'], $order_info['currency_code'], false, false);

			$item = new \stdClass();
			$item->SKU = (string)substr($product['product_id'], 0, 12);
			$item->Description = (string)substr($product['name'], 0, 26);
			$item->Quantity = (string)($product['quantity']);
			$item->UnitCost = $this->lowestDenomination($item_price, $order_info['currency_code']);
			$item->Total = $this->lowestDenomination($item_total, $order_info['currency_code']);

			$request->Items[] = $item;

			$invoice_desc .= $product['name'] . ', ';
		}

		$invoice_desc = (string)substr($invoice_desc, 0, -2);

		if (strlen($invoice_desc) > 64) {
			$invoice_desc = (string)substr($invoice_desc, 0, 61) . '...';
		}

		$shipping = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

		if ($shipping > 0) {
			$item = new \stdClass();
			$item->SKU = '';
			$item->Description = (string)substr($this->language->get('text_shipping'), 0, 26);
			$item->Quantity = 1;
			$item->UnitCost = $this->lowestDenomination($shipping, $order_info['currency_code']);
			$item->Total = $this->lowestDenomination($shipping, $order_info['currency_code']);

			$request->Items[] = $item;
		}

		$opt1 = new \stdClass();
		$opt1->Value = $order_info['order_id'];
		$request->Options = [$opt1];

		$request->Payment = new \stdClass();
		$request->Payment->TotalAmount = $this->lowestDenomination($amount, $order_info['currency_code']);
		$request->Payment->InvoiceNumber = (int)$this->session->data['order_id'];
		$request->Payment->InvoiceDescription = $invoice_desc;
		$request->Payment->InvoiceReference = (string)substr($this->config->get('config_name'), 0, 40) . ' - #' . $order_info['order_id'];
		$request->Payment->CurrencyCode = $order_info['currency_code'];
		$request->RedirectUrl = $this->url->link('extension/payment/eway/callback', '', true);

		if ($this->config->get('payment_eway_transaction_method') == 'auth') {
			$request->Method = 'Authorise';
		} else {
			$request->Method = 'ProcessPayment';
		}

		$request->TransactionType = 'Purchase';
		$request->DeviceID = 'opencart-' . VERSION . ' eway-trans-2.1.2';
		$request->CustomerIP = oc_get_ip();
		$request->PartnerID = '0f1bec3642814f89a2ea06e7d2800b7f';

		// Eway
		$this->load->model('extension/payment/eway');

		$template = 'eway';

		if ($this->config->get('payment_eway_paymode') == 'iframe') {
			$request->CancelUrl = 'http://www.example.org';
			$request->CustomerReadOnly = true;

			$result = $this->model_extension_payment_eway->getSharedAccessCode($request);

			$template = 'eway_iframe';
		} else {
			$result = $this->model_extension_payment_eway->getAccessCode($request);
		}

		// Check if any error returns
		if (isset($result->Errors)) {
			$lbl_error = '';

			$error_array = explode(",", $result->Errors);

			foreach ($error_array as $error) {
				$error = $this->language->get('text_card_message_' . $error);
				$lbl_error .= $error . "<br/>\n";
			}

			$this->log->write('eWAY Payment error: ' . $lbl_error);
		}

		if (isset($lbl_error)) {
			$data['error'] = $lbl_error;
		} else {
			if ($this->config->get('payment_eway_paymode') == 'iframe') {
				$data['callback'] = $this->url->link('extension/payment/eway/callback', 'AccessCode=' . $result->AccessCode, true);
				$data['SharedPaymentUrl'] = $result->SharedPaymentUrl;
			}

			$data['action'] = $result->FormActionURL;
			$data['AccessCode'] = $result->AccessCode;
		}

		return $this->load->view('extension/payment/' . $template, $data);
	}

	/**
	 * Lowest Denomination
	 *
	 * @param float $value
	 * @param float $currency
	 *
	 * @return float
	 */
	public function lowestDenomination(float $value, float $currency): float {
		$power = $this->currency->getDecimalPlace($currency);

		$value = (float)$value;

		return (int)($value * 10 ** $power);
	}

	/**
	 * Validate Denomination
	 *
	 * @param float $value
	 * @param float $currency
	 *
	 * @return float
	 */
	public function ValidateDenomination(float $value, float $currency): float {
		$power = $this->currency->getDecimalPlace($currency);
		$value = (float)$value;

		return (int)($value * 10 ** ('-' . $power));
	}

	/**
	 * Callback
	 *
	 * @return void
	 */
	public function callback(): void {
		$this->load->language('extension/payment/eway');

		if (isset($this->request->get['AccessCode']) || isset($this->request->get['amp;AccessCode'])) {
			// Eway
			$this->load->model('extension/payment/eway');

			if (isset($this->request->get['amp;AccessCode'])) {
				$access_code = $this->request->get['amp;AccessCode'];
			} else {
				$access_code = $this->request->get['AccessCode'];
			}

			$is_error = false;

			$result = $this->model_extension_payment_eway->getAccessCodeResult($access_code);

			// Check if any error returns
			if (isset($result->Errors)) {
				$is_error = true;

				$lbl_error = '';

				$error_array = explode(",", $result->Errors);

				foreach ($error_array as $error) {
					$error = $this->language->get('text_card_message_' . $error);

					$lbl_error .= $error . ", ";
				}

				$this->log->write('eWAY error: ' . $lbl_error);
			}

			$fraud = false;

			if (!$is_error) {
				if (!$result->TransactionStatus) {
					$is_error = true;

					$lbl_error = '';
					$log_error = '';

					$error_array = explode(", ", $result->ResponseMessage);

					foreach ($error_array as $error) {
						// Don't show fraud issues to customers
						if (stripos($error, 'F') === false) {
							$lbl_error .= $this->language->get('text_card_message_' . $error);
						} else {
							$fraud = true;
						}

						$log_error .= $this->language->get('text_card_message_' . $error) . ", ";
					}

					$log_error = substr($log_error, 0, -2);

					$this->log->write('eWAY payment failed: ' . $log_error);
				}
			}

			// Orders
			$this->load->model('checkout/order');

			if ($is_error) {
				if ($fraud) {
					$this->response->redirect($this->url->link('checkout/failure', '', true));
				} else {
					$this->session->data['error'] = $this->language->get('text_transaction_failed');

					$this->response->redirect($this->url->link('checkout/checkout', '', true));
				}
			} else {
				$order_id = $result->Options[0]->Value;

				$order_info = $this->model_checkout_order->getOrder($order_id);

				// Eway
				$this->load->model('extension/payment/eway');

				$eway_order_data = [
					'order_id'       => $order_id,
					'transaction_id' => $result->TransactionID,
					'amount'         => $this->ValidateDenomination($result->TotalAmount, $order_info['currency_code']),
					'currency_code'  => $order_info['currency_code'],
					'debug_data'     => json_encode($result)
				];

				$error_array = explode(", ", $result->ResponseMessage);

				$log_error = '';

				foreach ($error_array as $error) {
					if (stripos($error, 'F') !== false) {
						$fraud = true;
						$log_error .= $this->language->get('text_card_message_' . $error) . ", ";
					}
				}

				$log_error = substr($log_error, 0, -2);

				$eway_order_id = $this->model_extension_payment_eway->addOrder($eway_order_data);

				$transaction_id = (string)$result->TransactionID;

				$this->model_extension_payment_eway->addTransaction($eway_order_id, $this->config->get('payment_eway_transaction_method'), $transaction_id, $order_info['total'], $order_info['currency_code']);

				if ($fraud) {
					$message = 'Suspected fraud order: ' . $log_error . "\n";
				} else {
					$message = "eWAY Payment accepted\n";
				}

				$authorisation_code = (string)$result->AuthorisationCode;
				$response_code = (string)$result->ResponseCode;

				$message .= 'Transaction ID: ' . $transaction_id . "\n";
				$message .= 'Authorisation Code: ' . $authorisation_code . "\n";
				$message .= 'Card Response Code: ' . $response_code . "\n";

				if ($fraud) {
					$this->model_checkout_order->addHistory($order_id, $this->config->get('payment_eway_fraud_status_id'), $message);
				} elseif ($this->config->get('payment_eway_transaction_method') == 'payment') {
					$this->model_checkout_order->addHistory($order_id, $this->config->get('payment_eway_order_status_id'), $message);
				} else {
					$this->model_checkout_order->addHistory($order_id, $this->config->get('payment_eway_auth_status_id'), $message);
				}

				$token_customer_id = (string)$result->Customer->TokenCustomerID;

				if (!empty($token_customer_id) && $this->customer->isLogged() && isset($this->session->data['customer_token']) && !$this->model_extension_payment_eway->checkToken($token_customer_id)) {
					$card_data = [];

					$number = (int)$result->Customer->CardDetails->Number;
					$expiry_month = (int)$result->Customer->CardDetails->ExpiryMonth;
					$expiry_year = (int)$result->Customer->CardDetails->ExpiryYear;

					$card_data['customer_id'] = $this->customer->getId();
					$card_data['Token'] = $token_customer_id;
					$card_data['Last4Digits'] = substr(str_replace(' ', '', $number), -4, 4);
					$card_data['ExpiryDate'] = $expiry_month . '/' . $expiry_year;
					$card_data['CardType'] = '';

					$this->model_extension_payment_eway->addCard($this->session->data['order_id'], $card_data);
				}

				$this->response->redirect($this->url->link('checkout/success', '', true));
			}
		}
	}
}
