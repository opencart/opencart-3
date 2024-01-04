<?php
namespace Template;
/**
 * Class Twig
 *
 * @package
 */
class Twig {
	protected string $root = '';
	protected string $directory = '';
	protected object $loader;
	protected array $path = [];
	protected array $data = [];

	/**
	 * Set
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function set(string $key, mixed $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * Render
	 *
	 * @param string $filename
	 * @param        $code
	 *
	 * @throws \Twig\Error\LoaderError
	 * @throws \Twig\Error\RuntimeError
	 * @throws \Twig\Error\SyntaxError
	 *
	 * @return string
	 */
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
		$config = [
			'autoescape'  => false,
			'debug'       => false,
			'auto_reload' => true,
			'cache'       => DIR_CACHE . 'template/'
		];

		try {
			$loader = new \Twig\Loader\ArrayLoader([$filename . '.twig' => $code]);
			$twig = new \Twig\Environment($loader, $config);

			return $twig->render($filename . '.twig', $this->data);
		} catch (\Twig\Error\SyntaxError $e) {
			trigger_error('Error: Could not load template ' . $filename . '!');
			exit();
		}
	}
}
