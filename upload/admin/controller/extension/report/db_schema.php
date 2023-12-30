<?php
/**
 * Class DB Schema
 *
 * @package Admin\Controller\Extension\Report
 */
class ControllerExtensionReportDbSchema extends Controller {
	private array $error = [];

	/**
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/report/db_schema');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/report/db_schema', 'user_token=' . $this->session->data['user_token'] . $url, true)
		];

		$data['action'] = $this->url->link('extension/report/db_schema/getReport', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		$data['tables'] = [];

		// Structure
		$this->load->helper('db_schema');

		$tables = oc_db_schema();

		$filter_data = [
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		];

		$table_total = $this->model_extension_report_db_schema->getTotalTables();

		$results = $this->model_extension_report_db_schema->getTables($filter_data);

		foreach ($results as $result) {
			foreach ($tables as $table) {
				if ($table['primary'][0] == $result['Column_name'] && $result['COLUMN_KEY'] == 'PRI') {
					$data['tables'][] = [
						'table' => $result['TABLE_NAME'],
						'field' => $table['primary'][0],
						'type'  => $result['COLUMN_TYPE']
					];
				}
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$pagination = new \Pagination();
		$pagination->total = $table_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/report/db_schema', 'user_token=' . $this->session->data['user_token'] . '&page={page}' . $url, true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($table_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($table_total - $this->config->get('config_limit_admin'))) ? $table_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $table_total, ceil($table_total / $this->config->get('config_limit_admin')));

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/db_schema', $data));
	}

	/**
	 * getReport
	 *
	 * @return object\Action|null
	 */
	public function getReport(): ?object {
		$this->load->language('extension/report/db_schema');

		$data['title'] = $this->language->get('text_report');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if ($this->user->hasPermission('modify', 'extension/report/db_schema')) {
			// DB Schema
			$this->load->model('extension/report/db_schema');

			// DB Schema
			$this->load->helper('db_schema');

			$tables = oc_db_schema();

			foreach ($tables as $table) {
				if (in_array($table['name'], $selected)) {
					$field_type_data = [];
					$filter_data = [];

					$fields = $this->model_extension_report_db_schema->getTable($table['name']);

					foreach ($fields as $result) {
						foreach ($table['field'] as $field) {
							// Core
							if ($result['Column_name'] == $field['name']) {
								$data['tables'][$result['TABLE_NAME'] . '|parent'][] = [
									'name'          => $result['Column_name'],
									'previous_type' => $result['COLUMN_TYPE'],
									'type'          => $field['type']
								];

								$encoded_data = [
									'table' => $table['name'],
									'field' => $field['name']
								];

								$field_type_data[json_encode($encoded_data)] = $field['type'];
							}
							// Extensions
							else {
								$encoded_data = [
									'table' => $table['name'],
									'field' => $field['name']
								];

								$field_type_data[json_encode($encoded_data)] = $field['type'];
							}
						}
					}				

					// Foreign
					$foreign_extension_data = [];
						
					if (isset($table['foreign']) && $table['foreign']) {
						foreach ($table['foreign'] as $foreign) {
							$fields = $this->model_extension_report_db_schema->getTable($foreign['table']);

							foreach ($fields as $result) {
								foreach ($field_type_data as $key => $val) {
									if (json_validate($key)) {
										$key_data = json_decode($key, true);

										// Core
										if ($key_data['table'] == $result['TABLE_NAME']) {
											if ($key_data['field'] == $result['Column_name']) {
												if ($val == $result['COLUMN_TYPE']) {
													$type = $result['COLUMN_TYPE'];
												} else {
													$type = $val;
												}

												$data['tables'][$result['TABLE_NAME'] . '|child'][] = [
													'name'          => $result['Column_name'],
													'previous_type' => $result['COLUMN_TYPE'],
													'type'          => $type
												];
											}
											// Extensions
											else {
												$encoded_data = [
													'table' => $result['TABLE_NAME'],
													'field' => $result['Column_name']
												];

												$foreign_extension_data[json_encode($encoded_data)] = $result['COLUMN_TYPE'];
											}
										}
										// Extensions
										else {
											$encoded_data = [
												'table' => $result['TABLE_NAME'],
												'field' => $result['Column_name']
											];

											$foreign_extension_data[json_encode($encoded_data)] = $result['COLUMN_TYPE'];
										}
									}
								}
							}							
						}
					}
					
					// Extension fields from core tables
					$fields = $this->model_extension_report_db_schema->getTable($table['name']);
					
					foreach ($fields as $result) {
						foreach ($field_type_data as $key => $val) {
							if (json_validate($key)) {
								$key_data = json_decode($key, true);

								// Core
								if ($key_data['table'] == $result['TABLE_NAME']) {
									if ($key_data['field'] != $result['Column_name']) {
										$data['tables'][$result['TABLE_NAME'] . '|extension'][] = [
											'name'          => $result['Column_name'],
											'previous_type' => $result['COLUMN_TYPE'],
											'type'          => $result['COLUMN_TYPE']
										];
									}
								}
								// Extensions
								else {
									$data['tables'][$key_data['table'] . '|extension'][] = [
										'name'          => $key_data['field'],
										'previous_type' => $val,
										'type'          => $val
									];
								}
							}
						}
					}
				}
			}

			// Foreign extensions
			foreach ($foreign_extension_data as $key => $val) {
				if (json_validate($key)) {
					$key_data = json_decode($key, true);

					$fields = $this->model_extension_report_db_schema->getTable($key_data['table']);

					foreach ($fields as $result) {
						if ($result['Column_name'] == $key_data['field']) {
							$data['tables'][$result['TABLE_NAME'] . '|extension'][] = [
								'name'          => $result['Column_name'],
								'previous_type' => $result['COLUMN_TYPE'],
								'type'          => $result['COLUMN_TYPE']
							];
						}
					}
				}
			}

			$this->response->setOutput($this->load->view('extension/report/db_schema_report', $data));
		} else {
			return new \Action('error/permission');
		}

		return null;
	}

	/**
	 * Install
	 *
	 * @return void
	 */
	public function install(): void {
		// Settings
		$this->load->model('setting/setting');

		$post_data = [
			'report_db_schema_status' => 1
		];

		$this->model_setting_setting->editSetting('report_db_schema', $post_data);
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public function uninstall(): void {
		// Settings
		$this->load->model('setting/setting');

		$this->model_setting_setting->deleteSetting('report_db_schema');
	}
}
