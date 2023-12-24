<?php
// Site
$_['site_url']           = HTTP_SERVER;
$_['site_ssl']           = HTTPS_SERVER;

// Url
$_['url_autostart']      = false;

// Database
$_['db_autostart']       = true;
$_['db_engine']          = DB_DRIVER; // mpdo, mysqli or pgsql
$_['db_hostname']        = DB_HOSTNAME;
$_['db_username']        = DB_USERNAME;
$_['db_password']        = DB_PASSWORD;
$_['db_database']        = DB_DATABASE;
$_['db_port']            = DB_PORT;

// Session
$_['session_autostart']  = false;
$_['session_engine']     = 'db';
$_['session_name']       = 'OCSESSID';

// Template
$_['template_engine']    = 'twig';
$_['template_directory'] = '';
$_['template_cache']     = true;

// Autoload Libraries
$_['library_autoload'] = [];

// Actions
$_['action_pre_action'] = [
	'startup/setting',
	'startup/session',
	'startup/language',
	'startup/seo_url',
	'startup/customer',
	'startup/currency',
	'startup/tax',
	'startup/application',
	'startup/startup',
	'startup/marketing',
	'startup/error',
	'startup/event',
	'startup/sass',
	'startup/maintenance'
];

// Action Events
$_['action_event'] = [
	'controller/*/before' => [
		'event/language/before'
	],
	'controller/*/after' => [
		'event/language/after'
	],
	'view/*/before' => [
		500 => 'event/theme',
		998 => 'event/language',
	],
	'language/*/after' => [
		'event/translation'
	],
	//'view/*/before' => [
	//	1000  => 'event/debug/before'
	//],
	//'controller/*/after'  => [
	//	'event/debug/after'
//	]
];
