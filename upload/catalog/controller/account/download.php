<?php
class ControllerAccountDownload extends Controller {
	public function index(): void {
		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('account/download', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/download');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_downloads'),
			'href' => $this->url->link('account/download', 'customer_token=' . $this->session->data['customer_token'], true)
		);

		$this->load->model('account/download');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['downloads'] = array();

		$download_total = $this->model_account_download->getTotalDownloads();

		$results = $this->model_account_download->getDownloads(($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'));

		foreach ($results as $result) {
			if (file_exists(DIR_DOWNLOAD . $result['filename'])) {
				$size = filesize(DIR_DOWNLOAD . $result['filename']);

				$i = 0;

				$suffix = array(
					'B',
					'KB',
					'MB',
					'GB',
					'TB',
					'PB',
					'EB',
					'ZB',
					'YB'
				);

				while (($size / 1024) > 1) {
					$size = $size / 1024;
					
					$i++;
				}

				$data['downloads'][] = array(
					'order_id'   => $result['order_id'],
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'name'       => $result['name'],
					'size'       => round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i],
					'href'       => $this->url->link('account/download/download', 'customer_token=' . $this->session->data['customer_token'] . '&download_id=' . $result['download_id'], true)
				);
			}
		}

		$pagination = new \Pagination();
		$pagination->total = $download_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		$pagination->url = $this->url->link('account/download', 'customer_token=' . $this->session->data['customer_token'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($download_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($download_total - 10)) ? $download_total : ((($page - 1) * 10) + 10), $download_total, ceil($download_total / 10));
		
		$data['continue'] = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/download', $data));
	}

	public function download(): void {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/download', 'customer_token=' . $this->session->data['customer_token'], true);

			$this->response->redirect($this->url->link('account/login', 'customer_token=' . $this->session->data['customer_token'], true));
		}

		$this->load->model('account/download');

		if (isset($this->request->get['download_id'])) {
			$download_id = (int)$this->request->get['download_id'];
		} else {
			$download_id = 0;
		}

		$download_info = $this->model_account_download->getDownload($download_id);

		if ($download_info) {
			$file = DIR_DOWNLOAD . $download_info['filename'];
			$mask = basename($download_info['mask']);

			if (!headers_sent()) {
				if (file_exists($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					if (ob_get_level()) {
						ob_end_clean();
					}

					readfile($file, 'rb');

					exit();
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->response->redirect($this->url->link('account/download', '', true));
		}
	}
}