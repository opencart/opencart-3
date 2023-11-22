<?php
/**
 * Class Column Right
 *
 * @package Catalog\Controller\Common
 */
class ControllerCommonColumnRight extends Controller {
	/**
	 * @return string
	 */
    public function index(): string {
        // Layouts
        $this->load->model('design/layout');

        if (isset($this->request->get['route'])) {
            $route = (string)$this->request->get['route'];
        } else {
            $route = 'common/home';
        }

        $layout_id = 0;

        if ($route == 'product/category' && isset($this->request->get['path'])) {
            // Categories
            $this->load->model('catalog/category');

            $path = explode('_', (string)$this->request->get['path']);

            $layout_id = $this->model_catalog_category->getLayoutId(end($path));
        }

        if ($route == 'product/product' && isset($this->request->get['product_id'])) {
            // Products
            $this->load->model('catalog/product');

            $layout_id = $this->model_catalog_product->getLayoutId($this->request->get['product_id']);
        }

        if ($route == 'information/information' && isset($this->request->get['information_id'])) {
            // Information
            $this->load->model('catalog/information');

            $layout_id = $this->model_catalog_information->getLayoutId($this->request->get['information_id']);
        }

        if (!$layout_id) {
            $layout_id = $this->model_design_layout->getLayout($route);
        }

        if (!$layout_id) {
            $layout_id = $this->config->get('config_layout_id');
        }

        // Modules
        $this->load->model('setting/module');

        $data['modules'] = [];

        $modules = $this->model_design_layout->getModules($layout_id, 'column_right');

        foreach ($modules as $module) {
            $part = explode('.', $module['code']);

            if (isset($part[0]) && $this->config->get('module_' . $part[0] . '_status')) {
                $module_data = $this->load->controller('extension/module/' . $part[0]);

                if ($module_data) {
                    $data['modules'][] = $module_data;
                }
            }

            if (isset($part[1])) {
                $setting_info = $this->model_setting_module->getModule($part[1]);

                if ($setting_info && $setting_info['status']) {
                    $output = $this->load->controller('extension/module/' . $part[0], $setting_info);

                    if ($output) {
                        $data['modules'][] = $output;
                    }
                }
            }
        }

        return $this->load->view('common/column_right', $data);
    }
}
