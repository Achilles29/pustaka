INSERT INTO sys_menu (page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active)
SELECT p.id,'MAIN',n.menu_key,n.title,n.icon,n.url,n.sort_order,1,1 FROM sys_page p CROSS JOIN (
 SELECT 'library.exchange' menu_key,'Pinjam Antarlembaga' title,'ti ti-building-community' icon,'library-services/exchange' url,27 sort_order
 UNION ALL SELECT 'library.fines','Denda Manual','ti ti-receipt','library-services/fines',28
) n WHERE p.code='library.services' AND NOT EXISTS (SELECT 1 FROM sys_menu m WHERE m.menu_key=n.menu_key);
INSERT INTO sys_menu (page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active)
SELECT p.id,'LIBRARY',n.menu_key,n.title,n.icon,n.url,n.sort_order,1,1 FROM sys_page p CROSS JOIN (
 SELECT 'network.exchange' menu_key,'Pinjam Antarlembaga' title,'ti ti-building-community' icon,'library-services/exchange' url,56 sort_order
 UNION ALL SELECT 'network.fines','Denda Manual','ti ti-receipt','library-services/fines',57
) n WHERE p.code='library.workspace' AND NOT EXISTS (SELECT 1 FROM sys_menu m WHERE m.menu_key=n.menu_key);
