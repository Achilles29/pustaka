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
$route['libraries/verify/(:num)'] = 'libraries/verify/$1';
$route['libraries/photos/set-cover/(:num)'] = 'libraries/set_cover/$1';
$route['libraries/photos/delete/(:num)'] = 'libraries/delete_photo/$1';
$route['catalog'] = 'catalog/index';
$route['catalog/detail/(:num)'] = 'catalog/detail/$1';
$route['catalog/sync'] = 'catalog/sync';
$route['catalog/sync/run'] = 'catalog/run_sync';
$route['members'] = 'members/index';
$route['members/detail/(:num)'] = 'members/detail/$1';
$route['members/sync'] = 'members/sync';
$route['members/sync/run'] = 'members/run_sync';
$route['reading-points'] = 'reading_points/index';
$route['events'] = 'events/index';
$route['audit'] = 'audit/index';
$route['regions'] = 'regions/index';
$route['regions/districts/store'] = 'regions/store_district';
$route['regions/districts/update/(:num)'] = 'regions/update_district/$1';
$route['regions/districts/toggle/(:num)'] = 'regions/toggle_district/$1';
$route['regions/villages/store'] = 'regions/store_village';
$route['regions/villages/update/(:num)'] = 'regions/update_village/$1';
$route['regions/villages/toggle/(:num)'] = 'regions/toggle_village/$1';

$route['rbac'] = 'rbac/index';
$route['rbac/users'] = 'rbac/users';
$route['rbac/users/store'] = 'rbac/store_user';
$route['rbac/users/roles/(:num)'] = 'rbac/update_user_roles/$1';
$route['rbac/users/toggle/(:num)'] = 'rbac/toggle_user/$1';
$route['rbac/roles'] = 'rbac/roles';
$route['rbac/roles/store'] = 'rbac/store_role';
$route['rbac/roles/update/(:num)'] = 'rbac/update_role/$1';
$route['rbac/roles/toggle/(:num)'] = 'rbac/toggle_role/$1';
$route['rbac/roles/save-permissions/(:num)'] = 'rbac/save_role_permissions/$1';
$route['rbac/pages'] = 'rbac/pages';
$route['rbac/pages/store'] = 'rbac/store_page';
$route['rbac/pages/update/(:num)'] = 'rbac/update_page/$1';
$route['rbac/pages/toggle/(:num)'] = 'rbac/toggle_page/$1';
$route['rbac/sidebar'] = 'rbac/sidebar';
$route['rbac/sidebar/store'] = 'rbac/store_menu';
$route['rbac/sidebar/update/(:num)'] = 'rbac/update_menu/$1';
$route['rbac/sidebar/toggle/(:num)'] = 'rbac/toggle_menu/$1';
$route['rbac/sidebar/reorder'] = 'rbac/reorder_sidebar';

$route['sidebar/manage'] = 'rbac/sidebar';
$route['sidebar/manage/store'] = 'rbac/store_menu';
$route['sidebar/manage/update/(:num)'] = 'rbac/update_menu/$1';
$route['sidebar/manage/toggle/(:num)'] = 'rbac/toggle_menu/$1';
$route['sidebar/manage/reorder'] = 'rbac/reorder_sidebar';

$route['roles'] = 'rbac/roles';
$route['roles/store'] = 'rbac/store_role';
$route['roles/update/(:num)'] = 'rbac/update_role/$1';
$route['roles/toggle/(:num)'] = 'rbac/toggle_role/$1';
$route['roles/save-permissions/(:num)'] = 'rbac/save_role_permissions/$1';

$route['users'] = 'rbac/users';
$route['users/store'] = 'rbac/store_user';
$route['users/roles/(:num)'] = 'rbac/update_user_roles/$1';
$route['users/toggle/(:num)'] = 'rbac/toggle_user/$1';
