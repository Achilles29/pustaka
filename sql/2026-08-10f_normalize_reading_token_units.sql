-- Kuota reader dipotong ketika sesi buku dibuka; jangan tampilkan sebagai menit/halaman.
UPDATE `reading_points`
SET `daily_quota` = 5, `quota_unit` = 'books'
WHERE `status` = 'active' AND `quota_unit` <> 'books';

UPDATE `learn_reward_catalog`
SET `name` = 'Paket 5 Sesi Baca', `description` = 'Token untuk 5 sesi baca buku digital di luar zona gratis.',
    `quota_amount` = 5, `quota_unit` = 'books', `token_validity_days` = 14
WHERE `code` = 'read_30min';

UPDATE `learn_reward_catalog`
SET `name` = 'Paket 10 Sesi Baca', `description` = 'Token untuk 10 sesi baca buku digital di luar zona gratis.',
    `quota_amount` = 10, `quota_unit` = 'books', `token_validity_days` = 30
WHERE `code` = 'read_60min';

UPDATE `learn_reward_catalog`
SET `name` = 'Paket 1 Sesi Baca', `description` = 'Paket lama satu sesi; dinonaktifkan agar pilihan hadiah konsisten.', `is_active` = 0
WHERE `code` = 'read_1book';
