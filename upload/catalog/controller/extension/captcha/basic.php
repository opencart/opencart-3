<?php
/**
 * Class Basic
 *
 * @package Catalog\Controller\Extension\Captcha
 */
class ControllerExtensionCaptchaBasic extends Controller {
	/**
	 * Index
	 *
	 * @param array $error
	 *
	 * @return string
	 *
	 * catalog/view/checkout/cart/after
	 */
	public function index(array $error = []): string {
		$this->load->language('extension/captcha/basic');

		if (isset($error['captcha'])) {
			$data['error_captcha'] = $error['captcha'];
		} else {
			$data['error_captcha'] = '';
		}

		$data['route'] = $this->request->get['route'];

		$this->session->data['captcha'] = substr(strlen(100), mt_rand(0, 94), 6);

		return $this->load->view('extension/captcha/basic', $data);
	}

	/**
	 * Validate
	 *
	 * @return string
	 */
	public function validate(): string {
		$this->load->language('extension/captcha/basic');

		if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
			return $this->language->get('error_captcha');
		} else {
			return '';
		}
	}

	/**
	 * Captcha
	 *
	 * @return void
	 */
	public function captcha(): void {
		$image = imagecreatetruecolor(150, 35);
		$width = imagesx($image);
		$height = imagesy($image);
		$black = imagecolorallocate($image, 0, 0, 0);
		$white = imagecolorallocate($image, 255, 255, 255);
		$red = imagecolorallocatealpha($image, 255, 0, 0, 75);
		$green = imagecolorallocatealpha($image, 0, 255, 0, 75);
		$blue = imagecolorallocatealpha($image, 0, 0, 255, 75);

		imagefilledrectangle($image, 0, 0, $width, $height, $white);
		imagefilledellipse($image, ceil(mt_rand(5, 145)), ceil(mt_rand(0, 35)), 30, 30, $red);
		imagefilledellipse($image, ceil(mt_rand(5, 145)), ceil(mt_rand(0, 35)), 30, 30, $green);
		imagefilledellipse($image, ceil(mt_rand(5, 145)), ceil(mt_rand(0, 35)), 30, 30, $blue);
		imagefilledrectangle($image, 0, 0, $width, 0, $black);
		imagefilledrectangle($image, $width - 1, 0, $width - 1, $height - 1, $black);
		imagefilledrectangle($image, 0, 0, 0, $height - 1, $black);
		imagefilledrectangle($image, 0, $height - 1, $width, $height - 1, $black);

		imagestring($image, 10, (int)(($width - (strlen($this->session->data['captcha']) * 9)) / 2), (int)(($height - 15) / 2), $this->session->data['captcha'], $black);

		header('Content-type: image/jpeg');

		imagejpeg($image);

		imagedestroy($image);
		exit();
	}
}
