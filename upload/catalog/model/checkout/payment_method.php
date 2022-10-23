<?php
class ModelCheckoutPaymentMethod extends Model {
    public function getMethods(array $payment_address): array {
        $method_data = [];

        $this->load->model('setting/extension');

        $results     = $this->model_setting_extension->getExtensionsByType('payment');

        foreach ($results as $result) {
            if ($this->config->get('payment_' . $result['code'] . '_status')) {
                $this->load->model('extension/payment/' . $result['code']);

                $payment_method = $this->{'model_extension_payment_' . $result['code']}->getMethod($payment_address);
                if ($payment_method) {
                    $method_data[$result['code']] = $payment_method;
                }
            }
        }

        $sort_order = [];

        foreach ($method_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $method_data);

        // Since the API does not use the same session super globals
        // as the one during checkout, we need to validate both.
        if (isset($this->session->data['customer']['customer_id'])) {
            $customer_id = (int)$this->session->data['customer']['customer_id'];
        } elseif ($this->customer->getId()) {
            $customer_id = $this->customer->getId();
        } else {
            $customer_id = 0;
        }

        // Stored payment methods
        $this->load->model('account/payment_method');

        $payment_methods = $this->model_account_payment_method->getPaymentMethods($customer_id);

        foreach ($payment_methods as $payment_method) {
            $method_data[$result['code'] . '_' . $result['code']] = [
                'name' => $payment_method['name'],
                'code' => $payment_method['code']
            ];
        }

        return $method_data;
    }
}