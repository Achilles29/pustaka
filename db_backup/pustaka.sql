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

 Date: 03/08/2026 06:38:38
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for asset_migration_items
-- ----------------------------
DROP TABLE IF EXISTS `asset_migration_items`;
CREATE TABLE `asset_migration_items`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `run_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `asset_type` enum('cover','member_photo','digital_file') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inlislite_v3',
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `local_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('copied','skipped','missing','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_asset_migration_items_run`(`run_id`) USING BTREE,
  INDEX `idx_asset_migration_items_asset`(`asset_type`, `status`) USING BTREE,
  INDEX `idx_asset_migration_items_entity`(`entity_type`, `entity_id`) USING BTREE,
  INDEX `idx_asset_migration_items_source`(`source_system`, `source_id`) USING BTREE,
  CONSTRAINT `fk_asset_migration_items_run` FOREIGN KEY (`run_id`) REFERENCES `asset_migration_runs` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 13180 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for asset_migration_runs
-- ----------------------------
DROP TABLE IF EXISTS `asset_migration_runs`;
CREATE TABLE `asset_migration_runs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inlislite_v3',
  `asset_type` enum('all','cover','member_photo','digital_file') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `mode` enum('copy_missing','refresh_existing','dry_run') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'copy_missing',
  `status` enum('queued','running','success','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `source_root` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `target_root` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `started_at` datetime(0) NULL DEFAULT NULL,
  `finished_at` datetime(0) NULL DEFAULT NULL,
  `total_source` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_copied` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_skipped` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_missing` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_failed` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_asset_migration_runs_type`(`asset_type`) USING BTREE,
  INDEX `idx_asset_migration_runs_status`(`status`) USING BTREE,
  INDEX `idx_asset_migration_runs_created_at`(`created_at`) USING BTREE,
  INDEX `fk_asset_migration_runs_created_by`(`created_by`) USING BTREE,
  CONSTRAINT `fk_asset_migration_runs_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 82 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 161 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 5396 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 12387 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for book_classification_masters
-- ----------------------------
DROP TABLE IF EXISTS `book_classification_masters`;
CREATE TABLE `book_classification_masters`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(140) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_book_classification_masters_code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for book_content_categories
-- ----------------------------
DROP TABLE IF EXISTS `book_content_categories`;
CREATE TABLE `book_content_categories`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_book_content_categories_code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for book_items
-- ----------------------------
DROP TABLE IF EXISTS `book_items`;
CREATE TABLE `book_items`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) UNSIGNED NOT NULL,
  `library_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `source_location_library_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_location_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_rule_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_category_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_media_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_collection_source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_status_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `item_code` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `barcode` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `call_number` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `location_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `location_library_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `location_room_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `rule_name` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `collection_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `category_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `media_name` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_name` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `inventory_number` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('available','loaned','missing','damaged','unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `status_label` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_book_items_source`(`source_system`, `source_id`) USING BTREE,
  INDEX `idx_book_items_book`(`book_id`) USING BTREE,
  INDEX `idx_book_items_library`(`library_id`) USING BTREE,
  INDEX `idx_book_items_status`(`status`) USING BTREE,
  CONSTRAINT `fk_book_items_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_book_items_library` FOREIGN KEY (`library_id`) REFERENCES `libraries` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 22944 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for book_requests
-- ----------------------------
DROP TABLE IF EXISTS `book_requests`;
CREATE TABLE `book_requests`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` bigint(20) UNSIGNED NOT NULL,
  `book_item_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `member_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `request_type` enum('reservation','request') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'request',
  `requester_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `requester_email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `requester_phone` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `status` enum('pending','approved','rejected','fulfilled','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `processed_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `processed_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_book_requests_code`(`request_code`) USING BTREE,
  INDEX `idx_book_requests_book`(`book_id`) USING BTREE,
  INDEX `idx_book_requests_item`(`book_item_id`) USING BTREE,
  INDEX `idx_book_requests_member`(`member_id`) USING BTREE,
  INDEX `idx_book_requests_status`(`status`, `created_at`) USING BTREE,
  INDEX `fk_book_requests_processed_by`(`processed_by`) USING BTREE,
  CONSTRAINT `fk_book_requests_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_book_requests_item` FOREIGN KEY (`book_item_id`) REFERENCES `book_items` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_book_requests_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_book_requests_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 2009 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
  `content_category_id` int(10) UNSIGNED NULL DEFAULT NULL,
  `content_classification_id` int(10) UNSIGNED NULL DEFAULT NULL,
  `call_number` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `language` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `physical_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `abstract` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `cover_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cover_source_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cover_local_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cover_migration_status` enum('pending','copied','missing','failed','skipped') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `cover_migrated_at` datetime(0) NULL DEFAULT NULL,
  `status` enum('draft','published','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_books_source`(`source_system`, `source_id`) USING BTREE,
  INDEX `idx_books_title`(`title`) USING BTREE,
  INDEX `idx_books_isbn`(`isbn`) USING BTREE,
  INDEX `idx_books_classification`(`classification`) USING BTREE,
  INDEX `idx_books_status`(`status`) USING BTREE,
  INDEX `fk_books_created_by`(`created_by`) USING BTREE,
  INDEX `fk_books_updated_by`(`updated_by`) USING BTREE,
  INDEX `idx_books_content_category`(`content_category_id`) USING BTREE,
  INDEX `idx_books_content_classification`(`content_classification_id`) USING BTREE,
  CONSTRAINT `fk_books_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_books_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 14109 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 37025 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `migration_status` enum('pending','copied','missing','failed','skipped') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `migrated_at` datetime(0) NULL DEFAULT NULL,
  `file_original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `mime_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `access_policy` enum('online_only','download_allowed','location_only','member_only','internal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `is_downloadable` tinyint(1) NOT NULL DEFAULT 0,
  `rights_basis` enum('public_domain','licensed','owned','permission_letter','internal_use','unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `rights_holder` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `license_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `permission_reference` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `permission_starts_at` date NULL DEFAULT NULL,
  `permission_ends_at` date NULL DEFAULT NULL,
  `access_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `status` enum('draft','active','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `uploaded_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_digital_assets_book`(`book_id`) USING BTREE,
  INDEX `idx_digital_assets_policy`(`access_policy`) USING BTREE,
  INDEX `idx_digital_assets_status`(`status`) USING BTREE,
  INDEX `fk_digital_assets_uploaded_by`(`uploaded_by`) USING BTREE,
  INDEX `idx_digital_assets_rights_basis`(`rights_basis`) USING BTREE,
  INDEX `idx_digital_assets_permission_ends`(`permission_ends_at`) USING BTREE,
  CONSTRAINT `fk_digital_assets_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_digital_assets_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for event_registrations
-- ----------------------------
DROP TABLE IF EXISTS `event_registrations`;
CREATE TABLE `event_registrations`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `participant_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `participant_phone` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `participant_email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `attendance_token` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('registered','attended','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `registered_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `attended_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_event_registrations_event`(`event_id`) USING BTREE,
  INDEX `idx_event_registrations_member`(`member_id`) USING BTREE,
  CONSTRAINT `fk_event_registrations_event` FOREIGN KEY (`event_id`) REFERENCES `literacy_events` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_event_registrations_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for inlislite_master_references
-- ----------------------------
DROP TABLE IF EXISTS `inlislite_master_references`;
CREATE TABLE `inlislite_master_references`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inlislite_v3',
  `source_table` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_created_at` datetime(0) NULL DEFAULT NULL,
  `source_updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_inlislite_master_refs_source`(`source_system`, `source_table`, `source_id`) USING BTREE,
  INDEX `idx_inlislite_master_refs_table`(`source_table`) USING BTREE,
  INDEX `idx_inlislite_master_refs_name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 145 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for literacy_events
-- ----------------------------
DROP TABLE IF EXISTS `literacy_events`;
CREATE TABLE `literacy_events`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `library_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `title` varchar(220) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `event_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `starts_at` datetime(0) NULL DEFAULT NULL,
  `ends_at` datetime(0) NULL DEFAULT NULL,
  `location_name` varchar(220) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `latitude` decimal(10, 7) NULL DEFAULT NULL,
  `longitude` decimal(10, 7) NULL DEFAULT NULL,
  `quota` int(10) UNSIGNED NULL DEFAULT NULL,
  `registration_required` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('draft','published','closed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_literacy_events_library`(`library_id`) USING BTREE,
  INDEX `idx_literacy_events_status`(`status`, `starts_at`) USING BTREE,
  INDEX `fk_literacy_events_created_by`(`created_by`) USING BTREE,
  CONSTRAINT `fk_literacy_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_literacy_events_library` FOREIGN KEY (`library_id`) REFERENCES `libraries` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for loan_transaction_items
-- ----------------------------
DROP TABLE IF EXISTS `loan_transaction_items`;
CREATE TABLE `loan_transaction_items`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `loan_transaction_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `source_loan_id` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `book_item_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `source_member_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_collection_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `loan_date` datetime(0) NULL DEFAULT NULL,
  `due_date` datetime(0) NULL DEFAULT NULL,
  `actual_return_at` datetime(0) NULL DEFAULT NULL,
  `late_days` int(11) NULL DEFAULT NULL,
  `loan_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_created_at` datetime(0) NULL DEFAULT NULL,
  `source_updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_loan_transaction_items_source`(`source_system`, `source_id`) USING BTREE,
  INDEX `idx_loan_transaction_items_loan`(`loan_transaction_id`) USING BTREE,
  INDEX `idx_loan_transaction_items_member`(`member_id`) USING BTREE,
  INDEX `idx_loan_transaction_items_book_item`(`book_item_id`) USING BTREE,
  INDEX `idx_loan_transaction_items_status`(`loan_status`) USING BTREE,
  INDEX `idx_loan_transaction_items_date`(`loan_date`) USING BTREE,
  CONSTRAINT `fk_loan_transaction_items_book_item` FOREIGN KEY (`book_item_id`) REFERENCES `book_items` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_loan_transaction_items_loan` FOREIGN KEY (`loan_transaction_id`) REFERENCES `loan_transactions` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_loan_transaction_items_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2273 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for loan_transactions
-- ----------------------------
DROP TABLE IF EXISTS `loan_transactions`;
CREATE TABLE `loan_transactions`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_id` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `source_member_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `branch_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `location_library_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `collection_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `loan_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `return_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `late_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `extend_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `source_created_at` datetime(0) NULL DEFAULT NULL,
  `source_updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_loan_transactions_source`(`source_system`, `source_id`) USING BTREE,
  INDEX `idx_loan_transactions_member`(`member_id`) USING BTREE,
  INDEX `idx_loan_transactions_source_member`(`source_member_id`) USING BTREE,
  CONSTRAINT `fk_loan_transactions_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 44375 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for member_access_rules
-- ----------------------------
DROP TABLE IF EXISTS `member_access_rules`;
CREATE TABLE `member_access_rules`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source_table` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `source_member_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `rule_type` enum('category','location') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_rule_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `rule_label` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at_source` datetime(0) NULL DEFAULT NULL,
  `updated_at_source` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_member_access_rules_source`(`source_system`, `source_table`, `source_id`) USING BTREE,
  INDEX `idx_member_access_rules_member`(`member_id`) USING BTREE,
  INDEX `idx_member_access_rules_type`(`rule_type`, `source_rule_id`) USING BTREE,
  CONSTRAINT `fk_member_access_rules_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 36670 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for member_registration_requests
-- ----------------------------
DROP TABLE IF EXISTS `member_registration_requests`;
CREATE TABLE `member_registration_requests`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `full_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_number` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_place` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `birth_date` date NULL DEFAULT NULL,
  `gender` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `district` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `village` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_type` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'Umum',
  `education` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `occupation` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_rembang_resident` tinyint(1) NOT NULL DEFAULT 1,
  `residency_note` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ktp_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kk_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `support_letter_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('pending','verified','rejected','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `verified_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `verified_at` datetime(0) NULL DEFAULT NULL,
  `member_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_member_registration_code`(`registration_code`) USING BTREE,
  UNIQUE INDEX `idx_member_registration_public_token`(`public_token`) USING BTREE,
  INDEX `idx_member_registration_identity`(`identity_number`) USING BTREE,
  INDEX `idx_member_registration_status`(`status`, `created_at`) USING BTREE,
  INDEX `idx_member_registration_member`(`member_id`) USING BTREE,
  INDEX `fk_member_registration_verified_by`(`verified_by`) USING BTREE,
  CONSTRAINT `fk_member_registration_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_member_registration_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for member_visits
-- ----------------------------
DROP TABLE IF EXISTS `member_visits`;
CREATE TABLE `member_visits`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `visit_channel` enum('inlislite_guestbook','library_guestbook','member_dashboard','digital_access','reading_point','service_monitor','qr_checkin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inlislite_guestbook',
  `visit_origin` enum('library','reading_point','digital_external','digital_internal','legacy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'legacy',
  `source_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `library_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `reading_point_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `reading_session_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `auth_user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `source_member_no` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `visitor_no` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `visitor_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `group_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `group_leader_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `visitor_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `checkin_method` enum('guest_form','member_search','member_qr','member_gps','dashboard_auto','reader_quota','legacy_sync') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'legacy_sync',
  `qr_token_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `gender_id` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `gender_label` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `profession_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `profession_label` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `education_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `education_label` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `visit_status_label` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `location_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `location_label` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `location_loan_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `location_loan_label` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `purpose_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `purpose_label` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `information` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ip_address` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `latitude` decimal(10, 7) NULL DEFAULT NULL,
  `longitude` decimal(10, 7) NULL DEFAULT NULL,
  `metadata_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `visited_at` datetime(0) NULL DEFAULT NULL,
  `source_created_at` datetime(0) NULL DEFAULT NULL,
  `source_updated_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_member_visits_source`(`source_system`, `source_id`) USING BTREE,
  INDEX `idx_member_visits_member`(`member_id`) USING BTREE,
  INDEX `idx_member_visits_no`(`source_member_no`) USING BTREE,
  INDEX `idx_member_visits_visited`(`visited_at`) USING BTREE,
  INDEX `idx_member_visits_channel_date`(`visit_channel`, `visited_at`) USING BTREE,
  INDEX `idx_member_visits_origin_date`(`visit_origin`, `visited_at`) USING BTREE,
  INDEX `idx_member_visits_library_date`(`library_id`, `visited_at`) USING BTREE,
  INDEX `idx_member_visits_reading_point_date`(`reading_point_id`, `visited_at`) USING BTREE,
  INDEX `idx_member_visits_auth_user_date`(`auth_user_id`, `visited_at`) USING BTREE,
  CONSTRAINT `fk_member_visits_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 67608 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
  `identity_type_label` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `identity_number` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `gender` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `gender_label` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `birth_place` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `birth_date` date NULL DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `district` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `village` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `photo_source_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `photo_local_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `photo_migration_status` enum('pending','copied','missing','failed','skipped') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `photo_migrated_at` datetime(0) NULL DEFAULT NULL,
  `member_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_type_label` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `education` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `education_label` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `occupation` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `occupation_label` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('active','inactive','blocked','expired','unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `member_status_label` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `card_status` enum('active','blocked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `card_block_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `card_blocked_at` datetime(0) NULL DEFAULT NULL,
  `card_blocked_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `registered_at` datetime(0) NULL DEFAULT NULL,
  `expired_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_members_source`(`source_system`, `source_id`) USING BTREE,
  UNIQUE INDEX `uq_members_member_no`(`member_no`) USING BTREE,
  INDEX `idx_members_auth_user`(`auth_user_id`) USING BTREE,
  INDEX `idx_members_name`(`full_name`) USING BTREE,
  INDEX `idx_members_status`(`status`) USING BTREE,
  CONSTRAINT `fk_members_auth_user` FOREIGN KEY (`auth_user_id`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5395 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for membership_renewal_requests
-- ----------------------------
DROP TABLE IF EXISTS `membership_renewal_requests`;
CREATE TABLE `membership_renewal_requests`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `current_expired_at` datetime(0) NULL DEFAULT NULL,
  `requested_months` smallint(5) UNSIGNED NOT NULL DEFAULT 12,
  `status` enum('pending','approved','rejected','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `processed_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `processed_at` datetime(0) NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_membership_renewal_code`(`request_code`) USING BTREE,
  INDEX `idx_membership_renewal_member`(`member_id`) USING BTREE,
  INDEX `idx_membership_renewal_status`(`status`, `created_at`) USING BTREE,
  INDEX `fk_membership_renewal_processed_by`(`processed_by`) USING BTREE,
  CONSTRAINT `fk_membership_renewal_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_membership_renewal_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for reader_access_logs
-- ----------------------------
DROP TABLE IF EXISTS `reader_access_logs`;
CREATE TABLE `reader_access_logs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reading_session_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `member_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `digital_asset_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `book_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `event_type` enum('session_opened','pdf_stream','page_rendered','rate_limited','blocked','finished') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_number` int(10) UNSIGNED NULL DEFAULT NULL,
  `ip_address` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_reader_access_logs_session`(`reading_session_id`) USING BTREE,
  INDEX `idx_reader_access_logs_member_date`(`member_id`, `created_at`) USING BTREE,
  INDEX `idx_reader_access_logs_event_date`(`event_type`, `created_at`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 81 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for reading_points
-- ----------------------------
DROP TABLE IF EXISTS `reading_points`;
CREATE TABLE `reading_points`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `library_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `partner_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `latitude` decimal(10, 7) NULL DEFAULT NULL,
  `longitude` decimal(10, 7) NULL DEFAULT NULL,
  `radius_meters` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `daily_quota` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `quota_unit` enum('minutes','pages','books') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minutes',
  `opening_hours` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('draft','active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_reading_points_library`(`library_id`) USING BTREE,
  INDEX `idx_reading_points_status`(`status`) USING BTREE,
  INDEX `fk_reading_points_created_by`(`created_by`) USING BTREE,
  CONSTRAINT `fk_reading_points_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_reading_points_library` FOREIGN KEY (`library_id`) REFERENCES `libraries` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for reading_sessions
-- ----------------------------
DROP TABLE IF EXISTS `reading_sessions`;
CREATE TABLE `reading_sessions`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `book_id` bigint(20) UNSIGNED NOT NULL,
  `digital_asset_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `reading_point_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `reading_token_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `secure_token` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `started_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `last_seen_at` datetime(0) NULL DEFAULT NULL,
  `ended_at` datetime(0) NULL DEFAULT NULL,
  `last_page` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `ip_address` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `latitude` decimal(10, 7) NULL DEFAULT NULL,
  `longitude` decimal(10, 7) NULL DEFAULT NULL,
  `access_policy` enum('online_only','download_allowed','location_only','member_only','internal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'online_only',
  `access_origin` enum('external','reading_point','library','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'external',
  `access_location_label` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `quota_charged` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `quota_unit` enum('minutes','pages','books') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('active','finished','expired','blocked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_reading_sessions_member`(`member_id`) USING BTREE,
  INDEX `idx_reading_sessions_book`(`book_id`) USING BTREE,
  INDEX `idx_reading_sessions_asset`(`digital_asset_id`) USING BTREE,
  INDEX `idx_reading_sessions_point`(`reading_point_id`) USING BTREE,
  INDEX `fk_reading_sessions_token`(`reading_token_id`) USING BTREE,
  INDEX `idx_reading_sessions_secure_token`(`secure_token`) USING BTREE,
  INDEX `idx_reading_sessions_member_asset`(`member_id`, `digital_asset_id`, `status`) USING BTREE,
  CONSTRAINT `fk_reading_sessions_asset` FOREIGN KEY (`digital_asset_id`) REFERENCES `digital_assets` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_reading_sessions_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_reading_sessions_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_reading_sessions_point` FOREIGN KEY (`reading_point_id`) REFERENCES `reading_points` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_reading_sessions_token` FOREIGN KEY (`reading_token_id`) REFERENCES `reading_tokens` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for reading_tokens
-- ----------------------------
DROP TABLE IF EXISTS `reading_tokens`;
CREATE TABLE `reading_tokens`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `reading_point_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `token` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quota_total` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `quota_used` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `quota_unit` enum('minutes','pages','books') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minutes',
  `issued_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `expires_at` datetime(0) NULL DEFAULT NULL,
  `status` enum('active','used','expired','revoked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `issued_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `revoked_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `revoked_at` datetime(0) NULL DEFAULT NULL,
  `revoke_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_reading_tokens_token`(`token`) USING BTREE,
  INDEX `idx_reading_tokens_member`(`member_id`) USING BTREE,
  INDEX `idx_reading_tokens_point`(`reading_point_id`) USING BTREE,
  INDEX `idx_reading_tokens_status`(`status`, `expires_at`) USING BTREE,
  INDEX `fk_reading_tokens_issued_by`(`issued_by`) USING BTREE,
  INDEX `idx_reading_tokens_revoked_by`(`revoked_by`) USING BTREE,
  CONSTRAINT `fk_reading_tokens_issued_by` FOREIGN KEY (`issued_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_reading_tokens_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_reading_tokens_point` FOREIGN KEY (`reading_point_id`) REFERENCES `reading_points` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 30 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 71 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 62 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

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

-- ----------------------------
-- Table structure for transaction_sync_runs
-- ----------------------------
DROP TABLE IF EXISTS `transaction_sync_runs`;
CREATE TABLE `transaction_sync_runs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_database` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inlislite_v3',
  `source_table` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sync_type` enum('manual','scheduled','dry_run') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `mode` enum('import_new','refresh_existing','dry_run') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'import_new',
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
  INDEX `idx_transaction_sync_runs_status`(`status`) USING BTREE,
  INDEX `idx_transaction_sync_runs_created_at`(`created_at`) USING BTREE,
  INDEX `fk_transaction_sync_runs_created_by`(`created_by`) USING BTREE,
  CONSTRAINT `fk_transaction_sync_runs_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for visit_kiosk_qr_tokens
-- ----------------------------
DROP TABLE IF EXISTS `visit_kiosk_qr_tokens`;
CREATE TABLE `visit_kiosk_qr_tokens`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `library_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `token` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','used','expired','revoked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `expires_at` datetime(0) NOT NULL,
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_visit_kiosk_qr_tokens_token`(`token`) USING BTREE,
  INDEX `idx_visit_kiosk_qr_tokens_library`(`library_id`) USING BTREE,
  INDEX `idx_visit_kiosk_qr_tokens_status_expiry`(`status`, `expires_at`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 88 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for visit_kiosk_settings
-- ----------------------------
DROP TABLE IF EXISTS `visit_kiosk_settings`;
CREATE TABLE `visit_kiosk_settings`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL DEFAULT current_timestamp(0),
  `updated_at` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_visit_kiosk_settings_key`(`setting_key`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
