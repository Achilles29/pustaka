-- Menu-only migration. Keep every existing leaf ID, route and permission.
INSERT INTO sys_menu (menu_area,menu_key,title,icon,sort_order,is_visible,is_active)
SELECT 'LIBRARY',g.menu_key,g.title,g.icon,g.sort_order,1,1 FROM (
 SELECT 'network.group.collection' menu_key,'Koleksi & Inventaris' title,'ti ti-books' icon,20 sort_order
 UNION ALL SELECT 'network.group.members','Keanggotaan','ti ti-users',30
 UNION ALL SELECT 'network.group.circulation','Sirkulasi','ti ti-book-upload',40
 UNION ALL SELECT 'network.group.visits','Kunjungan','ti ti-door-enter',50
 UNION ALL SELECT 'network.group.settings','Pengaturan','ti ti-settings',80
) g WHERE NOT EXISTS (SELECT 1 FROM sys_menu m WHERE m.menu_key=g.menu_key);

UPDATE sys_menu child JOIN sys_menu parent ON parent.menu_key='network.group.collection' AND parent.menu_area='LIBRARY'
SET child.parent_id=parent.id
WHERE child.menu_area='LIBRARY' AND child.menu_key IN ('network.books','network.items','network.stock','network.labels');
UPDATE sys_menu child JOIN sys_menu parent ON parent.menu_key='network.group.members' AND parent.menu_area='LIBRARY'
SET child.parent_id=parent.id
WHERE child.menu_area='LIBRARY' AND child.menu_key IN ('network.members','network.cards','network.registrations');
UPDATE sys_menu child JOIN sys_menu parent ON parent.menu_key='network.group.circulation' AND parent.menu_area='LIBRARY'
SET child.parent_id=parent.id
WHERE child.menu_area='LIBRARY' AND child.menu_key IN ('network.loans','network.reservations','network.exchange','network.fines');
UPDATE sys_menu child JOIN sys_menu parent ON parent.menu_key='network.group.visits' AND parent.menu_area='LIBRARY'
SET child.parent_id=parent.id
WHERE child.menu_area='LIBRARY' AND child.menu_key IN ('network.visits','network.kiosk');
UPDATE sys_menu child JOIN sys_menu parent ON parent.menu_key='network.group.settings' AND parent.menu_area='LIBRARY'
SET child.parent_id=parent.id
WHERE child.menu_area='LIBRARY' AND child.menu_key IN ('network.profile','network.settings','network.admins','network.account');

UPDATE sys_menu child JOIN sys_menu parent ON parent.menu_key='collection_services' AND parent.menu_area='MAIN'
SET child.parent_id=parent.id, child.sort_order=CASE child.menu_key WHEN 'library.services' THEN 65 ELSE 75 END
WHERE child.menu_area='MAIN' AND child.menu_key IN ('library.services','manuscripts.index');
UPDATE sys_menu child JOIN sys_menu parent ON parent.menu_key='network_services' AND parent.menu_area='MAIN'
SET child.parent_id=parent.id,child.sort_order=15
WHERE child.menu_area='MAIN' AND child.menu_key='library-workspace';
UPDATE sys_menu child JOIN sys_menu parent ON parent.menu_key='transactions' AND parent.menu_area='MAIN'
SET child.parent_id=parent.id,child.sort_order=CASE child.menu_key
 WHEN 'catalog.requests' THEN 10 WHEN 'catalog.loans' THEN 20 WHEN 'library.exchange' THEN 30 ELSE 40 END
WHERE child.menu_area='MAIN' AND child.menu_key IN ('catalog.requests','catalog.loans','library.exchange','library.fines');
UPDATE sys_menu SET title='Sirkulasi & Kunjungan' WHERE menu_area='MAIN' AND menu_key='transactions';
UPDATE sys_menu SET sort_order=CASE menu_key
 WHEN 'transactions.index' THEN 50 WHEN 'guestbook.monitor' THEN 60 WHEN 'guestbook.settings' THEN 70 ELSE 80 END
WHERE menu_area='MAIN' AND menu_key IN ('transactions.index','guestbook.monitor','guestbook.settings','transactions.sync');
