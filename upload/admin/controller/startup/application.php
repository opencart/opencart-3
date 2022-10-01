<?php
class ControllerStartupApplication extends Controller {
    public function index(): void {
        // Url
        $this->registry->set('url', new \Url($this->config->get('site_url')));

        // Customer
        $this->registry->set('customer', new \Cart\Customer($this->registry));

        // Currency
        $this->registry->set('currency', new \Cart\Currency($this->registry));

        // Tax
        $this->registry->set('tax', new \Cart\Tax($this->registry));

        if ($this->config->get('config_tax_default') == 'shipping') {
            $this->tax->setShippingAddress((int)$this->config->get('config_country_id'), (int)$this->config->get('config_zone_id'));
        }

        if ($this->config->get('config_tax_default') == 'payment') {
            $this->tax->setPaymentAddress((int)$this->config->get('config_country_id'), (int)$this->config->get('config_zone_id'));
        }

        $this->tax->setStoreAddress((int)$this->config->get('config_country_id'), (int)$this->config->get('config_zone_id'));

        // Weight
        $this->registry->set('weight', new \Cart\Weight($this->registry));

        // Length
        $this->registry->set('length', new \Cart\Length($this->registry));

        // Cart
        $this->registry->set('cart', new \Cart\Cart($this->registry));
    }
}
