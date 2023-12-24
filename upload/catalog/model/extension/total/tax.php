<?php
/**
 * Class Tax
 *
 * @package Catalog\Model\Extension\Total
 */
class ModelExtensionTotalTax extends Model {
	/**
	 * getTotal
	 */
	public function getTotal(array $total): void {
		foreach ($total['taxes'] as $key => $value) {
			if ($value > 0) {
				$total['totals'][] = [
					'code'       => 'tax',
					'title'      => $this->tax->getRateName($key),
					'value'      => $value,
					'sort_order' => $this->config->get('total_tax_sort_order')
				];

				$total['total'] += $value;
			}
		}
	}
}
