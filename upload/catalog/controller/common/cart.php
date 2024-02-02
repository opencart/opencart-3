<?php
/**
 * Class Cart
 *
 * @package Catalog\Controller\Common
 */
class ControllerCommonCart extends Controller {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$this->load->language('common/cart');

		// Totals
		$this->load->model('setting/extension');

		$total = 0;
		$totals = [];
		$taxes = $this->cart->getTaxes();

		// Because __call cannot keep var references, so we put them into an array.
		$total_data = [
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		];

		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = [];

			$results = $this->model_setting_extension->getExtensionsByType('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					if (is_callable([$this->{'model_extension_total_' . $result['code']}, 'getTotal'])) {
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}
			}

			$sort_order = [];

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
		}

		$data['text_items'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

		// Images
		$this->load->model('tool/image');

		// Upload
		$this->load->model('tool/upload');

		$data['products'] = [];

		foreach ($this->cart->getProducts() as $product) {
			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = (float)$this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
			}

			$option_data = [];

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = [
					'name'  => $option['name'],
					'value' => (oc_strlen($value) > 20 ? oc_substr($value, 0, 20) . '..' : $value),
					'type'  => $option['type']
				];
			}

			// Subscriptions
			$description = '';

			if ($product['subscription']) {
				$trial_price = $this->currency->format($this->tax->calculate($product['subscription']['trial_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$trial_cycle = $product['subscription']['trial_cycle'];
				$trial_frequency = $this->language->get('text_' . $product['subscription']['trial_frequency']);
				$trial_duration = $product['subscription']['trial_duration'];

				if ($product['subscription']['trial_status']) {
					$description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
				}

				$price = $this->currency->format($this->tax->calculate($product['subscription']['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$cycle = $product['subscription']['cycle'];
				$frequency = $this->language->get('text_' . $product['subscription']['frequency']);
				$duration = $product['subscription']['duration'];

				if ($duration) {
					$description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
				} else {
					$description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
				}
			}

			$data['products'][] = [
				'cart_id'      => $product['cart_id'],
				'thumb'        => $image,
				'name'         => $product['name'],
				'model'        => $product['model'],
				'option'       => $option_data,
				'subscription' => $description,
				'quantity'     => $product['quantity'],
				'price'        => $price,
				'total'        => $total,
				'href'         => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			];
		}

		// Gift Voucher
		$data['vouchers'] = [];

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$data['vouchers'][] = [
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
				];
			}
		}

		$data['totals'] = [];

		foreach ($totals as $total) {
			$data['totals'][] = [
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
			];
		}

		$data['cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);

		return $this->load->view('common/cart', $data);
	}

	/**
	 * Info
	 *
	 * @return void
	 */
	public function info(): void {
		$this->response->setOutput($this->index());
	}
}
