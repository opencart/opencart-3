<?php
class ModelExtensionTotalTotal extends Model {
    public function getTotal(array &$totals): void {
        $this->load->language('extension/total/total');

        $totals['totals'][] = [
            'code'       => 'total',
            'title'      => $this->language->get('text_total'),
            'value'      => max(0, $totals['total']),
            'sort_order' => $this->config->get('total_total_sort_order')
        ];
    }
}