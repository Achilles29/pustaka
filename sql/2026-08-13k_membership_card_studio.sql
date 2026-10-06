CREATE TABLE IF NOT EXISTS `membership_card_settings` (
  `id` TINYINT UNSIGNED NOT NULL,
  `config_json` LONGTEXT NOT NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_membership_card_settings_user` FOREIGN KEY (`updated_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `membership_card_settings` (`id`, `config_json`)
VALUES (1, '{"primary":"#062D62","secondary":"#087D82","accent":"#F6C85F","surface":"grid","photo_position":"left","org_label":"PEMERINTAH KABUPATEN REMBANG","card_title":"Pustaka Digital Rembang","footer_label":"KARTU ANGGOTA DIGITAL","show_photo":true,"show_qr":true,"show_status":true,"show_expiry":true,"show_member_type":true,"corner_style":"rounded","tagline":"Merawat Ingatan, Membuka Pengetahuan.","layout":{"brand":{"x":6,"y":8,"w":56,"h":14},"status":{"x":77,"y":9,"w":17,"h":9},"photo":{"x":6,"y":29,"w":17,"h":37},"member_label":{"x":27,"y":29,"w":52,"h":5},"member_name":{"x":27,"y":34,"w":56,"h":22},"member_number":{"x":27,"y":58,"w":54,"h":5},"member_type":{"x":27,"y":64,"w":38,"h":5},"expiry":{"x":6,"y":82,"w":27,"h":11},"footer":{"x":42,"y":82,"w":30,"h":11},"qr":{"x":86,"y":79,"w":9,"h":14}},"custom_objects":[{"id":"heritage_line","type":"line","text":"","color":"#F6C85F","fill":"#F6C85F","font_size":14,"opacity":55,"x":5,"y":77,"w":90,"h":0.45}]}')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Kartu dan studio desain memakai hak Keanggotaan yang sudah ada.
INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.id, page.id, 'MAIN', 'members.cards', 'Kartu Anggota', 'ti ti-id-badge-2', 'members/cards', 15, 1, 1, 0
FROM `sys_menu` parent JOIN `sys_page` page ON page.code = 'members.index'
WHERE parent.menu_key = 'membership_services'
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`), `page_id`=VALUES(`page_id`), `title`=VALUES(`title`), `icon`=VALUES(`icon`), `url`=VALUES(`url`), `sort_order`=VALUES(`sort_order`), `is_visible`=1, `is_active`=1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.id, page.id, 'MAIN', 'members.card_design', 'Studio Desain Kartu', 'ti ti-palette', 'members/cards/design', 16, 1, 1, 0
FROM `sys_menu` parent JOIN `sys_page` page ON page.code = 'members.index'
WHERE parent.menu_key = 'membership_services'
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`), `page_id`=VALUES(`page_id`), `title`=VALUES(`title`), `icon`=VALUES(`icon`), `url`=VALUES(`url`), `sort_order`=VALUES(`sort_order`), `is_visible`=1, `is_active`=1;
