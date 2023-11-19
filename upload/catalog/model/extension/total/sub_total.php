<?php
/**
 * Class Sub Total
 *
 * @package Catalog\Model\Extension\Total
 */
class ModelExtensionTotalSubTotal extends Model {
    public function getTotal(array $total): void {
        $this->load->language('extension/total/sub_total');

        $sub_total = $this->cart->getSubTotal();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $voucher) {
                $sub_total += $voucher['amount'];
            }
        }

        $total['totals'][] = [
            'code'       => 'sub_total',
            'title'      => $this->language->get('text_sub_total'),
            'value'      => $sub_total,
            'sort_order' => $this->config->get('total_sub_total_sort_order')
        ];

        $total['total'] += $sub_total;
    }
}
