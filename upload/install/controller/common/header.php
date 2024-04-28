<?php
class ControllerCommonHeader extends Controller {
	/**
	 * Index
	 * 
	 * @return string
	 */
	public function index(): string {
		$this->load->language('common/header');

		$data['title'] = $this->document->getTitle();
		$data['description'] = $this->document->getDescription();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();

		$data['base'] = HTTP_SERVER;

		return $this->load->view('common/header', $data);
	}
}
