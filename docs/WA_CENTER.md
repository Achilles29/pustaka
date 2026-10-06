# WA Center Pustaka

WA Center mengadopsi engine QR WhatsApp dari Finance: engine hanya mendengar di `127.0.0.1:3071`, memakai token, menyimpan sesi pada `wa-engine/auth_info`, dan mengirim tabel `wa_outbox`.

1. Pasang Node.js LTS 20+ (`node -v`).
2. `cd /www/wwwroot/pustaka/wa-engine && cp .env.example .env` lalu isi DB dan token acak.
3. Samakan token dengan **WA Center → Pengaturan Koneksi**.
4. Jalankan `npm install --omit=dev` lalu `node index.js` untuk uji.
5. Produksi: gunakan systemd dengan `WorkingDirectory=/www/wwwroot/pustaka/wa-engine`, `ExecStart=/usr/bin/node /www/wwwroot/pustaka/wa-engine/index.js`, `Restart=always`, dan user web server. Jangan membuka port 3071 ke internet.
6. Buka `/wa/settings`, muat QR, lalu scan melalui WhatsApp → Perangkat tertaut.

## Otomasi peminjaman

Server menjalankan `/etc/cron.d/pustaka-wa-automation` setiap 15 menit dengan perintah `php index.php whatsapp_jobs run`. Job ini hanya membuat pesan di `wa_outbox`; service `pustaka-wa.service` tetap yang mengirimkannya.

- Pendaftaran diterima dan pendaftaran disetujui masuk ke antrian saat proses berlangsung.
- Perubahan status request, termasuk ketika buku diserahkan, menggunakan template `loan_request_status`.
- Pengingat H-1 jatuh tempo aktif secara default dan hanya dikirim sekali untuk setiap transaksi.
- Keterlambatan dapat dikirim manual dari **Transaksi Peminjaman → Terlambat → Ingatkan WA**, atau diaktifkan otomatis dari **WA Center → Pengaturan**. Interval pengulangan keterlambatan dapat diatur 1–30 hari dan setiap siklus tidak dikirim ganda.

Menghapus penautan dari Pengaturan WA mengeluarkan sesi WhatsApp dan menghapus kredensial lokal. Menonaktifkan WA hanya menghentikan pengiriman tanpa menghapus sesi.

Template mendukung `{member_name}`, `{member_no}`, `{request_code}`, `{book_title}`, `{status}`, `{staff_note}`, `{due_date}`, dan `{late_days}`.

Gunakan nomor layanan khusus. Baileys adalah WhatsApp Web tidak resmi; untuk volume tinggi gunakan WhatsApp Business Platform resmi. Broadcast/promo harus berbasis persetujuan penerima serta menyediakan cara berhenti berlangganan.
