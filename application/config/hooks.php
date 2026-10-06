<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/
$hook['post_controller_constructor'][] = [
    'class' => 'Library_access_hook', 'function' => 'enforce',
    'filename' => 'Library_access_hook.php', 'filepath' => 'hooks',
];
$hook['post_controller'][] = [
	'class' => 'Access_monitor_hook',
	'function' => 'capture',
	'filename' => 'Access_monitor_hook.php',
	'filepath' => 'hooks',
];
