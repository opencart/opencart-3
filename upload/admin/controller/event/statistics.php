<?php
class ControllerEventStatistics extends Controller {
	// admin/model/catalog/review/addReview/after
	public function addReview(&$route, &$args, &$output): void {
		$this->load->model('report/statistics');

		$this->model_report_statistics->addValue('review', 1);
	}
	
	// admin/model/catalog/review/deleteReview/after	
	public function deleteReview(&$route, &$args, &$output): void {
		$this->load->model('setting/statistics');

		$this->model_report_statistics->removeValue('review', 1);
	}
	
	// admin/model/sale/returns/addReturn/after
	public function addReturn(&$route, &$args, &$output): void {
		$this->load->model('report/statistics');

		$this->model_report_statistics->addValue('returns', 1);
	}

	// admin/model/sale/returns/deleteReturn/after
	public function deleteReturn(&$route, &$args, &$output): void {
		$this->load->model('setting/statistics');

		$this->model_report_statistics->removeValue('returns', 1);
	}
}
