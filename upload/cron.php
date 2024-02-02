<?php
// Configuration
if (!is_file('config.php')) {
	exit();
}

// Config
require_once('config.php');

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new \Registry();

// Config
$config = new \Config();
$registry->set('config', $config);

// Load the default config
$config->addPath(DIR_CONFIG);
$config->load('default');
$config->load('catalog');

// Set the default time zone
date_default_timezone_set($config->get('date_timezone'));

// Store
$config->set('config_store_id', 0);

// Logging
$log = new \Log($config->get('error_filename'));
$registry->set('log', $log);

// Error Handler
set_error_handler(function(int $code, string $message, string $file, int $line) use ($log, $config) {
	// error suppressed with @
	if (@error_reporting() === 0) {
		return false;
	}

	switch ($code) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;
		default:
			$error = 'Unknown';
			break;
	}

	if ($config->get('error_log')) {
		$log->write('PHP ' . $error . ':  ' . $message . ' in ' . $file . ' on line ' . $line);
	}

	if ($config->get('error_display')) {
		echo '<b>' . $error . '</b>: ' . $message . ' in <b>' . $file . '</b> on line <b>' . $line . '</b>';
	} else {
		header('Location: ' . $config->get('error_page'));
		exit();
	}

	return true;
});

// Exception Handler
set_exception_handler(function(\Throwable $e) use ($log, $config): void {
	if ($config->get('error_log')) {
		$log->write(get_class($e) . ':  ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
	}

	if ($config->get('error_display')) {
		echo '<b>' . get_class($e) . '</b>: ' . $e->getMessage() . ' in <b>' . $e->getFile() . '</b> on line <b>' . $e->getLine() . '</b>';
	} else {
		header('Location: ' . $config->get('error_page'));
		exit();
	}
});

// Loader
$loader = new \Loader($registry);
$registry->set('load', $loader);

// Request
$request = new \Request();
$registry->set('request', $request);

// Response
$response = new \Response();
$registry->set('response', $response);

// Database
if ($config->get('db_autostart')) {
	$db = new \DB($config->get('db_engine'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port'));
	$registry->set('db', $db);

	// Sync PHP and DB time zones
	$db->query("SET `time_zone` = '" . $db->escape(date('P')) . "'");
}

// Pre Actions
foreach ($config->get('action_pre_action') as $pre_action) {
	$loader->controller($pre_action);
}

// Currency - Suggested to run every 6 hours
if ($config->get('config_currency_engine') == 'ecb' && $config->get('currency_ecb_status')) {
	if ($config->get('currency_ecb_ip')) {
		if ($request->server['REMOTE_ADDR'] == $config->get('currency_ecb_ip')) {
			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);

			$response = curl_exec($curl);

			curl_close($curl);

			if ($response) {
				$dom = new \DOMDocument('1.0', 'UTF-8');
				$dom->loadXml($response);

				$cube = $dom->getElementsByTagName('Cube')->item(0);

				$currencies = [];

				$currencies['EUR'] = 1.0000;

				foreach ($cube->getElementsByTagName('Cube') as $currency) {
					if ($currency->getAttribute('currency')) {
						$currencies[$currency->getAttribute('currency')] = $currency->getAttribute('rate');
					}
				}

				if ($currencies) {
					$default = $config->get('config_currency');

					$registry->get('load')->model('localisation/currency')->model_localisation_currency->getCurrencies();

					foreach ($results as $result) {
						if (isset($currencies[$result['code']])) {
							$from = $currencies['EUR'];

							$to = $currencies[$result['code']];

							$registry->get('load')->model('extension/currency/ecb')->model_extension_currency_ecb->editValueByCode($result['code'], 1 / ($currencies[$default] * ($from / $to)));
						}
					}
				}

				$registry->get('load')->model('extension/currency/ecb')->model_extension_currency_ecb->editValueByCode($default, '1.00000');

				$registry->get('cache')->delete('currency');
			}
		}
	}
}

// Output
$response->output();
