-- Registrasi halaman panduan dan item sidebar admin yang dapat dikelola dari RBAC.

INSERT INTO sys_page (code, module, title, route, description, is_active)
VALUES ('guide.index', 'guide', 'Panduan Aplikasi', 'panduan', 'Panduan penggunaan Pustaka Digital Rembang untuk member dan petugas.', 1)
ON DUPLICATE KEY UPDATE
    module = VALUES(module),
    title = VALUES(title),
    route = VALUES(route),
    description = VALUES(description),
    is_active = 1;

INSERT INTO auth_role_permission (role_id, page_id, can_view, can_create, can_edit, can_delete, can_export, can_approve)
SELECT r.id, p.id, 1, 0, 0, 0, 0, 0
FROM auth_role r
JOIN sys_page p ON p.code = 'guide.index'
WHERE r.code IN ('SUPERADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE can_view = 1;

INSERT INTO sys_menu (parent_id, page_id, menu_area, menu_key, title, icon, url, sort_order, is_visible, is_active, is_locked)
SELECT NULL, p.id, 'MAIN', 'guide', 'Panduan Aplikasi', 'ti ti-book-2', 'panduan', 15, 1, 1, 0
FROM sys_page p
WHERE p.code = 'guide.index'
ON DUPLICATE KEY UPDATE
    parent_id = VALUES(parent_id),
    page_id = VALUES(page_id),
    title = VALUES(title),
    icon = VALUES(icon),
    url = VALUES(url),
    sort_order = VALUES(sort_order),
    is_visible = 1,
    is_active = 1;
