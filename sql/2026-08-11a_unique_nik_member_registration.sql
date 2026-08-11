-- NIK menjadi identitas utama: satu NIK hanya boleh memiliki satu data member
-- dan satu rekam pendaftaran. Data yang ada sudah diaudit tanpa duplikasi
-- sebelum migrasi ini dijalankan.

CREATE UNIQUE INDEX IF NOT EXISTS `uq_members_identity_number`
  ON `members` (`identity_number`);

CREATE UNIQUE INDEX IF NOT EXISTS `uq_member_registration_identity_number`
  ON `member_registration_requests` (`identity_number`);
