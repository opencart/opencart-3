<?php
/**
 * Loaded in an outside context
 *
 * @var Autoloader $autoloader
 */

// guzzlehttp/guzzle
$autoloader->register('GuzzleHttp', DIR_STORAGE . 'vendor/guzzlehttp/guzzle/src/', true);

if (is_file(DIR_STORAGE . 'vendor/guzzlehttp/guzzle/src/functions_include.php')) {
	require_once(DIR_STORAGE . 'vendor/guzzlehttp/guzzle/src/functions_include.php');
}

// guzzlehttp/promises
$autoloader->register('GuzzleHttp\Promise', DIR_STORAGE . 'vendor/guzzlehttp/promises/src/', true);

// guzzlehttp/psr7
$autoloader->register('GuzzleHttp\Psr7', DIR_STORAGE . 'vendor/guzzlehttp/psr7/src/', true);

// psr/http-client
$autoloader->register('Psr\Http\Client', DIR_STORAGE . 'vendor/psr/http-client/src/', true);

// psr/http-factory
$autoloader->register('Psr\Http\Message', DIR_STORAGE . 'vendor/psr/http-factory/src/', true);

// psr/http-message
$autoloader->register('Psr\Http\Message', DIR_STORAGE . 'vendor/psr/http-message/src/', true);

// ralouphie/getallheaders
if (is_file(DIR_STORAGE . 'vendor/ralouphie/getallheaders/src/getallheaders.php')) {
	require_once(DIR_STORAGE . 'vendor/ralouphie/getallheaders/src/getallheaders.php');
}

// scssphp/scssphp
$autoloader->register('ScssPhp\ScssPhp', DIR_STORAGE . 'vendor/scssphp/scssphp/src/', true);

// symfony/deprecation-contracts
if (is_file(DIR_STORAGE . 'vendor/symfony/deprecation-contracts/function.php')) {
	require_once(DIR_STORAGE . 'vendor/symfony/deprecation-contracts/function.php');
}

// symfony/polyfill-ctype
$autoloader->register('Symfony\Polyfill\Ctype', DIR_STORAGE . 'vendor/symfony/polyfill-ctype//', true);
if (is_file(DIR_STORAGE . 'vendor/symfony/polyfill-ctype/bootstrap.php')) {
	require_once(DIR_STORAGE . 'vendor/symfony/polyfill-ctype/bootstrap.php');
}

// symfony/polyfill-mbstring
$autoloader->register('Symfony\Polyfill\Mbstring', DIR_STORAGE . 'vendor/symfony/polyfill-mbstring//', true);
if (is_file(DIR_STORAGE . 'vendor/symfony/polyfill-mbstring/bootstrap.php')) {
	require_once(DIR_STORAGE . 'vendor/symfony/polyfill-mbstring/bootstrap.php');
}

// symfony/polyfill-php80
$autoloader->register('Symfony\Polyfill\Php80', DIR_STORAGE . 'vendor/symfony/polyfill-php80//', true);
if (is_file(DIR_STORAGE . 'vendor/symfony/polyfill-php80/bootstrap.php')) {
	require_once(DIR_STORAGE . 'vendor/symfony/polyfill-php80/bootstrap.php');
}

// twig/twig
$autoloader->register('Twig', DIR_STORAGE . 'vendor/twig/twig/src/', true);
