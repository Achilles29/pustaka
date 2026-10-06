-- Menu langsung menuju sumber riwayat sirkulasi yang sama dengan Request Buku.
INSERT INTO sys_menu (parent_id,page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active,is_locked)
SELECT parent.id,page.id,'MAIN','catalog.loans','Transaksi Peminjaman','ti ti-receipt-2','catalog/loans',26,1,1,0
FROM sys_menu parent
JOIN sys_page page ON page.code='catalog.requests'
WHERE parent.menu_key='collection_services'
ON DUPLICATE KEY UPDATE parent_id=VALUES(parent_id),page_id=VALUES(page_id),title=VALUES(title),icon=VALUES(icon),url=VALUES(url),sort_order=VALUES(sort_order),is_visible=1,is_active=1;
