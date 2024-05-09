<?php
/**
 * Class Database
 *
 * @package \Install\Controller\Startup
 */
class ControllerStartupDatabase extends Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		if (is_file(DIR_OPENCART . 'config.php') && filesize(DIR_OPENCART . 'config.php') > 0) {
			$lines = file(DIR_OPENCART . 'config.php');

			foreach ($lines as $line) {
				if (str_contains(strtoupper($line), 'DB_')) {
					eval($line);
				}
			}

			if (defined('DB_SSL_KEY')) {
				$db_ssl_key = DB_SSL_KEY;
			} else {
				$db_ssl_key = '';
			}

			if (defined('DB_SSL_CERT')) {
				$db_ssl_cert = DB_SSL_CERT;
			} else {
				$db_ssl_cert = '';
			}

			if (defined('DB_SSL_CA')) {
				$db_ssl_ca = DB_SSL_CA;
			} else {
				$db_ssl_ca = '';
			}

			if (defined('DB_PORT')) {
				$port = DB_PORT;
			} else {
				$port = ini_get('mysqli.default_port');
			}

			$this->registry->set('db', new \DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, $port, $db_ssl_key, $db_ssl_cert, $db_ssl_ca));
		}
	}
}
