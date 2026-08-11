<?php
/**
 * Batch kurasi SD Kelas 2/Fase A: 30 soal tambahan per mapel.
 * Semua entri ditulis eksplisit (bukan formula generator) dan divalidasi sebelum insert.
 * Jalankan: php tools/seed_sd2_curated_30_per_subject.php
 */
define('BASEPATH', __DIR__);
define('ENVIRONMENT', 'production');
$db = [];
require dirname(__DIR__) . '/application/config/database.php';
$c = $db['default'];
$m = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($m->connect_errno) throw new RuntimeException('Koneksi database gagal.');
$m->set_charset('utf8mb4');

$q = static function ($difficulty, $text, $correct, array $wrong, $explanation) {
    return compact('difficulty', 'text', 'correct', 'wrong', 'explanation');
};

$banks = [
'matematika' => [
 $q('easy','Nilai angka 5 pada bilangan 52 adalah ....','50',['5','2','52'],'Angka 5 berada pada tempat puluhan, nilainya 50.'),
 $q('easy','Bilangan yang tepat di antara 36 dan 38 adalah ....','37',['35','39','40'],'Urutan bilangannya 36, 37, 38.'),
 $q('easy','Hasil dari 9 + 8 adalah ....','17',['15','16','18'],'9 ditambah 8 sama dengan 17.'),
 $q('easy','Hasil dari 18 - 7 adalah ....','11',['10','12','13'],'18 dikurangi 7 sama dengan 11.'),
 $q('easy','Bilangan yang paling kecil adalah ....','45',['54','50','55'],'45 lebih kecil daripada 50, 54, dan 55.'),
 $q('easy','70 + 6 sama dengan ....','76',['67','70','706'],'Tujuh puluhan dan enam satuan membentuk 76.'),
 $q('easy','Angka satuan pada bilangan 89 adalah ....','9',['8','80','89'],'Angka paling kanan adalah satuan, yaitu 9.'),
 $q('easy','12 + 5 = ....','17',['15','16','18'],'12 ditambah 5 sama dengan 17.'),
 $q('easy','19 - 4 = ....','15',['14','16','13'],'19 dikurangi 4 sama dengan 15.'),
 $q('easy','Bentuk yang tidak mempunyai sudut adalah ....','lingkaran',['segitiga','persegi','persegi panjang'],'Lingkaran tidak memiliki sudut.'),
 $q('medium','Nina memiliki 13 manik-manik. Ia mendapat 6 lagi. Jumlah manik-manik Nina adalah ....','19',['18','20','7'],'13 ditambah 6 sama dengan 19.'),
 $q('medium','Ada 17 balon. Lima balon pecah. Balon yang tersisa adalah ....','12',['11','13','22'],'17 dikurangi 5 sama dengan 12.'),
 $q('medium','Urutan yang benar dari terbesar adalah ....','91, 79, 67',['67, 79, 91','79, 91, 67','91, 67, 79'],'Bandingkan puluhan: 9 puluhan, lalu 7 puluhan, lalu 6 puluhan.'),
 $q('medium','Empat anak masing-masing membawa 3 pensil. Jumlah pensil mereka adalah ....','12',['7','9','16'],'Hitung 3 + 3 + 3 + 3 = 12.'),
 $q('medium','Delapan kue dibagikan sama banyak kepada dua anak. Setiap anak mendapat ....','4',['2','6','8'],'8 dibagi menjadi dua bagian sama besar, masing-masing 4.'),
 $q('medium','Pola 5, 10, 15, ...., 25. Bilangan yang hilang adalah ....','20',['18','19','22'],'Pola bertambah 5 setiap kali.'),
 $q('medium','Dua puluh siswa memilih buah. Apel dipilih 9 siswa, pisang 6 siswa. Siswa yang memilih buah lain ada ....','5',['3','4','15'],'20 - 9 - 6 = 5.'),
 $q('medium','Satu pizza dibagi menjadi empat bagian sama besar. Satu bagian disebut ....','seperempat',['setengah','satu utuh','dua bagian'],'Satu dari empat bagian sama besar disebut seperempat.'),
 $q('medium','Kotak pensil berada di atas meja. Meja berada di bawah ....','kotak pensil',['kursi','lantai','tas'],'Jika kotak pensil di atas meja, meja di bawah kotak pensil.'),
 $q('medium','Benda yang lebih ringan adalah ....','daun',['batu','kursi','meja'],'Daun lebih ringan daripada benda-benda tersebut.'),
 $q('hard','Reno mempunyai 16 kartu. Ia memberi 4 kartu, lalu mendapat 3 kartu. Kartu Reno sekarang ada ....','15',['13','16','23'],'16 - 4 = 12, lalu 12 + 3 = 15.'),
 $q('hard','Ada 20 jeruk. Delapan jeruk dimasukkan ke keranjang merah dan 7 ke keranjang biru. Jeruk yang belum dimasukkan ada ....','5',['4','6','15'],'20 - 8 - 7 = 5.'),
 $q('hard','Tiga kotak berisi 5 kelereng tiap kotak. Enam kelereng diberikan kepada teman. Sisanya ....','9',['6','8','11'],'Awalnya 5 + 5 + 5 = 15, lalu 15 - 6 = 9.'),
 $q('hard','Dari 14, 18, dan 20, dua bilangan yang jumlahnya 32 adalah ....','14 dan 18',['14 dan 20','18 dan 20','14 dan 14'],'14 + 18 = 32.'),
 $q('hard','Mira membaca 7 halaman pagi hari dan 8 halaman sore hari. Ia membaca ulang 2 halaman yang sama. Halaman berbeda yang dibaca Mira ada ....','13',['15','11','17'],'7 + 8 = 15, tetapi 2 halaman dihitung dua kali; 15 - 2 = 13.'),
 $q('hard','Sebuah pita panjangnya 18 jengkal. Setelah dipotong 7 jengkal, sisa pita adalah ....','11 jengkal',['10 jengkal','12 jengkal','25 jengkal'],'18 - 7 = 11.'),
 $q('hard','Dua kelompok berisi 6 anak dan satu kelompok berisi 4 anak. Jumlah anak seluruhnya adalah ....','16',['10','12','18'],'6 + 6 + 4 = 16.'),
 $q('hard','Pada tabel, buku cerita dipinjam 8 anak dan buku pengetahuan dipinjam 5 anak. Jika 3 anak lagi meminjam buku pengetahuan, kedua jumlahnya menjadi ....','sama banyak',['buku cerita lebih banyak','buku pengetahuan lebih banyak','tidak dapat dibandingkan'],'5 + 3 = 8, sama dengan buku cerita.'),
 $q('hard','Bilangan 84 diuraikan menjadi puluhan dan satuan. Jika 2 satuan diambil, bilangan baru adalah ....','82',['64','80','86'],'84 - 2 = 82.'),
 $q('hard','Tono memiliki uang Rp10.000. Ia membeli pensil Rp3.000 dan penghapus Rp2.000. Uang Tono tersisa ....','Rp5.000',['Rp4.000','Rp6.000','Rp15.000'],'10.000 - 3.000 - 2.000 = 5.000.'),
],
'bahasa_indonesia' => [
 $q('easy','Kata yang menunjukkan nama orang adalah ....','Salsa',['sekolah','membaca','indah'],'Salsa adalah nama orang.'),
 $q('easy','Huruf pertama pada awal kalimat sebaiknya ditulis ....','kapital',['kecil semua','miring','berwarna merah'],'Awal kalimat menggunakan huruf kapital.'),
 $q('easy','Kata "lari" merupakan kegiatan ....','bergerak cepat',['makan','tidur','membaca'],'Lari adalah kegiatan bergerak cepat.'),
 $q('easy','Lawan kata "bersih" adalah ....','kotor',['rapi','wangi','putih'],'Lawan kata bersih adalah kotor.'),
 $q('easy','Kalimat yang benar adalah ....','Budi bermain layang-layang.',['Budi bermain layang layang.','Budi bermain layang-layang?','budi Bermain layang-layang'],'Nama Budi memakai kapital dan kalimat berita diakhiri titik.'),
 $q('easy','Kata yang tepat untuk "Ayah pergi ke .... untuk bekerja" adalah ....','kantor',['bantal','kolam','piring'],'Kantor adalah tempat yang sesuai untuk bekerja.'),
 $q('easy','Tanda baca untuk mengakhiri kalimat perintah adalah ....','titik',['koma','tanda tanya','tanda petik'],'Kalimat perintah biasa diakhiri titik.'),
 $q('easy','Kata "gembira" artinya ....','senang',['sedih','marah','lelah'],'Gembira berarti senang.'),
 $q('easy','Kata yang tepat untuk melengkapi "Adik minum ...." adalah ....','air',['buku','kursi','sepeda'],'Air dapat diminum.'),
 $q('easy','Huruf vokal pada kata "buku" adalah ....','u',['b','k','p'],'Huruf vokalnya adalah u dan u.'),
 $q('medium','Bacalah: "Paman membawa bibit mangga. Ia menanamnya di halaman." Yang ditanam paman adalah ....','bibit mangga',['halaman','paman','pohon besar'],'Teks menyebut paman membawa lalu menanam bibit mangga.'),
 $q('medium','Bacalah: "Nisa sakit gigi. Ia pergi ke dokter gigi." Mengapa Nisa pergi ke dokter gigi?','karena sakit gigi',['karena ingin bermain','karena membeli buku','karena hujan'],'Kalimat pertama menjelaskan Nisa sakit gigi.'),
 $q('medium','Susunan kata yang benar adalah ....','Ibu memasak nasi di dapur.',['dapur di nasi memasak Ibu','memasak Ibu dapur di nasi','nasi di Ibu dapur memasak'],'Susunannya jelas: subjek, kegiatan, benda, tempat.'),
 $q('medium','Bacalah: "Kelas dua mendapat jadwal piket hari Selasa." Informasi penting pada kalimat itu adalah ....','piket hari Selasa',['piket setiap hari','kelas satu piket','jadwal bermain'],'Kalimat menyatakan jadwal piket hari Selasa.'),
 $q('medium','Kalimat tanya yang tepat adalah ....','Siapa nama ketua kelas?',['Siapa nama ketua kelas.','Siapa nama ketua kelas!','Siapa nama ketua kelas,'],'Kalimat tanya memakai tanda tanya.'),
 $q('medium','Bacalah: "Sore itu angin bertiup kencang. Edo menutup jendela." Tindakan Edo adalah ....','menutup jendela',['membuka pintu','menyiram tanaman','berlari ke lapangan'],'Teks menyatakan Edo menutup jendela.'),
 $q('medium','Kata yang sesuai untuk menggambarkan bunga yang harum adalah ....','wangi',['kasar','gelap','pahit'],'Harum berarti wangi.'),
 $q('medium','Kalimat yang menggunakan kata "karena" dengan tepat adalah ....','Ayu memakai jas hujan karena hujan turun.',['Ayu karena jas hujan memakai turun.','Karena Ayu hujan jas memakai.','Ayu hujan karena turun memakai.'],'Kata karena menghubungkan sebab dengan akibat secara tepat.'),
 $q('medium','Bacalah: "Tari meminjam buku dari perpustakaan. Ia mengembalikannya tepat waktu." Tari bersikap ....','bertanggung jawab',['ceroboh','malas','sombong'],'Mengembalikan pinjaman tepat waktu adalah tanggung jawab.'),
 $q('medium','Kata tanya untuk menanyakan tempat adalah ....','di mana',['siapa','kapan','mengapa'],'Di mana digunakan untuk menanyakan tempat.'),
 $q('hard','Bacalah: "Pagi hari Rudi melihat halaman sekolah penuh daun. Ia mengambil sapu dan membersihkannya." Masalah yang dilihat Rudi adalah ....','halaman penuh daun',['sapu rusak','hujan deras','sekolah libur'],'Teks menyatakan halaman sekolah penuh daun.'),
 $q('hard','Bacalah: "Lia membawa bekal lebih. Ketika temannya lupa membawa bekal, Lia membaginya." Sifat Lia adalah ....','peduli',['pelit','marah','ceroboh'],'Lia berbagi saat temannya membutuhkan.'),
 $q('hard','Urutan kegiatan yang tepat adalah ....','mencuci tangan, makan, mencuci piring',['makan, mencuci tangan, mencuci piring','mencuci piring, makan, mencuci tangan','makan, mencuci piring, mencuci tangan'],'Tangan dicuci sebelum makan dan piring dibersihkan setelahnya.'),
 $q('hard','Bacalah: "Kereta tiba pukul delapan. Kakek berangkat dari rumah pukul tujuh agar tidak terlambat." Tujuan kakek berangkat pukul tujuh adalah ....','agar tidak terlambat',['agar tidur di kereta','agar membeli sepeda','agar bermain di rumah'],'Teks menyebut kakek ingin tidak terlambat.'),
 $q('hard','Kalimat yang paling santun untuk meminjam penghapus adalah ....','Bolehkah aku meminjam penghapusmu?',['Berikan penghapusmu!','Penghapusmu jelek!','Aku ambil penghapusmu.'],'Kata bolehkah menunjukkan permintaan yang santun.'),
 $q('hard','Bacalah: "Setelah hujan berhenti, jalanan masih basah." Pernyataan yang sesuai adalah ....','Sebelumnya turun hujan.',['Jalanan selalu kering.','Hujan belum pernah turun.','Hari pasti sangat panas.'],'Jalanan basah setelah hujan menunjukkan sebelumnya turun hujan.'),
 $q('hard','Kata yang tepat untuk melanjutkan kalimat "Rara membawa payung, .... langit mendung" adalah ....','karena',['tetapi','lalu','atau'],'Langit mendung menjadi alasan Rara membawa payung.'),
 $q('hard','Bacalah: "Damar menaruh sepeda pada tempatnya setelah dipakai." Pesan dari kalimat itu adalah ....','merapikan barang setelah dipakai',['meninggalkan barang sembarangan','meminjam tanpa izin','bermain di jalan raya'],'Kalimat memberi contoh merapikan barang setelah dipakai.'),
 $q('hard','Kalimat yang paling cocok menjadi judul cerita tentang membersihkan kelas adalah ....','Kelas Bersih dan Nyaman',['Sepeda Baru','Bermain Layang-Layang','Liburan ke Pantai'],'Judul harus sesuai isi cerita tentang kebersihan kelas.'),
 $q('hard','Bacalah: "Sinta membaca petunjuk sebelum merakit mainan." Mengapa Sinta membaca petunjuk?','agar tahu cara merakit mainan',['agar mainan hilang','agar tidak perlu bermain','agar kertasnya robek'],'Petunjuk membantu mengetahui cara merakit.'),
],
'ppkn' => [
 $q('easy','Saat masuk rumah teman, kita sebaiknya ....','mengucapkan salam',['berteriak','mendorong pintu','membuang sampah'],'Mengucapkan salam adalah sikap sopan.'),
 $q('easy','Aturan di jalan saat lampu merah menyala adalah ....','berhenti',['berlari','menyeberang sembarangan','bermain bola'],'Lampu merah berarti berhenti.'),
 $q('easy','Jika meminjam barang teman, kita harus ....','meminta izin',['langsung mengambil','menyembunyikan barang','merusaknya'],'Barang teman dipinjam dengan izin.'),
 $q('easy','Saat teman beribadah, kita harus ....','menghormati',['mengganggu','mengejek','berteriak'],'Setiap orang berhak beribadah dengan tenang.'),
 $q('easy','Contoh berkata jujur adalah ....','mengakui saat memecahkan gelas',['menyalahkan teman','menyembunyikan kesalahan','berbohong kepada ibu'],'Mengakui perbuatan adalah sikap jujur.'),
 $q('easy','Saat antre, kita harus ....','menunggu giliran',['menyerobot','mendorong teman','berlari ke depan'],'Antre berarti menunggu giliran.'),
 $q('easy','Kewajiban siswa adalah ....','mengikuti pelajaran',['merusak meja','mengganggu teman','tidak masuk tanpa alasan'],'Belajar termasuk kewajiban siswa.'),
 $q('easy','Jika melihat sampah di lantai kelas, kita dapat ....','memungutnya',['menendangnya','menyembunyikannya','membiarkannya'],'Memungut sampah membantu menjaga kebersihan.'),
 $q('easy','Saat berbicara dengan orang tua, gunakan bahasa yang ....','sopan',['kasar','keras sekali','mengejek'],'Bahasa sopan menunjukkan rasa hormat.'),
 $q('easy','Kerja bakti dilakukan agar lingkungan menjadi ....','bersih',['kotor','berantakan','bising'],'Kerja bakti bertujuan membersihkan lingkungan.'),
 $q('medium','Dua teman berbeda suku bermain bersama. Sikap yang tepat adalah ....','tetap berteman baik',['memilih-milih teman','mengejek sukunya','tidak mau bekerja sama'],'Perbedaan suku tidak menghalangi pertemanan.'),
 $q('medium','Kamu mendapat tugas menyiram tanaman kelas. Yang harus dilakukan adalah ....','menyiram sesuai jadwal',['menunggu teman saja','membuang tanaman','melupakan tugas'],'Tugas harus dikerjakan dengan tanggung jawab.'),
 $q('medium','Ketika musyawarah memilih permainan, keputusan yang baik diambil dengan ....','berdiskusi',['berteriak paling keras','memaksa teman','berkelahi'],'Musyawarah dilakukan melalui diskusi.'),
 $q('medium','Teman baru belum tahu letak toilet. Kamu sebaiknya ....','menunjukkan dengan ramah',['menertawakannya','membiarkannya bingung','memberi arah salah'],'Membantu teman baru adalah sikap baik.'),
 $q('medium','Saat memakai fasilitas sekolah, kita wajib ....','menjaganya',['mencoretnya','merusaknya','membawanya pulang'],'Fasilitas sekolah digunakan dan dijaga bersama.'),
 $q('medium','Jika pendapatmu berbeda saat diskusi, kamu dapat ....','menyampaikannya dengan sopan',['memotong pembicaraan','marah','mengejek pendapat lain'],'Pendapat berbeda boleh disampaikan secara sopan.'),
 $q('medium','Kakak sedang belajar. Sikap yang tepat adalah ....','tidak membuat gaduh',['menyalakan musik keras','mengganggunya','merebut bukunya'],'Menjaga suasana tenang menghargai kakak belajar.'),
 $q('medium','Saat bermain, semua anggota tim berhak ....','mendapat kesempatan',['selalu duduk di luar','dilarang bermain','diejek teman'],'Setiap anggota perlu mendapat kesempatan yang adil.'),
 $q('medium','Kamu menemukan uang di kelas. Yang tepat dilakukan adalah ....','memberikannya kepada guru',['menyimpannya diam-diam','membelanjakannya','menuduh teman'],'Guru dapat membantu mencari pemiliknya.'),
 $q('medium','Salah satu cara menghargai perbedaan makanan kesukaan teman adalah ....','tidak mengejek pilihannya',['memaksa memilih makanan kita','menertawakan makanannya','mengambil makanannya'],'Pilihan makanan teman perlu dihargai.'),
 $q('hard','Saat bermain, bola memecahkan pot bunga sekolah. Tim yang bermain sebaiknya ....','melapor dan membantu bertanggung jawab',['langsung pulang','menyalahkan orang lain','menyembunyikan pecahan'],'Melapor dan bertanggung jawab adalah tindakan jujur.'),
 $q('hard','Kelas akan memilih ketua. Agar adil, cara yang tepat adalah ....','memilih dengan kesepakatan kelas',['hanya guru yang ditebak','memaksa teman memilih kita','tidak memberi kesempatan memilih'],'Kesepakatan atau pemungutan suara membuat proses lebih adil.'),
 $q('hard','Kamu ingin bermain, tetapi ibu meminta bantuan merapikan meja. Sebaiknya ....','membantu ibu lebih dahulu',['berpura-pura tidak dengar','marah kepada ibu','meninggalkan rumah'],'Membantu keluarga adalah tanggung jawab di rumah.'),
 $q('hard','Saat teman menyampaikan pendapat, kamu tidak setuju. Sikap paling tepat adalah ....','mendengarkan lalu memberi tanggapan sopan',['menertawakan','membentak','berjalan pergi tanpa alasan'],'Perbedaan pendapat disikapi dengan hormat.'),
 $q('hard','Di perpustakaan, ada teman berbicara keras. Tindakan yang tepat adalah ....','mengingatkan dengan sopan',['ikut berteriak','mengejeknya','merusak bukunya'],'Mengingatkan sopan membantu menjaga aturan perpustakaan.'),
 $q('hard','Kamu dan teman sama-sama ingin menjadi pemimpin barisan. Solusi yang baik adalah ....','bergantian pada hari berbeda',['berebut','tidak mau berbaris','menyuruh teman pulang'],'Bergantian memberi kesempatan yang adil.'),
 $q('hard','Seorang teman tidak dapat ikut permainan karena kakinya cedera. Sikap yang baik adalah ....','mengajak melakukan peran yang aman',['meninggalkannya','mengejeknya','memaksa berlari'],'Teman tetap dapat dilibatkan dengan cara aman.'),
 $q('hard','Saat melihat teman dibully, tindakan yang tepat adalah ....','membantu mencari guru',['ikut mengejek','merekam untuk ditertawakan','diam saja'],'Guru dapat membantu menghentikan tindakan tidak baik.'),
 $q('hard','Kamu terlambat karena bangun kesiangan. Sikap jujur adalah ....','mengatakan alasan yang sebenarnya',['menyalahkan teman','membuat cerita palsu','tidak masuk kelas'],'Kejujuran berarti menyampaikan keadaan sebenarnya.'),
 $q('hard','Dalam kelompok, hasil kerja teman berbeda dengan idemu. Cara menghargainya adalah ....','mendiskusikan kelebihan tiap ide',['merobek hasilnya','menolak tanpa membaca','menganggap hanya ide sendiri benar'],'Diskusi membantu memilih ide bersama dengan hormat.'),
],
'seni_budaya' => [
 $q('easy','Garis yang berdiri dari atas ke bawah disebut garis ....','tegak',['mendatar','lengkung','zigzag'],'Garis tegak arahnya atas ke bawah.'),
 $q('easy','Garis dari kiri ke kanan disebut garis ....','mendatar',['tegak','spiral','putus-putus'],'Garis mendatar arahnya kiri ke kanan.'),
 $q('easy','Bahan yang dapat dipakai untuk menggambar adalah ....','pensil',['sendok','sandal','piring'],'Pensil digunakan untuk menggambar.'),
 $q('easy','Warna merah dan kuning dicampur menghasilkan ....','oranye',['hijau','ungu','biru'],'Merah dan kuning menghasilkan warna oranye.'),
 $q('easy','Suara drum termasuk bunyi yang dihasilkan dengan cara ....','dipukul',['ditiup','dipetik','digesek'],'Drum berbunyi saat dipukul.'),
 $q('easy','Gerakan mengayun tangan dapat dilakukan saat ....','menari',['tidur','membaca','makan'],'Mengayun tangan dapat menjadi bagian gerak tari.'),
 $q('easy','Saat mewarnai, agar warna tidak keluar garis kita harus ....','berhati-hati',['terburu-buru','menutup mata','merobek kertas'],'Ketelitian membantu warna tetap di dalam bentuk.'),
 $q('easy','Benda untuk merekatkan kertas pada kolase adalah ....','lem',['air','pasir','angin'],'Lem digunakan untuk merekatkan bahan kolase.'),
 $q('easy','Bunyi pelan disebut bunyi ....','lembut',['keras','kasar','pecah'],'Bunyi pelan terdengar lembut.'),
 $q('easy','Warna daun pada gambar biasanya ....','hijau',['ungu','hitam','merah muda'],'Daun umum digambarkan berwarna hijau.'),
 $q('medium','Pola bunyi tepuk, diam, tepuk, diam, .... dilanjutkan dengan ....','tepuk',['diam, diam','teriak','musik cepat'],'Pola bergantian antara tepuk dan diam.'),
 $q('medium','Untuk membuat kolase bertema kebun, bahan yang cocok adalah ....','daun kering dan biji-bijian',['air dan asap','kaca pecah','makanan basah'],'Daun kering dan biji dapat ditempel menjadi kolase.'),
 $q('medium','Jika ingin menggambar hujan, garis yang dapat banyak digunakan adalah ....','garis miring pendek',['garis lingkaran besar','garis datar panjang saja','titik tanpa arah'],'Garis miring pendek dapat menggambarkan air hujan.'),
 $q('medium','Temanmu bernyanyi pelan. Agar terdengar bersama dengan baik, kamu sebaiknya ....','menyanyi dengan volume sesuai',['berteriak paling keras','menutup telinga','berlari keluar'],'Volume yang sesuai membuat nyanyian selaras.'),
 $q('medium','Warna biru dan merah dicampur menghasilkan ....','ungu',['hijau','oranye','kuning'],'Biru dan merah menghasilkan ungu.'),
 $q('medium','Sebelum menampilkan tari kelompok, yang perlu dilakukan adalah ....','berlatih gerakan bersama',['saling mengejek','tidak menghafal gerakan','membuang musik'],'Latihan membantu gerakan kelompok kompak.'),
 $q('medium','Karya gambar teman berbeda dari gambarmu. Sikap yang tepat adalah ....','menghargai karyanya',['menyobeknya','mengejeknya','menyalinnya tanpa izin'],'Setiap karya dapat memiliki bentuk yang berbeda.'),
 $q('medium','Untuk membuat warna lebih muda, warna dapat dicampur dengan ....','putih',['hitam saja','pasir','lem'],'Putih membuat warna tampak lebih muda.'),
 $q('medium','Gerak tari yang dilakukan mengikuti ketukan menunjukkan gerak yang ....','berirama',['acak','diam','tersembunyi'],'Ketukan membantu gerak tari berirama.'),
 $q('medium','Saat menggunakan gunting untuk karya, kita harus ....','memegang dengan hati-hati',['berlari sambil membawa','mengarahkannya ke teman','melemparkannya'],'Gunting harus digunakan dengan aman.'),
 $q('hard','Kamu membuat poster tentang kebersihan. Gambar yang paling sesuai adalah ....','anak membuang sampah pada tempatnya',['anak merusak taman','jalan penuh sampah','anak mencoret dinding'],'Gambar harus mendukung pesan kebersihan.'),
 $q('hard','Dalam kelompok musik, satu teman memukul ritme terlalu cepat. Yang baik dilakukan adalah ....','mengingatkan mengikuti ketukan',['menertawakannya','membiarkannya tanpa latihan','merebut alatnya'],'Mengingatkan dengan baik membantu kelompok tampil selaras.'),
 $q('hard','Untuk membuat gambar pasar tampak ramai, kamu dapat menambahkan ....','orang dan berbagai barang dagangan',['langit kosong saja','satu titik','kertas tanpa gambar'],'Orang dan barang dagangan menunjukkan suasana pasar.'),
 $q('hard','Kamu ingin memakai daun sebagai cap gambar. Langkah yang tepat adalah ....','memberi cat pada permukaan daun lalu menekannya ke kertas',['merobek kertas dahulu','mencampur daun dengan air minum','membuang daun setelah dicat'],'Daun bercat dapat ditekan untuk meninggalkan bentuk pada kertas.'),
 $q('hard','Dua warna yang membuat gambar tampak kontras adalah ....','kuning dan ungu',['biru dan biru','merah dan merah','putih dan putih'],'Kuning dan ungu memberi perbedaan warna yang jelas.'),
 $q('hard','Saat latihan drama, teman lupa dialog. Sikap kelompok yang baik adalah ....','membantu mengingatkan dengan tenang',['mengejeknya','langsung membubarkan latihan','menyembunyikan naskah'],'Dukungan membantu teman melanjutkan latihan.'),
 $q('hard','Karya seni dari bahan bekas bermanfaat karena ....','menggunakan kembali bahan yang masih dapat dipakai',['selalu lebih mahal','harus dibuang cepat','tidak perlu ide'],'Bahan bekas dapat dimanfaatkan menjadi karya baru.'),
 $q('hard','Untuk menunjukkan suasana malam pada gambar, pilihan warna yang sesuai adalah ....','biru tua dan ungu',['kuning terang seluruhnya','putih saja','merah muda saja'],'Warna gelap seperti biru tua dan ungu dapat menunjukkan malam.'),
 $q('hard','Ketika melihat pertunjukan, cara memberi apresiasi yang tepat adalah ....','bertepuk tangan setelah selesai',['berbicara keras saat tampil','bermain sendiri di depan panggung','mengejek kesalahan pemain'],'Bertepuk tangan setelah selesai menghargai penampil.'),
 $q('hard','Jika gambar terlihat terlalu kosong, kamu dapat menambahkan ....','detail yang sesuai tema',['coretan acak','sobekan kertas','tulisan tidak terkait'],'Detail yang sesuai tema membuat gambar lebih lengkap.'),
],
'penjaskes' => [
 $q('easy','Gerak melompat menggunakan kekuatan ....','kaki',['telinga','hidung','rambut'],'Melompat dilakukan dengan dorongan kaki.'),
 $q('easy','Pakaian yang nyaman saat berolahraga adalah ....','pakaian olahraga',['jas hujan tebal','seragam basah','baju yang sangat sempit'],'Pakaian olahraga memudahkan bergerak.'),
 $q('easy','Sebelum dan sesudah makan, kita perlu ....','mencuci tangan',['berlari','melompat','berteriak'],'Mencuci tangan menjaga kebersihan.'),
 $q('easy','Makanan yang membantu tubuh sehat adalah ....','sayur',['permen setiap waktu','makanan basi','minuman bersoda saja'],'Sayur termasuk makanan bergizi.'),
 $q('easy','Saat guru memberi aba-aba berhenti, kita harus ....','berhenti',['tetap berlari','mendorong teman','bersembunyi'],'Aba-aba guru perlu dipatuhi demi keselamatan.'),
 $q('easy','Gerak mengayunkan lengan dapat dilakukan saat ....','pemanasan',['tidur','makan','membaca diam'],'Mengayun lengan dapat menjadi gerak pemanasan.'),
 $q('easy','Setelah selesai memakai alat olahraga, kita harus ....','mengembalikannya',['membuangnya','menyembunyikannya','menginjaknya'],'Alat dipakai bersama dan perlu dikembalikan.'),
 $q('easy','Tempat yang aman untuk bermain bola adalah ....','lapangan',['jalan raya','dekat kendaraan','tangga'],'Lapangan lebih aman daripada jalan atau tangga.'),
 $q('easy','Saat haus setelah bergerak, minumlah ....','air putih',['air sabun','minuman basi','cat air'],'Air putih aman untuk minum.'),
 $q('easy','Agar gigi sehat, kita perlu ....','menyikat gigi',['makan permen terus','tidak minum','tidur dengan gigi kotor'],'Menyikat gigi membantu menjaga kebersihan gigi.'),
 $q('medium','Saat menangkap bola, mata sebaiknya ....','melihat arah bola',['terpejam','melihat ke belakang','melihat lantai terus'],'Melihat arah bola membantu menangkap dengan aman.'),
 $q('medium','Jika lapangan sangat panas, kita sebaiknya ....','beristirahat di tempat teduh',['memaksa berlari lama','tidak minum','berjemur terus'],'Tempat teduh dan istirahat membantu mencegah kepanasan.'),
 $q('medium','Saat teman sedang melakukan lompatan, kita tidak boleh ....','berdiri di jalurnya',['memberi ruang','menunggu giliran','melihat dari jarak aman'],'Jalur lompatan harus bebas agar aman.'),
 $q('medium','Sikap tubuh saat mengangkat benda ringan adalah ....','menggunakan posisi yang aman dan tidak memaksa',['membungkuk sembarangan','mengangkat sambil berlari','melemparkannya ke atas'],'Posisi aman mengurangi risiko cedera.'),
 $q('medium','Setelah berolahraga, napas masih cepat. Yang sebaiknya dilakukan adalah ....','pendinginan dan istirahat',['langsung berlari lagi','tidak minum seharian','mendorong teman'],'Pendinginan membantu tubuh kembali tenang.'),
 $q('medium','Dalam permainan estafet, tongkat diberikan kepada teman dengan ....','hati-hati',['dilempar keras ke wajah','disembunyikan','dibuang jauh'],'Tongkat diberikan aman kepada teman yang siap.'),
 $q('medium','Untuk menjaga kebugaran, kita perlu ....','bergerak aktif secara teratur',['duduk sepanjang hari','tidur saat pelajaran','makan tanpa henti'],'Gerak aktif teratur membantu kebugaran.'),
 $q('medium','Saat bermain berpasangan, temanmu lebih lambat. Sikap tepat adalah ....','menyesuaikan gerak dan menyemangati',['meninggalkannya','mengejeknya','mendorongnya'],'Kerja sama berarti saling menyesuaikan dan mendukung.'),
 $q('medium','Jika tali sepatu terlepas sebelum berlari, kamu harus ....','mengikatnya dahulu',['tetap berlari cepat','membiarkannya menjuntai','menarik tali teman'],'Tali sepatu longgar dapat membuat tersandung.'),
 $q('medium','Ketika bermain di luar ruangan, perlindungan dari panas dapat memakai ....','topi',['selimut tebal','sepatu basah','sarung tangan dapur'],'Topi membantu melindungi kepala dari panas matahari.'),
 $q('hard','Saat permainan, dua teman berlari berlawanan arah dan hampir bertabrakan. Yang sebaiknya dilakukan adalah ....','memperlambat gerak dan memberi jalan',['tetap berlari kencang','saling mendorong','menutup mata'],'Memperlambat dan memberi jalan mencegah tabrakan.'),
 $q('hard','Kamu melihat lantai aula basah sebelum senam. Tindakan tepat adalah ....','memberi tahu guru dan menjauhi bagian basah',['berlari di atasnya','menyiram lebih banyak','menutupinya dengan tas'],'Lantai basah dapat licin sehingga perlu dilaporkan.'),
 $q('hard','Tim kamu tertinggal skor. Sikap sportif yang tepat adalah ....','tetap bermain sesuai aturan',['curang agar menang','marah kepada lawan','berhenti mengganggu permainan'],'Sportivitas berarti tetap mematuhi aturan.'),
 $q('hard','Saat teman kesulitan mengikat tali sepatu, kamu dapat ....','menawarkan bantuan',['menertawakannya','merebut sepatunya','meninggalkannya di jalan'],'Membantu teman mendukung keselamatan bersama.'),
 $q('hard','Kamu merasa nyeri saat melakukan gerakan. Yang sebaiknya dilakukan adalah ....','berhenti dan memberi tahu guru',['memaksa sampai selesai','menyembunyikan rasa nyeri','berlari lebih cepat'],'Nyeri perlu ditangani dengan berhenti dan meminta bantuan.'),
 $q('hard','Dalam permainan, bola keluar lapangan setelah disentuh timmu. Sikap jujur adalah ....','mengakui bola keluar dari timmu',['mengatakan lawan yang menyentuh','menyembunyikan bola','memulai permainan tanpa aturan'],'Mengakui kejadian sesuai aturan adalah jujur.'),
 $q('hard','Sebelum bermain di kolam, hal yang penting dilakukan adalah ....','mengikuti aturan dan pengawasan orang dewasa',['langsung melompat tanpa melihat','berlari di tepi kolam','bermain sendiri jauh dari pengawas'],'Aturan dan pengawasan membuat kegiatan di kolam lebih aman.'),
 $q('hard','Saat latihan kelompok, teman belum paham gerakan. Cara membantu yang tepat adalah ....','memperagakan gerakan perlahan',['mengejeknya','membiarkannya sendiri','menyuruhnya pulang'],'Memperagakan perlahan membantu teman belajar.'),
 $q('hard','Jika tubuh berkeringat banyak, selain minum air kita perlu ....','beristirahat secukupnya',['memakai baju basah terus','tidak membersihkan diri','berlari lebih lama'],'Istirahat membantu tubuh pulih setelah aktivitas.'),
 $q('hard','Mengapa kita tidak boleh bercanda sambil membawa alat olahraga?','dapat membahayakan diri dan teman',['agar alat lebih berat','supaya permainan lebih lama','karena alat tidak berguna'],'Bercanda dengan alat dapat menyebabkan cedera.'),
],
];

$subjectRows = $m->query("SELECT id, code FROM quiz_subjects")->fetch_all(MYSQLI_ASSOC);
$subjects = array_column($subjectRows, 'id', 'code');
$grade = $m->query("SELECT id FROM quiz_grade_levels WHERE code='sd_2'")->fetch_assoc();
if (! $grade) throw new RuntimeException('Master SD Kelas 2 tidak ditemukan.');
$gradeId = (int) $grade['id'];
$sessionCodes = ['matematika'=>'LAT-SD2-MAT-CP26','bahasa_indonesia'=>'LAT-SD2-BIN-CP26','ppkn'=>'LAT-SD2-PAN-CP26','seni_budaya'=>'LAT-SD2-SEN-CP26','penjaskes'=>'LAT-SD2-PJK-CP26'];

foreach ($banks as $code => $items) {
    if (count($items) !== 30) throw new RuntimeException("Bank {$code} harus berisi tepat 30 soal.");
    if (count(array_filter($items, static fn($x) => $x['difficulty'] === 'easy')) !== 10
        || count(array_filter($items, static fn($x) => $x['difficulty'] === 'medium')) !== 10
        || count(array_filter($items, static fn($x) => $x['difficulty'] === 'hard')) !== 10) {
        throw new RuntimeException("Komposisi kesulitan {$code} harus 10/10/10.");
    }
}

$scope = $m->prepare('SELECT id FROM quiz_curriculum_scopes WHERE subject_id=? AND grade_level_id=? AND is_active=1');
$exists = $m->prepare('SELECT id FROM quiz_questions WHERE deleted_at IS NULL AND LOWER(TRIM(question_text))=LOWER(TRIM(?)) LIMIT 1');
$insert = $m->prepare('INSERT INTO quiz_questions (subject_id,grade_level_id,type,difficulty,question_text,explanation,correct_option_index,score_weight,is_active,created_at,updated_at) VALUES (?,?,"multiple_choice",?,?,?,?,1,1,NOW(),NOW())');
$option = $m->prepare('INSERT INTO quiz_question_options (question_id,option_index,option_text) VALUES (?,?,?)');

$m->begin_transaction();
try {
    $inserted = [];
    foreach ($banks as $code => $items) {
        if (empty($subjects[$code])) throw new RuntimeException("Mapel {$code} tidak ditemukan.");
        $subjectId = (int) $subjects[$code];
        $scope->bind_param('ii', $subjectId, $gradeId); $scope->execute();
        if (! $scope->get_result()->fetch_assoc()) throw new RuntimeException("Cakupan kurikulum {$code}/sd_2 belum aktif.");
        foreach ($items as $n => $item) {
            if (preg_match('/^(Saat menyusun kesimpulan|Pada evaluasi pemahaman|Pada latihan)/iu', $item['text'])) throw new RuntimeException('Awalan template terdeteksi.');
            $choices = array_merge([$item['correct']], $item['wrong']);
            if (count($choices) !== 4 || count(array_unique(array_map(static fn($v) => mb_strtolower(trim($v)), $choices))) !== 4) throw new RuntimeException("Opsi tidak valid: {$item['text']}");
            $rotation = $n % 4; $choices = array_merge(array_slice($choices, $rotation), array_slice($choices, 0, $rotation));
            $correct = array_search($item['correct'], $choices, true);
            $exists->bind_param('s', $item['text']); $exists->execute();
            if ($exists->get_result()->fetch_assoc()) throw new RuntimeException("Soal duplikat terdeteksi: {$item['text']}");
            $insert->bind_param('iisssi', $subjectId, $gradeId, $item['difficulty'], $item['text'], $item['explanation'], $correct);
            $insert->execute(); $qid = (int) $m->insert_id;
            foreach ($choices as $index => $choice) { $option->bind_param('iis', $qid, $index, $choice); $option->execute(); }
            $inserted[$code] = ($inserted[$code] ?? 0) + 1;
        }
    }
    foreach ($sessionCodes as $code => $sessionCode) {
        $subjectId = (int) $subjects[$code];
        $countResult = $m->query("SELECT COUNT(*) n FROM quiz_questions WHERE deleted_at IS NULL AND is_active=1 AND subject_id={$subjectId} AND grade_level_id={$gradeId}")->fetch_assoc();
        $count = (int) $countResult['n'];
        $timeLimit = max(30, (int) ceil($count * 1.5));
        $stmt = $m->prepare('UPDATE quiz_sessions SET status="open", is_published=1, is_paused=0, question_count=?, time_limit_minutes=? WHERE code=?');
        $stmt->bind_param('iis', $count, $timeLimit, $sessionCode); $stmt->execute();
    }
    $m->commit();
    foreach ($inserted as $code => $count) echo $code . ': ' . $count . " soal ditambahkan\n";
} catch (Throwable $e) {
    $m->rollback();
    fwrite(STDERR, 'Seed dibatalkan: ' . $e->getMessage() . "\n");
    exit(1);
}
