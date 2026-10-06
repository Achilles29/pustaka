# Backup Database Berkala

Halaman admin: `/system/database-backups` pada menu **Pengaturan Sistem → Backup Database**.

- Jadwal mendukung harian, mingguan, atau bulanan dengan jam yang dapat diatur.
- Retensi menyimpan 2–365 file terbaru dan menghapus file lama setelah backup baru berhasil.
- Dump memakai `mariadb-dump --single-transaction` beserta routine, trigger, event, dan data biner.
- Kredensial database hanya berada dalam file sementara mode `0600` selama proses CLI.
- File backup disimpan privat di `storage/digital-donations/.system/database-backups` dengan mode `0600` dan hanya diunduh melalui controller berizin.
- Backup manual dimasukkan ke antrean agar request web tidak menjalankan proses sistem.

Cron akun aplikasi memanggil worker setiap menit:

```cron
* * * * * cd /www/wwwroot/pustaka && /usr/bin/php index.php database_backup_jobs run >> /tmp/pustaka-database-backup.log 2>&1
```

Worker hanya membuat backup ketika jadwal jatuh tempo atau terdapat permintaan manual. Pemeriksaan file awal dapat dilakukan dengan `gzip -t` untuk backup terkompresi.
