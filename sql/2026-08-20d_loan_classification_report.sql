INSERT INTO `sys_page` (`code`,`module`,`title`,`route`,`description`) VALUES
('reports.loan_classification','reports','Peminjaman per Klasifikasi','reports/loans-by-classification','Analitik buku yang dipinjam berdasarkan kelas utama klasifikasi DDC.')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`),`route`=VALUES(`route`),`description`=VALUES(`description`),`is_active`=1;

INSERT INTO `auth_role_permission` (`role_id`,`page_id`,`can_view`,`can_create`,`can_edit`,`can_delete`,`can_export`,`can_approve`)
SELECT r.id,p.id,1,0,0,0,1,0 FROM `auth_role` r JOIN `sys_page` p ON p.code='reports.loan_classification' WHERE r.code IN ('SUPERADMIN','ADMIN')
ON DUPLICATE KEY UPDATE `can_view`=1,`can_export`=1;

SET @reports_parent := (SELECT id FROM `sys_menu` WHERE menu_key='reports' LIMIT 1);
INSERT INTO `sys_menu` (`parent_id`,`page_id`,`menu_area`,`menu_key`,`title`,`icon`,`url`,`sort_order`,`is_visible`,`is_active`,`is_locked`)
SELECT @reports_parent,p.id,'MAIN','reports.loan_classification','Peminjaman per Klasifikasi','ti ti-library','reports/loans-by-classification',20,1,1,0 FROM `sys_page` p WHERE p.code='reports.loan_classification'
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`),`page_id`=VALUES(`page_id`),`title`=VALUES(`title`),`icon`=VALUES(`icon`),`url`=VALUES(`url`),`sort_order`=VALUES(`sort_order`),`is_visible`=1,`is_active`=1;
