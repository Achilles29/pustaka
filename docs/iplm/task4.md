# Keputusan lanjutan task3

## 1. Verifikasi aktivasi akun publik — perlu dipilih sebelum login mandiri diaktifkan
=> “kode aktivasi unik” aktivasi manual oleh perpus, tinggal pilih lalu login ganti password. akun yang sudah di login tidak bisa di klaim lagi oleh orang lain. jika nanti ada kesalahan maka admin kabupaten yang akan menyelesaikan



- masukkan /aktivasi-perpustakaan dalam 1 halaman di sidebar admin biar nggak lupa
- instruksi ulang untuk persandingan agar cepat: 
   1. Perpusda hanya 1, tidak ada yang lain. hanya nomor 1 di database dan baris 641 di excel pendataan tadi
   2. jika nama sekolah teridentifikasi sama tapi npsn berbeda, maka gunakan saja yang ada di database
   3. jika nama perpustakaan atau lembaga induk di pendataan tidak ada unsur sekolah, maka itu bukan perpustakaan sekolah, bisa jadi TBM , seperti "Taman Bacaan Masyarakat Aji Gineng", dan Perpustakaan Swasta untuk pondok pesantren, seperti "Perpustakaan Al-Anwar 2 Putri", dan pespustakaan sekolah swasta seperti "Perpustakaan Maktabah" karena di lembaga induknya jelas SMP ISLAM AN- NAWAWIYYAH. ketika di nama maupun lembaga induk jelas menyebut SMP / SD yang disinyalir sebagai sekolah swasta, dan misal juga tidak ada di database eksisting, maka masukkan ke perpustakaan sekolah swasta. jadi database kita ini berisi database sekolah berdasarkan dapodik
   4. untuk perpustakaan sekolah yang disinyalir negeri tapi tidak ada di database, misal seperti beda nomor (di database DOROPAYUNG 1, di pendataan DOROPAYUNG 3), bisa jadi itu kesalahan pendataan. beri keterangan
   5. kalau di database ada tapi di pendataan belum ada, bisa jadi memang belum didata
   6. jika Sub jenis di pendataan KELURAHAN/DESA, maka itu adalah perpustakaan Desa / kelurahan, dan seterusnya sesuai Jenis dan sub jenis perpustakaan masing masing agar tidak overlab
   7. kesimpulannya, yang ada di database sekarang adalah dapodik sekolah negeri. perhatikan jenis dan sub jenis dari pendataan, itu yang jadi acuan, jika ada yang belum masuk jenis / sub jenis maka masukkan dulu di database kita. baru petakan dan masukkan perpustakaan selain perpusda dan perpus sekolah negeri yang sudah masuk database kita, walaupun itu bukan kewenangan kabupaten / kota tetap masukkan sub jenisnya
