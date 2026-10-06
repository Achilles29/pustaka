CREATE TABLE IF NOT EXISTS learn_game_user_access (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 module_code VARCHAR(40) NOT NULL,
 is_blocked TINYINT(1) NOT NULL DEFAULT 0,
 reason VARCHAR(300) NULL,
 blocked_by INT UNSIGNED NULL,
 blocked_at DATETIME NULL,
 updated_at DATETIME NULL,
 UNIQUE KEY uq_game_user_module(user_id,module_code),
 KEY idx_game_access_module(module_code,is_blocked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO sys_page(code,module,title,route,description,is_active)
VALUES('learn_progress.index','Pembelajaran','Progres Pengguna','learn-progress','Manajemen progres dan akses pengguna seluruh game',1)
ON DUPLICATE KEY UPDATE title=VALUES(title),route=VALUES(route),description=VALUES(description),is_active=1;
SET @grp=(SELECT id FROM sys_menu WHERE menu_key='learn.group' LIMIT 1);
INSERT INTO sys_menu(parent_id,page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active,is_locked)
VALUES(@grp,(SELECT id FROM sys_page WHERE code='learn_progress.index'),'MAIN','learn.progress','Progres Pengguna','ti ti-chart-dots-3','learn-progress',12,1,1,0)
ON DUPLICATE KEY UPDATE page_id=VALUES(page_id),title=VALUES(title),icon=VALUES(icon),url=VALUES(url),is_visible=1,is_active=1;
INSERT INTO auth_role_permission(role_id,page_id,can_view,can_create,can_edit,can_delete,can_export,can_approve)
SELECT rp.role_id,p.id,1,0,1,1,1,0 FROM (SELECT 1 role_id UNION SELECT 2) rp JOIN sys_page p ON p.code='learn_progress.index'
ON DUPLICATE KEY UPDATE can_view=1,can_edit=1,can_delete=1,can_export=1;
CREATE TABLE IF NOT EXISTS learn_game_user_access (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id INT UNSIGNED NOT NULL,module_code VARCHAR(40) NOT NULL,is_blocked TINYINT(1) NOT NULL DEFAULT 0,reason VARCHAR(300) NULL,blocked_by INT UNSIGNED NULL,blocked_at DATETIME NULL,updated_at DATETIME NULL,UNIQUE KEY uq_game_user_module(user_id,module_code),KEY idx_game_access_module(module_code,is_blocked)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO sys_page(code,module,title,route,description,is_active) VALUES('learn_progress.index','Pembelajaran','Progres Pengguna','learn-progress','Manajemen progres dan akses pengguna seluruh game',1) ON DUPLICATE KEY UPDATE title=VALUES(title),route=VALUES(route),description=VALUES(description),is_active=1;
SET @grp=(SELECT id FROM sys_menu WHERE menu_key='learn.group' LIMIT 1);
INSERT INTO sys_menu(parent_id,page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active,is_locked) VALUES(@grp,(SELECT id FROM sys_page WHERE code='learn_progress.index'),'MAIN','learn.progress','Progres Pengguna','ti ti-chart-dots-3','learn-progress',12,1,1,0) ON DUPLICATE KEY UPDATE page_id=VALUES(page_id),title=VALUES(title),icon=VALUES(icon),url=VALUES(url),is_visible=1,is_active=1;
INSERT INTO auth_role_permission(role_id,page_id,can_view,can_create,can_edit,can_delete,can_export,can_approve) SELECT r.role_id,p.id,1,0,1,1,1,0 FROM (SELECT 1 role_id UNION SELECT 2) r JOIN sys_page p ON p.code='learn_progress.index' ON DUPLICATE KEY UPDATE can_view=1,can_edit=1,can_delete=1,can_export=1;
