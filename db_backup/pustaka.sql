/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100432
 Source Host           : localhost:3306
 Source Schema         : pustaka

 Target Server Type    : MySQL
 Target Server Version : 100432
 File Encoding         : 65001

 Date: 29/07/2026 21:07:43
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for audit_log
-- ----------------------------
DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE `audit_log`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `event_code` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `entity_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `old_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `new_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_audit_log_user_id`(`user_id`) USING BTREE,
  INDEX `idx_audit_log_event_code`(`event_code`) USING BTREE,
  INDEX `idx_audit_log_entity`(`entity_type`, `entity_id`) USING BTREE,
  INDEX `idx_audit_log_created_at`(`created_at`) USING BTREE,
  CONSTRAINT `fk_audit_log_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of audit_log
-- ----------------------------
INSERT INTO `audit_log` VALUES (1, 4, 'permission.denied', 'sys_page', 'audit.index', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '{\"action\":\"view\",\"uri\":\"audit\"}', '2026-07-29 08:08:06');
INSERT INTO `audit_log` VALUES (2, 4, 'permission.denied', 'sys_page', 'audit.index', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '{\"action\":\"view\",\"uri\":\"audit\"}', '2026-07-29 08:12:04');
INSERT INTO `audit_log` VALUES (3, 4, 'permission.denied', 'sys_page', 'catalog.sync', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '{\"action\":\"view\",\"uri\":\"catalog\\/sync\"}', '2026-07-29 11:50:53');
INSERT INTO `audit_log` VALUES (4, 1, 'sidebar.reordered', 'sys_menu', 'MAIN', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', '{\"rows\":[{\"id\":\"1\",\"parent_id\":null,\"page_id\":\"1\",\"menu_area\":\"MAIN\",\"menu_key\":\"dashboard\",\"title\":\"Dashboard\",\"icon\":\"ti ti-layout-dashboard\",\"url\":\"admin\",\"sort_order\":\"10\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:00:52\",\"page_code\":\"dashboard.index\",\"page_title\":\"Dashboard\"},{\"id\":\"35\",\"parent_id\":null,\"page_id\":null,\"menu_area\":\"MAIN\",\"menu_key\":\"master-data\",\"title\":\"Data Master\",\"icon\":\"ti ti-database\",\"url\":null,\"sort_order\":\"15\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-29 11:45:36\",\"updated_at\":null,\"page_code\":null,\"page_title\":null},{\"id\":\"36\",\"parent_id\":\"35\",\"page_id\":\"35\",\"menu_area\":\"MAIN\",\"menu_key\":\"master.regions\",\"title\":\"Master Wilayah\",\"icon\":\"ti ti-map-pin-cog\",\"url\":\"regions\",\"sort_order\":\"16\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 11:45:36\",\"updated_at\":null,\"page_code\":\"regions.index\",\"page_title\":\"Master Wilayah\"},{\"id\":\"2\",\"parent_id\":null,\"page_id\":\"2\",\"menu_area\":\"MAIN\",\"menu_key\":\"libraries.gis\",\"title\":\"Perpustakaan GIS\",\"icon\":\"ti ti-map-2\",\"url\":\"libraries\",\"sort_order\":\"20\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"libraries.index\",\"page_title\":\"Perpustakaan GIS\"},{\"id\":\"3\",\"parent_id\":null,\"page_id\":\"3\",\"menu_area\":\"MAIN\",\"menu_key\":\"catalog\",\"title\":\"Katalog\",\"icon\":\"ti ti-books\",\"url\":\"catalog\",\"sort_order\":\"30\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"catalog.index\",\"page_title\":\"Katalog Buku\"},{\"id\":\"4\",\"parent_id\":null,\"page_id\":\"4\",\"menu_area\":\"MAIN\",\"menu_key\":\"members\",\"title\":\"Membership\",\"icon\":\"ti ti-id-badge-2\",\"url\":\"members\",\"sort_order\":\"40\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"members.index\",\"page_title\":\"Membership Digital\"},{\"id\":\"5\",\"parent_id\":null,\"page_id\":\"6\",\"menu_area\":\"MAIN\",\"menu_key\":\"reading-points\",\"title\":\"Pojok Baca\",\"icon\":\"ti ti-current-location\",\"url\":\"reading-points\",\"sort_order\":\"50\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"reading_points.index\",\"page_title\":\"Pojok Baca Digital\"},{\"id\":\"6\",\"parent_id\":null,\"page_id\":\"7\",\"menu_area\":\"MAIN\",\"menu_key\":\"events\",\"title\":\"Event\",\"icon\":\"ti ti-calendar-event\",\"url\":\"events\",\"sort_order\":\"60\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"events.index\",\"page_title\":\"Event Literasi\"},{\"id\":\"7\",\"parent_id\":null,\"page_id\":null,\"menu_area\":\"MAIN\",\"menu_key\":\"system\",\"title\":\"Pengaturan Akses\",\"icon\":\"ti ti-shield-lock\",\"url\":null,\"sort_order\":\"900\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":null,\"page_title\":null},{\"id\":\"8\",\"parent_id\":\"7\",\"page_id\":\"8\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.users\",\"title\":\"User\",\"icon\":\"ti ti-users\",\"url\":\"rbac\\/users\",\"sort_order\":\"910\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":\"auth.users.index\",\"page_title\":\"Manajemen User\"},{\"id\":\"9\",\"parent_id\":\"7\",\"page_id\":\"9\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.roles\",\"title\":\"Role & Permission\",\"icon\":\"ti ti-key\",\"url\":\"rbac\\/roles\",\"sort_order\":\"920\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":\"auth.roles.index\",\"page_title\":\"Role dan Hak Akses\"},{\"id\":\"31\",\"parent_id\":\"7\",\"page_id\":\"31\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.pages\",\"title\":\"Registry Halaman\",\"icon\":\"ti ti-file-settings\",\"url\":\"rbac\\/pages\",\"sort_order\":\"925\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 06:43:15\",\"updated_at\":null,\"page_code\":\"system.pages.index\",\"page_title\":\"Registry Halaman\"},{\"id\":\"10\",\"parent_id\":\"7\",\"page_id\":\"10\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.sidebar\",\"title\":\"Sidebar\",\"icon\":\"ti ti-layout-sidebar\",\"url\":\"rbac\\/sidebar\",\"sort_order\":\"930\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":\"system.sidebar.manage\",\"page_title\":\"Manajemen Sidebar\"},{\"id\":\"33\",\"parent_id\":\"7\",\"page_id\":\"33\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.audit\",\"title\":\"Audit Log\",\"icon\":\"ti ti-history\",\"url\":\"audit\",\"sort_order\":\"940\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 07:56:57\",\"updated_at\":null,\"page_code\":\"audit.index\",\"page_title\":\"Audit Log\"}]}', '{\"rows\":[{\"id\":\"1\",\"parent_id\":null,\"page_id\":\"1\",\"menu_area\":\"MAIN\",\"menu_key\":\"dashboard\",\"title\":\"Dashboard\",\"icon\":\"ti ti-layout-dashboard\",\"url\":\"admin\",\"sort_order\":\"10\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:00:52\",\"page_code\":\"dashboard.index\",\"page_title\":\"Dashboard\"},{\"id\":\"35\",\"parent_id\":null,\"page_id\":null,\"menu_area\":\"MAIN\",\"menu_key\":\"master-data\",\"title\":\"Data Master\",\"icon\":\"ti ti-database\",\"url\":null,\"sort_order\":\"15\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-29 11:45:36\",\"updated_at\":null,\"page_code\":null,\"page_title\":null},{\"id\":\"36\",\"parent_id\":\"35\",\"page_id\":\"35\",\"menu_area\":\"MAIN\",\"menu_key\":\"master.regions\",\"title\":\"Master Wilayah\",\"icon\":\"ti ti-map-pin-cog\",\"url\":\"regions\",\"sort_order\":\"16\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 11:45:36\",\"updated_at\":null,\"page_code\":\"regions.index\",\"page_title\":\"Master Wilayah\"},{\"id\":\"2\",\"parent_id\":null,\"page_id\":\"2\",\"menu_area\":\"MAIN\",\"menu_key\":\"libraries.gis\",\"title\":\"Perpustakaan GIS\",\"icon\":\"ti ti-map-2\",\"url\":\"libraries\",\"sort_order\":\"20\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"libraries.index\",\"page_title\":\"Perpustakaan GIS\"},{\"id\":\"3\",\"parent_id\":null,\"page_id\":\"3\",\"menu_area\":\"MAIN\",\"menu_key\":\"catalog\",\"title\":\"Katalog\",\"icon\":\"ti ti-books\",\"url\":\"catalog\",\"sort_order\":\"30\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"catalog.index\",\"page_title\":\"Katalog Buku\"},{\"id\":\"4\",\"parent_id\":null,\"page_id\":\"4\",\"menu_area\":\"MAIN\",\"menu_key\":\"members\",\"title\":\"Membership\",\"icon\":\"ti ti-id-badge-2\",\"url\":\"members\",\"sort_order\":\"40\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"members.index\",\"page_title\":\"Membership Digital\"},{\"id\":\"5\",\"parent_id\":null,\"page_id\":\"6\",\"menu_area\":\"MAIN\",\"menu_key\":\"reading-points\",\"title\":\"Pojok Baca\",\"icon\":\"ti ti-current-location\",\"url\":\"reading-points\",\"sort_order\":\"50\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"reading_points.index\",\"page_title\":\"Pojok Baca Digital\"},{\"id\":\"6\",\"parent_id\":null,\"page_id\":\"7\",\"menu_area\":\"MAIN\",\"menu_key\":\"events\",\"title\":\"Event\",\"icon\":\"ti ti-calendar-event\",\"url\":\"events\",\"sort_order\":\"60\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"events.index\",\"page_title\":\"Event Literasi\"},{\"id\":\"7\",\"parent_id\":null,\"page_id\":null,\"menu_area\":\"MAIN\",\"menu_key\":\"system\",\"title\":\"Pengaturan Akses\",\"icon\":\"ti ti-shield-lock\",\"url\":null,\"sort_order\":\"900\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":null,\"page_title\":null},{\"id\":\"8\",\"parent_id\":\"7\",\"page_id\":\"8\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.users\",\"title\":\"User\",\"icon\":\"ti ti-users\",\"url\":\"rbac\\/users\",\"sort_order\":\"910\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":\"auth.users.index\",\"page_title\":\"Manajemen User\"},{\"id\":\"9\",\"parent_id\":\"7\",\"page_id\":\"9\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.roles\",\"title\":\"Role & Permission\",\"icon\":\"ti ti-key\",\"url\":\"rbac\\/roles\",\"sort_order\":\"920\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":\"auth.roles.index\",\"page_title\":\"Role dan Hak Akses\"},{\"id\":\"31\",\"parent_id\":\"7\",\"page_id\":\"31\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.pages\",\"title\":\"Registry Halaman\",\"icon\":\"ti ti-file-settings\",\"url\":\"rbac\\/pages\",\"sort_order\":\"925\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 06:43:15\",\"updated_at\":null,\"page_code\":\"system.pages.index\",\"page_title\":\"Registry Halaman\"},{\"id\":\"10\",\"parent_id\":\"7\",\"page_id\":\"10\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.sidebar\",\"title\":\"Sidebar\",\"icon\":\"ti ti-layout-sidebar\",\"url\":\"rbac\\/sidebar\",\"sort_order\":\"930\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":\"system.sidebar.manage\",\"page_title\":\"Manajemen Sidebar\"},{\"id\":\"33\",\"parent_id\":\"7\",\"page_id\":\"33\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.audit\",\"title\":\"Audit Log\",\"icon\":\"ti ti-history\",\"url\":\"audit\",\"sort_order\":\"940\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 07:56:57\",\"updated_at\":null,\"page_code\":\"audit.index\",\"page_title\":\"Audit Log\"}]}', '2026-07-29 20:34:50');
INSERT INTO `audit_log` VALUES (5, 1, 'sidebar.reordered', 'sys_menu', 'MAIN', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', '{\"rows\":[{\"id\":\"1\",\"parent_id\":null,\"page_id\":\"1\",\"menu_area\":\"MAIN\",\"menu_key\":\"dashboard\",\"title\":\"Dashboard\",\"icon\":\"ti ti-layout-dashboard\",\"url\":\"admin\",\"sort_order\":\"10\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:00:52\",\"page_code\":\"dashboard.index\",\"page_title\":\"Dashboard\"},{\"id\":\"35\",\"parent_id\":null,\"page_id\":null,\"menu_area\":\"MAIN\",\"menu_key\":\"master-data\",\"title\":\"Data Master\",\"icon\":\"ti ti-database\",\"url\":null,\"sort_order\":\"15\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-29 11:45:36\",\"updated_at\":null,\"page_code\":null,\"page_title\":null},{\"id\":\"36\",\"parent_id\":\"35\",\"page_id\":\"35\",\"menu_area\":\"MAIN\",\"menu_key\":\"master.regions\",\"title\":\"Master Wilayah\",\"icon\":\"ti ti-map-pin-cog\",\"url\":\"regions\",\"sort_order\":\"16\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 11:45:36\",\"updated_at\":null,\"page_code\":\"regions.index\",\"page_title\":\"Master Wilayah\"},{\"id\":\"2\",\"parent_id\":null,\"page_id\":\"2\",\"menu_area\":\"MAIN\",\"menu_key\":\"libraries.gis\",\"title\":\"Perpustakaan GIS\",\"icon\":\"ti ti-map-2\",\"url\":\"libraries\",\"sort_order\":\"20\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"libraries.index\",\"page_title\":\"Perpustakaan GIS\"},{\"id\":\"3\",\"parent_id\":null,\"page_id\":\"3\",\"menu_area\":\"MAIN\",\"menu_key\":\"catalog\",\"title\":\"Katalog\",\"icon\":\"ti ti-books\",\"url\":\"catalog\",\"sort_order\":\"30\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"catalog.index\",\"page_title\":\"Katalog Buku\"},{\"id\":\"4\",\"parent_id\":null,\"page_id\":\"4\",\"menu_area\":\"MAIN\",\"menu_key\":\"members\",\"title\":\"Membership\",\"icon\":\"ti ti-id-badge-2\",\"url\":\"members\",\"sort_order\":\"40\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"members.index\",\"page_title\":\"Membership Digital\"},{\"id\":\"5\",\"parent_id\":null,\"page_id\":\"6\",\"menu_area\":\"MAIN\",\"menu_key\":\"reading-points\",\"title\":\"Pojok Baca\",\"icon\":\"ti ti-current-location\",\"url\":\"reading-points\",\"sort_order\":\"50\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"reading_points.index\",\"page_title\":\"Pojok Baca Digital\"},{\"id\":\"6\",\"parent_id\":null,\"page_id\":\"7\",\"menu_area\":\"MAIN\",\"menu_key\":\"events\",\"title\":\"Event\",\"icon\":\"ti ti-calendar-event\",\"url\":\"events\",\"sort_order\":\"60\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"events.index\",\"page_title\":\"Event Literasi\"},{\"id\":\"7\",\"parent_id\":null,\"page_id\":null,\"menu_area\":\"MAIN\",\"menu_key\":\"system\",\"title\":\"Pengaturan Akses\",\"icon\":\"ti ti-shield-lock\",\"url\":null,\"sort_order\":\"900\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":null,\"page_title\":null},{\"id\":\"8\",\"parent_id\":\"7\",\"page_id\":\"8\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.users\",\"title\":\"User\",\"icon\":\"ti ti-users\",\"url\":\"rbac\\/users\",\"sort_order\":\"910\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":\"auth.users.index\",\"page_title\":\"Manajemen User\"},{\"id\":\"9\",\"parent_id\":\"7\",\"page_id\":\"9\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.roles\",\"title\":\"Role & Permission\",\"icon\":\"ti ti-key\",\"url\":\"rbac\\/roles\",\"sort_order\":\"920\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":\"auth.roles.index\",\"page_title\":\"Role dan Hak Akses\"},{\"id\":\"31\",\"parent_id\":\"7\",\"page_id\":\"31\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.pages\",\"title\":\"Registry Halaman\",\"icon\":\"ti ti-file-settings\",\"url\":\"rbac\\/pages\",\"sort_order\":\"925\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 06:43:15\",\"updated_at\":null,\"page_code\":\"system.pages.index\",\"page_title\":\"Registry Halaman\"},{\"id\":\"10\",\"parent_id\":\"7\",\"page_id\":\"10\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.sidebar\",\"title\":\"Sidebar\",\"icon\":\"ti ti-layout-sidebar\",\"url\":\"rbac\\/sidebar\",\"sort_order\":\"930\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:43:15\",\"page_code\":\"system.sidebar.manage\",\"page_title\":\"Manajemen Sidebar\"},{\"id\":\"33\",\"parent_id\":\"7\",\"page_id\":\"33\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.audit\",\"title\":\"Audit Log\",\"icon\":\"ti ti-history\",\"url\":\"audit\",\"sort_order\":\"940\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 07:56:57\",\"updated_at\":null,\"page_code\":\"audit.index\",\"page_title\":\"Audit Log\"}]}', '{\"rows\":[{\"id\":\"1\",\"parent_id\":null,\"page_id\":\"1\",\"menu_area\":\"MAIN\",\"menu_key\":\"dashboard\",\"title\":\"Dashboard\",\"icon\":\"ti ti-layout-dashboard\",\"url\":\"admin\",\"sort_order\":\"10\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 06:00:52\",\"page_code\":\"dashboard.index\",\"page_title\":\"Dashboard\"},{\"id\":\"36\",\"parent_id\":\"35\",\"page_id\":\"35\",\"menu_area\":\"MAIN\",\"menu_key\":\"master.regions\",\"title\":\"Master Wilayah\",\"icon\":\"ti ti-map-pin-cog\",\"url\":\"regions\",\"sort_order\":\"10\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 11:45:36\",\"updated_at\":\"2026-07-29 20:36:48\",\"page_code\":\"regions.index\",\"page_title\":\"Master Wilayah\"},{\"id\":\"8\",\"parent_id\":\"7\",\"page_id\":\"8\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.users\",\"title\":\"User\",\"icon\":\"ti ti-users\",\"url\":\"rbac\\/users\",\"sort_order\":\"10\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 20:36:48\",\"page_code\":\"auth.users.index\",\"page_title\":\"Manajemen User\"},{\"id\":\"2\",\"parent_id\":null,\"page_id\":\"2\",\"menu_area\":\"MAIN\",\"menu_key\":\"libraries.gis\",\"title\":\"Perpustakaan GIS\",\"icon\":\"ti ti-map-2\",\"url\":\"libraries\",\"sort_order\":\"20\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"libraries.index\",\"page_title\":\"Perpustakaan GIS\"},{\"id\":\"9\",\"parent_id\":\"7\",\"page_id\":\"9\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.roles\",\"title\":\"Role & Permission\",\"icon\":\"ti ti-key\",\"url\":\"rbac\\/roles\",\"sort_order\":\"20\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 20:36:48\",\"page_code\":\"auth.roles.index\",\"page_title\":\"Role dan Hak Akses\"},{\"id\":\"3\",\"parent_id\":null,\"page_id\":\"3\",\"menu_area\":\"MAIN\",\"menu_key\":\"catalog\",\"title\":\"Katalog\",\"icon\":\"ti ti-books\",\"url\":\"catalog\",\"sort_order\":\"30\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"catalog.index\",\"page_title\":\"Katalog Buku\"},{\"id\":\"31\",\"parent_id\":\"7\",\"page_id\":\"31\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.pages\",\"title\":\"Registry Halaman\",\"icon\":\"ti ti-file-settings\",\"url\":\"rbac\\/pages\",\"sort_order\":\"30\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 06:43:15\",\"updated_at\":\"2026-07-29 20:36:48\",\"page_code\":\"system.pages.index\",\"page_title\":\"Registry Halaman\"},{\"id\":\"4\",\"parent_id\":null,\"page_id\":\"4\",\"menu_area\":\"MAIN\",\"menu_key\":\"members\",\"title\":\"Membership\",\"icon\":\"ti ti-id-badge-2\",\"url\":\"members\",\"sort_order\":\"40\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"members.index\",\"page_title\":\"Membership Digital\"},{\"id\":\"10\",\"parent_id\":\"7\",\"page_id\":\"10\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.sidebar\",\"title\":\"Sidebar\",\"icon\":\"ti ti-layout-sidebar\",\"url\":\"rbac\\/sidebar\",\"sort_order\":\"40\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 20:36:48\",\"page_code\":\"system.sidebar.manage\",\"page_title\":\"Manajemen Sidebar\"},{\"id\":\"33\",\"parent_id\":\"7\",\"page_id\":\"33\",\"menu_area\":\"MAIN\",\"menu_key\":\"system.audit\",\"title\":\"Audit Log\",\"icon\":\"ti ti-history\",\"url\":\"audit\",\"sort_order\":\"50\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-29 07:56:57\",\"updated_at\":\"2026-07-29 20:36:48\",\"page_code\":\"audit.index\",\"page_title\":\"Audit Log\"},{\"id\":\"5\",\"parent_id\":null,\"page_id\":\"6\",\"menu_area\":\"MAIN\",\"menu_key\":\"reading-points\",\"title\":\"Pojok Baca\",\"icon\":\"ti ti-current-location\",\"url\":\"reading-points\",\"sort_order\":\"50\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"reading_points.index\",\"page_title\":\"Pojok Baca Digital\"},{\"id\":\"6\",\"parent_id\":null,\"page_id\":\"7\",\"menu_area\":\"MAIN\",\"menu_key\":\"events\",\"title\":\"Event\",\"icon\":\"ti ti-calendar-event\",\"url\":\"events\",\"sort_order\":\"60\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"0\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":null,\"page_code\":\"events.index\",\"page_title\":\"Event Literasi\"},{\"id\":\"35\",\"parent_id\":null,\"page_id\":null,\"menu_area\":\"MAIN\",\"menu_key\":\"master-data\",\"title\":\"Data Master\",\"icon\":\"ti ti-database\",\"url\":null,\"sort_order\":\"70\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-29 11:45:36\",\"updated_at\":\"2026-07-29 20:36:48\",\"page_code\":null,\"page_title\":null},{\"id\":\"7\",\"parent_id\":null,\"page_id\":null,\"menu_area\":\"MAIN\",\"menu_key\":\"system\",\"title\":\"Pengaturan Akses\",\"icon\":\"ti ti-shield-lock\",\"url\":null,\"sort_order\":\"80\",\"is_visible\":\"1\",\"is_active\":\"1\",\"is_locked\":\"1\",\"created_at\":\"2026-07-28 22:09:29\",\"updated_at\":\"2026-07-29 20:36:48\",\"page_code\":null,\"page_title\":null}]}', '2026-07-29 20:36:48');

-- ----------------------------
-- Table structure for auth_role
-- ----------------------------
DROP TABLE IF EXISTS `auth_role`;
CREATE TABLE `auth_role`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `level` smallint(5) UNSIGNED NOT NULL DEFAULT 100,
  `scope_type` enum('global','library','self') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'self',
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_auth_role_code`(`code`) USING BTREE,
  INDEX `idx_auth_role_level`(`level`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of auth_role
-- ----------------------------
INSERT INTO `auth_role` VALUES (1, 'SUPERADMIN', 'Superadmin', 'Akses penuh seluruh sistem dan konfigurasi.', 1, 'global', 1, 1, '2026-07-28 22:09:29', NULL);
INSERT INTO `auth_role` VALUES (2, 'ADMIN', 'Admin', 'Pengelola operasional perpustakaan atau unit yang ditugaskan.', 20, 'library', 1, 1, '2026-07-28 22:09:29', NULL);
INSERT INTO `auth_role` VALUES (3, 'USER', 'User/Pemustaka', 'Pemustaka/member aplikasi digital.', 100, 'self', 1, 1, '2026-07-28 22:09:29', NULL);

-- ----------------------------
-- Table structure for auth_role_permission
-- ----------------------------
DROP TABLE IF EXISTS `auth_role_permission`;
CREATE TABLE `auth_role_permission`  (
  `role_id` int(10) UNSIGNED NOT NULL,
  `page_id` int(10) UNSIGNED NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `can_export` tinyint(1) NOT NULL DEFAULT 0,
  `can_approve` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`role_id`, `page_id`) USING BTREE,
  INDEX `idx_auth_role_permission_page_id`(`page_id`) USING BTREE,
  CONSTRAINT `fk_auth_role_permission_page` FOREIGN KEY (`page_id`) REFERENCES `sys_page` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_auth_role_permission_role` FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of auth_role_permission
-- ----------------------------
INSERT INTO `auth_role_permission` VALUES (1, 1, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 2, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 3, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 4, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 5, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 6, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 7, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 8, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 9, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 10, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 31, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 33, 1, 0, 0, 0, 1, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 35, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 36, 1, 1, 1, 1, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (1, 39, 1, 1, 1, 0, 1, 1, NULL);
INSERT INTO `auth_role_permission` VALUES (2, 1, 1, 1, 1, 0, 1, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (2, 2, 1, 1, 1, 0, 1, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (2, 3, 1, 1, 1, 0, 1, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (2, 4, 1, 1, 1, 0, 1, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (2, 5, 1, 1, 1, 0, 1, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (2, 6, 1, 1, 1, 0, 1, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (2, 7, 1, 1, 1, 0, 1, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (2, 35, 1, 0, 0, 0, 1, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (3, 1, 1, 0, 0, 0, 0, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (3, 3, 1, 0, 0, 0, 0, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (3, 4, 1, 0, 0, 0, 0, 0, NULL);
INSERT INTO `auth_role_permission` VALUES (3, 7, 1, 0, 0, 0, 0, 0, NULL);

-- ----------------------------
-- Table structure for auth_session_log
-- ----------------------------
DROP TABLE IF EXISTS `auth_session_log`;
CREATE TABLE `auth_session_log`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `event_type` enum('login_success','login_failed','logout','password_changed','permission_denied') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username_attempt` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `meta_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_auth_session_log_user_id`(`user_id`) USING BTREE,
  INDEX `idx_auth_session_log_event_type`(`event_type`) USING BTREE,
  INDEX `idx_auth_session_log_created_at`(`created_at`) USING BTREE,
  CONSTRAINT `fk_auth_session_log_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 41 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of auth_session_log
-- ----------------------------
INSERT INTO `auth_session_log` VALUES (1, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-28 22:58:37');
INSERT INTO `auth_session_log` VALUES (2, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-28 23:00:29');
INSERT INTO `auth_session_log` VALUES (3, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-28 23:00:44');
INSERT INTO `auth_session_log` VALUES (4, 1, 'logout', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-28 23:00:44');
INSERT INTO `auth_session_log` VALUES (5, 1, 'login_success', 'superadmin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', NULL, '2026-07-29 05:22:45');
INSERT INTO `auth_session_log` VALUES (6, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 05:31:24');
INSERT INTO `auth_session_log` VALUES (7, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 05:31:48');
INSERT INTO `auth_session_log` VALUES (8, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 05:33:46');
INSERT INTO `auth_session_log` VALUES (9, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:02:33');
INSERT INTO `auth_session_log` VALUES (10, 4, 'login_success', 'admin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:02:34');
INSERT INTO `auth_session_log` VALUES (11, 5, 'login_success', 'pemustaka', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:02:34');
INSERT INTO `auth_session_log` VALUES (12, 4, 'login_success', 'admin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:06:13');
INSERT INTO `auth_session_log` VALUES (13, 5, 'login_success', 'pemustaka', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:06:13');
INSERT INTO `auth_session_log` VALUES (14, 5, 'login_success', 'pemustaka', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL, '2026-07-29 06:21:36');
INSERT INTO `auth_session_log` VALUES (15, 4, 'login_success', 'admin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:25:50');
INSERT INTO `auth_session_log` VALUES (16, 4, 'login_success', 'admin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:28:57');
INSERT INTO `auth_session_log` VALUES (17, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:50:28');
INSERT INTO `auth_session_log` VALUES (18, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:52:36');
INSERT INTO `auth_session_log` VALUES (19, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 06:58:12');
INSERT INTO `auth_session_log` VALUES (20, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 08:07:41');
INSERT INTO `auth_session_log` VALUES (21, 4, 'login_success', 'admin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 08:07:41');
INSERT INTO `auth_session_log` VALUES (22, 4, 'login_success', 'admin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 08:08:06');
INSERT INTO `auth_session_log` VALUES (23, 4, 'login_success', 'admin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 08:12:03');
INSERT INTO `auth_session_log` VALUES (24, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 08:12:03');
INSERT INTO `auth_session_log` VALUES (25, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 09:00:55');
INSERT INTO `auth_session_log` VALUES (26, 1, 'login_success', 'superadmin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', NULL, '2026-07-29 11:39:04');
INSERT INTO `auth_session_log` VALUES (27, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 11:50:30');
INSERT INTO `auth_session_log` VALUES (28, 4, 'login_success', 'admin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 11:50:53');
INSERT INTO `auth_session_log` VALUES (29, 1, 'login_success', 'superadmin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', NULL, '2026-07-29 13:41:26');
INSERT INTO `auth_session_log` VALUES (30, 1, 'login_success', 'superadmin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', NULL, '2026-07-29 19:49:05');
INSERT INTO `auth_session_log` VALUES (31, 5, 'login_success', 'pemustaka', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL, '2026-07-29 19:58:29');
INSERT INTO `auth_session_log` VALUES (32, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 20:16:13');
INSERT INTO `auth_session_log` VALUES (33, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 20:17:32');
INSERT INTO `auth_session_log` VALUES (34, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 20:34:30');
INSERT INTO `auth_session_log` VALUES (35, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 20:34:50');
INSERT INTO `auth_session_log` VALUES (36, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 20:35:04');
INSERT INTO `auth_session_log` VALUES (37, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 20:53:35');
INSERT INTO `auth_session_log` VALUES (38, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 20:54:17');
INSERT INTO `auth_session_log` VALUES (39, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 20:58:35');
INSERT INTO `auth_session_log` VALUES (40, 1, 'login_success', 'superadmin', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8894', NULL, '2026-07-29 20:58:56');

-- ----------------------------
-- Table structure for auth_user
-- ----------------------------
DROP TABLE IF EXISTS `auth_user`;
CREATE TABLE `auth_user`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_source_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `library_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `status` enum('active','inactive','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `force_password_change` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_auth_user_username`(`username`) USING BTREE,
  UNIQUE INDEX `uq_auth_user_email`(`email`) USING BTREE,
  INDEX `idx_auth_user_library_id`(`library_id`) USING BTREE,
  INDEX `idx_auth_user_status`(`status`) USING BTREE,
  INDEX `idx_auth_user_library_status`(`library_id`, `status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of auth_user
-- ----------------------------
INSERT INTO `auth_user` VALUES (1, 'superadmin', 'superadmin@pustaka.local', '$2y$10$TWH9LG5tA9N1Ap0MNTUCtOcimEFZTR1LqQFjF8ePID/CIc7V9AA.e', 'Superadmin Pustaka', NULL, NULL, NULL, 'active', 1, '2026-07-29 15:58:56', '2026-07-28 22:09:29', '2026-07-29 20:58:56');
INSERT INTO `auth_user` VALUES (4, 'admin', 'admin@pustaka.local', '$2y$10$KUmmwFVXyIUIK6mEbtLtbOvHP.ryk4iuNhtt/KJgdzCepjXApPTUW', 'Admin Pustaka', NULL, NULL, NULL, 'active', 1, '2026-07-29 06:50:53', '2026-07-29 06:00:52', '2026-07-29 11:50:53');
INSERT INTO `auth_user` VALUES (5, 'pemustaka', 'pemustaka@pustaka.local', '$2y$10$KUmmwFVXyIUIK6mEbtLtbOvHP.ryk4iuNhtt/KJgdzCepjXApPTUW', 'Pemustaka Demo', NULL, NULL, NULL, 'active', 1, '2026-07-29 14:58:29', '2026-07-29 06:00:52', '2026-07-29 19:58:29');

-- ----------------------------
-- Table structure for auth_user_permission_override
-- ----------------------------
DROP TABLE IF EXISTS `auth_user_permission_override`;
CREATE TABLE `auth_user_permission_override`  (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `page_id` int(10) UNSIGNED NOT NULL,
  `can_view` tinyint(1) NULL DEFAULT NULL,
  `can_create` tinyint(1) NULL DEFAULT NULL,
  `can_edit` tinyint(1) NULL DEFAULT NULL,
  `can_delete` tinyint(1) NULL DEFAULT NULL,
  `can_export` tinyint(1) NULL DEFAULT NULL,
  `can_approve` tinyint(1) NULL DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`user_id`, `page_id`) USING BTREE,
  INDEX `idx_auth_user_permission_override_page_id`(`page_id`) USING BTREE,
  CONSTRAINT `fk_auth_user_permission_override_page` FOREIGN KEY (`page_id`) REFERENCES `sys_page` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_auth_user_permission_override_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for auth_user_role
-- ----------------------------
DROP TABLE IF EXISTS `auth_user_role`;
CREATE TABLE `auth_user_role`  (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`user_id`, `role_id`) USING BTREE,
  INDEX `idx_auth_user_role_role_id`(`role_id`) USING BTREE,
  CONSTRAINT `fk_auth_user_role_role` FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_auth_user_role_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of auth_user_role
-- ----------------------------
INSERT INTO `auth_user_role` VALUES (1, 1, '2026-07-28 22:09:29');
INSERT INTO `auth_user_role` VALUES (4, 2, '2026-07-29 06:00:52');
INSERT INTO `auth_user_role` VALUES (5, 3, '2026-07-29 06:00:52');

-- ----------------------------
-- Table structure for book_authors
-- ----------------------------
DROP TABLE IF EXISTS `book_authors`;
CREATE TABLE `book_authors`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 100,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_book_authors_book`(`book_id`) USING BTREE,
  INDEX `idx_book_authors_name`(`name`) USING BTREE,
  CONSTRAINT `fk_book_authors_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for book_items
-- ----------------------------
DROP TABLE IF EXISTS `book_items`;
CREATE TABLE `book_items`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) UNSIGNED NOT NULL,
  `library_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `item_code` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `barcode` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `call_number` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `location_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `collection_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `inventory_number` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('available','loaned','missing','damaged','unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_book_items_source`(`source_system`, `source_id`) USING BTREE,
  INDEX `idx_book_items_book`(`book_id`) USING BTREE,
  INDEX `idx_book_items_library`(`library_id`) USING BTREE,
  INDEX `idx_book_items_status`(`status`) USING BTREE,
  CONSTRAINT `fk_book_items_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_book_items_library` FOREIGN KEY (`library_id`) REFERENCES `libraries` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for book_subjects
-- ----------------------------
DROP TABLE IF EXISTS `book_subjects`;
CREATE TABLE `book_subjects`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_book_subjects_book`(`book_id`) USING BTREE,
  INDEX `idx_book_subjects_subject`(`subject`) USING BTREE,
  CONSTRAINT `fk_book_subjects_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for books
-- ----------------------------
DROP TABLE IF EXISTS `books`;
CREATE TABLE `books`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `statement_responsibility` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `edition` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `publish_place` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `publisher` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `publish_year` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `isbn` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `classification` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `call_number` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `language` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `physical_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `abstract` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `cover_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('draft','published','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_books_source`(`source_system`, `source_id`) USING BTREE,
  INDEX `idx_books_title`(`title`) USING BTREE,
  INDEX `idx_books_isbn`(`isbn`) USING BTREE,
  INDEX `idx_books_classification`(`classification`) USING BTREE,
  INDEX `idx_books_status`(`status`) USING BTREE,
  INDEX `fk_books_created_by`(`created_by`) USING BTREE,
  INDEX `fk_books_updated_by`(`updated_by`) USING BTREE,
  CONSTRAINT `fk_books_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_books_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for catalog_sync_maps
-- ----------------------------
DROP TABLE IF EXISTS `catalog_sync_maps`;
CREATE TABLE `catalog_sync_maps`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` enum('book','book_item','digital_asset','author','subject') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inlislite_v3',
  `source_table` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` bigint(20) UNSIGNED NOT NULL,
  `last_sync_run_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_catalog_sync_maps_source`(`entity_type`, `source_system`, `source_table`, `source_id`) USING BTREE,
  INDEX `idx_catalog_sync_maps_target`(`entity_type`, `target_id`) USING BTREE,
  INDEX `idx_catalog_sync_maps_run`(`last_sync_run_id`) USING BTREE,
  CONSTRAINT `fk_catalog_sync_maps_run` FOREIGN KEY (`last_sync_run_id`) REFERENCES `catalog_sync_runs` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for catalog_sync_runs
-- ----------------------------
DROP TABLE IF EXISTS `catalog_sync_runs`;
CREATE TABLE `catalog_sync_runs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_database` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inlislite_v3',
  `source_table` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sync_type` enum('manual','scheduled','dry_run') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `status` enum('queued','running','success','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `started_at` datetime(0) NULL DEFAULT NULL,
  `finished_at` datetime(0) NULL DEFAULT NULL,
  `total_source` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_inserted` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_updated` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_failed` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_catalog_sync_runs_status`(`status`) USING BTREE,
  INDEX `idx_catalog_sync_runs_created_at`(`created_at`) USING BTREE,
  INDEX `fk_catalog_sync_runs_created_by`(`created_by`) USING BTREE,
  CONSTRAINT `fk_catalog_sync_runs_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for ci_sessions
-- ----------------------------
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions`  (
  `id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ci_sessions_timestamp`(`timestamp`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for digital_assets
-- ----------------------------
DROP TABLE IF EXISTS `digital_assets`;
CREATE TABLE `digital_assets`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) UNSIGNED NOT NULL,
  `file_original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `mime_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `access_policy` enum('online_only','download_allowed','location_only','member_only','internal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `is_downloadable` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('draft','active','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `uploaded_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_digital_assets_book`(`book_id`) USING BTREE,
  INDEX `idx_digital_assets_policy`(`access_policy`) USING BTREE,
  INDEX `idx_digital_assets_status`(`status`) USING BTREE,
  INDEX `fk_digital_assets_uploaded_by`(`uploaded_by`) USING BTREE,
  CONSTRAINT `fk_digital_assets_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_digital_assets_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for libraries
-- ----------------------------
DROP TABLE IF EXISTS `libraries`;
CREATE TABLE `libraries`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `library_type_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `manager_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `district_id` int(10) UNSIGNED NULL DEFAULT NULL,
  `village_id` int(10) UNSIGNED NULL DEFAULT NULL,
  `district` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `village` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `website` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `opening_hours` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `latitude` decimal(10, 7) NOT NULL,
  `longitude` decimal(10, 7) NOT NULL,
  `service_radius_meters` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `facilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `status` enum('active','inactive','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `verified_at` datetime(0) NULL DEFAULT NULL,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_libraries_code`(`code`) USING BTREE,
  INDEX `idx_libraries_type`(`library_type_id`) USING BTREE,
  INDEX `idx_libraries_status`(`status`) USING BTREE,
  INDEX `idx_libraries_district_village`(`district`, `village`) USING BTREE,
  INDEX `idx_libraries_coordinate`(`latitude`, `longitude`) USING BTREE,
  INDEX `idx_libraries_source`(`source_system`, `source_id`) USING BTREE,
  INDEX `fk_libraries_created_by`(`created_by`) USING BTREE,
  INDEX `fk_libraries_updated_by`(`updated_by`) USING BTREE,
  INDEX `idx_libraries_district_id`(`district_id`) USING BTREE,
  INDEX `idx_libraries_village_id`(`village_id`) USING BTREE,
  INDEX `idx_libraries_verified`(`is_verified`) USING BTREE,
  CONSTRAINT `fk_libraries_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_libraries_type` FOREIGN KEY (`library_type_id`) REFERENCES `library_types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_libraries_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for library_photos
-- ----------------------------
DROP TABLE IF EXISTS `library_photos`;
CREATE TABLE `library_photos`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `library_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `is_cover` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_library_photos_library`(`library_id`) USING BTREE,
  INDEX `idx_library_photos_cover`(`library_id`, `is_cover`) USING BTREE,
  INDEX `fk_library_photos_uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `idx_library_photos_deleted`(`deleted_at`) USING BTREE,
  CONSTRAINT `fk_library_photos_library` FOREIGN KEY (`library_id`) REFERENCES `libraries` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_library_photos_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for library_types
-- ----------------------------
DROP TABLE IF EXISTS `library_types`;
CREATE TABLE `library_types`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `marker_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0b6b86',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_library_types_code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of library_types
-- ----------------------------
INSERT INTO `library_types` VALUES (1, 'perpusda', 'Perpustakaan Daerah', 'Perpustakaan daerah/kabupaten sebagai simpul utama layanan.', '#0b6b86', 1, '2026-07-29 05:26:06', NULL);
INSERT INTO `library_types` VALUES (2, 'sekolah', 'Perpustakaan Sekolah', 'Perpustakaan sekolah SD, SMP, SMA/SMK, dan sederajat.', '#2f8f66', 1, '2026-07-29 05:26:06', NULL);
INSERT INTO `library_types` VALUES (3, 'desa', 'Perpustakaan Desa', 'Perpustakaan desa/kelurahan dan taman baca lokal.', '#c58a12', 1, '2026-07-29 05:26:06', NULL);
INSERT INTO `library_types` VALUES (4, 'swasta', 'Perpustakaan Swasta', 'Perpustakaan swasta atau institusi non-pemerintah.', '#4263eb', 1, '2026-07-29 05:26:06', NULL);
INSERT INTO `library_types` VALUES (5, 'komunitas', 'Komunitas Literasi', 'Komunitas, TBM, atau ruang baca masyarakat.', '#ae3ec9', 1, '2026-07-29 05:26:06', NULL);
INSERT INTO `library_types` VALUES (6, 'mitra', 'Mitra Pojok Baca', 'Lokasi mitra untuk pojok baca digital dan layanan kolaborasi.', '#d9480f', 1, '2026-07-29 05:26:06', NULL);

-- ----------------------------
-- Table structure for member_sync_runs
-- ----------------------------
DROP TABLE IF EXISTS `member_sync_runs`;
CREATE TABLE `member_sync_runs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_database` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inlislite_v3',
  `source_table` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sync_type` enum('manual','scheduled','dry_run') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `status` enum('queued','running','success','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `started_at` datetime(0) NULL DEFAULT NULL,
  `finished_at` datetime(0) NULL DEFAULT NULL,
  `total_source` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_inserted` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_updated` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_users_created` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_failed` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_member_sync_runs_status`(`status`) USING BTREE,
  INDEX `idx_member_sync_runs_created_at`(`created_at`) USING BTREE,
  INDEX `fk_member_sync_runs_created_by`(`created_by`) USING BTREE,
  CONSTRAINT `fk_member_sync_runs_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for members
-- ----------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auth_user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_no` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `full_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_type` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `identity_number` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `gender` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `birth_place` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `birth_date` date NULL DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `district` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `village` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `education` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `occupation` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('active','inactive','blocked','expired','unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `registered_at` datetime(0) NULL DEFAULT NULL,
  `expired_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_members_source`(`source_system`, `source_id`) USING BTREE,
  UNIQUE INDEX `uq_members_member_no`(`member_no`) USING BTREE,
  INDEX `idx_members_auth_user`(`auth_user_id`) USING BTREE,
  INDEX `idx_members_name`(`full_name`) USING BTREE,
  INDEX `idx_members_status`(`status`) USING BTREE,
  CONSTRAINT `fk_members_auth_user` FOREIGN KEY (`auth_user_id`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for ref_districts
-- ----------------------------
DROP TABLE IF EXISTS `ref_districts`;
CREATE TABLE `ref_districts`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `province_code` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '33',
  `regency_code` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '17',
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_ref_districts_code`(`code`) USING BTREE,
  UNIQUE INDEX `uq_ref_districts_name`(`name`) USING BTREE,
  UNIQUE INDEX `uq_ref_districts_full_code`(`full_code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 43 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ref_districts
-- ----------------------------
INSERT INTO `ref_districts` VALUES (1, '33', '17', '01', '33.17.01', 'Sumber', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (2, '33', '17', '02', '33.17.02', 'Bulu', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (3, '33', '17', '03', '33.17.03', 'Gunem', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (4, '33', '17', '04', '33.17.04', 'Sale', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (5, '33', '17', '05', '33.17.05', 'Sarang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (6, '33', '17', '06', '33.17.06', 'Sedan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (7, '33', '17', '07', '33.17.07', 'Pamotan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (8, '33', '17', '08', '33.17.08', 'Sulang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (9, '33', '17', '09', '33.17.09', 'Kaliori', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (10, '33', '17', '10', '33.17.10', 'Rembang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (11, '33', '17', '11', '33.17.11', 'Pancur', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (12, '33', '17', '12', '33.17.12', 'Kragan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (13, '33', '17', '13', '33.17.13', 'Sluke', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_districts` VALUES (14, '33', '17', '14', '33.17.14', 'Lasem', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');

-- ----------------------------
-- Table structure for ref_villages
-- ----------------------------
DROP TABLE IF EXISTS `ref_villages`;
CREATE TABLE `ref_villages`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `district_id` int(10) UNSIGNED NOT NULL,
  `province_code` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '33',
  `regency_code` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '17',
  `district_code` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_type` enum('desa','kelurahan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'desa',
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_ref_villages_code`(`code`) USING BTREE,
  INDEX `idx_ref_villages_district_name`(`district_id`, `name`) USING BTREE,
  CONSTRAINT `fk_ref_villages_district` FOREIGN KEY (`district_id`) REFERENCES `ref_districts` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 513 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ref_villages
-- ----------------------------
INSERT INTO `ref_villages` VALUES (1, 1, '33', '17', '01', '3317012015', 'desa', 'Bogorejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (2, 1, '33', '17', '01', '3317012011', 'desa', 'Grawan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (3, 1, '33', '17', '01', '3317012010', 'desa', 'Jadi', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (4, 1, '33', '17', '01', '3317012008', 'desa', 'Jatihadi', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (5, 1, '33', '17', '01', '3317012017', 'desa', 'Kedungasem', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (6, 1, '33', '17', '01', '3317012006', 'desa', 'Kedungtulup', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (7, 1, '33', '17', '01', '3317012005', 'desa', 'Krikilan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (8, 1, '33', '17', '01', '3317012002', 'desa', 'Logede', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (9, 1, '33', '17', '01', '3317012004', 'desa', 'Logung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (10, 1, '33', '17', '01', '3317012016', 'desa', 'Megulung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (11, 1, '33', '17', '01', '3317012003', 'desa', 'Pelemsari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (12, 1, '33', '17', '01', '3317012007', 'desa', 'Polbayem', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (13, 1, '33', '17', '01', '3317012012', 'desa', 'Randuagung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (14, 1, '33', '17', '01', '3317012001', 'desa', 'Ronggomulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (15, 1, '33', '17', '01', '3317012018', 'desa', 'Sekarsari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (16, 1, '33', '17', '01', '3317012013', 'desa', 'Sukorejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (17, 1, '33', '17', '01', '3317012009', 'desa', 'Sumber', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (18, 1, '33', '17', '01', '3317012014', 'desa', 'Tlogotunggal', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (19, 2, '33', '17', '02', '3317022014', 'desa', 'Bulu', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (20, 2, '33', '17', '02', '3317022006', 'desa', 'Cabean', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (21, 2, '33', '17', '02', '3317022013', 'desa', 'Jukung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (22, 2, '33', '17', '02', '3317022016', 'desa', 'Kadiwono', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (23, 2, '33', '17', '02', '3317022010', 'desa', 'Karangasem', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (24, 2, '33', '17', '02', '3317022007', 'desa', 'Lambangan Kulon', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (25, 2, '33', '17', '02', '3317022008', 'desa', 'Lambangan Wetan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (26, 2, '33', '17', '02', '3317022015', 'desa', 'Mantingan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (27, 2, '33', '17', '02', '3317022001', 'desa', 'Mlatirejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (28, 2, '33', '17', '02', '3317022012', 'desa', 'Ngulaan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (29, 2, '33', '17', '02', '3317022011', 'desa', 'Pasedan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (30, 2, '33', '17', '02', '3317022005', 'desa', 'Pinggan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (31, 2, '33', '17', '02', '3317022003', 'desa', 'Pondokrejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (32, 2, '33', '17', '02', '3317022002', 'desa', 'Sendangmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (33, 2, '33', '17', '02', '3317022009', 'desa', 'Sumbermulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (34, 2, '33', '17', '02', '3317022004', 'desa', 'Warugunung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (35, 3, '33', '17', '03', '3317032015', 'desa', 'Banyuurip', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (36, 3, '33', '17', '03', '3317032014', 'desa', 'Demaan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (37, 3, '33', '17', '03', '3317032006', 'desa', 'Dowan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (38, 3, '33', '17', '03', '3317032008', 'desa', 'Gunem', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (39, 3, '33', '17', '03', '3317032001', 'desa', 'Kajar', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (40, 3, '33', '17', '03', '3317032009', 'desa', 'Kulutan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (41, 3, '33', '17', '03', '3317032013', 'desa', 'Panohan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (42, 3, '33', '17', '03', '3317032004', 'desa', 'Pasucen', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (43, 3, '33', '17', '03', '3317032016', 'desa', 'Sambongpayak', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (44, 3, '33', '17', '03', '3317032012', 'desa', 'Sendangmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (45, 3, '33', '17', '03', '3317032010', 'desa', 'Sidomulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (46, 3, '33', '17', '03', '3317032005', 'desa', 'Suntri', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (47, 3, '33', '17', '03', '3317032003', 'desa', 'Tegaldowo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (48, 3, '33', '17', '03', '3317032011', 'desa', 'Telgawah', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (49, 3, '33', '17', '03', '3317032002', 'desa', 'Timbrangan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (50, 3, '33', '17', '03', '3317032007', 'desa', 'Trembes', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (51, 4, '33', '17', '04', '3317042001', 'desa', 'Bancang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (52, 4, '33', '17', '04', '3317042012', 'desa', 'Bitingan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (53, 4, '33', '17', '04', '3317042005', 'desa', 'Gading', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (54, 4, '33', '17', '04', '3317042006', 'desa', 'Jinanten', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (55, 4, '33', '17', '04', '3317042007', 'desa', 'Joho', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (56, 4, '33', '17', '04', '3317042002', 'desa', 'Mrayun', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (57, 4, '33', '17', '04', '3317042003', 'desa', 'Ngajaran', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (58, 4, '33', '17', '04', '3317042013', 'desa', 'Pakis', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (59, 4, '33', '17', '04', '3317042014', 'desa', 'Rendeng', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (60, 4, '33', '17', '04', '3317042008', 'desa', 'Sale', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (61, 4, '33', '17', '04', '3317042010', 'desa', 'Sumbermulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (62, 4, '33', '17', '04', '3317042004', 'desa', 'Tahunan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (63, 4, '33', '17', '04', '3317042011', 'desa', 'Tengger', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (64, 4, '33', '17', '04', '3317042015', 'desa', 'Ukir', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (65, 4, '33', '17', '04', '3317042009', 'desa', 'Wonokerto', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (66, 5, '33', '17', '05', '3317052007', 'desa', 'Babaktulung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (67, 5, '33', '17', '05', '3317052021', 'desa', 'Bajingjowo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (68, 5, '33', '17', '05', '3317052022', 'desa', 'Bajingmeduro', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (69, 5, '33', '17', '05', '3317052018', 'desa', 'Banowan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (70, 5, '33', '17', '05', '3317052006', 'desa', 'Baturno', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (71, 5, '33', '17', '05', '3317052003', 'desa', 'Bonjor', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (72, 5, '33', '17', '05', '3317052016', 'desa', 'Dadapmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (73, 5, '33', '17', '05', '3317052011', 'desa', 'Gilis', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (74, 5, '33', '17', '05', '3317052013', 'desa', 'Gonggang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (75, 5, '33', '17', '05', '3317052012', 'desa', 'Gunungmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (76, 5, '33', '17', '05', '3317052009', 'desa', 'Jambangan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (77, 5, '33', '17', '05', '3317052015', 'desa', 'Kalipang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (78, 5, '33', '17', '05', '3317052020', 'desa', 'Karangmangu', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (79, 5, '33', '17', '05', '3317052001', 'desa', 'Lodan Kulon', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (80, 5, '33', '17', '05', '3317052002', 'desa', 'Lodan Wetan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (81, 5, '33', '17', '05', '3317052008', 'desa', 'Nglojo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (82, 5, '33', '17', '05', '3317052010', 'desa', 'Pelang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (83, 5, '33', '17', '05', '3317052005', 'desa', 'Sampung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (84, 5, '33', '17', '05', '3317052023', 'desa', 'Sarangmeduro', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (85, 5, '33', '17', '05', '3317052017', 'desa', 'Sendangmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (86, 5, '33', '17', '05', '3317052014', 'desa', 'Sumbermulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (87, 5, '33', '17', '05', '3317052004', 'desa', 'Tawangrejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (88, 5, '33', '17', '05', '3317052019', 'desa', 'Temperak', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (89, 6, '33', '17', '06', '3317062018', 'desa', 'Bogorejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (90, 6, '33', '17', '06', '3317062013', 'desa', 'Candimulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (91, 6, '33', '17', '06', '3317062016', 'desa', 'Dadapan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (92, 6, '33', '17', '06', '3317062012', 'desa', 'Gandrirojo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (93, 6, '33', '17', '06', '3317062005', 'desa', 'Gesikan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (94, 6, '33', '17', '06', '3317062020', 'desa', 'Jambeyan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (95, 6, '33', '17', '06', '3317062008', 'desa', 'Karangasem', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (96, 6, '33', '17', '06', '3317062003', 'desa', 'Karas', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (97, 6, '33', '17', '06', '3317062011', 'desa', 'Kedungringin', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (98, 6, '33', '17', '06', '3317062019', 'desa', 'Kenongo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (99, 6, '33', '17', '06', '3317062015', 'desa', 'Kumbo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (100, 6, '33', '17', '06', '3317062014', 'desa', 'Lemahputih', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (101, 6, '33', '17', '06', '3317062021', 'desa', 'Menoro', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (102, 6, '33', '17', '06', '3317062004', 'desa', 'Mojosari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (103, 6, '33', '17', '06', '3317062001', 'desa', 'Ngulahan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (104, 6, '33', '17', '06', '3317062002', 'desa', 'Pacing', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (105, 6, '33', '17', '06', '3317062006', 'desa', 'Sambiroto', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (106, 6, '33', '17', '06', '3317062017', 'desa', 'Sambong', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (107, 6, '33', '17', '06', '3317062007', 'desa', 'Sedan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (108, 6, '33', '17', '06', '3317062010', 'desa', 'Sidomulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (109, 6, '33', '17', '06', '3317062009', 'desa', 'Sidorejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (110, 7, '33', '17', '07', '3317072006', 'desa', 'Bamban', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (111, 7, '33', '17', '07', '3317072007', 'desa', 'Bangunrejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (112, 7, '33', '17', '07', '3317072005', 'desa', 'Gambiran', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (113, 7, '33', '17', '07', '3317072018', 'desa', 'Gegersimo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (114, 7, '33', '17', '07', '3317072020', 'desa', 'Japerejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (115, 7, '33', '17', '07', '3317072011', 'desa', 'Joho', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (116, 7, '33', '17', '07', '3317072013', 'desa', 'Kepohagung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (117, 7, '33', '17', '07', '3317072016', 'desa', 'Ketangi', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (118, 7, '33', '17', '07', '3317072001', 'desa', 'Megal', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (119, 7, '33', '17', '07', '3317072012', 'desa', 'Mlagen', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (120, 7, '33', '17', '07', '3317072014', 'desa', 'Mlawat', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (121, 7, '33', '17', '07', '3317072002', 'desa', 'Ngemplakrejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (122, 7, '33', '17', '07', '3317072008', 'desa', 'Pamotan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (123, 7, '33', '17', '07', '3317072003', 'desa', 'Pragen', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (124, 7, '33', '17', '07', '3317072022', 'desa', 'Ringin', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (125, 7, '33', '17', '07', '3317072004', 'desa', 'Samaran', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (126, 7, '33', '17', '07', '3317072015', 'desa', 'Segoromulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (127, 7, '33', '17', '07', '3317072017', 'desa', 'Sendangagung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (128, 7, '33', '17', '07', '3317072009', 'desa', 'Sidorejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (129, 7, '33', '17', '07', '3317072023', 'desa', 'Sumbangrejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (130, 7, '33', '17', '07', '3317072019', 'desa', 'Sumberjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (131, 7, '33', '17', '07', '3317072010', 'desa', 'Tempaling', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (132, 7, '33', '17', '07', '3317072021', 'desa', 'Tulung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (133, 8, '33', '17', '08', '3317082011', 'desa', 'Bogorame', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (134, 8, '33', '17', '08', '3317082010', 'desa', 'Glebeg', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (135, 8, '33', '17', '08', '3317082008', 'desa', 'Jatimudo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (136, 8, '33', '17', '08', '3317082012', 'desa', 'Kaliombo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (137, 8, '33', '17', '08', '3317082007', 'desa', 'Karangharjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (138, 8, '33', '17', '08', '3317082014', 'desa', 'Karangsari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (139, 8, '33', '17', '08', '3317082016', 'desa', 'Kebonagung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (140, 8, '33', '17', '08', '3317082002', 'desa', 'Kemadu', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (141, 8, '33', '17', '08', '3317082021', 'desa', 'Kerep', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (142, 8, '33', '17', '08', '3317082006', 'desa', 'Korowelang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (143, 8, '33', '17', '08', '3317082009', 'desa', 'Kunir', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (144, 8, '33', '17', '08', '3317082020', 'desa', 'Landoh', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (145, 8, '33', '17', '08', '3317082019', 'desa', 'Pedak', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (146, 8, '33', '17', '08', '3317082004', 'desa', 'Pomahan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (147, 8, '33', '17', '08', '3317082015', 'desa', 'Pragu', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (148, 8, '33', '17', '08', '3317082018', 'desa', 'Pranti', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (149, 8, '33', '17', '08', '3317082005', 'desa', 'Rukem', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (150, 8, '33', '17', '08', '3317082017', 'desa', 'Seren', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (151, 8, '33', '17', '08', '3317082013', 'desa', 'Sudo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (152, 8, '33', '17', '08', '3317082003', 'desa', 'Sulang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (153, 8, '33', '17', '08', '3317082001', 'desa', 'Tanjung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (154, 9, '33', '17', '09', '3317092010', 'desa', 'Babadan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (155, 9, '33', '17', '09', '3317092005', 'desa', 'Banggi', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (156, 9, '33', '17', '09', '3317092022', 'desa', 'Banyudono', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (157, 9, '33', '17', '09', '3317092021', 'desa', 'Bogoharjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (158, 9, '33', '17', '09', '3317092017', 'desa', 'Dresi Kulon', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (159, 9, '33', '17', '09', '3317092018', 'desa', 'Dresi Wetan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (160, 9, '33', '17', '09', '3317092007', 'desa', 'Gunungsari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (161, 9, '33', '17', '09', '3317092009', 'desa', 'Karangsekar', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (162, 9, '33', '17', '09', '3317092006', 'desa', 'Kuangsan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (163, 9, '33', '17', '09', '3317092002', 'desa', 'Maguan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (164, 9, '33', '17', '09', '3317092001', 'desa', 'Meteseh', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (165, 9, '33', '17', '09', '3317092013', 'desa', 'Mojorembun', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (166, 9, '33', '17', '09', '3317092016', 'desa', 'Mojowarno', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (167, 9, '33', '17', '09', '3317092023', 'desa', 'Pantiharjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (168, 9, '33', '17', '09', '3317092011', 'desa', 'Pengkol', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (169, 9, '33', '17', '09', '3317092020', 'desa', 'Purworejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (170, 9, '33', '17', '09', '3317092012', 'desa', 'Sambiyan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (171, 9, '33', '17', '09', '3317092008', 'desa', 'Sendangagung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (172, 9, '33', '17', '09', '3317092003', 'desa', 'Sidomulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (173, 9, '33', '17', '09', '3317092015', 'desa', 'Tambakagung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (174, 9, '33', '17', '09', '3317092019', 'desa', 'Tasikharjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (175, 9, '33', '17', '09', '3317092014', 'desa', 'Tunggulsari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (176, 9, '33', '17', '09', '3317092004', 'desa', 'Wiroto', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (177, 10, '33', '17', '10', '3317102011', 'desa', 'Gedangan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (178, 10, '33', '17', '10', '3317102021', 'desa', 'Gegunung Wetan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (179, 10, '33', '17', '10', '3317102033', 'desa', 'Kabongan Kidul', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (180, 10, '33', '17', '10', '3317102032', 'desa', 'Kabongan Lor', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (181, 10, '33', '17', '10', '3317102007', 'desa', 'Kasreman', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (182, 10, '33', '17', '10', '3317102001', 'desa', 'Kedungrejo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (183, 10, '33', '17', '10', '3317102016', 'desa', 'Ketanggi', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (184, 10, '33', '17', '10', '3317102003', 'desa', 'Kumendung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (185, 10, '33', '17', '10', '3317102014', 'desa', 'Mondoteko', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (186, 10, '33', '17', '10', '3317102015', 'desa', 'Ngadem', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (187, 10, '33', '17', '10', '3317102013', 'desa', 'Ngotet', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (188, 10, '33', '17', '10', '3317102030', 'desa', 'Padaran', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (189, 10, '33', '17', '10', '3317102005', 'desa', 'Pandean', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (190, 10, '33', '17', '10', '3317102010', 'desa', 'Pasarbanggi', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (191, 10, '33', '17', '10', '3317102017', 'desa', 'Pulo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (192, 10, '33', '17', '10', '3317102008', 'desa', 'Punjulharjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (193, 10, '33', '17', '10', '3317102026', 'desa', 'Sawahan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (194, 10, '33', '17', '10', '3317102004', 'desa', 'Sridadi', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (195, 10, '33', '17', '10', '3317102031', 'desa', 'Sukoharjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (196, 10, '33', '17', '10', '3317102024', 'desa', 'Sumberjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (197, 10, '33', '17', '10', '3317102025', 'desa', 'Tasikagung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (198, 10, '33', '17', '10', '3317102034', 'desa', 'Tireman', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (199, 10, '33', '17', '10', '3317102006', 'desa', 'Tlogomojo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (200, 10, '33', '17', '10', '3317102009', 'desa', 'Tritunggal', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (201, 10, '33', '17', '10', '3317102002', 'desa', 'Turusgede', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (202, 10, '33', '17', '10', '3317102018', 'desa', 'Waru', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (203, 10, '33', '17', '10', '3317102012', 'desa', 'Weton', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (204, 10, '33', '17', '10', '3317101028', 'desa', 'Sidowayah', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (205, 10, '33', '17', '10', '3317101029', 'desa', 'Kutoharjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (206, 10, '33', '17', '10', '3317101027', 'desa', 'Leteh', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (207, 10, '33', '17', '10', '3317101022', 'desa', 'Pacar', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (208, 10, '33', '17', '10', '3317101023', 'desa', 'Tanjungsari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (209, 10, '33', '17', '10', '3317101019', 'desa', 'Magersari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (210, 10, '33', '17', '10', '3317101020', 'desa', 'Gegunung Kulon', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (211, 11, '33', '17', '11', '3317112020', 'desa', 'Banyuurip', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (212, 11, '33', '17', '11', '3317112017', 'desa', 'Criwik', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (213, 11, '33', '17', '11', '3317112003', 'desa', 'Doropayung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (214, 11, '33', '17', '11', '3317112007', 'desa', 'Gemblengmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (215, 11, '33', '17', '11', '3317112001', 'desa', 'Japeledok', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (216, 11, '33', '17', '11', '3317112002', 'desa', 'Jeruk', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (217, 11, '33', '17', '11', '3317112021', 'desa', 'Johogunung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (218, 11, '33', '17', '11', '3317112009', 'desa', 'Kalitengah', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (219, 11, '33', '17', '11', '3317112004', 'desa', 'Karaskepoh', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (220, 11, '33', '17', '11', '3317112011', 'desa', 'Kedung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (221, 11, '33', '17', '11', '3317112013', 'desa', 'Langkir', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (222, 11, '33', '17', '11', '3317112023', 'desa', 'Ngroto', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (223, 11, '33', '17', '11', '3317112019', 'desa', 'Ngulangan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (224, 11, '33', '17', '11', '3317112014', 'desa', 'Pancur', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (225, 11, '33', '17', '11', '3317112006', 'desa', 'Pandan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (226, 11, '33', '17', '11', '3317112015', 'desa', 'Pohlandak', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (227, 11, '33', '17', '11', '3317112012', 'desa', 'Punggurharjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (228, 11, '33', '17', '11', '3317112010', 'desa', 'Sidowayah', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (229, 11, '33', '17', '11', '3317112008', 'desa', 'Sumberagung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (230, 11, '33', '17', '11', '3317112022', 'desa', 'Trenggulunan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (231, 11, '33', '17', '11', '3317112005', 'desa', 'Tuyuhan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (232, 11, '33', '17', '11', '3317112016', 'desa', 'Warugunung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (233, 11, '33', '17', '11', '3317112018', 'desa', 'Wuwur', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (234, 12, '33', '17', '12', '3317122014', 'desa', 'Balongmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (235, 12, '33', '17', '12', '3317122009', 'desa', 'Karanganyar', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (236, 12, '33', '17', '12', '3317122011', 'desa', 'Karangharjo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (237, 12, '33', '17', '12', '3317122010', 'desa', 'Karanglincak', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (238, 12, '33', '17', '12', '3317122008', 'desa', 'Kebloran', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (239, 12, '33', '17', '12', '3317122005', 'desa', 'Kendalagung', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (240, 12, '33', '17', '12', '3317122012', 'desa', 'Kragan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (241, 12, '33', '17', '12', '3317122006', 'desa', 'Mojokerto', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (242, 12, '33', '17', '12', '3317122015', 'desa', 'Narukan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (243, 12, '33', '17', '12', '3317122004', 'desa', 'Ngasinan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (244, 12, '33', '17', '12', '3317122025', 'desa', 'Pandangan Kulon', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (245, 12, '33', '17', '12', '3317122024', 'desa', 'Pandangan Wetan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (246, 12, '33', '17', '12', '3317122022', 'desa', 'Plawangan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (247, 12, '33', '17', '12', '3317122018', 'desa', 'Sendang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (248, 12, '33', '17', '12', '3317122002', 'desa', 'Sendangmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (249, 12, '33', '17', '12', '3317122003', 'desa', 'Sendangwaru', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (250, 12, '33', '17', '12', '3317122016', 'desa', 'Sudan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (251, 12, '33', '17', '12', '3317122023', 'desa', 'Sumbergayam', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (252, 12, '33', '17', '12', '3317122027', 'desa', 'Sumbersari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (253, 12, '33', '17', '12', '3317122021', 'desa', 'Sumurpule', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (254, 12, '33', '17', '12', '3317122026', 'desa', 'Sumurtawang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (255, 12, '33', '17', '12', '3317122007', 'desa', 'Tanjungan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (256, 12, '33', '17', '12', '3317122001', 'desa', 'Tanjungsari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (257, 12, '33', '17', '12', '3317122013', 'desa', 'Tegalmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (258, 12, '33', '17', '12', '3317122017', 'desa', 'Terjan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (259, 12, '33', '17', '12', '3317122019', 'desa', 'Watupecah', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (260, 12, '33', '17', '12', '3317122020', 'desa', 'Woro', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (261, 13, '33', '17', '13', '3317132003', 'desa', 'Bendo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (262, 13, '33', '17', '13', '3317132006', 'desa', 'Blimbing', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (263, 13, '33', '17', '13', '3317132008', 'desa', 'Jatisari', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (264, 13, '33', '17', '13', '3317132011', 'desa', 'Jurangjero', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (265, 13, '33', '17', '13', '3317132004', 'desa', 'Labuhan Kidul', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (266, 13, '33', '17', '13', '3317132009', 'desa', 'Langgar', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (267, 13, '33', '17', '13', '3317132012', 'desa', 'Leran', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (268, 13, '33', '17', '13', '3317132007', 'desa', 'Manggar', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (269, 13, '33', '17', '13', '3317132014', 'desa', 'Pangkalan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (270, 13, '33', '17', '13', '3317132002', 'desa', 'Rakitan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (271, 13, '33', '17', '13', '3317132001', 'desa', 'Sanetan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (272, 13, '33', '17', '13', '3317132005', 'desa', 'Sendangmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (273, 13, '33', '17', '13', '3317132010', 'desa', 'Sluke', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (274, 13, '33', '17', '13', '3317132013', 'desa', 'Trahan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (275, 14, '33', '17', '14', '3317142005', 'desa', 'Babagan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (276, 14, '33', '17', '14', '3317142020', 'desa', 'Binangun', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (277, 14, '33', '17', '14', '3317142019', 'desa', 'Bonang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (278, 14, '33', '17', '14', '3317142008', 'desa', 'Dasun', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (279, 14, '33', '17', '14', '3317142006', 'desa', 'Dorokandang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (280, 14, '33', '17', '14', '3317142007', 'desa', 'Gedongmulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (281, 14, '33', '17', '14', '3317142015', 'desa', 'Gowak', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (282, 14, '33', '17', '14', '3317142002', 'desa', 'Jolotundo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (283, 14, '33', '17', '14', '3317142014', 'desa', 'Kajar', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (284, 14, '33', '17', '14', '3317142004', 'desa', 'Karangturi', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (285, 14, '33', '17', '14', '3317142001', 'desa', 'Karasgede', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (286, 14, '33', '17', '14', '3317142013', 'desa', 'Ngargomulyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (287, 14, '33', '17', '14', '3317142010', 'desa', 'Ngemplak', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (288, 14, '33', '17', '14', '3317142011', 'desa', 'Selopuro', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (289, 14, '33', '17', '14', '3317142016', 'desa', 'Sendangasri', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (290, 14, '33', '17', '14', '3317142012', 'desa', 'Sendangcoyo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (291, 14, '33', '17', '14', '3317142009', 'desa', 'Soditan', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (292, 14, '33', '17', '14', '3317142018', 'desa', 'Sriombo', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (293, 14, '33', '17', '14', '3317142003', 'desa', 'Sumbergirang', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');
INSERT INTO `ref_villages` VALUES (294, 14, '33', '17', '14', '3317142017', 'desa', 'Tasiksono', 1, '2026-07-29 07:56:57', '2026-07-29 08:58:35');

-- ----------------------------
-- Table structure for sys_menu
-- ----------------------------
DROP TABLE IF EXISTS `sys_menu`;
CREATE TABLE `sys_menu`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) UNSIGNED NULL DEFAULT NULL,
  `page_id` int(10) UNSIGNED NULL DEFAULT NULL,
  `menu_area` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MAIN',
  `menu_key` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `url` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_sys_menu_key`(`menu_key`) USING BTREE,
  INDEX `idx_sys_menu_parent_id`(`parent_id`) USING BTREE,
  INDEX `idx_sys_menu_page_id`(`page_id`) USING BTREE,
  INDEX `idx_sys_menu_area_order`(`menu_area`, `sort_order`) USING BTREE,
  CONSTRAINT `fk_sys_menu_page` FOREIGN KEY (`page_id`) REFERENCES `sys_page` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_sys_menu_parent` FOREIGN KEY (`parent_id`) REFERENCES `sys_menu` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 39 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_menu
-- ----------------------------
INSERT INTO `sys_menu` VALUES (1, NULL, 1, 'MAIN', 'dashboard', 'Dashboard', 'ti ti-layout-dashboard', 'admin', 10, 1, 1, 1, '2026-07-28 22:09:29', '2026-07-29 06:00:52');
INSERT INTO `sys_menu` VALUES (2, NULL, 2, 'MAIN', 'libraries.gis', 'Perpustakaan GIS', 'ti ti-map-2', 'libraries', 20, 1, 1, 0, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_menu` VALUES (3, NULL, 3, 'MAIN', 'catalog', 'Katalog', 'ti ti-books', 'catalog', 30, 1, 1, 0, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_menu` VALUES (4, NULL, 4, 'MAIN', 'members', 'Membership', 'ti ti-id-badge-2', 'members', 40, 1, 1, 0, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_menu` VALUES (5, NULL, 6, 'MAIN', 'reading-points', 'Pojok Baca', 'ti ti-current-location', 'reading-points', 50, 1, 1, 0, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_menu` VALUES (6, NULL, 7, 'MAIN', 'events', 'Event', 'ti ti-calendar-event', 'events', 60, 1, 1, 0, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_menu` VALUES (7, NULL, NULL, 'MAIN', 'system', 'Pengaturan Akses', 'ti ti-shield-lock', NULL, 80, 1, 1, 1, '2026-07-28 22:09:29', '2026-07-29 20:36:48');
INSERT INTO `sys_menu` VALUES (8, 7, 8, 'MAIN', 'system.users', 'User', 'ti ti-users', 'rbac/users', 10, 1, 1, 0, '2026-07-28 22:09:29', '2026-07-29 20:36:48');
INSERT INTO `sys_menu` VALUES (9, 7, 9, 'MAIN', 'system.roles', 'Role & Permission', 'ti ti-key', 'rbac/roles', 20, 1, 1, 0, '2026-07-28 22:09:29', '2026-07-29 20:36:48');
INSERT INTO `sys_menu` VALUES (10, 7, 10, 'MAIN', 'system.sidebar', 'Sidebar', 'ti ti-layout-sidebar', 'rbac/sidebar', 40, 1, 1, 0, '2026-07-28 22:09:29', '2026-07-29 20:36:48');
INSERT INTO `sys_menu` VALUES (31, 7, 31, 'MAIN', 'system.pages', 'Registry Halaman', 'ti ti-file-settings', 'rbac/pages', 30, 1, 1, 0, '2026-07-29 06:43:15', '2026-07-29 20:36:48');
INSERT INTO `sys_menu` VALUES (33, 7, 33, 'MAIN', 'system.audit', 'Audit Log', 'ti ti-history', 'audit', 50, 1, 1, 0, '2026-07-29 07:56:57', '2026-07-29 20:36:48');
INSERT INTO `sys_menu` VALUES (35, NULL, NULL, 'MAIN', 'master-data', 'Data Master', 'ti ti-database', NULL, 70, 1, 1, 1, '2026-07-29 11:45:36', '2026-07-29 20:36:48');
INSERT INTO `sys_menu` VALUES (36, 35, 35, 'MAIN', 'master.regions', 'Master Wilayah', 'ti ti-map-pin-cog', 'regions', 10, 1, 1, 0, '2026-07-29 11:45:36', '2026-07-29 20:36:48');

-- ----------------------------
-- Table structure for sys_page
-- ----------------------------
DROP TABLE IF EXISTS `sys_page`;
CREATE TABLE `sys_page`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_sys_page_code`(`code`) USING BTREE,
  INDEX `idx_sys_page_module`(`module`) USING BTREE,
  INDEX `idx_sys_page_route`(`route`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 41 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_page
-- ----------------------------
INSERT INTO `sys_page` VALUES (1, 'dashboard.index', 'dashboard', 'Dashboard', 'admin/index', 'Ringkasan aplikasi dan status migrasi.', 1, '2026-07-28 22:09:29', '2026-07-29 06:00:52');
INSERT INTO `sys_page` VALUES (2, 'libraries.index', 'libraries', 'Perpustakaan GIS', 'libraries', 'Direktori perpustakaan terintegrasi berbasis GIS.', 1, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_page` VALUES (3, 'catalog.index', 'catalog', 'Katalog Buku', 'catalog', 'Manajemen katalog dan koleksi buku.', 1, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_page` VALUES (4, 'members.index', 'members', 'Membership Digital', 'members', 'Manajemen akun/member dan kartu digital.', 1, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_page` VALUES (5, 'gis.index', 'gis', 'Peta GIS', 'gis', 'Peta seluruh titik perpustakaan dan pojok baca.', 1, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_page` VALUES (6, 'reading_points.index', 'reading_points', 'Pojok Baca Digital', 'reading-points', 'Lokasi GPS, token, dan kuota baca digital.', 1, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_page` VALUES (7, 'events.index', 'events', 'Event Literasi', 'events', 'Agenda dan kegiatan literasi.', 1, '2026-07-28 22:09:29', NULL);
INSERT INTO `sys_page` VALUES (8, 'auth.users.index', 'auth', 'Manajemen User', 'rbac/users', 'Kelola user, status, dan penugasan role.', 1, '2026-07-28 22:09:29', '2026-07-29 06:43:15');
INSERT INTO `sys_page` VALUES (9, 'auth.roles.index', 'auth', 'Role dan Hak Akses', 'rbac/roles', 'Kelola role dan matriks permission.', 1, '2026-07-28 22:09:29', '2026-07-29 06:43:15');
INSERT INTO `sys_page` VALUES (10, 'system.sidebar.manage', 'system', 'Manajemen Sidebar', 'rbac/sidebar', 'Kelola susunan, ikon, dan akses menu sidebar.', 1, '2026-07-28 22:09:29', '2026-07-29 06:43:15');
INSERT INTO `sys_page` VALUES (31, 'system.pages.index', 'system', 'Registry Halaman', 'rbac/pages', 'Kelola registry halaman yang menjadi dasar permission dan sidebar.', 1, '2026-07-29 06:43:15', NULL);
INSERT INTO `sys_page` VALUES (33, 'audit.index', 'audit', 'Audit Log', 'audit', 'Pantauan aktivitas penting sistem.', 1, '2026-07-29 07:56:57', NULL);
INSERT INTO `sys_page` VALUES (35, 'regions.index', 'regions', 'Master Wilayah', 'regions', 'Kelola kecamatan dan Desa / Kelurahan Rembang.', 1, '2026-07-29 11:45:36', NULL);
INSERT INTO `sys_page` VALUES (36, 'catalog.sync', 'catalog', 'Sinkronisasi Katalog', 'catalog/sync', 'Pantau dan jalankan sinkronisasi katalog dari INLISLite.', 1, '2026-07-29 11:45:36', NULL);
INSERT INTO `sys_page` VALUES (39, 'members.sync', 'members', 'Sinkronisasi Member', 'members/sync', 'Pantau dan jalankan sinkronisasi anggota dari INLISLite.', 1, '2026-07-29 20:07:25', NULL);

-- ----------------------------
-- Table structure for sys_sidebar_favorite
-- ----------------------------
DROP TABLE IF EXISTS `sys_sidebar_favorite`;
CREATE TABLE `sys_sidebar_favorite`  (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `menu_id` int(10) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`user_id`, `menu_id`) USING BTREE,
  INDEX `idx_sys_sidebar_favorite_menu_id`(`menu_id`) USING BTREE,
  CONSTRAINT `fk_sys_sidebar_favorite_menu` FOREIGN KEY (`menu_id`) REFERENCES `sys_menu` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_sys_sidebar_favorite_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
