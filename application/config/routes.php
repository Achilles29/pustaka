<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'auth/index';
$route['auth/do_login'] = 'auth/do_login';
$route['logout'] = 'auth/logout';
$route['admin'] = 'admin/index';
$route['dashboard'] = 'admin/index';
$route['user/dashboard'] = 'user_dashboard/index';
$route['libraries'] = 'libraries/index';
$route['libraries/create'] = 'libraries/create';
$route['libraries/store'] = 'libraries/store';
$route['libraries/edit/(:num)'] = 'libraries/edit/$1';
$route['libraries/update/(:num)'] = 'libraries/update/$1';
$route['libraries/toggle/(:num)'] = 'libraries/toggle/$1';
$route['catalog'] = 'catalog/index';
$route['members'] = 'members/index';
$route['reading-points'] = 'reading_points/index';
$route['events'] = 'events/index';

$route['sidebar/manage'] = 'sidebar/manage';
$route['sidebar/manage/store'] = 'sidebar/store';
$route['sidebar/manage/update/(:num)'] = 'sidebar/update/$1';
$route['sidebar/manage/toggle/(:num)'] = 'sidebar/toggle/$1';

$route['roles'] = 'roles/index';
$route['roles/save-permissions/(:num)'] = 'roles/save_permissions/$1';

$route['users'] = 'users/index';
$route['users/store'] = 'users/store';
$route['users/roles/(:num)'] = 'users/update_roles/$1';
$route['users/toggle/(:num)'] = 'users/toggle/$1';
