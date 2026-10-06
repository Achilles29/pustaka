# Rencana Pengembangan English Quest RPG

## 1. Tujuan Dokumen

Dokumen ini menjadi acuan review dan pengembangan lanjutan modul **English Quest RPG** pada Pustaka Digital Rembang. Rencana ini sengaja memisahkan:

- fitur yang sudah tersedia;
- fitur yang masih perlu audit atau penyempurnaan;
- fitur baru yang direncanakan;
- urutan pengerjaan, dependensi, risiko, dan kriteria selesai.

Dokumen ini bukan pernyataan bahwa seluruh rencana sudah diimplementasikan. Status harus diperbarui setelah fitur benar-benar diuji.

## 2. Visi Produk

English Quest RPG adalah permainan petualangan berbahasa Inggris untuk anak. Pemain belajar melalui cerita, eksplorasi, dialog, mendengar, berbicara, menyusun kalimat, dan mengambil keputusan. Pengalaman belajar harus terasa seperti bermain RPG, bukan mengerjakan lembar soal yang diberi dekorasi game.

Target hasil belajar:

- menambah kosakata dalam konteks;
- memahami instruksi dan dialog bahasa Inggris;
- melatih listening dan pronunciation;
- memahami pola kalimat tanpa menghafal satu template;
- meningkatkan keberanian membaca dan berbicara;
- mengulang materi lemah secara adaptif;
- memberi motivasi melalui cerita, koleksi, progres, dan pencapaian.

Prinsip desain:

1. Cerita dan aktivitas belajar harus saling terkait.
2. Jawaban benar tidak boleh mudah ditebak dari posisi atau pola kalimat.
3. Kesulitan bertambah perlahan sesuai kemampuan pemain.
4. Kesalahan dipakai sebagai bahan latihan ulang, bukan sekadar hukuman.
5. Bahasa Indonesia menjadi bantuan bertahap, bukan bahasa utama permainan.
6. Semua konten penting harus dapat dikelola admin tanpa mengubah kode.
7. Pengalaman utama harus tetap dapat digunakan di ponsel dan koneksi terbatas.

## 3. Kondisi Saat Dokumen Dibuat

Tanggal baseline: **13 Agustus 2026**.

### 3.1 Sudah tersedia

- Halaman pemain `/belajar/english-quest` dan halaman admin `/learn-english-rpg`.
- 15 chapter dengan 15 adegan per chapter atau sekitar 225 adegan aktif.
- Tantangan pilihan ganda, listening, Sentence Forge, dan pilihan cerita.
- Pengacakan pilihan jawaban dengan referensi indeks jawaban asli.
- Sentence Forge dengan variasi kalimat yang tidak hanya memakai pola `I learned the word ...`.
- Peta dunia, penguncian chapter, progres, XP, nyawa, loot, inventory, dan boss visual.
- Profil hero, class, streak, mastery, side quest, dan reputasi.
- Review Camp adaptif dan Adventure Dictionary.
- Latihan speaking berbasis pengenal suara browser.
- Equipment, Heart Potion, dan daily reward.
- Raport pemain dan laporan admin dasar.
- Intro cutscene sederhana dan animasi antarmuka.

### 3.2 Perlu audit sebelum dinyatakan matang

- Kualitas dan konsistensi seluruh 225 adegan.
- Kesesuaian prompt, opsi, kunci, terjemahan, audio, dan Sentence Forge.
- Distribusi posisi kunci jawaban pada semua chapter.
- Keseimbangan XP, nyawa, loot, class, equipment, streak, dan hadiah.
- Dukungan speaking pada browser dan perangkat yang berbeda.
- Hak akses pemain dan admin untuk seluruh endpoint baru.
- Tampilan peta dunia serta navigasi fitur di desktop dan ponsel.
- Analytics admin, query laporan, pagination, filter, dan performa data besar.
- Accessibility: keyboard, fokus, kontras, teks alternatif, dan reduced motion.

## 4. Sasaran Pengembangan

### Sasaran pembelajaran

- Setiap chapter memiliki tujuan CEFR atau kompetensi yang jelas.
- Satu kata dipakai dalam beberapa konteks dan bentuk latihan.
- Pemain dapat melihat apa yang sudah dikuasai dan perlu dilatih.
- Orang tua/guru dapat memahami kemajuan tanpa membaca data teknis.

### Sasaran permainan

- Pemain merasakan eksplorasi, konflik, pilihan, perkembangan karakter, dan hadiah.
- Chapter memiliki identitas visual, mekanik, NPC, dan boss yang berbeda.
- Keputusan pemain memberikan konsekuensi yang terlihat.
- Aktivitas tidak berubah menjadi pola soal berulang.

### Sasaran pengelolaan

- Admin dapat menyusun chapter, scene, dialog, tantangan, cabang, reward, audio, dan cutscene.
- Konten bisa diimpor, divalidasi, ditinjau, dipratinjau, dan dipublikasikan.
- Perubahan konten memiliki draft, versi, audit log, dan opsi rollback.

## 5. Peta Jalan Pengembangan

## Fase 0 — Audit dan Stabilisasi Fondasi

Prioritas: **wajib sebelum menambah konten besar**.

- [ ] Audit semua route, login, role, permission, CSRF, dan kepemilikan data.
- [ ] Audit 225 adegan menggunakan pemeriksaan otomatis dan review manual sampel.
- [ ] Deteksi kunci jawaban dominan, opsi duplikat, prompt kosong, dan JSON rusak.
- [ ] Cocokkan Sentence Forge: prompt, token, kalimat benar, kapitalisasi, dan tanda baca.
- [ ] Deteksi terjemahan yang tidak sesuai atau membocorkan jawaban.
- [ ] Tambahkan transaksi database pada perubahan progres dan reward kritis.
- [ ] Cegah submit ganda, race condition, manipulasi nomor scene, XP, dan reward.
- [ ] Pastikan respons AJAX selalu JSON, termasuk error autentikasi dan validasi.
- [ ] Tambahkan logging error yang tidak membocorkan detail server kepada pemain.
- [ ] Uji regresi desktop, Android, iOS, Chrome, Edge, Firefox, dan Safari.
- [ ] Tetapkan baseline performa halaman, ukuran aset, dan waktu respons API.

Kriteria selesai:

- tidak ada error PHP/JavaScript pada alur satu chapter penuh;
- pemain tidak dapat melompati scene atau mengambil reward dua kali;
- seluruh pemeriksaan integritas konten lulus;
- akses endpoint sesuai role;
- progres tetap konsisten setelah refresh, koneksi putus, dan submit berulang.

## Fase 1 — Content Quality System

Tujuan: mencegah konten monoton atau tidak cocok sebelum diterbitkan.

- [ ] Validator komposisi soal per chapter.
- [ ] Statistik distribusi kunci A/B/C/D dan peringatan bila terlalu timpang.
- [ ] Pendeteksi kemiripan template kalimat.
- [ ] Pendeteksi kata/kalimat yang berulang berlebihan.
- [ ] Validasi bahwa semua token Sentence Forge membentuk jawaban yang benar.
- [ ] Validasi opsi pengecoh agar masuk akal tetapi tidak ambigu.
- [ ] Pemeriksaan tingkat kesulitan kosakata dan panjang kalimat.
- [ ] Status konten: draft, review, approved, published, archived.
- [ ] Preview chapter sebagai pemain tanpa memengaruhi progres.
- [ ] Catatan reviewer dan riwayat revisi.
- [ ] Duplicate scene/chapter sebagai titik awal penyuntingan.
- [ ] Import/export Excel dan TXT dengan laporan baris bermasalah.

Aturan variasi Sentence Forge:

- Gunakan declarative, negative, interrogative, imperative, request, dan response.
- Variasikan subject, tense, pronoun, determiner, adjective, adverb, dan preposition.
- Gunakan dialog serta situasi chapter, bukan kalimat definisi generik.
- Hindari pola yang sama lebih dari dua kali dalam satu chapter.
- Sertakan pengecoh secukupnya pada level lanjut, tanpa membuat jawaban ambigu.

Contoh pola:

- `The merchant keeps the silver coin.`
- `Where did you find this coin?`
- `Please return the coin to its owner.`
- `We did not spend the ancient coin.`
- `Is this coin part of the treasure?`

## Fase 2 — Percabangan Cerita Nyata

Tujuan: keputusan pemain mengubah perjalanan, bukan hanya angka reputasi.

- [ ] Definisikan node cerita dan hubungan antar-node.
- [ ] Pilihan dapat menuju scene berbeda.
- [ ] Syarat cabang berdasarkan reputasi, item, class, mastery, atau pilihan sebelumnya.
- [ ] Flag cerita permanen per pemain.
- [ ] Cabang dapat bertemu kembali tanpa merusak progres.
- [ ] Beberapa ending: heroik, bijaksana, netral, atau ending rahasia.
- [ ] Tampilan konsekuensi keputusan tanpa selalu membocorkan hasil masa depan.
- [ ] Timeline keputusan pada raport pemain.
- [ ] Editor visual sederhana untuk hubungan antar-scene di admin.
- [ ] Validator dead end, loop tak berujung, dan node yang tidak terjangkau.

Struktur data yang perlu dirancang:

- story nodes;
- story edges/options;
- conditions;
- effects;
- player flags;
- decision history;
- ending definitions.

## Fase 3 — NPC, Dialog, Toko, dan Quest

- [ ] NPC dengan nama, avatar, lokasi, karakter, dan gaya bicara.
- [ ] Dialog bertahap yang berubah berdasarkan progres.
- [ ] Quest giver dengan objective dan reward.
- [ ] Main quest, side quest, daily quest, dan class quest.
- [ ] Toko menggunakan mata uang game yang tidak melibatkan pembayaran nyata.
- [ ] Item shop, buy/sell, harga, stok, dan persyaratan unlock.
- [ ] Conversation challenge: memilih respons yang sesuai konteks.
- [ ] Relationship/reputation per kelompok atau NPC.
- [ ] NPC glossary untuk mengenalkan ungkapan sosial.
- [ ] Admin CRUD NPC, dialog, toko, quest, objective, dan reward.

## Fase 4 — Speaking dan Pronunciation di Dalam Chapter

- [ ] Tipe scene `speaking` menjadi bagian chapter utama.
- [ ] Tombol rekam, status mendengarkan, transcript, dan coba ulang.
- [ ] Normalisasi variasi transcript yang masih benar.
- [ ] Penilaian kata target, kelengkapan frasa, dan kelancaran sederhana.
- [ ] Mode latihan tanpa penalti sebelum jawaban final.
- [ ] Bonus class Speaking Bard.
- [ ] Fallback manual bila Speech Recognition tidak tersedia.
- [ ] Indikator kompatibilitas browser dan izin mikrofon.
- [ ] Kebijakan privasi: audio tidak disimpan secara default.
- [ ] Jika audio hendak disimpan, wajib ada persetujuan, masa retensi, dan kontrol hapus.

Catatan: Web Speech API tidak seragam pada semua browser. Fitur inti tidak boleh bergantung sepenuhnya pada dukungan tersebut.

## Fase 5 — Listening dan Sistem Audio

- [ ] Audio dialog per NPC dan narasi per scene.
- [ ] Kontrol play, pause, ulangi, kecepatan, dan volume.
- [ ] Pilihan suara/narator jika tersedia.
- [ ] Preload audio adegan berikutnya tanpa membebani koneksi.
- [ ] Cache audio dan fallback Text-to-Speech.
- [ ] Efek suara UI, benar/salah, loot, serangan, dan kemenangan.
- [ ] Musik latar per wilayah dengan mute permanen di preferensi pemain.
- [ ] Subtitle selalu tersedia.
- [ ] Admin dapat upload audio atau memakai TTS lalu preview.
- [ ] Normalisasi volume dan format aset.

## Fase 6 — Combat dan Skill Tree Edukatif

- [ ] Battle berbasis giliran dengan tantangan bahasa sebagai aksi.
- [ ] Jenis musuh memiliki kelemahan terhadap skill tertentu.
- [ ] Jawaban benar menghasilkan serangan, pertahanan, heal, atau buff.
- [ ] Jawaban salah memberi feedback edukatif sebelum penalti.
- [ ] Boss memiliki beberapa fase dan variasi mekanik.
- [ ] Skill tree per class.
- [ ] Word Knight fokus vocabulary dan defense.
- [ ] Grammar Mage fokus sentence dan combo.
- [ ] Listening Ranger fokus audio dan critical hit.
- [ ] Speaking Bard fokus pronunciation dan support.
- [ ] Batas kekuatan item agar pembelajaran tetap menjadi faktor utama.
- [ ] Simulasi balancing XP, damage, heart, potion, drop, dan boss.

## Fase 7 — Dunia, Eksplorasi, dan Presentasi RPG

- [ ] Peta wilayah interaktif, bukan hanya daftar chapter.
- [ ] Pemain bergerak di lokasi atau memilih titik eksplorasi.
- [ ] Area rahasia berdasarkan item atau mastery.
- [ ] Cutscene intro, transisi, boss, dan ending.
- [ ] Animasi karakter: idle, walk, attack, hurt, victory.
- [ ] Parallax background dan perubahan cuaca/waktu.
- [ ] Dialog bubble, portrait NPC, dan ekspresi karakter.
- [ ] Tema unik setiap chapter.
- [ ] Reduced-motion mode dan opsi mematikan efek berat.
- [ ] Gunakan aset berlisensi jelas serta catat sumber/lisensinya.

Target teknis awal tetap DOM/CSS/JavaScript ringan. Pertimbangkan canvas/game engine hanya setelah prototipe performa menunjukkan kebutuhan nyata.

## Fase 8 — Adaptive Learning Engine

- [ ] Mastery per kata, grammar pattern, listening, speaking, dan reading.
- [ ] Bobot berdasarkan benar/salah, waktu jawab, bantuan, dan jumlah percobaan.
- [ ] Spaced repetition dengan jadwal review.
- [ ] Review otomatis setelah beberapa scene, akhir chapter, dan sesi berikutnya.
- [ ] Pemilihan soal berdasarkan kelemahan tanpa membuat permainan terasa berulang.
- [ ] Difficulty adjustment bertahap.
- [ ] Pisahkan kemampuan mengenali, memahami, dan menghasilkan kalimat.
- [ ] Jelaskan rekomendasi kepada pemain/guru secara sederhana.
- [ ] Hindari label negatif terhadap anak.
- [ ] Sediakan reset atau koreksi mastery oleh admin berwenang.

## Fase 9 — Progression, Collection, dan Retention

- [ ] Level hero dan level class.
- [ ] Achievement dan badge khusus RPG.
- [ ] Koleksi monster, NPC, item, buku, atau lore.
- [ ] Daily/weekly mission yang sehat dan tidak menghukum absen.
- [ ] Streak freeze dan recovery.
- [ ] Event musiman yang dapat dikonfigurasi admin.
- [ ] Reward calendar.
- [ ] Cosmetic equipment terpisah dari equipment statistik.
- [ ] Save slot hanya jika memang dibutuhkan; default tetap satu progres per akun.
- [ ] Hindari dark pattern, loot box berbayar, dan tekanan berlebihan pada anak.

## Fase 10 — Raport Guru/Orang Tua dan Analytics Admin

Raport pemain:

- [ ] chapter selesai dan progres tiap chapter;
- [ ] waktu belajar, jumlah sesi, dan streak;
- [ ] vocabulary/mastery;
- [ ] listening, speaking, reading, grammar, dan Sentence Forge;
- [ ] kata/pola terkuat dan perlu dilatih;
- [ ] riwayat review dan perkembangan periodik;
- [ ] quest, pilihan cerita, class, equipment, dan achievement;
- [ ] rekomendasi belajar berikutnya;
- [ ] cetak/PDF yang ramah orang tua.

Analytics admin:

- [ ] pemain aktif harian/mingguan/bulanan;
- [ ] completion dan drop-off per chapter/scene;
- [ ] tingkat benar dan durasi per tantangan;
- [ ] soal dengan error atau tingkat gagal tidak wajar;
- [ ] distribusi jawaban dan deteksi kunci berpola;
- [ ] penggunaan audio, terjemahan, hint, speaking, dan retry;
- [ ] performa konten berdasarkan jenjang/kelompok;
- [ ] filter tanggal, chapter, kelas, sekolah, dan pengguna;
- [ ] export terkontrol tanpa membocorkan data anak;
- [ ] query agregasi/caching untuk menjaga performa.

## Fase 11 — Editor Konten Admin Lengkap

- [ ] Dashboard daftar campaign, chapter, dan scene.
- [ ] Form scene sesuai tipe challenge.
- [ ] Drag-and-drop urutan scene.
- [ ] Editor dialog dan branching.
- [ ] Preview desktop/mobile.
- [ ] Pengelola NPC, enemy, item, class, skill, quest, achievement, dan audio.
- [ ] Import Excel/TXT dan export backup.
- [ ] Media library dengan validasi ukuran, tipe, dan lisensi.
- [ ] Draft/review/publish dan scheduled publish.
- [ ] Version history, diff, rollback, dan audit log.
- [ ] Permission terpisah untuk author, reviewer, publisher, dan analyst.
- [ ] Clone chapter serta template chapter tanpa menghasilkan konten repetitif.

## Fase 12 — Sosial dan Multiplayer Opsional

Fase ini dikerjakan setelah pengalaman solo stabil.

- [ ] Leaderboard sehat berdasarkan progres atau konsistensi, bukan hanya waktu bermain.
- [ ] Kelompok kelas/sekolah dengan kontrol guru.
- [ ] Tantangan mingguan.
- [ ] Co-op quest tanpa chat bebas.
- [ ] Battle vocabulary terkontrol.
- [ ] Matchmaking berdasarkan level.
- [ ] Moderasi nama hero dan konten pengguna.
- [ ] Privasi anak dan persetujuan yang sesuai.
- [ ] Rate limit, anti-cheat, dan pelaporan penyalahgunaan.

## Fase 13 — PWA, Offline, dan Performa

- [ ] Installable PWA.
- [ ] Cache shell aplikasi dan aset chapter terpilih.
- [ ] Antrean progres offline lalu sinkronisasi aman.
- [ ] Strategi konflik jika progres berubah di dua perangkat.
- [ ] Kompresi gambar/audio dan lazy loading.
- [ ] Hilangkan ketergantungan CDN untuk aset inti bila mode offline dipakai.
- [ ] Budget performa ponsel kelas rendah.
- [ ] Monitoring error JavaScript dan API.
- [ ] Backup serta recovery data progres.

## 6. Kurikulum dan Rencana Konten

Setiap chapter harus memiliki lembar desain:

- judul dan lokasi;
- target usia/jenjang;
- level CEFR perkiraan;
- tujuan pembelajaran;
- vocabulary baru dan vocabulary review;
- grammar/function;
- ekspresi komunikasi;
- susunan 15 atau lebih scene;
- NPC dan konflik;
- challenge mix;
- boss/final task;
- reward;
- cabang cerita;
- audio dan visual;
- indikator keberhasilan.

Komposisi awal yang disarankan untuk 15 scene:

| Aktivitas | Jumlah acuan |
|---|---:|
| Dialog/pilihan kontekstual | 3 |
| Vocabulary | 2 |
| Listening | 2 |
| Sentence Forge | 2 |
| Speaking | 2 |
| Reading/comprehension | 1 |
| Story decision | 2 |
| Boss/final integrated challenge | 1 |

Komposisi boleh berubah sesuai cerita. Targetnya variasi dan keterpaduan, bukan memenuhi angka secara mekanis.

Urutan tema konten yang dapat dikembangkan:

1. Home and Family.
2. School Day.
3. Market and Money.
4. Forest and Animals.
5. Town and Directions.
6. Food and Cooking.
7. Health and Body.
8. Weather and Travel.
9. Friendship and Feelings.
10. Environment and Nature.
11. Science and Inventions.
12. Folklore and Culture.
13. Mystery and Problem Solving.
14. Space and Future.
15. Final Kingdom Adventure.

Konten berikutnya dapat menjadi campaign baru, bukan terus memperpanjang satu campaign tanpa struktur.

## 7. Model Data yang Perlu Dikaji

Entitas utama yang perlu tersedia atau diperluas:

- campaign;
- chapter/episode;
- scene/node;
- choice/edge;
- condition dan effect;
- vocabulary dan learning objective;
- NPC dan dialogue;
- quest dan objective;
- enemy dan encounter;
- item, equipment, inventory, currency, dan shop;
- skill/class;
- player profile, progress, flag, mastery, dan review schedule;
- audio/media asset;
- attempt, answer, event telemetry, dan report aggregate;
- content version, approval, dan audit log.

Semua migrasi harus:

- memiliki nama bertanggal dan deskriptif;
- aman dijalankan sekali;
- memiliki pengecekan struktur yang sesuai pola proyek;
- tidak menghapus data produksi tanpa prosedur backup dan persetujuan;
- menyertakan indeks untuk query progres dan analytics.

## 8. Keamanan dan Privasi

- Semua endpoint pemain wajib login.
- Semua endpoint admin wajib permission spesifik.
- Jangan mempercayai kunci jawaban, XP, reward, scene, atau skor dari client.
- Gunakan CSRF, validasi input, escaping output, dan query builder/binding.
- Batasi request jawaban, speaking, reward, import, dan upload.
- Cegah IDOR pada progres, inventory, laporan, dan data pemain lain.
- Jangan tampilkan stack trace atau HTML error pada respons JSON produksi.
- Minimalkan pengumpulan data anak.
- Audio speaking tidak disimpan secara default.
- Ekspor laporan harus mengikuti kewenangan dan ruang lingkup pengguna.
- Audit perubahan konten, reward, permission, dan progres manual.

## 9. Accessibility dan Pengalaman Anak

- Seluruh aksi utama dapat digunakan dengan keyboard.
- Fokus terlihat dan urutan tab logis.
- Jangan hanya mengandalkan warna untuk status benar/salah.
- Subtitle dan teks selalu tersedia bersama audio.
- Kontrol volume, mute, kecepatan suara, dan reduced motion.
- Ukuran tombol nyaman untuk layar sentuh.
- Bahasa feedback positif, jelas, dan tidak mempermalukan.
- Hindari animasi berkedip cepat.
- Sediakan mode font lebih besar dan kontras tinggi pada tahap lanjutan.

## 10. Strategi Pengujian

### Otomatis

- lint PHP dan JavaScript;
- unit test normalisasi jawaban dan scoring;
- integration test login, progres, reward, equipment, dan branching;
- validator seluruh konten;
- test distribusi kunci dan variasi template;
- test idempotensi submit/reward;
- test permission dan IDOR;
- smoke test semua route;
- test migrasi pada salinan database.

### Manual

- mainkan setiap chapter dari awal sampai akhir;
- refresh dan putuskan koneksi pada berbagai titik;
- uji tombol kembali, multi-tab, dan double click;
- uji desktop dan ponsel;
- uji audio/mikrofon serta fallback;
- uji akun member, non-login, admin terbatas, dan superadmin;
- lakukan playtest dengan anak/guru menggunakan persetujuan yang tepat.

### Metrik penerimaan

- tidak ada error fatal/uncaught exception;
- tidak ada scene buntu;
- kunci jawaban tidak berpola mencolok;
- prompt dan jawaban sesuai;
- progres tidak hilang atau meloncat;
- waktu muat memenuhi budget yang ditentukan;
- pemain memahami instruksi tanpa penjelasan developer.

## 11. Prioritas Eksekusi yang Disarankan

### Paket A — Stabil dan dapat dipercaya

1. Audit fondasi dan hak akses.
2. Validator konten dan pemeriksaan 225 adegan.
3. Test regresi progres/reward.
4. Perbaikan mobile, accessibility, dan performa dasar.

### Paket B — Terasa seperti RPG

1. Percabangan cerita nyata.
2. NPC, dialog, quest, dan toko.
3. Speaking di dalam chapter.
4. Combat berbasis kemampuan bahasa.
5. Cutscene dan animasi yang lebih kaya.

### Paket C — Pembelajaran semakin kuat

1. Mastery multidimensi.
2. Spaced repetition.
3. Difficulty adaptation.
4. Raport guru/orang tua.
5. Rekomendasi belajar.

### Paket D — Skalabilitas konten

1. Workflow author-review-publish.
2. Editor branching dan preview.
3. Import/export serta validator.
4. Versioning dan rollback.
5. Campaign/chapter baru.

### Paket E — Ekspansi opsional

1. PWA/offline.
2. Event dan achievement lanjutan.
3. Fitur kelas/sekolah.
4. Co-op atau battle terkontrol.

## 12. Estimasi Relatif

Estimasi berikut menggunakan ukuran relatif, bukan janji tanggal:

| Paket | Ukuran | Ketergantungan utama |
|---|---|---|
| Audit dan stabilisasi | M | akses lingkungan uji dan data |
| Validator kualitas konten | M | format scene konsisten |
| Branching nyata | L | desain graph dan migrasi progres |
| NPC/quest/shop | L | branching dan ekonomi game |
| Speaking chapter | M–L | browser, UX izin, fallback |
| Combat/skill tree | L | balancing dan content tooling |
| Editor admin lengkap | XL | model data final |
| Adaptive learning lanjut | XL | telemetry berkualitas |
| Audio/cutscene penuh | L–XL | produksi aset dan lisensi |
| PWA/offline | L | audit dependensi dan sinkronisasi |
| Multiplayer | XL | solo stabil, moderasi, anti-cheat |

## 13. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Konten banyak tetapi repetitif | anak cepat bosan | validator variasi dan review editorial |
| Fitur game menutupi tujuan belajar | hasil belajar lemah | objective dan mastery per scene |
| Branching merusak progres lama | kehilangan progres | versioning, migration mapping, backup |
| Speaking tidak didukung browser | scene tidak bisa lanjut | fallback teks/listening dan feature detection |
| Aset audio/visual terlalu berat | lambat di ponsel | compression, lazy load, budget aset |
| Reward mudah dieksploitasi | ekonomi game rusak | server authority, idempotency, audit log |
| Admin editor terlalu kompleks | sulit dipakai | progressive disclosure dan template |
| Analytics membuka data anak | risiko privasi | agregasi, permission, minimisasi, audit |
| Penambahan fitur tanpa test | regresi berulang | quality gate per fase |

## 14. Definition of Done per Fitur

Sebuah fitur hanya boleh ditandai selesai jika:

- kebutuhan dan perilakunya terdokumentasi;
- migrasi database aman dan sudah diuji;
- permission serta navigasi tersedia;
- validasi server dan penanganan error tersedia;
- tampilan desktop dan mobile berfungsi;
- accessibility dasar terpenuhi;
- test relevan lulus;
- alur manual utama berhasil;
- analytics/logging seperlunya tersedia;
- dokumentasi admin/pemain diperbarui;
- tidak ada data uji yang merusak progres produksi.

## 15. Keputusan yang Perlu Direview

Sebelum implementasi fase besar berikutnya, sepakati:

1. Target usia, jenjang, dan level bahasa utama.
2. Apakah campaign lama dipertahankan atau direstrukturisasi.
3. Gaya visual: emoji ringan, ilustrasi 2D, pixel art, atau kombinasi.
4. Apakah audio memakai rekaman manusia, TTS, atau keduanya.
5. Seberapa dalam branching dan berapa ending per campaign.
6. Model battle: sederhana atau turn-based penuh.
7. Mata uang dan ekonomi game tanpa mekanisme manipulatif.
8. Siapa yang menjadi author, reviewer, dan publisher konten.
9. Kebijakan penyimpanan audio dan telemetry anak.
10. Prioritas offline dibanding multiplayer.

## 16. Rekomendasi Saat Pengembangan Dilanjutkan

Mulai dari **Paket A**, kemudian buat satu vertical slice berkualitas tinggi untuk **Paket B**: satu chapter lengkap dengan NPC, cabang nyata, speaking, combat, audio, cutscene, dan boss. Uji chapter tersebut terlebih dahulu sebelum mekanik baru diterapkan ke seluruh campaign. Pendekatan ini memberi contoh standar mutu sekaligus menghindari penggandaan konten yang masih bermasalah.

Setelah vertical slice disetujui, baru:

1. finalisasi model data;
2. lengkapi editor admin;
3. migrasikan chapter lama bertahap;
4. produksi campaign berikutnya;
5. lanjut ke adaptive learning, offline, atau fitur sosial berdasarkan hasil penggunaan nyata.

## 17. Catatan Perubahan Dokumen

| Tanggal | Perubahan |
|---|---|
| 2026-08-13 | Dokumen rencana lengkap pertama dibuat untuk review lanjutan. |
| 2026-08-13 | Ditambahkan arsitektur campaign berbasis Season. Campaign yang sudah ada ditetapkan sebagai Season 1; Season 2 direncanakan sebagai RPG eksplorasi interaktif. |

## 18. Pengembangan Season

### Season 1 — The First Quest

Seluruh chapter English RPG yang sudah tersedia menjadi **Season 1**. Season menjadi lapisan navigasi baru: pemain membuka halaman RPG, memilih season, lalu melihat peta chapter seperti sebelumnya. Progress, unlock, loot, dan history chapter tetap kompatibel dengan data lama.

Kriteria implementasi Season 1:

- [x] Tabel season dan relasi episode tersedia.
- [x] Episode lama otomatis masuk ke Season 1.
- [x] Halaman awal pemain menampilkan kartu season, bukan daftar chapter langsung.
- [x] Halaman detail season menampilkan chapter, progres, dan status unlock.
- [x] Admin dapat melihat season, membuat season, dan mengatur episode di dalamnya.
- [x] Link lama ke episode tetap bekerja.
- [x] Reset progres chapter menghormati batas season.
- [ ] Halaman laporan RPG menampilkan filter dan ringkasan per season.
- [ ] Tambahkan pengaturan unlock antar-season dan prasyarat campaign.

### Season 2 — World of Living Stories

Season 2 akan memakai format eksplorasi top-down yang terinspirasi RPG klasik seperti *Suikoden*, namun memakai aset orisinal dan mekanik belajar yang aman untuk anak. Season 2 dibuat sebagai vertical slice terlebih dahulu sebelum diterapkan ke banyak area.

Vertical slice awal (fondasi sudah dieksekusi):

- [x] Peta desa kecil `Lanternbrook Village` dengan jalur, pohon, bangunan, dan collision;
- [x] Hero pixel-art orisinal dengan idle, arah hadap, dan animasi langkah ringan;
- [x] Kontrol keyboard (WASD/arrow) dan D-pad touch;
- [x] Collision server-side dan tombol reduced-motion;
- [x] Empat NPC dengan dialog Inggris, terjemahan, dan kosakata target;
- [x] Quest log dengan objective utama dan progres 3 petunjuk;
- [x] Satu quest eksplorasi `The Missing Story Sigils`;
- [x] Reward XP dunia dan item inventory setelah quest diklaim;
- [x] Autosave posisi, quest, dan interaksi pemain;
- [x] UI dan dialog gameplay menggunakan bahasa Inggris; konten world slice ditanam langsung sebagai kode/migrasi tanpa menu CRUD admin Season 1;
- [x] Latar desa pixel-art orisinal serta sprite sheet hero/NPC dipasang sebagai aset game;
- [ ] Challenge vocabulary, listening, sentence, dan speaking sebagai interaksi NPC/objek;
- [ ] Quest sampingan, pembukaan area bertahap, portrait ilustratif, dan animasi combat/victory.

Fondasi data Season 2:

- `learn_english_rpg_world_maps` untuk layout dan titik awal;
- `learn_english_rpg_world_npcs` untuk posisi, dialog, dan kosakata NPC;
- `learn_english_rpg_world_quests` untuk objective dan reward;
- `learn_english_rpg_world_player_states` untuk posisi, arah, dan XP dunia;
- `learn_english_rpg_world_player_quests` untuk status/progres quest;
- `learn_english_rpg_world_interactions` untuk telemetry minimal dan audit.

Urutan pengerjaan:

1. Stabilkan Season 1 dan migrasikan seluruh chapter ke season.
2. Buat halaman season dan detail season untuk pemain serta admin.
3. Audit unlock, reset, report, dan history agar memakai season/chapter secara konsisten.
4. [x] Buat prototipe peta Season 2 tanpa memindahkan gameplay Season 1.
5. [x] Tambahkan satu hero, empat NPC, satu map, dan satu quest vertical slice.
6. [x] Uji keyboard, touch, collision, reward, autosave, dan reduced motion dasar.
7. [ ] Uji browser/device lengkap, performa mobile, dan aksesibilitas lanjutan.
8. [ ] Baru lanjutkan produksi map, NPC, quest, dan chapter Season 2 secara bertahap.

## 19. Season 1 Quality Gate — Hasil Eksekusi

Audit awal Season 1 telah dijalankan terhadap 15 chapter dan 225 adegan aktif.

- [x] Setiap chapter memiliki 15 adegan aktif.
- [x] Distribusi challenge terukur: 105 choice, 45 listening, 45 sentence, dan 30 story.
- [x] Tidak ditemukan dialog, prompt, kosakata, terjemahan, atau audio wajib yang kosong.
- [x] Tidak ditemukan JSON pilihan rusak, jumlah kunci selain satu, atau pilihan duplikat.
- [x] Urutan kunci pilihan diseimbangkan menjadi 50 posisi pertama, 51 posisi kedua, dan 49 posisi ketiga; pengacakan per pemain tetap aktif.
- [x] Prompt dan pilihan story dibuat kontekstual per chapter, tidak lagi memakai template yang sama.
- [x] Pengecoh generik pada 175 adegan diganti dengan pilihan yang relevan terhadap kosakata dan konteks chapter.
- [x] Dialog duplikat antar-chapter diperbaiki.
- [x] Editor admin kini menyediakan tipe challenge, audio text, dan jawaban Sentence Forge.
- [x] Editor admin mempertahankan metadata `choice_code` dan `reputation_delta` saat story scene diedit.
- [x] Season root, peta Season 1, detail chapter, laporan, progres pengguna, dan endpoint chapter diuji tanpa error PHP/database.

Temuan yang masih menjadi pekerjaan lanjutan sebelum Season 1 dinyatakan final:

- [ ] Review editorial manual untuk makna, tingkat kesulitan, dan kualitas pengecoh setiap adegan.
- [ ] Validator server-side khusus Sentence Forge untuk memastikan token dan jawaban konsisten sebelum publish.
- [ ] Filter laporan admin berdasarkan season dan agregasi completion/drop-off per chapter.
- [ ] Uji browser/device lengkap serta baseline performa aset audio dan animasi.

## 20. Season 2 Vertical Slice — Hasil Eksekusi

Story development is now maintained separately in the [English Quest Story Bible](english_rpg_story/README.md). The first chapter concept, [Chapter 1 — The Missing Story Sigils](english_rpg_story/CHAPTER_01_LANTERNBROOK.md), has been approved for staged implementation. It expands the current short 3-NPC flow into six acts, multiple objectives, optional side quest, new supporting characters, checkpoints, contextual Hint/Translate assistance with XP cost, and a complete narrative ending. Exact costs remain subject to playtest balancing.

Season 2 sekarang dapat dibuka dari `/belajar/english-quest`, lalu memilih kartu **World of Living Stories**. Route `/belajar/english-quest/season/2` menampilkan dunia interaktif pertama tanpa mengubah progres Season 1.

- [x] Migrasi database Season 2 dijalankan dan dibuat idempotent: map, NPC, quest, state pemain, state quest, dan log interaksi.
- [x] Map `Lanternbrook Village` berukuran 18×12 memiliki collision yang diperiksa server-side.
- [x] Posisi pemain tersimpan setiap gerakan dan dibuat ulang saat halaman dibuka kembali.
- [x] NPC Mira, Eli, Nova, dan Ren dapat diajak bicara ketika pemain berada di petak yang berdekatan.
- [x] Quest `The Missing Story Sigils` berjalan dari mulai, mengumpulkan 3 petunjuk, hingga klaim hadiah.
- [x] Klaim quest idempotent: status berubah menjadi `claimed`, XP dunia dan item `Lantern Story Sigil` diberikan satu kali.
- [x] Uji manual end-to-end berhasil: gerak, collision, dialog, progres 0→3, klaim reward, dan penyimpanan state.
- [x] Sidebar quest menjadi scroll container mandiri sehingga scroll objective tidak menggeser area game utama.
- [x] Chapter 1 memiliki penutup eksplisit setelah kembali ke Mira: `Chapter 1 Complete` dan jalan menuju `Whispering Grove`.
- [x] Area map dan sprite diperbesar, dengan animasi bob/footstep saat karakter bergerak.
- [x] Arah hadap diperbarui segera saat tombol ditekan, termasuk ketika jalur sedang terhalang; perpindahan petak memakai transisi halus dan animasi langkah.
- [x] Collision server-side diselaraskan dengan rumah, air mancur, pasar, dan area bangunan pada ilustrasi `Lanternbrook Village`.
- [x] Petak NPC juga menjadi solid: pemain tidak dapat menembus atau bertumpuk dengan Mira, Eli, Nova, maupun Ren.
- [x] Chapter 1 memakai dialog dua arah: Mira → Eli → Nova → Ren → Mira. Setiap NPC memberi pertanyaan pilihan ganda, jawaban salah dapat diulang, jawaban benar membuka tujuan berikutnya, dan jawaban clue terakhir membuka jalan baru.
- [x] Pilihan jawaban memakai handler mouse, pointer, dan touch langsung agar tombol `Answer` aktif konsisten pada desktop maupun perangkat sentuh.
- [x] Jawaban percakapan kini terkirim otomatis saat opsi dipilih; tombol submit manual disembunyikan.
- [x] Layout world memakai lebar layar penuh proporsional, peta mengikuti tinggi viewport, dan sidebar desktop diperlebar serta diringkas agar terbaca tanpa scroll internal.
- [x] Alur Chapter 1 diperpanjang menjadi delapan percakapan yang saling terhubung dengan kunjungan ulang Mira, Eli, dan Nova sebelum penutup terakhir.
- [x] Progres Season 2 ditampilkan pada `/learn-english-rpg/progress/user/*`, lengkap dengan riwayat interaksi serta reset terpisah yang tidak menghapus progres Season 1.
- [x] Latar desa dan sprite sheet hero/NPC pixel-art orisinal sudah dipasang.
- [ ] Musik, efek suara lingkungan, combat, dan area lanjutan belum diproduksi.
