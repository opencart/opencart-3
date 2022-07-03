<?php
namespace Template;
final class Template {
	private $data = array();
		
	public function set(string $key, string $value): void {
		$this->data[$key] = $value;
	}
	
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
