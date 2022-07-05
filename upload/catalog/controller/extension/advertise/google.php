<?php
use \googleshopping\traits\StoreLoader;
use \googleshopping\traits\LibraryLoader;

class ControllerExtensionAdvertiseGoogle extends Controller {
    use StoreLoader;
    use LibraryLoader;

    private int $store_id = 0;

    public function __construct(object $registry) {
        parent::__construct($registry);

        if (getenv("ADVERTISE_GOOGLE_STORE_ID")) {
            $this->store_id = (int)getenv("ADVERTISE_GOOGLE_STORE_ID");
        } else {
            $this->store_id = (int)$this->config->get('config_store_id');
        }

        $this->loadStore($this->store_id);
    }

	// catalog/view/common/header/after
    public function google_global_site_tag(string &$route, array &$args, mixed &$output): bool|string {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return false;
        }

        // If there is no tracker, do nothing
        if (!$this->setting->has('advertise_google_conversion_tracker')) {
            return false;
        }

        $tracker = $this->setting->get('advertise_google_conversion_tracker');

        // Insert the tags before the closing <head> tag
        $output = str_replace('</head>', $tracker['google_global_site_tag'] . '</head>', $output);
    }

	// catalog/controller/checkout/success/before
    public function before_checkout_success(string &$route, array &$data): void {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return;
        }

        // If there is no tracker, do nothing
        if (!$this->setting->has('advertise_google_conversion_tracker')) {
            return;
        }

        // In case there is no order, do nothing
        if (!isset($this->session->data['order_id'])) {
            return;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        $this->load->model('checkout/order');		
        $this->load->model('extension/advertise/google');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $tracker = $this->setting->get('advertise_google_conversion_tracker');
        $currency = $order_info['currency_code'];

        $total = $this->googleshopping->convertAndFormat($order_info['total'], $currency);

        $search = array(
            '{VALUE}',
            '{CURRENCY}'
        );

        $replace = array(
            $total,
            $currency
        );

        $snippet = str_replace($search, $replace, $tracker['google_event_snippet']);

        // Store the snippet to display it in the order success view
        $tax = 0;
        $shipping = 0;
        $coupon = $this->model_extension_advertise_google->getCoupon($order_info['order_id']);

        foreach ($this->model_checkout_order->getOrderTotals($order_info['order_id']) as $order_total) {
            if ($order_total['code'] == 'shipping') {
                $shipping += $this->googleshopping->convertAndFormat($order_total['value'], $currency);
            }

            if ($order_total['code'] == 'tax') {
                $tax += $this->googleshopping->convertAndFormat($order_total['value'], $currency);
            }
        }

        $order_products = $this->model_checkout_order->getOrderProducts($order_info['order_id']);

        foreach ($order_products as &$order_product) {
            $order_product['option'] = $this->model_checkout_order->getOrderOptions($order_info['order_id'], $order_product['order_product_id']);
        }

        $purchase_data = array(
            'transaction_id' 	=> $order_info['order_id'],
            'value' 			=> $total,
            'currency' 			=> $currency,
            'tax' 				=> $tax,
            'shipping' 			=> $shipping,
            'items' 			=> $this->model_extension_advertise_google->getRemarketingItems($order_products, $order_info['store_id']),
            'ecomm_prodid' 		=> $this->model_extension_advertise_google->getRemarketingProductIds($order_products, $order_info['store_id'])
        );

        if ($coupon !== null) {
            $purchase_data['coupon'] = $coupon;
        }

        $this->googleshopping->setEventSnippet($snippet);
        $this->googleshopping->setPurchaseData($purchase_data);
    }

	// catalog/view/common/success/after
    public function google_dynamic_remarketing_purchase(string &$route, array &$data, mixed &$output): bool|string {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return false;
        }

        // If the library has not been loaded, or if there is no snippet, do nothing
        if (!$this->registry->has('googleshopping') || $this->googleshopping->getEventSnippet() === null || $this->googleshopping->getPurchaseData() === null) {
            return false;
        }

        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();

        $purchase_data = $this->googleshopping->getPurchaseData();

        $data['transaction_id'] = $purchase_data['transaction_id'];
        $data['value'] = $purchase_data['value'];
        $data['currency'] = $purchase_data['currency'];
        $data['tax'] = $purchase_data['tax'];
        $data['shipping'] = $purchase_data['shipping'];
        $data['items'] = json_encode($purchase_data['items']);
        $data['ecomm_prodid'] = json_encode($purchase_data['ecomm_prodid']);
        $data['ecomm_totalvalue'] = $purchase_data['value'];

        $purchase_snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_purchase', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $this->googleshopping->getEventSnippet() . $purchase_snippet . '</body>', $output);
    }

	// catalog/view/common/home/after
    public function google_dynamic_remarketing_home(string &$route, array &$data, mixed &$output): bool|string {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return false;
        }

        // If we are not on the home page, do nothing
        if (isset($this->request->get['route']) && $this->request->get['route'] != $this->config->get('action_default')) {
            return false;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return false;
        }

        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_home', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

	// catalog/view/product/search/after
    public function google_dynamic_remarketing_searchresults(string &$route, array &$data, mixed &$output): bool|string {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return false;
        }

        // If we are not on the search page, do nothing
        if (!isset($this->request->get['route']) || $this->request->get['route'] != 'product/search' || !isset($this->request->get['search'])) {
            return false;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return false;
        }

        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();
        $data['search_term'] = $this->request->get['search'];

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_searchresults', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

	// catalog/view/product/category/after
    public function google_dynamic_remarketing_category(string &$route, array &$data, mixed &$output): bool|string {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return false;
        }

        // If we are not on the search page, do nothing
        if (!isset($this->request->get['route']) || $this->request->get['route'] != 'product/category') {
            return false;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return false;
        }

        if (isset($this->request->get['path'])) {
            $parts = explode('_', $this->request->get['path']);
            $category_id = (int)end($parts);
        } elseif (isset($this->request->get['category_id'])) {
            $category_id = (int)$this->request->get['category_id'];
        } else {
            $category_id = 0;
        }

        $this->load->model('extension/advertise/google');

        $data = array();
		
        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();
        $data['description'] = str_replace('"', '\\"', $this->model_extension_advertise_google->getHumanReadableOpenCartCategory($category_id));

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_category', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

	// catalog/view/product/product/after
    public function google_dynamic_remarketing_product(string &$route, array &$data, mixed &$output): bool|string {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return false;
        }

        // If we do not know the viewed product, do nothing
        if (!isset($this->request->get['product_id']) || !isset($this->request->get['route']) || $this->request->get['route'] != 'product/product') {
            return false;
        }

        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct((int)$this->request->get['product_id']);

        // If product does not exist, do nothing
        if (!$product_info) {
            return false;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return false;
        }

        $this->load->model('extension/advertise/google');

        $category_name = $this->model_extension_advertise_google->getHumanReadableCategory($product_info['product_id'], $this->store_id);

        $option_map = $this->model_extension_advertise_google->getSizeAndColorOptionMap($product_info['product_id'], $this->store_id);
		
		$data = array();

        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();
        $data['option_map'] = json_encode($option_map);
        $data['brand'] = $product_info['manufacturer'];
        $data['name'] = $product_info['name'];
        $data['category'] = str_replace('"', '\\"', $category_name);

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_product', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

	// catalog/view/checkout/cart/after
    public function google_dynamic_remarketing_cart(string &$route, array &$data, mixed &$output): bool|string {
        // In case the extension is disabled, do nothing
        if (!$this->setting->get('advertise_google_status')) {
            return false;
        }

        // If we are not on the cart page, do nothing
        if (!isset($this->request->get['route']) || $this->request->get['route'] != 'checkout/cart') {
            return false;
        }

        if (!$this->registry->has('googleshopping')) {
            $this->loadLibrary($this->store_id);
        }

        if (null === $this->googleshopping->getEventSnippetSendTo()) {
            return false;
        }

		$this->load->model('extension/advertise/google');
        $this->load->model('catalog/product');        

        $data = array();
		
        $data['send_to'] = $this->googleshopping->getEventSnippetSendTo();
        $data['ecomm_totalvalue'] = $this->cart->getTotal();
        $data['ecomm_prodid'] = json_encode($this->model_extension_advertise_google->getRemarketingProductIds($this->cart->getProducts(), $this->store_id));
        $data['items'] = json_encode($this->model_extension_advertise_google->getRemarketingItems($this->cart->getProducts(), $this->store_id));

        $snippet = $this->load->view('extension/advertise/google_dynamic_remarketing_cart', $data);

        // Insert the snippet after the output
        $output = str_replace('</body>', $snippet . '</body>', $output);
    }

    public function cron(int $cron_id = null, string $code = null, string $cycle = null, string $date_added = null, string $date_modified = null) {
        $this->loadLibrary($this->store_id);

        if (!$this->validateCRON()) {
            // In case this is not a CRON task
            return false;
        }

        $this->load->language('extension/advertise/google');

        // Reset taxes to use the store address and zone
        $this->tax->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
        $this->tax->setPaymentAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
        $this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

        $this->googleshopping->cron();
    }

    protected function validateCRON() {
        if (!$this->setting->get('advertise_google_status')) {
            // In case the extension is disabled, do nothing
            return false;
        }

        if (!$this->setting->get('advertise_google_gmc_account_selected')) {
            return false;
        }

        if (!$this->setting->get('advertise_google_gmc_shipping_taxes_configured')) {
            return false;
        }

        try {
            if (count($this->googleshopping->getTargets($this->store_id)) === 0) {
                return false;
            }
        } catch (\RuntimeException $e) {
            return false;
        }

        if (isset($this->request->get['cron_token']) && $this->request->get['cron_token'] == $this->config->get('advertise_google_cron_token')) {
            return true;
        }

        if (defined('ADVERTISE_GOOGLE_ROUTE')) {
            return true;
        }

        return false;
    }
}