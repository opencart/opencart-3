<?php
class ModelExtensionTotalTax extends Model {
    public function getTotal(array &$totals): void {
        foreach ($totals['taxes'] as $key => $value) {
            if ($value > 0) {
                $totals['totals'][] = [
                    'code'       => 'tax',
                    'title'      => $this->tax->getRateName($key),
                    'value'      => $value,
                    'sort_order' => $this->config->get('total_tax_sort_order')
                ];

                $totals['total'] += $value;
            }
        }
    }
}