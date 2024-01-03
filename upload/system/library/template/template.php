<?php
namespace Template;
/**
 * Class Template
 *
 * @package System\Library\Template
 */
class Template {
	private array $data = [];
	/**
	 * addPath
	 *
	 * @param string $namespace
	 * @param string $directory
	 *
	 * @return	 void
	 */
	public function set(string $key, string $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * Render
	 *
	 * @param string $filename
	 * @param array	 $data
	 * @param string $code
	 *
	 * @return string
	 */
	public function render(string $template): string {
		$file = DIR_TEMPLATE . $template . '.tpl';

		if (is_file($file)) {
			extract($this->data);

			ob_start();

			require($file);

			return ob_get_clean();
		}

		throw new \Exception('Error: Could not load template ' . $file . '!');
		exit();
	}
}
