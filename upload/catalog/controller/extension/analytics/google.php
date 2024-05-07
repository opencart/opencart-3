<?php
/**
 * Class Google
 *
 * @package Catalog\Controller\Extension\Analytics
 */
class ControllerExtensionAnalyticsGoogle extends Controller {
	/**
     * Index
	 *
	 * @return string
	 */
	public function index(): string {
		return html_entity_decode($this->config->get('analytics_google_code'), ENT_QUOTES, 'UTF-8');
	}
}
