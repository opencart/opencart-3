<?php
/**
 * Class DB Schema
 *
 * @package Admin\Controller\Extension\Other
 */
class ControllerExtensionOtherDbSchema extends Controller {
	private array $error = [];

	/**
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/other/db_schema');

		$this->document->setTitle($this->language->get('heading_title'));

		// Settings
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('other_db_schema', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=other', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=other', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/other/db_schema', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['action'] = $this->url->link('extension/other/db_schema', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=other', true);

		if (isset($this->request->post['other_db_schema_status'])) {
			$data['other_db_schema_status'] = $this->request->post['other_db_schema_status'];
		} else {
			$data['other_db_schema_status'] = $this->config->get('other_db_schema_status');
		}

		if (isset($this->request->post['other_db_schema_sort_order'])) {
			$data['other_db_schema_sort_order'] = $this->request->post['other_db_schema_sort_order'];
		} else {
			$data['other_db_schema_sort_order'] = $this->config->get('other_db_schema_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/other/db_schema_form', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/other/db_schema')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	/**
	 * Report
	 *
	 * @return string
	 */
	public function report(): string {
		$this->load->language('extension/other/db_schema');

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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=other', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/other/db_schema', 'user_token=' . $this->session->data['user_token'] . $url, true)
		];

		$data['action'] = $this->url->link('extension/other/db_schema/getReport', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=other', true);

		$data['tables'] = [];

		// Structure
		$this->load->helper('db_schema');

		$tables = oc_db_schema();

		$filter_data = [
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		];

		$table_total = $this->model_extension_other_db_schema->getTotalTables();

		$results = $this->model_extension_other_db_schema->getTables($filter_data);

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
		$pagination->url = $this->url->link('extension/other/db_schema', 'user_token=' . $this->session->data['user_token'] . '&page={page}' . $url, true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($table_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($table_total - $this->config->get('config_limit_admin'))) ? $table_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $table_total, ceil($table_total / $this->config->get('config_limit_admin')));

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $this->load->view('extension/other/db_schema_info', $data);
	}

	/**
	 * getReport
	 *
	 * @return object\Action|null
	 */
	public function getReport(): ?object {
		$this->load->language('extension/other/db_schema');

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

		if ($this->user->hasPermission('modify', 'extension/other/db_schema')) {
			// DB Schema
			$this->load->model('extension/other/db_schema');

			// DB Schema
			$this->load->helper('db_schema');

			$tables = oc_db_schema();

			foreach ($tables as $table) {
				if (in_array($table['name'], $selected)) {
					$field_type_data = [];

					$fields = $this->model_extension_other_db_schema->getTable($table['name']);

					foreach ($fields as $result) {
						foreach ($table['field'] as $field) {
							// Core
							if ($result['Column_name'] == $field['name']) {
								$data['tables'][$result['TABLE_NAME'] . '|parent'][] = [
									'name'          => $result['Column_name'],
									'previous_type' => $result['COLUMN_TYPE'],
									'type'          => $field['type'],
									'key'			=> $result['COLUMN_KEY']
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
							$fields = $this->model_extension_other_db_schema->getTable($foreign['table']);

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
													'type'          => $type,
													'key'			=> $result['COLUMN_KEY']
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

					// Indexes
					$index_data = [];
					
					if (isset($table['index']) && $table['index']) {
						foreach ($table['index'] as $index) {
							if (!empty($index['key']) && is_array($index['key'])) {
								$filter_data = [];

								foreach ($index['key'] as $val) {
									$filter_data[] = $val;
								}

								$filter_data = array_merge($filter_data, [$index['name']]);
								$filter_data = array_unique($filter_data);

								$fields = $this->model_extension_other_db_schema->getIndexes($table['name'], $filter_data);

								foreach ($fields as $result) {
									foreach ($table['field'] as $field) {
										// Core
										if ($field['name'] == $result['Column_name']) {
											$data['tables'][$result['TABLE_NAME'] . '|index'][] = [
												'name'          => $result['Column_name'],
												'previous_type' => $result['COLUMN_TYPE'],
												'type'          => $field['type'],
												'key'			=> $result['COLUMN_KEY']
											];
										} 
										// Extensions
										else {
											$encoded_data = [
												'table' 		=> $result['TABLE_NAME'],
												'field' 		=> $result['Column_name'],
												'previous_type' => $result['COLUMN_TYPE']
											];
											
											$index_data[json_encode($encoded_data)] = $field['type'];
										}
									}
								}
							}
						}
					}

					// Index extension fields from core tables
					foreach ($index_data as $key => $val) {
						if (json_validate($key)) {
							$key_data = json_decode($key, true);

							$data['tables'][$key_data['table'] . '|extension'][] = [
								'name'          => $key_data['field'],
								'previous_type' => $key_data['previous_type'],
								'type'          => $val,
								'key'			=> $result['COLUMN_KEY']
							];
						}
					}
					
					// Extension fields from core tables
					$fields = $this->model_extension_other_db_schema->getTable($table['name']);
					
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
											'type'          => $result['COLUMN_TYPE'],
											'key'			=> $result['COLUMN_KEY']
										];
									}
								}
								// Extensions
								else {
									$data['tables'][$result['TABLE_NAME'] . '|extension'][] = [
										'name'          => $result['Column_name'],
										'previous_type' => $result['COLUMN_TYPE'],
										'type'          => $result['COLUMN_TYPE'],
										'key'			=> $result['COLUMN_KEY']
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

					$fields = $this->model_extension_other_db_schema->getTable($key_data['table']);

					foreach ($fields as $result) {
						if ($result['Column_name'] == $key_data['field']) {
							$data['tables'][$result['TABLE_NAME'] . '|extension'][] = [
								'name'          => $result['Column_name'],
								'previous_type' => $result['COLUMN_TYPE'],
								'type'          => $result['COLUMN_TYPE'],
								'key'			=> $result['COLUMN_KEY']
							];
						}
					}
				}
			}

			$this->response->setOutput($this->load->view('extension/other/db_schema_report', $data));
		} else {
			return new \Action('error/permission');
		}

		return null;
	}
}
