<?php
$current_dir = __DIR__;

require_once $current_dir . DIRECTORY_SEPARATOR . 'cron_functions.php';

if ($index = advertise_google_init($current_dir)) {
	require_once $index;
}
