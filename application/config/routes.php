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
$route['user/reading-checkin'] = 'user_dashboard/reading_checkin';
$route['user/reading-checkin/store'] = 'user_dashboard/store_reading_checkin';
$route['katalog'] = 'public_catalog/index';
$route['katalog/detail/(:num)'] = 'public_catalog/detail/$1';
$route['katalog/request/(:num)'] = 'public_catalog/request/$1';
$route['membership/verify/(:num)/(:any)'] = 'membership/verify/$1/$2';
$route['membership/register'] = 'membership/register';
$route['membership/register/pending/(:any)'] = 'membership/pending/$1';
$route['membership/register/submit'] = 'membership/submit_registration';
$route['membership/renewal/request'] = 'membership/renewal_request';
$route['guestbook/monitor'] = 'guestbook/monitor';
$route['guestbook/search-members'] = 'guestbook/search_members';
$route['guestbook/store-guest'] = 'guestbook/store_guest';
$route['guestbook/store-member'] = 'guestbook/store_member';
$route['guestbook/checkin/(:any)'] = 'guestbook/qr_checkin/$1';
$route['guestbook/settings'] = 'guestbook_settings/index';
$route['guestbook/settings/update'] = 'guestbook_settings/update';
$route['reports'] = 'reports/visits';
$route['reports/visits'] = 'reports/visits';
$route['reports/visits/print'] = 'reports/visits_print';
$route['reports/visits/excel'] = 'reports/visits_excel';
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
$route['catalog/create'] = 'catalog/create';
$route['catalog/store'] = 'catalog/store';
$route['catalog/edit/(:num)'] = 'catalog/edit/$1';
$route['catalog/update/(:num)'] = 'catalog/update/$1';
$route['catalog/delete/(:num)'] = 'catalog/delete/$1';
$route['catalog/detail/(:num)'] = 'catalog/detail/$1';
$route['catalog/masters'] = 'catalog_masters/index';
$route['catalog/masters/categories/store'] = 'catalog_masters/store_category';
$route['catalog/masters/categories/update/(:num)'] = 'catalog_masters/update_category/$1';
$route['catalog/masters/classifications/store'] = 'catalog_masters/store_classification';
$route['catalog/masters/classifications/update/(:num)'] = 'catalog_masters/update_classification/$1';
$route['catalog/requests'] = 'catalog/requests';
$route['catalog/requests/update/(:num)'] = 'catalog/update_request/$1';
$route['catalog/items/store/(:num)'] = 'catalog/store_item/$1';
$route['catalog/items/update/(:num)/(:num)'] = 'catalog/update_item/$1/$2';
$route['catalog/items/delete/(:num)/(:num)'] = 'catalog/delete_item/$1/$2';
$route['catalog/sync'] = 'catalog/sync';
$route['catalog/sync/run'] = 'catalog/run_sync';
$route['assets-migration'] = 'asset_migration/index';
$route['assets-migration/run'] = 'asset_migration/run';
$route['members'] = 'members/index';
$route['members/create'] = 'members/create';
$route['members/store'] = 'members/store';
$route['members/edit/(:num)'] = 'members/edit/$1';
$route['members/update/(:num)'] = 'members/update/$1';
$route['members/delete/(:num)'] = 'members/delete/$1';
$route['members/detail/(:num)'] = 'members/detail/$1';
$route['members/card/update/(:num)'] = 'members/update_card/$1';
$route['members/registrations'] = 'members/registrations';
$route['members/registrations/update/(:num)'] = 'members/update_registration/$1';
$route['members/renewals'] = 'members/renewals';
$route['members/renewals/update/(:num)'] = 'members/update_renewal/$1';
$route['members/sync'] = 'members/sync';
$route['members/sync/run'] = 'members/run_sync';
$route['transactions'] = 'transactions/index';
$route['transactions/sync'] = 'transactions/sync';
$route['transactions/sync/run'] = 'transactions/run_sync';
$route['reader/assets'] = 'reader/assets';
$route['reader/assets/create'] = 'reader/create';
$route['reader/assets/store'] = 'reader/store';
$route['reader/assets/edit/(:num)'] = 'reader/edit/$1';
$route['reader/assets/update/(:num)'] = 'reader/update/$1';
$route['reader/assets/status/(:num)'] = 'reader/status/$1';
$route['reader/audit'] = 'reader/audit';
$route['reader/read/(:num)'] = 'reader/read/$1';
$route['reader/stream/(:num)'] = 'reader/stream/$1';
$route['reader/page-info/(:num)'] = 'reader/page_info/$1';
$route['reader/page/(:num)/(:num)'] = 'reader/page/$1/$2';
$route['reader/admin-page-info/(:num)'] = 'reader/admin_page_info/$1';
$route['reader/admin-page/(:num)/(:num)'] = 'reader/admin_page/$1/$2';
$route['reader/audit-page'] = 'reader/audit_page';
$route['reading-points'] = 'reading_points/index';
$route['reading-points/tokens'] = 'reading_points/tokens';
$route['reading-points/tokens/revoke/(:num)'] = 'reading_points/revoke_token/$1';
$route['reading-points/create'] = 'reading_points/create';
$route['reading-points/store'] = 'reading_points/store';
$route['reading-points/edit/(:num)'] = 'reading_points/edit/$1';
$route['reading-points/update/(:num)'] = 'reading_points/update/$1';
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
