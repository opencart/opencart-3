<?php
namespace Template;
class Template {
	private array $data = array();
		
	public function set(string $key, object $value): void {
		$this->data[$key] = $value;
	}
	
	public function render(string $template): void {
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
