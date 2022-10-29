<?php
class ModelExtensionTotalHandling extends Model {
    public function getTotal(array &$totals): void {
        if (($this->cart->getSubTotal() > $this->config->get('total_handling_total')) && ($this->cart->getSubTotal() > 0) && ($this->cart->hasDownload() == false) && $this->cart->hasShipping() == true) {
            $this->load->language('extension/total/handling');

            $totals['totals'][] = [
                'code'       => 'handling',
                'title'      => $this->language->get('text_handling'),
                'value'      => $this->config->get('total_handling_fee'),
                'sort_order' => $this->config->get('total_handling_sort_order')
            ];

            if ($this->config->get('total_handling_tax_class_id')) {
                $tax_rates = $this->tax->getRates($this->config->get('total_handling_fee'), $this->config->get('total_handling_tax_class_id'));

                foreach ($tax_rates as $tax_rate) {
                    if (!isset($totals['taxes'][$tax_rate['tax_rate_id']])) {
                        $totals['taxes'][$tax_rate['tax_rate_id']] = $tax_rate['amount'];
                    } else {
                        $totals['taxes'][$tax_rate['tax_rate_id']] += $tax_rate['amount'];
                    }
                }
            }

            $totals['total'] += $this->config->get('total_handling_fee');
        }
    }
}