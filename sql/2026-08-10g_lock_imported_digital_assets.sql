-- Koleksi yang diimpor pada 2026-08-10 hanya dibaca melalui Reader aman.
UPDATE `digital_assets`
SET `access_policy` = 'online_only',
    `reader_audience` = 'member',
    `pdf_delivery` = 'render_locked',
    `is_downloadable` = 0,
    `updated_at` = NOW()
WHERE `source_system` IN ('buku_kemendikdasmen_2026', 'project_gutenberg');

-- Pakai satu cover fallback resmi secara eksplisit hingga cover individual tersedia.
UPDATE `books`
SET `cover_local_path` = 'assets/img/book-cover-default.webp',
    `updated_at` = NOW()
WHERE `source_system` IN ('buku_kemendikdasmen_2026', 'project_gutenberg')
  AND (`cover_local_path` IS NULL OR `cover_local_path` = '');
