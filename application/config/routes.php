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
$route['user/member-card'] = 'user_dashboard/member_card';
$route['user/account'] = 'user_dashboard/account';
$route['user/account/username'] = 'user_dashboard/update_username';
$route['user/account/password'] = 'user_dashboard/update_password';
$route['user/reading-checkin'] = 'user_dashboard/reading_checkin';
$route['user/reading-checkin/store'] = 'user_dashboard/store_reading_checkin';
$route['user/token-request/store'] = 'user_dashboard/store_token_request';
$route['panduan'] = 'guide/index';
$route['katalog'] = 'public_catalog/index';
$route['katalog/detail/(:num)'] = 'public_catalog/detail/$1';
$route['katalog/request/(:num)'] = 'public_catalog/request/$1';
$route['agenda'] = 'agenda/index';
$route['agenda/detail/(:num)'] = 'agenda/detail/$1';
$route['agenda/register/(:num)'] = 'agenda/register/$1';
$route['agenda/ticket/(:any)'] = 'agenda/ticket/$1';
$route['membership/verify/(:num)/(:any)'] = 'membership/verify/$1/$2';
$route['membership/register'] = 'membership/register';
$route['membership/registration-status'] = 'membership/registration_status';
$route['membership/register/pending/(:any)'] = 'membership/pending/$1';
$route['membership/register/submit'] = 'membership/submit_registration';
$route['donasi-digital'] = 'digital_donation/form';
$route['donasi-digital/submit'] = 'digital_donation/submit';
$route['donasi-digital/status/(:any)'] = 'digital_donation/status/$1';
$route['suara-pemustaka'] = 'patron_voice/form';
$route['suara-pemustaka/submit'] = 'patron_voice/submit';
$route['suara-pemustaka/status/(:any)'] = 'patron_voice/status/$1';
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
$route['reports/pemustaka'] = 'patron_insights/index';
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
$route['catalog/highlights'] = 'catalog/highlights';
$route['catalog/highlights/books'] = 'catalog/highlight_book_search';
$route['catalog/highlights/store'] = 'catalog/store_highlight';
$route['catalog/highlights/update/(:num)'] = 'catalog/update_highlight/$1';
$route['catalog/highlights/delete/(:num)'] = 'catalog/delete_highlight/$1';
$route['catalog/detail/(:num)'] = 'catalog/detail/$1';
$route['catalog/masters'] = 'catalog_masters/index';
$route['catalog/masters/categories/store'] = 'catalog_masters/store_category';
$route['catalog/masters/categories/update/(:num)'] = 'catalog_masters/update_category/$1';
$route['catalog/masters/classifications/store'] = 'catalog_masters/store_classification';
$route['catalog/masters/classifications/update/(:num)'] = 'catalog_masters/update_classification/$1';
$route['catalog/masters/collection-types/store'] = 'catalog_masters/store_collection_type';
$route['catalog/masters/collection-types/update/(:num)'] = 'catalog_masters/update_collection_type/$1';
$route['catalog/masters/collection-types/delete/(:num)'] = 'catalog_masters/delete_collection_type/$1';
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
$route['reading-points/token-settings'] = 'reading_points/token_settings';
$route['reading-points/token-settings/update'] = 'reading_points/update_token_settings';
$route['reading-points/tokens/revoke/(:num)'] = 'reading_points/revoke_token/$1';
$route['reading-points/tokens/approve-request/(:num)'] = 'reading_points/approve_token_request/$1';
$route['reading-points/tokens/reject-request/(:num)'] = 'reading_points/reject_token_request/$1';
$route['reading-points/create'] = 'reading_points/create';
$route['reading-points/store'] = 'reading_points/store';
$route['reading-points/edit/(:num)'] = 'reading_points/edit/$1';
$route['reading-points/update/(:num)'] = 'reading_points/update/$1';
$route['events'] = 'events/index';
$route['events/qr'] = 'events/qr';
$route['events/create'] = 'events/create';
$route['events/store'] = 'events/store';
$route['events/categories/store'] = 'events/store_category';
$route['events/detail/(:num)'] = 'events/detail/$1';
$route['events/edit/(:num)'] = 'events/edit/$1';
$route['events/update/(:num)'] = 'events/update/$1';
$route['events/status/(:num)'] = 'events/status/$1';
$route['events/fields/store/(:num)'] = 'events/store_field/$1';
$route['events/fields/update/(:num)/(:num)'] = 'events/update_field/$1/$2';
$route['events/fields/toggle/(:num)/(:num)'] = 'events/toggle_field/$1/$2';
$route['events/registrations/update/(:num)/(:num)'] = 'events/update_registration/$1/$2';
$route['events/checkin/(:any)'] = 'events/checkin/$1';
$route['audit'] = 'audit/index';
$route['access-monitor'] = 'access_monitor/index';
$route['regions'] = 'regions/index';
$route['regions/districts/store'] = 'regions/store_district';
$route['regions/districts/update/(:num)'] = 'regions/update_district/$1';
$route['regions/districts/toggle/(:num)'] = 'regions/toggle_district/$1';
$route['regions/villages/store'] = 'regions/store_village';
$route['regions/villages/update/(:num)'] = 'regions/update_village/$1';
$route['regions/villages/toggle/(:num)'] = 'regions/toggle_village/$1';
$route['membership/regions/regencies/(:num)'] = 'membership/region_regencies/$1';
$route['membership/regions/districts/(:num)'] = 'membership/region_districts/$1';
$route['membership/regions/villages/(:num)'] = 'membership/region_villages/$1';

$route['rbac'] = 'rbac/index';
$route['rbac/admins'] = 'rbac/admins';
$route['rbac/admins/store'] = 'rbac/store_user';
$route['rbac/admins/roles/(:num)'] = 'rbac/update_user_roles/$1';
$route['rbac/admins/toggle/(:num)'] = 'rbac/toggle_user/$1';
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

// ── Quiz Engine ──────────────────────────────────────────────────────────────
$route['quiz-config']                                   = 'quiz_config/index';
$route['quiz-config/store_grade']                       = 'quiz_config/store_grade';
$route['quiz-config/update_grade/(:num)']               = 'quiz_config/update_grade/$1';
$route['quiz-config/delete_grade/(:num)']               = 'quiz_config/delete_grade/$1';
$route['quiz-config/store_subject']                     = 'quiz_config/store_subject';
$route['quiz-config/update_subject/(:num)']             = 'quiz_config/update_subject/$1';
$route['quiz-config/delete_subject/(:num)']             = 'quiz_config/delete_subject/$1';

$route['quiz-bank']                                     = 'quiz_bank/index';
$route['quiz-bank/create']                              = 'quiz_bank/create';
$route['quiz-bank/store']                               = 'quiz_bank/store';
$route['quiz-bank/edit/(:num)']                         = 'quiz_bank/edit/$1';
$route['quiz-bank/update/(:num)']                       = 'quiz_bank/update/$1';
$route['quiz-bank/delete/(:num)']                       = 'quiz_bank/delete/$1';
$route['quiz-bank/bulk-delete']                         = 'quiz_bank/bulk_delete';
$route['quiz-bank/import']                              = 'quiz_bank/import';
$route['quiz-bank/analyze']                             = 'quiz_bank/analyze';
$route['quiz-bank/commit-import']                       = 'quiz_bank/commit_import';
$route['quiz-bank/template']                            = 'quiz_bank/template';
$route['quiz-bank/template/(:any)']                     = 'quiz_bank/template/$1';

// ── Sirkulasi peminjaman fisik ──────────────────────────────────────────────
$route['catalog/loans']                                 = 'catalog/loans';
$route['catalog/loans/issue']                           = 'catalog/issue_manual_loan';
$route['catalog/requests/issue/(:num)']                 = 'catalog/issue_request_loan/$1';
$route['catalog/loans/return/(:num)']                   = 'catalog/return_loan/$1';
$route['catalog/loans/reconcile']                       = 'catalog/reconcile_loans';
$route['catalog/loans/settings']                        = 'catalog/save_loan_settings';
$route['digital-donations']                             = 'digital_donations/index';
$route['digital-donations/review/(:num)']               = 'digital_donations/review/$1';
$route['digital-donations/file/(:num)']                 = 'digital_donations/file/$1';
$route['patron-feedback']                               = 'patron_feedback/index';
$route['patron-feedback/review/(:num)']                 = 'patron_feedback/review/$1';

$route['quiz-sessions']                                 = 'quiz_sessions/index';
$route['quiz-sessions/create']                          = 'quiz_sessions/create';
$route['quiz-sessions/store']                           = 'quiz_sessions/store';
$route['quiz-sessions/edit/(:num)']                     = 'quiz_sessions/edit/$1';
$route['quiz-sessions/update/(:num)']                   = 'quiz_sessions/update/$1';
$route['quiz-sessions/questions/update/(:num)']         = 'quiz_sessions/update_question_source/$1';
$route['quiz-sessions/delete/(:num)']                   = 'quiz_sessions/delete/$1';
$route['quiz-sessions/toggle_status/(:num)']            = 'quiz_sessions/toggle_status/$1';
$route['quiz-sessions/attempts/(:num)']                 = 'quiz_sessions/attempts/$1';
$route['quiz-sessions/grade/(:num)']                    = 'quiz_sessions/grade/$1';

$route['quiz-competitions']                             = 'quiz_competitions/index';
$route['quiz-competitions/create']                      = 'quiz_competitions/create';
$route['quiz-competitions/store']                       = 'quiz_competitions/store';
$route['quiz-competitions/edit/(:num)']                 = 'quiz_competitions/edit/$1';
$route['quiz-competitions/update/(:num)']               = 'quiz_competitions/update/$1';
$route['quiz-competitions/delete/(:num)']               = 'quiz_competitions/delete/$1';
$route['quiz-competitions/set_status/(:num)']           = 'quiz_competitions/set_status/$1';
$route['quiz-competitions/announce/(:num)']             = 'quiz_competitions/announce/$1';
$route['quiz-competitions/questions/(:num)']            = 'quiz_competitions/questions/$1';
$route['quiz-competitions/add_question/(:num)']         = 'quiz_competitions/add_question/$1';
$route['quiz-competitions/remove_question/(:num)/(:num)'] = 'quiz_competitions/remove_question/$1/$2';
$route['quiz-competitions/participants/(:num)']         = 'quiz_competitions/participants/$1';
$route['quiz-competitions/add_participant/(:num)']      = 'quiz_competitions/add_participant/$1';
$route['quiz-competitions/edit_participant/(:num)']     = 'quiz_competitions/edit_participant/$1';
$route['quiz-competitions/delete_participant/(:num)']   = 'quiz_competitions/delete_participant/$1';
$route['quiz-competitions/import_participants/(:num)']  = 'quiz_competitions/import_participants/$1';
$route['quiz-competitions/export_participants/(:num)']  = 'quiz_competitions/export_participants/$1';
$route['quiz-competitions/results/(:num)']              = 'quiz_competitions/results/$1';
$route['quiz-competitions/grade/(:num)']                = 'quiz_competitions/grade/$1';

// ── Learn Config (admin: poin & lencana) ─────────────────────────────────────
$route['learn-config']                          = 'learn_config/index';
$route['learn-config/store_rule']               = 'learn_config/store_rule';
$route['learn-config/update_rule/(:num)']       = 'learn_config/update_rule/$1';
$route['learn-config/delete_rule/(:num)']       = 'learn_config/delete_rule/$1';
$route['learn-config/store_badge']              = 'learn_config/store_badge';
$route['learn-config/update_badge/(:num)']      = 'learn_config/update_badge/$1';
$route['learn-config/delete_badge/(:num)']      = 'learn_config/delete_badge/$1';
$route['learn-config/leaderboard']              = 'learn_config/leaderboard';

// ── Learn Games (admin: konten game) ─────────────────────────────────────────
$route['learn-games']                           = 'learn_games/index';
$route['learn-games/toggle/(:num)']             = 'learn_games/toggle_game_type/$1';
$route['learn-games/update_type/(:num)']        = 'learn_games/update_game_type/$1';
$route['learn-games/store_category']            = 'learn_games/store_category';
$route['learn-games/update_category/(:num)']    = 'learn_games/update_category/$1';
$route['learn-games/delete_category/(:num)']    = 'learn_games/delete_category/$1';
$route['learn-games/content/(:num)']            = 'learn_games/content/$1';
$route['learn-games/store_set/(:num)']          = 'learn_games/store_set/$1';
$route['learn-games/update_set/(:num)']         = 'learn_games/update_set/$1';
$route['learn-games/delete_set/(:num)']         = 'learn_games/delete_set/$1';
$route['learn-games/items/(:num)']              = 'learn_games/items/$1';
$route['learn-games/store_item/(:num)']         = 'learn_games/store_item/$1';
$route['learn-games/update_item/(:num)']        = 'learn_games/update_item/$1';
$route['learn-games/delete_item/(:num)']        = 'learn_games/delete_item/$1';
$route['learn-english-rpg']                     = 'learn_english_rpg/index';
$route['learn-english-rpg/reports']             = 'learn_english_rpg_reports/index';
$route['learn-english-rpg/episode/store']        = 'learn_english_rpg/save_episode';
$route['learn-english-rpg/episode/update/(:num)']= 'learn_english_rpg/save_episode/$1';
$route['learn-english-rpg/episode/(:num)']       = 'learn_english_rpg/detail/$1';
$route['learn-english-rpg/scene/store/(:num)']   = 'learn_english_rpg/save_scene/$1';
$route['learn-english-rpg/scene/update/(:num)/(:num)'] = 'learn_english_rpg/save_scene/$1/$2';
$route['learn-english-rpg/scene/delete/(:num)/(:num)'] = 'learn_english_rpg/delete_scene/$1/$2';

// ── Raport Belajar (admin) ───────────────────────────────────────────────────
$route['learn-reports']                         = 'learn_reports/index';
$route['learn-reports/view/(:num)']             = 'learn_reports/view/$1';

// ── Mode Battle (admin pool soal) ────────────────────────────────────────────
$route['learn-battle']                          = 'learn_battle/index';
$route['learn-battle/store']                    = 'learn_battle/store';
$route['learn-battle/update/(:num)']            = 'learn_battle/update/$1';
$route['learn-battle/delete/(:num)']            = 'learn_battle/delete/$1';
$route['learn-battle/sessions/create']           = 'learn_battle/session_create';
$route['learn-battle/sessions/store']            = 'learn_battle/session_store';
$route['learn-battle/sessions/edit/(:num)']      = 'learn_battle/session_edit/$1';
$route['learn-battle/sessions/update/(:num)']    = 'learn_battle/session_update/$1';
$route['learn-battle/sessions/questions/(:num)'] = 'learn_battle/session_questions/$1';
$route['learn-battle/sessions/questions/save/(:num)'] = 'learn_battle/session_questions_save/$1';
$route['learn-battle/sessions/import/(:num)']    = 'learn_battle/session_import/$1';
$route['learn-battle/template']                  = 'learn_battle/template';
$route['learn-battle/template/(:any)']           = 'learn_battle/template/$1';

// ── Notifikasi (admin broadcast) ─────────────────────────────────────────────
$route['learn-notifications']                   = 'learn_notifications/index';
$route['learn-notifications/send']              = 'learn_notifications/send';

// ── Story Quiz (admin) ───────────────────────────────────────────────────────
$route['learn-story']                           = 'learn_story/index';
$route['learn-story/store_passage']             = 'learn_story/store_passage';
$route['learn-story/update_passage/(:num)']     = 'learn_story/update_passage/$1';
$route['learn-story/delete_passage/(:num)']     = 'learn_story/delete_passage/$1';
$route['learn-story/toggle_passage/(:num)']     = 'learn_story/toggle_passage/$1';
$route['learn-story/questions/(:num)']          = 'learn_story/questions/$1';
$route['learn-story/store_question/(:num)']     = 'learn_story/store_question/$1';
$route['learn-story/update_question/(:num)']    = 'learn_story/update_question/$1';
$route['learn-story/delete_question/(:num)']    = 'learn_story/delete_question/$1';

// ── Flashcard (admin) ────────────────────────────────────────────────────────
$route['learn-flashcards']                      = 'learn_flashcards/index';
$route['learn-flashcards/store_deck']           = 'learn_flashcards/store_deck';
$route['learn-flashcards/update_deck/(:num)']   = 'learn_flashcards/update_deck/$1';
$route['learn-flashcards/delete_deck/(:num)']   = 'learn_flashcards/delete_deck/$1';
$route['learn-flashcards/toggle_deck/(:num)']   = 'learn_flashcards/toggle_deck/$1';
$route['learn-flashcards/cards/(:num)']         = 'learn_flashcards/cards/$1';
$route['learn-flashcards/store_card/(:num)']    = 'learn_flashcards/store_card/$1';
$route['learn-flashcards/update_card/(:num)']   = 'learn_flashcards/update_card/$1';
$route['learn-flashcards/delete_card/(:num)']   = 'learn_flashcards/delete_card/$1';

// ── Tukar Poin (reward catalog admin) ────────────────────────────────────────
$route['learn-rewards']                         = 'learn_rewards/index';
$route['learn-rewards/store']                   = 'learn_rewards/store';
$route['learn-rewards/update/(:num)']           = 'learn_rewards/update/$1';
$route['learn-rewards/delete/(:num)']           = 'learn_rewards/delete/$1';
$route['learn-rewards/toggle/(:num)']           = 'learn_rewards/toggle/$1';
$route['learn-rewards/redemptions']             = 'learn_rewards/redemptions';

// ── Public arena belajar (no admin auth) ─────────────────────────────────────
$route['manuscripts']                            = 'manuscripts/index';
$route['manuscripts/create']                     = 'manuscripts/create';
$route['manuscripts/store']                      = 'manuscripts/store';
$route['manuscripts/edit/(:num)']                = 'manuscripts/edit/$1';
$route['manuscripts/update/(:num)']              = 'manuscripts/update/$1';
$route['manuscripts/delete/(:num)']              = 'manuscripts/delete/$1';
$route['manuscripts/page/(:num)/delete/(:num)']  = 'manuscripts/delete_page/$1/$2';
$route['manuscripts/convert-pdf/(:num)']         = 'manuscripts/convert_pdf/$1';
$route['naskah-kuno']                            = 'manuscript_collection/index';
$route['naskah-kuno/detail/(:num)']              = 'manuscript_collection/detail/$1';
$route['naskah-kuno/baca/(:num)']                = 'manuscript_collection/viewer/$1';
$route['naskah-kuno/page/(:num)/(:num)']         = 'manuscript_collection/page/$1/$2';
$route['naskah-kuno/preview/(:num)']             = 'manuscript_collection/preview/$1';
$route['belajar']                               = 'play_game/index';
$route['belajar/pilih/(:any)']                  = 'play_game/choose/$1';
$route['belajar/play/(:any)/(:num)']            = 'play_game/play/$1/$2';
$route['belajar/play/(:any)']                   = 'play_game/play/$1';
$route['belajar/finish']                        = 'play_game/finish';
$route['belajar/content/(:num)']                = 'play_game/content_api/$1';
$route['belajar/tukar/redeem']                  = 'play_game/redeem_reward';
$route['belajar/tukar']                         = 'play_game/hadiah';
$route['belajar/flashcard/progress']            = 'play_game/flashcard_progress';
$route['belajar/flashcard/finish']              = 'play_game/flashcard_finish';
$route['belajar/flashcard/(:any)']              = 'play_game/flashcard_study/$1';
$route['belajar/flashcard']                     = 'play_game/flashcard';
$route['belajar/cerita/submit']                 = 'play_game/cerita_submit';
$route['belajar/cerita/(:any)']                 = 'play_game/cerita_read/$1';
$route['belajar/cerita']                        = 'play_game/cerita';
$route['belajar/notifikasi/read']               = 'play_game/notif_read';
$route['belajar/notifikasi']                    = 'play_game/notifikasi';
$route['belajar/battle/create']                 = 'play_game/battle_create';
$route['belajar/battle/join']                   = 'play_game/battle_join';
$route['belajar/battle/start']                  = 'play_game/battle_start';
$route['belajar/battle/answer']                 = 'play_game/battle_answer';
$route['belajar/battle/state/(:any)']           = 'play_game/battle_state/$1';
$route['belajar/battle/room/(:any)']            = 'play_game/battle_room/$1';
$route['belajar/battle']                        = 'play_game/battle';
$route['belajar/raport']                        = 'play_game/raport';
$route['belajar/latihan']                       = 'play_game/latihan';
$route['belajar/english-quest/answer']          = 'play_game/english_quest_answer';
$route['belajar/english-quest/sentence']        = 'play_game/english_quest_sentence';
$route['belajar/english-quest/story']           = 'play_game/english_quest_story';
$route['belajar/english-quest/hero/save']       = 'play_game/english_quest_hero_save';
$route['belajar/english-quest/hero']            = 'play_game/english_quest_hero';
$route['belajar/english-quest/quests/claim/(:num)'] = 'play_game/english_quest_claim/$1';
$route['belajar/english-quest/quests']          = 'play_game/english_quest_quests';
$route['belajar/english-quest/review/submit']   = 'play_game/english_quest_review_submit';
$route['belajar/english-quest/review']          = 'play_game/english_quest_review';
$route['belajar/english-quest/dictionary']      = 'play_game/english_quest_dictionary';
$route['belajar/english-quest/equipment']       = 'play_game/english_quest_equipment';
$route['belajar/english-quest/equip/(:num)']    = 'play_game/english_quest_equip/$1';
$route['belajar/english-quest/potion']          = 'play_game/english_quest_potion';
$route['belajar/english-quest/daily']           = 'play_game/english_quest_daily';
$route['belajar/english-quest/report']          = 'play_game/english_quest_report';
$route['belajar/english-quest/reset']           = 'play_game/english_quest_reset';
$route['belajar/english-quest/(:any)']          = 'play_game/english_quest_play/$1';
$route['belajar/english-quest']                 = 'play_game/english_quest';

// Public quiz (no admin auth required)
$route['quiz/practice/(:any)']   = 'quiz_play/practice/$1';
$route['quiz/login']             = 'quiz_play/login';
$route['quiz/login/(:any)']      = 'quiz_play/login/$1';
$route['quiz/do_login']          = 'quiz_play/do_login';
$route['quiz/exam/(:any)']       = 'quiz_play/exam/$1';
$route['quiz/save_answer']       = 'quiz_play/save_answer';
$route['quiz/fraud_event']       = 'quiz_play/fraud_event';
$route['quiz/heartbeat']         = 'quiz_play/heartbeat';
$route['quiz/submit']            = 'quiz_play/submit';
$route['quiz/result/(:num)']     = 'quiz_play/result/$1';
$route['quiz/review/(:num)']     = 'quiz_play/review/$1';

// Rak pembelajaran publik
$route['buku-pelajaran']         = 'public_textbooks/index';
