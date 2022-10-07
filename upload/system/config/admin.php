<?php
// Site
$_['site_url']          = HTTP_SERVER;
$_['site_ssl']          = HTTPS_SERVER;

// Database
$_['db_autostart']      = true;
$_['db_engine']         = DB_DRIVER; // mpdo, mysqli or pgsql
$_['db_hostname']       = DB_HOSTNAME;
$_['db_username']       = DB_USERNAME;
$_['db_password']       = DB_PASSWORD;
$_['db_database']       = DB_DATABASE;
$_['db_port']           = DB_PORT;

// Session
$_['session_autostart'] = true;
$_['session_engine']    = 'db'; // db or file

// Template
$_['template_cache']    = true;

// Actions
$_['action_pre_action'] = [
	'startup/setting',
	'startup/session',
	'startup/language',
	'startup/application',	
	'startup/startup',
	'startup/error',
	'startup/event',
	'startup/sass',
	'startup/login',
	'startup/permission'
];

// Actions
$_['action_default'] 	= 'common/dashboard';

// Action Events
$_['action_event'] 		= [
	'controller/*/before' => [
		'event/language/before'
	],
	'controller/*/after' => [
		'event/language/after'
	],
	'view/*/before' => [
		999  => 'event/language',
		1000 => 'event/theme'
	],
	'view/*/after' => [
		'event/language'
	]
];