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
 * Class Registry
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
 * @property ?Squareup                     $squareup
 * @property Url                           $url
 * @property Mail                          $mail
 */
class Registry {
	/**
	 * @var array<string, object>
	 */
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
	 * Get
	 *
	 * @param string $key
	 *
	 * @return object|null
	 */
	public function get(string $key): ?object {
		return $this->data[$key] ?? null;
	}

	/**
	 * Set
	 *
	 * @param string $key
	 * @param object $value
	 *
	 * @return void
	 */
	public function set(string $key, object $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * Has
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool {
		return isset($this->data[$key]);
	}
}
