<?php
namespace Squareup;
class Exception extends \Exception {
	/**
	 * @var string
	 */
	public const ERR_CODE_ACCESS_TOKEN_REVOKED = 'ACCESS_TOKEN_REVOKED';
	/**
	 * @var string
	 */
	public const ERR_CODE_ACCESS_TOKEN_EXPIRED = 'ACCESS_TOKEN_EXPIRED';
	/**
	 * @var object $config
	 */
	private object $config;
	/**
	 * @var object $log
	 */
	private object $log;
	/**
	 * @var object $language
	 */
	private object $language;
	/**
	 * @var object $errors
	 */
	private object $errors;
	/**
	 * @var bool $isCurlError
	 */
	private bool $isCurlError = false;
	/**
	 * @var array<string, mixed> $overrideFields
	 */
	private array $overrideFields = [
		'billing_address.country',
		'shipping_address.country',
		'email_address',
		'phone_number'
	];

	/**
	 * Constructor
	 *
	 * @property \Registry $registry
	 *
	 * @param mixed                $registry
	 * @param array<string, mixed> $errors
	 * @param bool                 $is_curl_error
	 */
	public function __construct($registry, $errors, $is_curl_error = false) {
		$this->errors = $errors;
		$this->isCurlError = $is_curl_error;
		$this->config = $registry->get('config');
		$this->log = $registry->get('log');
		$this->language = $registry->get('language');

		$message = $this->concatErrors();

		if ($this->config->get('config_error_log')) {
			$this->log->write($message);
		}

		parent::__construct($message);
	}

	/**
	 * Is Curl Error
	 *
	 * @return bool
	 */
	public function isCurlError(): bool {
		return $this->isCurlError;
	}

	/**
	 * Is Access Token Revoked
	 *
	 * @return bool
	 */
	public function isAccessTokenRevoked(): bool {
		return $this->errorCodeExists(self::ERR_CODE_ACCESS_TOKEN_REVOKED);
	}

	/**
	 * Is Access Token Expired
	 *
	 * @return bool
	 */
	public function isAccessTokenExpired(): bool {
		return $this->errorCodeExists(self::ERR_CODE_ACCESS_TOKEN_EXPIRED);
	}

	/**
	 * Error Code Exists
	 *
	 * @param string $code
	 *
	 * @return bool
	 */
	protected function errorCodeExists(string $code): bool {
		if (!empty($this->errors) && is_array($this->errors)) {
			foreach ($this->errors as $error) {
				if ($error['code'] == $code) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Override Error
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	protected function overrideError(string $field): string {
		return $this->language->get('squareup_override_error_' . $field);
	}

	/**
	 * Parse Error
	 *
	 * @param array<string, mixed> $error
	 *
	 * @return string
	 */
	protected function parseError(array $error): string {
		if (!empty($error['field']) && in_array($error['field'], $this->overrideFields)) {
			return $this->overrideError($error['field']);
		}

		$message = $error['detail'];

		if (!empty($error['field'])) {
			$message .= sprintf($this->language->get('squareup_error_field'), $error['field']);
		}

		return $message;
	}

	/**
	 * Concat Errors
	 *
	 * @return string
	 */
	protected function concatErrors(): string {
		$messages = [];

		if (is_array($this->errors)) {
			foreach ($this->errors as $error) {
				$messages[] = $this->parseError($error);
			}
		} else {
			$messages[] = $this->errors;
		}

		return implode(' ', $messages);
	}
}
