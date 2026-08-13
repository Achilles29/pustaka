-- Gunakan nama ikon yang tersedia pada Tabler Icons versi yang dipakai aplikasi.
-- Mencegah kotak ikon kosong pada sidebar dan instalasi baru.
UPDATE `sys_menu`
SET `icon` = 'ti ti-feather'
WHERE `menu_key` = 'manuscripts.index';

UPDATE `sys_menu`
SET `icon` = 'ti ti-settings'
WHERE `menu_key` = 'system';

-- Ikon Reader juga sebelumnya tidak tersedia pada font icon aktif.
UPDATE `sys_menu`
SET `icon` = 'ti ti-file-description'
WHERE `menu_key` = 'reader.assets';
