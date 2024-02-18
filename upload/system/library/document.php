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
 * Document class
 */
class Document {
	private string $title = '';
	private string $description = '';
	private string $keywords = '';
	private array $links = [];
	private array $styles = [];
	private array $scripts = [];

	/**
	 * @param string $title
	 *
	 * @return void
	 */
	public function setTitle(string $title): void {
		$this->title = $title;
	}

	/**
	 * getTitle
	 *
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * setDescription
	 *
	 * @param string $description
	 *
	 * @return void
	 */
	public function setDescription(string $description): void {
		$this->description = $description;
	}

	/**
	 * getDescription
	 *
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * setKeywords
	 *
	 * @param string $keywords
	 *
	 * @return void
	 */
	public function setKeywords(string $keywords): void {
		$this->keywords = $keywords;
	}

	/**
	 * getKeywords
	 *
	 * @return string
	 */
	public function getKeywords(): string {
		return $this->keywords;
	}

	/**
	 * addLink
	 *
	 * @param string $href
	 * @param string $rel
	 *
	 * @return void
	 */
	public function addLink(string $href, string $rel): void {
		$this->links[$href] = [
			'href' => $href,
			'rel'  => $rel
		];
	}

	/**
	 * getLinks
	 *
	 * @return array<string, mixed>
	 */
	public function getLinks(): array {
		return $this->links;
	}

	/**
	 * addStyle
	 *
	 * @param string $href
	 * @param string $rel
	 * @param string $media
	 *
	 * @return void
	 */
	public function addStyle(string $href, string $rel = 'stylesheet', string $media = 'screen'): void {
		$this->styles[$href] = [
			'href'  => $href,
			'rel'   => $rel,
			'media' => $media
		];
	}

	/**
	 * getStyles
	 *
	 * @return array<string, array<string, string>>
	 */
	public function getStyles(): array {
		return $this->styles;
	}

	/**
	 * addScript
	 *
	 * @param string $href
	 * @param string $position
	 *
	 * @return void
	 */
	public function addScript(string $href, string $position = 'header'): void {
		$this->scripts[$position][$href] = ['href' => $href];
	}

	/**
	 * getScripts
	 *
	 * @param string $position
	 *
	 * @return array<string, mixed>
	 */
	public function getScripts(string $position = 'header'): array {
		if (isset($this->scripts[$position])) {
			return $this->scripts[$position];
		} else {
			return [];
		}
	}
}
