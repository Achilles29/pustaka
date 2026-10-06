-- Add registry/menu only. Do not change existing accounts or operational data.
INSERT INTO sys_page (code,module,title,route,description)
SELECT 'reports.network','reports','Laporan Gabungan Perpustakaan','reports/network','Rekap kabupaten lintas dataset; data tanpa pemilik tetap belum terpetakan.'
WHERE NOT EXISTS (SELECT 1 FROM sys_page WHERE code='reports.network');

INSERT INTO auth_role_permission (role_id,page_id,can_view,can_create,can_edit,can_delete,can_export,can_approve)
SELECT r.id,p.id,1,0,0,0,1,0 FROM auth_role r CROSS JOIN sys_page p
WHERE r.code IN ('ADMIN','SUPERADMIN') AND p.code='reports.network'
AND NOT EXISTS (SELECT 1 FROM auth_role_permission rp WHERE rp.role_id=r.id AND rp.page_id=p.id);

INSERT INTO sys_menu (parent_id,page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active)
SELECT (SELECT id FROM sys_menu WHERE menu_key='reports' LIMIT 1),p.id,'MAIN','reports.network','Gabungan Perpustakaan','ti ti-building-community','reports/network',25,1,1
FROM sys_page p WHERE p.code='reports.network'
AND NOT EXISTS (SELECT 1 FROM sys_menu WHERE menu_key='reports.network');

INSERT INTO sys_menu (page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active)
SELECT p.id,'LIBRARY',n.menu_key,n.title,n.icon,n.url,n.sort_order,1,1 FROM sys_page p CROSS JOIN (
 SELECT 'network.dashboard' menu_key,'Dashboard' title,'ti ti-layout-dashboard' icon,'library-workspace' url,10 sort_order
 UNION ALL SELECT 'network.books','Katalog Buku','ti ti-books','library-workspace/records/books',20
 UNION ALL SELECT 'network.items','Eksemplar','ti ti-barcode','library-workspace/records/items',30
 UNION ALL SELECT 'network.members','Anggota Lokal','ti ti-users','library-workspace/records/members',40
 UNION ALL SELECT 'network.loans','Peminjaman','ti ti-book-upload','library-workspace/loans',50
 UNION ALL SELECT 'network.visits','Buku Tamu','ti ti-door-enter','library-workspace/visits',60
 UNION ALL SELECT 'network.reports','Laporan','ti ti-chart-bar','library-workspace/reports',70
 UNION ALL SELECT 'network.profile','Profil Perpustakaan','ti ti-building-community','library-workspace/profile',80
 UNION ALL SELECT 'network.settings','Aturan Peminjaman','ti ti-adjustments','library-workspace/settings',90
 UNION ALL SELECT 'network.admins','Admin Perpustakaan','ti ti-user-cog','library-workspace/admins',100
 UNION ALL SELECT 'network.account','Ganti Password','ti ti-key','library-workspace/account',110
) n WHERE p.code='library.workspace'
AND NOT EXISTS (SELECT 1 FROM sys_menu m WHERE m.menu_key=n.menu_key);
