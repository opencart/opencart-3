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
 * Registry class
 *
 * @property Autoloader                    $autoloader
 * @property Cache                         $cache
 * @property Cart\Cart                     $cart
 * @property Cart\Currency                 $currency
 * @property Cart\Customer                 $customer
 * @property Cart\Length                   $length
 * @property Cart\Tax                      $tax
 * @property ?Cart\User                    $user
 * @property Cart\Weight                   $weight
 * @property Config                        $config
 * @property Config                        $setting
 * @property DB                            $db
 * @property Document                      $document
 * @property Event                         $event
 * @property googleshopping\Googleshopping $googleshopping
 * @property Language                      $language
 * @property Loader                        $load
 * @property Log                           $log
 * @property Request                       $request
 * @property Response                      $response
 * @property Session                       $session
 * @property Url                           $url
 */
class Registry {
	private array $data = [];

	/**
	 * __get
	 *
	 * https://www.php.net/manual/en/language.oop5.overloading.php#object.get
	 *
	 * @param string $key
	 *
	 * @return ?object
	 */
	public function __get(string $key): ?object {
		return $this->get($key);
	}

	/**
	 * @param string $key
	 *
	 * @return object|null
	 */
	public function get(string $key): ?object {
		return $this->data[$key] ?? null;
	}

	/**
	 * @param string $key
	 * @param object $value
	 */
	public function set(string $key, object $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * Has
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function has($key) {
		return isset($this->data[$key]);
	}
}
