<?php
// psr/http-client
$autoloader->register('Psr\Http\Client', DIR_STORAGE . 'vendor/psr/http-client/src/', true);

// psr/http-factory
$autoloader->register('Psr\Http\Message', DIR_STORAGE . 'vendor/psr/http-factory/src/', true);

// psr/http-message
$autoloader->register('Psr\Http\Message', DIR_STORAGE . 'vendor/psr/http-message/src/', true);

// scssphp/scssphp
$autoloader->register('ScssPhp\ScssPhp', DIR_STORAGE . 'vendor/scssphp/scssphp/src/', true);

// symfony/polyfill-ctype
$autoloader->register('Symfony\Polyfill\Ctype', DIR_STORAGE . 'vendor/symfony/polyfill-ctype//', true);
require_once(DIR_STORAGE . 'vendor/symfony/polyfill-ctype/bootstrap.php');

// symfony/polyfill-mbstring
$autoloader->register('Symfony\Polyfill\Mbstring', DIR_STORAGE . 'vendor/symfony/polyfill-mbstring//', true);
require_once(DIR_STORAGE . 'vendor/symfony/polyfill-mbstring/bootstrap.php');

// twig/twig
$autoloader->register('Twig', DIR_STORAGE . 'vendor/twig/twig/src/', true);
