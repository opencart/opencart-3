<?php
/**
 * @package        OpenCart
 *
 * @author         Daniel Kerr
 * @copyright      Copyright (c) 2005 - 2022, OpenCart, Ltd. (https://www.opencart.com/)
 * @license        https://opensource.org/licenses/GPL-3.0
 *
 * @see           https://www.opencart.com
 */

/**
 * URL class
 */
class Url {
	/**
	 * @var string
	 */
	private string $url = '';
	/**
	 * @var string
	 */
	private string $ssl = '';
	/**
	 * @var array<string, mixed>
	 */
	private array $rewrite = [];

	/**
	 * Constructor
	 *
	 * @param string $url
	 * @param string $ssl
	 */
	public function __construct(string $url, string $ssl = '') {
		$this->url = $url;
		$this->ssl = $ssl;
	}

	/**
	 * addRewrite
	 *
	 * @param string $rewrite
	 *
	 * @return void
	 */
	public function addRewrite(string $rewrite): void {
		$this->rewrite[] = $rewrite;
	}

	/**
	 * Link
	 *
	 * @param string $route
	 * @param mixed  $args
	 * @param bool   $secure
	 *
	 * @return string
	 */
	public function link(string $route, $args = '', bool $secure = false): string {
		if ($this->ssl && $secure) {
			$url = $this->ssl . 'index.php?route=' . $route;
		} else {
			$url = $this->url . 'index.php?route=' . $route;
		}

		if ($args) {
			if (is_array($args)) {
				$url .= '&amp;' . http_build_query($args);
			} else {
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
		}

		foreach ($this->rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		return $url;
	}
}
