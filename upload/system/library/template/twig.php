<?php
namespace Template;
final class Twig {
	protected string $root;
	protected object $loader;
	protected string $directory;
	protected array $path = array();

	public function set(string $key, mixed $value): void {
		$this->data[$key] = $value;
	}
	
	public function render(string $filename, $code = ''): string {
		if (!$code) {
			$file = DIR_TEMPLATE . $filename . '.twig';

			if (is_file($file)) {
				$code = file_get_contents($file);
			} else {
				throw new \Exception('Error: Could not load template ' . $file . '!');
				exit();
			}
		}

		// initialize Twig environment
		$config = array(
			'autoescape'  => false,
			'debug'       => false,
			'auto_reload' => true,
			'cache'       => DIR_CACHE . 'template/'
		);

		try {
			$loader = new \Twig\Loader\ArrayLoader(array($filename . '.twig' => $code));

			$twig = new \Twig\Environment($loader, $config);

			return $twig->render($filename . '.twig', $this->data);
		} catch (Exception $e) {
			trigger_error('Error: Could not load template ' . $filename . '!');
			exit();
		}	
	}	
}
