
1. **Rentang data IPLM 2026** ==> Periode penilaian 2026, untuk menilai rentang tahun 2025. sekalian buatkan kalau belum ada

2. **Populasi resmi** => hitung populasi berdasarkan perpustakaan terdaftat di libraries di database kita yang sesuai dengan sub jenis yang masuk penilaian.

3. **TK dan SKB** => abaikan untuk pendataan iplm dan perhitungan karena tidak termasuk sub jenis yang menjadi kewenangan

4. **Kualifikasi tenaga** => gunakan D2 minimal, nanti akan saya ubah sendiri kalau ada perubahan

5. **Bukti anggaran** => data dukung lebih lengkap saya taruh file sendiri

6. **Parameter analisis** => saya tidak punya, kalau kamu punya atau bisa cari boleh

7. **Pemustaka (orang)**: jumlah pemanfaatan/kunjungan, tidak terbatas orang unik


tambahan: 
- /iplm/settings jadikan halaman nya ber tab
- buat tampilan /iplm/form/ lebih rapi dan enak dibaca, buat ber tab, form bukti dukung posisinya seblah kanan form input
- jenis bukti dukung dapat dilihat di bukti_dukung.xlsx



tambahan tugas:
file pendataan.xls merupakan file hasil pendataan ke perpustakaan seluruh kabupaten rembang. jenis data yang dihimpun bisa jadi berbeda dengan data iplm yang kita butuhkan, tapi beberapa data ada yang sama dan itu harus sinkron.
1. petakaan perpustakaan mana yang ada di pendaatan tapi belum ada di kita, dan sebaliknya. untuk perpustakaan bisa jadi berbeda karena penulisan penamaan yang berbeda. harusnya nanti kita samakan dengan merubah hasil pendataan
2. untuk koordinat saya lebih percaya dengan data kita, tapi tidak ada salahnya kalau bisa di cek selama tidak menghabiskan waktu dan token
3. petakan data di pendataan yang sudah ada di database kita dan yang belum ada. untuk yang belum ada arahnya tetap kita masukkan sebagai bahan pendataan, namun tidak terkait dengan IPLM


form input tautan bukti dukung jadi 1 saja ya.
untuk yang ditampilkan di masing maising soal itu keterangannya aja, bukti dukungnya berupa apa sesuai di excel bukti dukung



masing masing perpustakaan (kecuali perpustakaan pusat) belum tau kalau mereka punya akun. jadi polanya nanti buatkan halaman publik untuk masing masing perpustakaan mencari data mereka. halaman tersebut menampilkan peta dan ada kolom pencarian ajax data nama perpustakaan atau nama sekolah atau npsn. jadi user pertama masuk halaman bisa mencari di gps atau mengetik nama tadi di form pencarian. lalu di pilih, setelah dipilih akan ada modal warning untuk meyakinkan bahwa itu adalah milik user, setelah oke langsung login dan ganti password.