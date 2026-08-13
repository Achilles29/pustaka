-- Akses sementara role ADMIN: seluruh halaman aktif kecuali modul sinkronisasi.
-- Aman dijalankan ulang; halaman sinkronisasi dikenali dari code atau route yang memuat "sync".

INSERT INTO auth_role_permission (
    role_id, page_id, can_view, can_create, can_edit, can_delete, can_export, can_approve
)
SELECT
    r.id, p.id, 1, 1, 1, 1, 1, 1
FROM auth_role r
JOIN sys_page p ON p.is_active = 1
WHERE r.code = 'ADMIN'
  AND LOWER(p.code) NOT LIKE '%sync%'
  AND LOWER(p.route) NOT LIKE '%sync%'
ON DUPLICATE KEY UPDATE
    can_view = VALUES(can_view),
    can_create = VALUES(can_create),
    can_edit = VALUES(can_edit),
    can_delete = VALUES(can_delete),
    can_export = VALUES(can_export),
    can_approve = VALUES(can_approve);

DELETE rp
FROM auth_role_permission rp
JOIN auth_role r ON r.id = rp.role_id AND r.code = 'ADMIN'
JOIN sys_page p ON p.id = rp.page_id
WHERE LOWER(p.code) LIKE '%sync%'
   OR LOWER(p.route) LIKE '%sync%';
