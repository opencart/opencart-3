<?php
/**
 * Class Filter
 *
 * @package Catalog\Controller\Extension\Module
 */
class ControllerExtensionModuleFilter extends Controller {
	/**
	 * @return string
	 */
    public function index(): string {
        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
        } else {
            $parts = [];
        }

        $category_id = end($parts);

        // Categories
        $this->load->model('catalog/category');

        $category_info = $this->model_catalog_category->getCategory($category_id);

        if ($category_info) {
            $this->load->language('extension/module/filter');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['action'] = str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url));

            if (isset($this->request->get['filter'])) {
                $data['filter_category'] = explode(',', $this->request->get['filter']);
            } else {
                $data['filter_category'] = [];
            }

            // Products
            $this->load->model('catalog/product');

            $data['filter_groups'] = [];

            $filter_groups = $this->model_catalog_category->getCategoryFilters($category_id);

            if ($filter_groups) {
                foreach ($filter_groups as $filter_group) {
                    $childen_data = [];

                    foreach ($filter_group['filter'] as $filter) {
                        $filter_data = [
                            'filter_category_id' => $category_id,
                            'filter_filter'      => $filter['filter_id']
                        ];

                        $childen_data[] = [
                            'filter_id' => $filter['filter_id'],
                            'name'      => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : '')
                        ];
                    }

                    $data['filter_groups'][] = [
                        'filter_group_id' => $filter_group['filter_group_id'],
                        'name'            => $filter_group['name'],
                        'filter'          => $childen_data
                    ];
                }

                return $this->load->view('extension/module/filter', $data);
            }
        }

        return '';
    }
}
