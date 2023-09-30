<?php
class ControllerCommonMenu extends Controller {
    public function index(): string {
        $this->load->language('common/menu');

        // Product
        $this->load->model('catalog/product');

        // Category
        $this->load->model('catalog/category');

        $data['categories'] = [];

        $categories = $this->model_catalog_category->getCategories(0);

        foreach ($categories as $category) {
            if ($category['top']) {
                // Level 2
                $children_data = [];

                $children = $this->model_catalog_category->getCategories($category['category_id']);

                foreach ($children as $child) {
                    $filter_data = [
                        'filter_category_id'  => $child['category_id'],
                        'filter_sub_category' => true
                    ];

                    $children_data[] = [
                        'name' => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                        'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
                    ];
                }

                // Level 1
                $data['categories'][] = [
                    'name'     => $category['name'],
                    'children' => $children_data,
                    'column'   => $category['column'] ? $category['column'] : 1,
                    'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
                ];
            }
        }

        return $this->load->view('common/menu', $data);
    }
}
