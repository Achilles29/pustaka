# Chapter 1 — The Missing Story Sigils

**Subtitle:** *Lanternbrook Village*

**Status:** Konsep disetujui untuk implementasi bertahap; belum diimplementasikan.

## 1. Janji pengalaman pemain

Pemain tidak hanya “berbicara dengan tiga orang”. Pemain tiba di Lanternbrook ketika cahaya pelindung desa padam, menerima tugas dari penjaga arsip, lalu berkeliling untuk:

- memahami masalah desa;
- meminta petunjuk dari beberapa orang dengan cara yang sopan;
- membantu pekerjaan kecil yang membuka informasi baru;
- membaca tanda, mengikuti arah, mengantar pesan, dan menemukan benda;
- mengumpulkan tiga Story Sigils yang memiliki sejarah berbeda;
- memperbaiki wadah sigil bersama ahli pembuat lentera;
- mengingat kembali semua petunjuk;
- membuka gerbang utara menuju Whispering Grove.

Target durasi satu kali tamat adalah **30–45 menit**, dapat dibagi menjadi beberapa sesi dengan checkpoint setelah setiap sigil. Pemain selalu tahu objective berikutnya, tetapi tidak langsung diberi seluruh jawabannya.

## 2. Inti misteri

Lanternbrook memiliki **Lantern Archive**, arsip hidup yang menyimpan cerita dan kosakata desa. Setiap malam, tiga Story Sigils menyalakan arsip:

1. **Root Sigil** — mengingatkan desa dari mana ia berasal.
2. **Promise Sigil** — menjaga janji agar cerita tidak diputarbalikkan.
3. **Crossing Sigil** — membuka jalan bagi orang yang siap mempelajari sesuatu yang baru.

Menjelang Festival of First Light, ketiganya hilang dari kotak arsip. Mira, penjaga arsip, awalnya mengira sigil dicuri. Setelah pemain mengumpulkan petunjuk, terungkap bahwa para penjaga lama menyembunyikan sigil untuk melindunginya dari **The Hollow Echo**, suara yang meniru cerita tetapi menghapus maknanya. Mereka menyebarkan petunjuk di antara warga dan membuatnya hanya dapat ditemukan oleh seseorang yang mau mendengar, membantu, dan menghubungkan cerita.

Chapter 1 berakhir ketika pemain memulihkan arsip. Namun, cahaya arsip menampilkan satu kalimat baru:

> **“The first story is safe. The next one is waiting beyond Whispering Grove.”**

Ini menutup masalah chapter pertama sekaligus memberi alasan yang kuat untuk melanjutkan chapter kedua.

## 3. Tujuan belajar

Konten ditujukan untuk level dasar menuju dasar-menengah. Bahasa utama selalu bahasa Inggris; terjemahan hanya tersedia lewat tombol hint.

### Kosakata inti

`archive`, `sigil`, `root`, `promise`, `crossing`, `clue`, `keeper`, `market`, `bridge`, `forge`, `path`, `memory`, `restore`, `protect`, `trade`, `return`.

### Kemampuan bahasa

- memahami sapaan, permintaan, dan respons singkat;
- memahami arah dan preposisi tempat: `left`, `right`, `behind`, `across`, `beside`, `near`;
- memahami instruksi berurutan: `first`, `then`, `after that`, `finally`;
- memakai present simple untuk kebiasaan dan fakta;
- memakai past simple sederhana untuk menceritakan apa yang terjadi;
- memakai kalimat sopan: `Could you help me?`, `May I ask...`, `Thank you for...`;
- memilih respons berdasarkan konteks, bukan berdasarkan posisi kunci jawaban;
- menyusun kalimat dengan subjek, kata kerja, objek, waktu, dan tempat yang bervariasi;
- mendengarkan detail penting dalam dialog pendek.

## 4. Karakter

| Karakter | Peran cerita | Lokasi utama | Fungsi belajar |
|---|---|---|---|
| **Lantern** (pemain) | Pendatang yang membantu Lanternbrook | Seluruh desa | Menghasilkan respons, membaca, mendengar, dan memilih tindakan |
| **Mira** | Keeper of the Lantern Archive; pemberi misi | Archive House, sisi barat | Menetapkan tujuan, merangkum petunjuk, final comprehension |
| **Eli** | Garden keeper yang mengetahui asal Root Sigil | Kebun dan rumah kaca | Kosakata alam, instruksi, urutan tindakan |
| **Nova** | Traveling story merchant yang pernah membawa Promise Sigil | Market stall | Percakapan transaksi, past simple, polite requests |
| **Ren** | Scout yang menjaga jalur jembatan | South bridge dan tepi sungai | Arah, preposisi, membaca tanda, listening |
| **Tessa** | Innkeeper yang menerima pesan lama dari penjaga arsip | Lantern Inn | Membaca pesan, memahami maksud, respons sosial |
| **Bram** | Lantern smith yang dapat memperbaiki sigil case | Forge di timur | Instruksi kerja, material, sequence, Sentence Forge |
| **Pip** | Kurir muda yang mengenal jalan-jalan kecil | Alun-alun dan kebun | Tutorial arah dan side quest ringan |
| **Orin** | Warden of the Northern Stairs | Gerbang utara | Pemeriksaan kesiapan dan pengenalan ancaman Hollow Echo |
| **The Hollow Echo** | Ancaman yang baru terlihat melalui suara dan bayangan | Muncul di final | Hook untuk chapter 2, bukan boss penuh di chapter 1 |

### Aturan karakter

- Setiap NPC menyimpan **satu fakta, satu perasaan, dan satu petunjuk**. Dialog tidak hanya menyebut jawaban.
- NPC tidak otomatis berpindah ke NPC berikutnya setelah jawaban benar. Pemain harus berjalan, mengamati, dan kembali bila objective memintanya.
- Setelah quest selesai, dialog NPC berubah menjadi refleksi singkat sehingga dunia terasa hidup.
- Tiga NPC lama tetap penting; empat NPC baru memperpanjang alur tanpa menghapus progres yang sudah ada.

## 5. Struktur chapter

Chapter terdiri dari enam act. Setiap act memiliki objective yang selesai dan checkpoint yang disimpan.

```text
Prologue
  └─ Act I  The Darkened Archive
       └─ Act II The Root Remembers
            └─ Act III A Promise in the Market
                 └─ Act IV The Crossing Trail
                      └─ Act V The Broken Case
                           └─ Act VI The Northern Gate
```

## 6. Urutan scene dan quest

Nomor di bawah adalah urutan desain, bukan kewajiban bahwa setiap baris menjadi satu file scene. Pada implementasi, beberapa baris dapat menjadi satu scene dialog dengan beberapa langkah objective.

### Prologue — The Light Goes Out

| ID | Momen | Aksi pemain | Tantangan bahasa | Hasil |
|---|---|---|---|---|
| P0 | Festival terhenti | Menonton cutscene pendek; lampu arsip padam | Mendengar 3 kalimat narasi | Pemain memahami masalah sebelum bergerak |
| P1 | Mira meminta bantuan | Mendekati Mira dan menyentuh prompt percakapan | Memilih respons sopan terhadap permintaan | `chapter_started`, mendapat **Empty Sigil Case** |
| P2 | What is a sigil? | Memilih makna `sigil` dari konteks Mira | Vocabulary in context | Pemain mengenal kata kunci |
| P3 | Misi pertama | Membaca objective board | Mengurutkan `first / then / finally` | Terbuka jalur menuju Eli dan Pip |

### Act I — The Darkened Archive

| ID | Momen | Aksi pemain | Tantangan bahasa | Hasil |
|---|---|---|---|---|
| A1 | Petunjuk dari Pip | Menyentuh Pip; berjalan mengikuti arah ke kebun | `behind the fountain`, `beside the garden` | Pemain belajar navigasi |
| A2 | Side quest: Pip’s Lost Storybook | Memilih membantu atau menunda mencari buku | Membaca tiga label lokasi | Side quest aktif; pilihan memengaruhi dialog Pip |
| A3 | Buku di dekat rumah kaca | Menemukan storybook di hotspot | Memahami instruksi pendek | Mendapat **Story Scrap 1** dan bonus vocabulary card |
| A4 | Kembali ke Mira | Menyerahkan storybook atau hanya melaporkan | Memilih respons jujur | Mira memberi petunjuk Root Sigil tanpa menghukum pilihan |

### Act II — The Root Remembers

| ID | Momen | Aksi pemain | Tantangan bahasa | Hasil |
|---|---|---|---|---|
| R1 | Bertemu Eli | Berjalan ke Eli lalu membuka prompt | Memahami pertanyaan tentang asal desa | Eli menyebut `root cellar` tetapi belum memberi sigil |
| R2 | Garden repair | Mengikuti 3 instruksi: water, move, cover | Listening + imperative verbs | Kebun kembali menyala; jalur kecil terbuka |
| R3 | Tiga daun bercahaya | Menemukan tiga daun dengan label berbeda | Memilih benda sesuai deskripsi | Mendapat **Green Leaf Token** |
| R4 | Pintu root cellar | Membaca catatan lama pada pintu | Menentukan urutan tindakan yang benar | Pintu terbuka; pemain menemukan jejak sigil |
| R5 | Eli’s second question | Menjawab apa yang dilindungi penjaga lama | Reading comprehension | `clue_root` aktif; mendapat **Root Sigil Fragment** |
| R6 | Checkpoint Mira | Kembali ke Mira dan menceritakan kembali clue | Menyusun ringkasan pendek, bukan menebak kata | Act II selesai; market objective terbuka |

### Act III — A Promise in the Market

| ID | Momen | Aksi pemain | Tantangan bahasa | Hasil |
|---|---|---|---|---|
| M1 | Nova’s bargain | Menghampiri Nova di market | Memahami dialog transaksi | Nova mengaku pernah membawa benda bercahaya |
| M2 | The old promise | Menanyakan apa yang diperdagangkan Nova | Past simple: `traded`, `promised`, `returned` | Petunjuk mengarah ke Tessa |
| M3 | Pesan untuk Tessa | Membawa **Promise Note** ke Lantern Inn | Membaca nama penerima dan tujuan | Tessa membuka kotak pesan |
| M4 | Tessa’s listening test | Mendengarkan cerita Tessa lalu memilih detail yang benar | Listening for who/when/why | Mendapat **Sealed Parcel** |
| M5 | Delivery choice | Memilih mengantar parcel langsung atau membaca label dengan hati-hati | Polite request dan consequence ringan | Trust Tessa berubah; tidak ada dead end |
| M6 | Nova’s second conversation | Mengembalikan parcel dan menjawab apa isi janji lama | Contextual comprehension | `clue_promise` aktif; mendapat **Promise Sigil Fragment** |
| M7 | Festival memory | Nova menceritakan bagaimana Hollow Echo meniru suara | Memilah fakta dan rumor | Ancaman utama mulai dikenali |

### Act IV — The Crossing Trail

| ID | Momen | Aksi pemain | Tantangan bahasa | Hasil |
|---|---|---|---|---|
| C1 | Ren at the bridge | Menghampiri Ren | Memahami pertanyaan tentang arah | Ren menolak memberi sigil sebelum pemain membaca trail |
| C2 | Three trail markers | Memeriksa penanda `north`, `across`, `under` | Prepositions and map reading | Jalur sungai terbuka |
| C3 | Pip’s shortcut | Memilih rute aman atau rute cepat | Membaca dua instruksi dengan konsekuensi berbeda | Rute cepat memberi waktu bonus; rute aman memberi hint |
| C4 | Orin’s warning | Berbicara dengan Orin di northern stairs | Memahami larangan dan alasan | Orin mengungkap nama Hollow Echo |
| C5 | Bridge inspection | Mengambil tiga potongan pita penanda | Menjawab `Where was the blue mark?` | Mendapat **Blue Trail Token** |
| C6 | Ren’s second conversation | Menyusun urutan perjalanan berdasarkan tiga marker | Sentence ordering dengan pola baru | `clue_crossing` aktif; mendapat **Crossing Sigil Fragment** |

### Act V — The Broken Case

| ID | Momen | Aksi pemain | Tantangan bahasa | Hasil |
|---|---|---|---|---|
| F1 | Bram at the forge | Membawa Empty Sigil Case ke Bram | Memahami nama material | Bram bersedia membantu setelah tugas kecil |
| F2 | Gather materials | Mengumpulkan brass wire, blue glass, dan dry wood | Memilih item dari deskripsi | Material lengkap; terbuka repair table |
| F3 | Repair instructions | Menekan langkah perbaikan sesuai instruksi | Imperative listening dan sequence | Case repaired; `case_repaired` aktif |
| F4 | Sentence Forge: the repair note | Menyusun beberapa variasi kalimat perbaikan | Declarative, negative, question; tidak memakai template yang sama | Mendapat **Restored Sigil Case** |
| F5 | Three memory visits | Mengunjungi Eli, Nova, dan Ren dalam urutan bebas | Setiap NPC meminta satu kalimat recap berbeda | Ketiga fragmen dapat dipasang |
| F6 | Optional Story Scraps | Jika side quest Pip selesai, mencari dua scrap tambahan | Reading for detail | Membuka ending rahasia kecil dan bonus title |

### Act VI — The Northern Gate

| ID | Momen | Aksi pemain | Tantangan bahasa | Hasil |
|---|---|---|---|---|
| G1 | Return to Mira | Menyerahkan tiga fragmen dan case | Menjawab tiga pertanyaan berbasis clue, bukan hafalan | Mira memberi **Lantern Key** |
| G2 | The final story check | Menjelaskan hubungan root, promise, dan crossing | Pilihan respons + satu Sentence Forge | `final_check_passed`; jalan ke gerbang terbuka |
| G3 | Orin’s gate | Berbicara dengan Orin dan memilih kalimat izin yang tepat | Polite request: `May we pass?` | Gerbang utara aktif |
| G4 | Place the sigils | Menyentuh tiga slot dalam urutan yang benar | Sequence and contextual vocabulary | Lantern Archive menyala kembali |
| G5 | The Hollow Echo | Cutscene pendek; suara meniru kalimat pemain | Listening and recognition | Ancaman chapter 2 diperkenalkan |
| G6 | Chapter complete | Menerima reward, membaca recap, memilih “Enter Whispering Grove later” | Self-reflection vocabulary | Chapter 2 terbuka; progres tersimpan |

## 7. Side quest — Pip’s Lost Storybook

Side quest ini tidak boleh mengunci main quest. Fungsinya memberi jeda emosional, memperluas dunia, dan membuat pemain merasa tindakan kecilnya penting.

1. Pip kehilangan buku cerita milik ibunya.
2. Pemain mendapat tiga petunjuk lokasi, tetapi hanya satu yang benar-benar menyebut buku.
3. Setelah menemukan buku, pemain memilih mengembalikannya langsung atau membaca satu halaman bersama Pip.
4. Jika membaca bersama, pemain mendapat `Story Scrap 1` dan Pip akan membantu memberi arah di Act IV.
5. Jika melewati side quest, Pip tetap muncul di ending, tetapi tidak membuka bonus title.

**Bonus title:** `Listener of Small Stories`.

## 8. Percabangan yang aman

Chapter pertama menggunakan percabangan ringan agar mudah dipahami dan tidak membuat anak terjebak.

| Keputusan | Dampak langsung | Dampak akhir |
|---|---|---|
| Membantu Pip sekarang atau nanti | Dialog Pip berbeda | Bantuan arah dan bonus story scrap |
| Memilih rute aman atau cepat | Waktu dan hint berbeda | Tidak mengubah main ending |
| Menjawab sopan tetapi salah | NPC memberi contoh dan retry | Trust tetap dapat dipulihkan |
| Mengaku belum tahu | Mendapat hint lebih jelas | Tidak dihukum; feedback lebih panjang |
| Membaca story scrap tambahan | Lore dan title | Secret epilogue satu paragraf |

Tidak ada pilihan yang membuat chapter tidak dapat diselesaikan. Kesalahan bahasa memengaruhi jumlah percobaan, hint, atau bonus, bukan akses ke cerita utama.

## 9. Ritme dialog dan gameplay

Siklus setiap bagian harus mengikuti pola:

```text
Talk → Understand → Move/Help → Check understanding → Return/Unlock → Story reveal
```

Aturan ritme:

- Jangan menampilkan pertanyaan berturut-turut lebih dari dua kali tanpa aksi eksplorasi.
- Setelah tantangan sulit, berikan dialog reflektif atau reward kecil.
- Setiap NPC utama memiliki minimal dua pertemuan yang berbeda.
- Pertemuan kedua tidak mengulang dialog pertama; NPC menyebut konsekuensi tindakan pemain.
- Pemain selalu mendapat objective yang konkret: siapa, di mana, dan mengapa.
- Modal prompt NPC hanya memulai percakapan; isi percakapan tetap berada di dialogue modal yang sudah ada.

## 10. Tantangan bahasa per act

| Act | Fokus | Contoh aktivitas |
|---|---|---|
| I | Sapaan, makna kata, arah dasar | Memilih respons dan mengikuti petunjuk Pip |
| II | Imperative, benda alam, urutan | Water the seedlings, move the stone, cover the root |
| III | Past simple dan transaksi | What did Nova trade? Why did Tessa keep the note? |
| IV | Preposition dan listening | Find the marker across the bridge, not under it |
| V | Instruksi dan sentence variety | Repair note dalam bentuk pernyataan, negatif, dan pertanyaan |
| VI | Ringkasan dan transfer makna | Menghubungkan tiga sigil dengan tiga konsep |

Setiap jawaban salah harus memberi feedback dalam bahasa Inggris sederhana, misalnya:

> “Almost. The note says **across the bridge**, not **under the bridge**. Try again.”

Hint Bahasa Indonesia muncul hanya setelah tombol bantuan disentuh, dan tidak langsung menyorot pilihan benar.

## 11. Sistem bantuan: hint dan penerjemah

Setiap objective dan tantangan quest wajib memiliki tombol bantuan yang mudah ditemukan. Bantuan dirancang untuk pemain yang benar-benar kesulitan, bukan untuk menyembunyikan materi.

### Jenis bantuan

| Bantuan | Fungsi | Biaya rekomendasi |
|---|---|---:|
| **Hint** | Memberi petunjuk konteks, lokasi, atau langkah berikutnya tanpa membocorkan jawaban | -5 XP |
| **Translate** | Menampilkan terjemahan kalimat/petunjuk yang sedang aktif dan 2–3 kata penting | -8 XP |
| **Explain** | Menjelaskan mengapa pilihan tertentu benar setelah pemain salah atau meminta bantuan kedua kali | -10 XP |
| **Reveal answer** | Hanya untuk tantangan yang sudah dicoba dan tetap sulit; menghilangkan sebagian reward tantangan | -15 XP |

Biaya di atas adalah baseline Chapter 1 dan dapat diseimbangkan setelah playtest. UI harus menampilkan konfirmasi sebelum biaya diterapkan, misalnya:

> **Use Translate for 8 XP?** You can still try without it.

### Aturan perilaku bantuan

1. Hint dan Translate tersedia pada setiap quest utama, termasuk percakapan NPC, hotspot, objective membaca, puzzle, dan final check.
2. Bantuan tampil dalam konteks langkah yang sedang dikerjakan; tidak membuka seluruh walkthrough chapter.
3. Hint pertama hanya memberi arah. Hint kedua boleh mengungkap kata kunci. Jawaban penuh baru muncul melalui **Reveal answer**.
4. Translate tidak menerjemahkan pilihan jawaban satu per satu sebelum pemain mencoba; hal ini mencegah fitur menjadi kunci jawaban otomatis.
5. Setelah hint dipakai, tombol berubah menjadi **Used** untuk langkah tersebut agar pemain tidak membayar dua kali untuk bantuan yang sama.
6. Kesalahan menjawab tidak mengurangi XP. Pengurangan hanya terjadi ketika pemain secara sadar menekan dan mengonfirmasi bantuan.
7. XP tidak boleh menjadi negatif. Jika XP pemain kurang dari biaya, bantuan tetap dapat digunakan dengan biaya sebesar sisa XP dan pesan bahwa bantuan tetap tersedia agar pemain tidak terjebak.
8. Menggunakan bantuan menandai `assisted=true` pada attempt dan terlihat di recap/raport, tetapi tidak mengurangi hak membuka chapter berikutnya.
9. Bantuan teknis atau aksesibilitas (subtitle, kontrol suara, reduced motion, dan fallback speech recognition) tidak dikenai biaya.
10. Pada mode review setelah chapter selesai, terjemahan dan penjelasan dapat dibuka tanpa biaya karena tujuan mode tersebut adalah penguatan materi.

### Urutan bantuan adaptif

```text
Pemain mencoba → feedback singkat → Hint → coba lagi → Translate → coba lagi → Explain/Reveal answer
```

Sistem tidak memaksa pemain melewati semua tingkat. Pemain dapat langsung memilih Translate atau Explain, tetapi biayanya tetap lebih besar. Untuk anak yang baru belajar, guru/admin nantinya dapat mengatur biaya, mematikan Reveal answer, atau memberi kuota bantuan harian.

### Contoh di Chapter 1

- Pada objective `Find the root cellar`, Hint berkata: **“Look behind the greenhouse.”** Translate menampilkan arti `root cellar` dan `greenhouse`.
- Pada dialog Nova, Hint berkata: **“Listen for what Nova traded.”** Translate menjelaskan kalimat pertanyaan tanpa menerjemahkan semua opsi.
- Pada puzzle tiga sigil, Explain memberi pengingat: **“Root comes first, promise follows, crossing opens the way.”**
- Pada final check, Reveal answer hanya aktif setelah satu percobaan dan mengurangi reward final, bukan mengunci gerbang.

## 12. Progres, item, dan reward

### Flag cerita minimum

```text
chapter_1_started
pip_storybook_started
pip_storybook_completed
clue_root
clue_promise
clue_crossing
case_repaired
final_check_passed
gate_opened
hollow_echo_seen
secret_scraps_complete
```

### Item penting

```text
empty_sigil_case
green_leaf_token
promise_note
sealed_parcel
blue_trail_token
root_sigil_fragment
promise_sigil_fragment
crossing_sigil_fragment
restored_sigil_case
lantern_key
```

### Reward pacing

- Checkpoint Act II: XP kecil + vocabulary card `root`.
- Checkpoint Act III: XP kecil + vocabulary card `promise`.
- Checkpoint Act IV: XP kecil + vocabulary card `crossing`.
- Act V selesai: title sementara `Sigil Repair Apprentice`.
- Chapter complete: XP utama, full glossary recap, cosmetic badge, dan unlock Whispering Grove.
- Secret side quest: title `Listener of Small Stories` dan satu dialog tambahan di awal chapter 2.

Reward tidak boleh membuat item lebih penting daripada kemampuan bahasa. Item hanya membuka percakapan, area, atau bonus cerita.

## 13. Penutup chapter

Setelah tiga sigil dipasang, archive light menyala. Mira tidak hanya berkata “Chapter Complete”; ia merangkum kontribusi pemain:

1. Eli mengajarkan bahwa asal harus diingat.
2. Nova mengajarkan bahwa janji harus dijaga.
3. Ren mengajarkan bahwa jalan baru membutuhkan keberanian.

Di dinding arsip muncul bayangan yang mengucapkan ulang kalimat pemain dengan satu kata yang hilang. Orin menutup gerbang sebentar dan berkata:

> **“The Hollow Echo has heard you. Beyond the grove, choose your words carefully.”**

Mira lalu memberi pilihan:

- **Enter Whispering Grove** — lanjut ke chapter 2.
- **Review the village** — kembali bebas untuk menyelesaikan side quest, mendengar ulang dialog, atau mengambil vocabulary card.

Dengan begitu chapter selesai secara naratif, tetapi dunia tidak terasa langsung ditutup.

## 13A. Rekomendasi transisi ant area

Chapter 1 sebaiknya tetap memiliki ending utama yang tuntas, lalu membuka peta
lanjutan dari dalam dunia—bukan memaksa pemain kembali ke halaman daftar.
Empat arah pada peta dapat dipakai dengan fungsi yang berbeda:

- **Utara — Whispering Grove:** jalur utama menuju Chapter 2. Setelah quest
  selesai, tampilkan panah besar dan tombol `Continue north`.
- **Barat — Lantern Inn:** side quest Pip/Tessa dan latihan listening singkat.
- **Timur — Market Trail:** side quest Nova, vocabulary barter, dan item.
- **Selatan — Riverside Crossing:** area review, replay dialog, dan koleksi
  kata sebelum pemain melanjutkan cerita utama.

Dengan pola ini Chapter 1 tidak dipanjangkan secara artifisial, tetapi pemain
tetap merasa dunia terus hidup. Pintu utara baru sebaiknya dibuat playable
setelah desain Chapter 2 disetujui agar panah tidak membawa pemain ke area
kosong atau placeholder.

## 14. Batas implementasi setelah review

Urutan implementasi yang disarankan:

1. Tambahkan story flags dan item state tanpa menghapus progres Season 2 yang ada.
2. Ubah quest state dari sekadar `visited 3 NPC` menjadi objective chain per act.
3. Tambahkan prompt NPC berbasis kedekatan dan target tap-to-NPC.
4. Tambahkan hotspot interaktif untuk storybook, leaves, markers, materials, dan gate.
5. Tambahkan NPC Tessa, Bram, Pip, dan Orin.
6. Tambahkan dialog dua pertemuan dan pertanyaan yang berbeda untuk setiap NPC utama.
7. Tambahkan checkpoint, recap, reward, dan ending flags.
8. Baru setelah itu menulis konten chapter 2 dengan pola yang berbeda.

## 15. Kriteria cerita siap dikodekan

- [ ] Urutan 6 act dan 24 momen utama disetujui.
- [ ] Nama, peran, dan lokasi NPC disetujui.
- [ ] Tiga makna sigil dan hubungan dengan Whispering Grove disetujui.
- [ ] Side quest Pip dipertahankan atau dihapus sebelum implementasi.
- [ ] Pilihan percabangan ringan disetujui.
- [ ] Target durasi 30–45 menit dianggap sesuai untuk pemain.
- [ ] Semua dialog final ditulis dalam bahasa Inggris dan direview tingkat kesulitannya.
- [ ] Setiap scene memiliki objective, kondisi selesai, feedback salah, dan reward.
- [ ] Setiap quest memiliki Hint dan Translate; quest sulit memiliki Explain/Reveal answer dengan biaya yang jelas.
- [ ] Biaya bantuan, status `assisted`, batas XP minimum, dan konfirmasi biaya disepakati.
- [ ] Tidak ada scene yang hanya mengulang pola `I learned the word ...`.
- [ ] Setelah kriteria di atas selesai, implementasi dilakukan bertahap dan diuji per act.

## 16. Pertanyaan review untuk pemilik produk

1. Apakah **The Hollow Echo** cocok menjadi misteri utama, atau ingin antagonis yang lebih terlihat sejak awal?
2. Apakah side quest Pip wajib untuk semua pemain, atau tetap opsional seperti rancangan ini?
3. Apakah target durasi 30–45 menit per chapter sesuai dengan kebiasaan belajar pengguna?
4. Apakah nama karakter baru (Tessa, Bram, Pip, Orin) dipertahankan?
5. Apakah reward title dan story scrap ingin tampil di profil/raport pemain?
