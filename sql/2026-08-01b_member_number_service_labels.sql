UPDATE `sys_page`
SET `title` = 'Layanan Harian',
    `description` = 'Buku tamu, hak layanan, peminjaman, dan detail item hasil sinkronisasi INLISLite.'
WHERE `code` = 'transactions.index';

UPDATE `sys_page`
SET `title` = 'Sinkronisasi Layanan',
    `description` = 'Sinkronisasi buku tamu, hak layanan, dan histori peminjaman dari INLISLite.'
WHERE `code` = 'transactions.sync';

UPDATE `sys_menu`
SET `title` = 'Layanan Harian',
    `icon` = 'ti ti-activity-heartbeat'
WHERE `menu_key` = 'transactions';

UPDATE `sys_menu`
SET `title` = 'Aktivitas Layanan',
    `icon` = 'ti ti-timeline-event'
WHERE `menu_key` = 'transactions.index';

UPDATE `sys_menu`
SET `title` = 'Sinkronisasi Layanan',
    `icon` = 'ti ti-refresh'
WHERE `menu_key` = 'transactions.sync';
