sekarang saya ingin melakukan penambahan modul untuk penginputan komponen penilaian IPLM
- akun admin pemkab bisa CRUD dan melihat dashboard rekap semua hasil inputan admin lokal
- admin lokal melakukan input form sesuai data yang diperlukan, dan input link data dukung (link biasanya google drive), jadi data dukungnya diupload di google drive masing masing

template form data dukung ada di /www/wwwroot/pustaka/docs/iplm/Template_IPLM_2026.xlsx
form isian untuk kabupaten /kota ada di sheet IPLM Kab-Kota

petakan form isian data, dan buatkan form nya. untuk data yang sudah ada maka tidak perlu sekolah input ulang. seperti jenis sekolah, npsn, langsung munculkan data sesuai data sekolah masing masing.
namun sekolah tetap bisa merubah, dan jika ada ketidaksesuaian data dengan data di /libraries berikan warning, dan pastikan mana yang benar. 
misal ada data yang diubah, jangan ubah dulu data lama yang menurut mereka salah, hanya lakukan perubahan di form yang mereka input, tapi berika warning pada dashboard adamin bahwa terdapat perbedaan data sehingga admin harus melakukan verifikasi

kendalanya beberapa data memang sudah ada di /libraries menurutmu bagaimana? apakah kita jadikan 1 saja di tabel "pendataan" agar tidak doble data? kamu kasih saran ya.

untuk data yang sifatnya pilihan maka buatkan dropdown enum sehingga perpus lokal tidak perlu input manual (seperti jenis dan sub perpustakaan itu bisa diambil dari sheet Validasi Data di excel nya
untuk jenis dan sub jenis perpustakaan ini lakukan penyesuaian untuk aplikasi kita dan refraktur database dan data yang sudah diinput untuk menambahkan jenis dan sub jenis sesuai di excel tersebut:

Jenis Perpustakaan		Sub Jenis Perpustakaan
Perpustakaan Umum		Perpustakaan Kabupaten/Kota
Perpustakaan Umum		Perpustakaan Kecamatan
Perpustakaan Umum		Perpustakaan Desa/Kelurahan
Perpustakaan Umum		Perpustakaan TBM/Rumah Baca/penamaan lainnya
Perpustakaan Sekolah	Perpustakaan SMP (Negeri maupun Swasta)
Perpustakaan Sekolah	Perpustakaan SD (Negeri maupun Swasta)
Perpustakaan Khusus		OPD Kabupaten

buatkan master database tambahkan data diatas, dan buatkan halaman CRUD untuk Master Jenis Perpustakaan dan Sub Jenis Perpustakaan.

sesuaikan juga form input di /libraries/create dan semua tampilan yang relevan.
jadi untuk semua sekolah yang sudah terinput jenis nya diganti Perpustakaan Sekolah, tambah Sub Jenis sesuai masing masing. kalau kemarin ada TK maka tambah sub jenis TK.
untuk Jenis Perpustakaan yang saat ini ada di enum dropdown aplikasi kita tapi tidak ada di pilihan baru tersebut, jangan hapus, tetap tambahkan (seperti Pojok Baca,Komunitas Litaeras, taruh datanya dibawah setelah data diatas).


buatkan definis yang jelas untuk masing masing form agar user tidak kesulitan saat input. (definisi ada di sheet Definisi)
untuk list bukti dukung yang harus di upload sementara ada di Format Bukti Dukung.pptx halaman 2 dan 3, sementara isi sesuai itu nanti kita sesuaikan. admin kabupaten harus mempunyai akses CRUD terhadap form nya


tambahkan role akses dan halamanya ke sidebar sesuai kewenangan masing masing



lalu untuk cara analisa dan perhitungan skornya ada di bahan_iplm.pptx halaman 13-15. coba kamu buatkan juga halaman analisa perhitungannya sesuai paparan tersebut dan sesuai ketentuan yang berlaku (kamu cari ketentuannya) agar bisa dilihat di dashbord iplm admin kabupaten
