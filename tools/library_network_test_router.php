<?php
// Copy into the private test directory; never route production through this file.
if(PHP_SAPI!=='cli-server'||!is_file(__DIR__.'/fixtures.json')){http_response_code(404);exit;}
$root='/www/wwwroot/pustaka/';$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if(preg_match('#^/(assets|img)/#',$path)&&strpos($path,'..')===false&&is_file($root.ltrim($path,'/')))return false;
$_SERVER['SCRIPT_NAME']='/index.php';$_SERVER['PHP_SELF']='/index.php';
define('ENVIRONMENT','production');define('FCPATH',$root);define('BASEPATH',$root.'system/');define('APPPATH',__DIR__.'/application/');define('VIEWPATH',$root.'application/views/');define('SELF','index.php');define('SYSDIR','system');
require BASEPATH.'core/CodeIgniter.php';
