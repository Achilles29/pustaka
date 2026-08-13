-- Flashcard SMP kelas 7–9 dan SMA/SMK kelas 10–12 (6 deck, 72 kartu).
INSERT INTO learn_flashcard_decks(code,name,description,subject_id,grade_level_id,icon,color,sort_order,is_active) VALUES
('ipa_smp7_ekosistem','IPA Kelas 7 — Ekosistem & Klasifikasi','Konsep organisme, klasifikasi, interaksi, dan lingkungan.',4,9,'ti-plant-2','#16a34a',110,1),
('mtk_smp8_aljabar','Matematika Kelas 8 — Aljabar & Relasi','Variabel, persamaan, fungsi, gradien, dan pola bilangan.',1,10,'ti-math-function','#f97316',120,1),
('english_smp9_expression','English Kelas 9 — Expressions & Texts','Ungkapan, jenis teks, tata bahasa, dan kosakata kontekstual.',3,11,'ti-language','#2563eb',130,1),
('fisika_sma10_dasar','Fisika Kelas 10 — Gerak & Pengukuran','Besaran, satuan, vektor, gerak, gaya, energi, dan momentum.',15,12,'ti-wave-sine','#7c3aed',140,1),
('biologi_sma11_sel','Biologi Kelas 11 — Sel & Sistem Tubuh','Struktur sel, jaringan, transport membran, dan sistem organ.',14,13,'ti-dna-2','#059669',150,1),
('kimia_sma12_reaksi','Kimia Kelas 12 — Reaksi & Elektrokimia','Redoks, sel elektrokimia, sifat koligatif, dan kimia karbon.',16,14,'ti-flask-2','#db2777',160,1)
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),subject_id=VALUES(subject_id),grade_level_id=VALUES(grade_level_id),icon=VALUES(icon),color=VALUES(color),is_active=1;

DROP TEMPORARY TABLE IF EXISTS tmp_flashcards_secondary;
CREATE TEMPORARY TABLE tmp_flashcards_secondary(code VARCHAR(60),front VARCHAR(300),back VARCHAR(1000),hint VARCHAR(300),sort_order INT) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO tmp_flashcards_secondary VALUES
('ipa_smp7_ekosistem','Organisme','Individu makhluk hidup, seperti seekor kucing atau satu pohon mangga.','Satuan tunggal makhluk hidup',1),
('ipa_smp7_ekosistem','Populasi','Sekumpulan organisme sejenis yang hidup di tempat dan waktu yang sama.','Sekumpulan ikan nila dalam satu kolam',2),
('ipa_smp7_ekosistem','Komunitas','Seluruh populasi berbeda yang hidup bersama pada suatu wilayah.','Populasi tumbuhan, hewan, dan mikroba',3),
('ipa_smp7_ekosistem','Biotik','Komponen lingkungan yang berupa makhluk hidup.','Tumbuhan, hewan, manusia, mikroorganisme',4),
('ipa_smp7_ekosistem','Abiotik','Komponen tak hidup yang memengaruhi kehidupan.','Air, tanah, cahaya, suhu',5),
('ipa_smp7_ekosistem','Simbiosis mutualisme','Hubungan dua organisme yang keduanya memperoleh keuntungan.','Lebah dan bunga',6),
('ipa_smp7_ekosistem','Simbiosis komensalisme','Hubungan saat satu pihak untung dan pihak lain tidak dirugikan.','Anggrek menempel pada pohon',7),
('ipa_smp7_ekosistem','Simbiosis parasitisme','Hubungan saat satu pihak untung sedangkan pihak lain dirugikan.','Benalu dan pohon inang',8),
('ipa_smp7_ekosistem','Produsen','Organisme yang mampu membuat makanan sendiri.','Tumbuhan hijau',9),
('ipa_smp7_ekosistem','Konsumen','Organisme yang mendapatkan energi dengan memakan organisme lain.','Hewan dan manusia',10),
('ipa_smp7_ekosistem','Dekomposer','Organisme pengurai sisa makhluk hidup menjadi zat sederhana.','Jamur dan bakteri',11),
('ipa_smp7_ekosistem','Kunci determinasi','Panduan berpasangan untuk mengidentifikasi atau mengelompokkan makhluk hidup.','Memakai ciri yang berlawanan',12),

('mtk_smp8_aljabar','Variabel','Lambang, biasanya huruf, yang mewakili nilai yang belum diketahui.','Contoh x pada 2x + 3',1),
('mtk_smp8_aljabar','Koefisien','Bilangan yang mengalikan variabel dalam bentuk aljabar.','Pada 5x, koefisiennya 5',2),
('mtk_smp8_aljabar','Konstanta','Suku berupa bilangan yang tidak memiliki variabel.','Pada 2x + 7, konstantanya 7',3),
('mtk_smp8_aljabar','Suku sejenis','Suku yang memiliki variabel dan pangkat variabel sama.','3x dan -5x',4),
('mtk_smp8_aljabar','Persamaan linear','Persamaan dengan variabel berpangkat tertinggi satu.','2x + 3 = 9',5),
('mtk_smp8_aljabar','Relasi','Hubungan antara anggota suatu himpunan dengan himpunan lain.','Dapat disajikan dengan diagram panah',6),
('mtk_smp8_aljabar','Fungsi','Relasi yang memasangkan setiap anggota domain tepat dengan satu anggota kodomain.','Setiap input punya satu output',7),
('mtk_smp8_aljabar','Domain','Himpunan semua nilai masukan pada sebuah fungsi.','Daerah asal',8),
('mtk_smp8_aljabar','Range','Himpunan nilai keluaran yang benar-benar dihasilkan fungsi.','Daerah hasil',9),
('mtk_smp8_aljabar','Gradien','Ukuran kemiringan suatu garis lurus.','Perubahan y dibagi perubahan x',10),
('mtk_smp8_aljabar','Teorema Pythagoras','Pada segitiga siku-siku, kuadrat sisi miring sama dengan jumlah kuadrat dua sisi lainnya.','a² + b² = c²',11),
('mtk_smp8_aljabar','Pola bilangan','Susunan bilangan yang mengikuti aturan tertentu.','2, 4, 6, 8 adalah pola genap',12),

('english_smp9_expression','Congratulations!','Ungkapan untuk memberi selamat atas keberhasilan seseorang.','Say it when your friend wins',1),
('english_smp9_expression','I hope you recover soon.','Ungkapan harapan agar seseorang segera sembuh.','An expression of hope',2),
('english_smp9_expression','You should take a rest.','Ungkapan memberikan saran untuk beristirahat.','Should is used for advice',3),
('english_smp9_expression','Procedure text','A text that explains how to make or do something through ordered steps.','Goal, materials, steps',4),
('english_smp9_expression','Report text','A factual text describing a class of things in general.','General classification and description',5),
('english_smp9_expression','Narrative text','A story text intended to entertain, usually with a problem and resolution.','Orientation, complication, resolution',6),
('english_smp9_expression','Passive voice','A structure emphasizing the receiver of an action.','The cake is baked by Ana',7),
('english_smp9_expression','Present perfect','A tense connecting a past action or experience with the present.','She has visited Bali',8),
('english_smp9_expression','Unless','A conjunction meaning if not.','You cannot enter unless you have a ticket',9),
('english_smp9_expression','Environment','The natural world and surroundings in which living things exist.','We must protect the environment',10),
('english_smp9_expression','Preserve','To protect something and keep it in good condition.','Preserve forests and local culture',11),
('english_smp9_expression','In conclusion','A phrase used to introduce the final summary or judgment.','Often used at the end of a text',12),

('fisika_sma10_dasar','Besaran pokok','Besaran yang satuannya ditetapkan terlebih dahulu dan tidak diturunkan dari besaran lain.','Panjang, massa, waktu',1),
('fisika_sma10_dasar','Besaran turunan','Besaran yang dibentuk dari kombinasi besaran pokok.','Kecepatan, luas, gaya',2),
('fisika_sma10_dasar','Skalar','Besaran yang hanya memiliki nilai dan satuan tanpa arah.','Massa dan suhu',3),
('fisika_sma10_dasar','Vektor','Besaran yang memiliki nilai, satuan, dan arah.','Perpindahan dan gaya',4),
('fisika_sma10_dasar','Jarak','Panjang seluruh lintasan yang ditempuh benda.','Besaran skalar',5),
('fisika_sma10_dasar','Perpindahan','Perubahan posisi dari titik awal ke titik akhir beserta arahnya.','Besaran vektor',6),
('fisika_sma10_dasar','Kecepatan','Perpindahan yang terjadi tiap satuan waktu.','v = Δx/Δt',7),
('fisika_sma10_dasar','Percepatan','Perubahan kecepatan tiap satuan waktu.','a = Δv/Δt',8),
('fisika_sma10_dasar','Hukum I Newton','Benda mempertahankan keadaan diam atau bergerak lurus beraturan jika resultan gaya nol.','Hukum kelembaman',9),
('fisika_sma10_dasar','Hukum II Newton','Percepatan berbanding lurus dengan resultan gaya dan berbanding terbalik dengan massa.','ΣF = ma',10),
('fisika_sma10_dasar','Usaha','Energi yang dipindahkan oleh gaya sepanjang perpindahan.','W = F s cos θ',11),
('fisika_sma10_dasar','Momentum','Ukuran kesukaran menghentikan benda bergerak, hasil kali massa dan kecepatan.','p = mv',12),

('biologi_sma11_sel','Membran plasma','Lapisan selektif yang mengatur keluar masuk zat dari dan ke dalam sel.','Tersusun terutama dari fosfolipid dan protein',1),
('biologi_sma11_sel','Nukleus','Organel yang menyimpan materi genetik dan mengendalikan aktivitas sel.','Inti sel',2),
('biologi_sma11_sel','Mitokondria','Organel tempat utama respirasi seluler dan pembentukan ATP.','Pembangkit energi sel',3),
('biologi_sma11_sel','Ribosom','Struktur sel tempat berlangsungnya sintesis protein.','Dapat menempel pada RE kasar',4),
('biologi_sma11_sel','Difusi','Perpindahan partikel dari konsentrasi tinggi ke konsentrasi rendah.','Tidak memerlukan energi langsung',5),
('biologi_sma11_sel','Osmosis','Perpindahan air melalui membran semipermeabel dari larutan lebih encer ke lebih pekat.','Difusi khusus untuk air',6),
('biologi_sma11_sel','Transport aktif','Perpindahan zat melawan gradien konsentrasi dengan menggunakan energi.','Memerlukan ATP',7),
('biologi_sma11_sel','Jaringan epitel','Jaringan yang melapisi permukaan tubuh, organ, dan rongga.','Berfungsi melindungi dan menyerap',8),
('biologi_sma11_sel','Hemoglobin','Protein dalam eritrosit yang mengikat dan mengangkut oksigen.','Mengandung unsur besi',9),
('biologi_sma11_sel','Alveolus','Kantung udara kecil di paru-paru tempat pertukaran oksigen dan karbon dioksida.','Dikelilingi kapiler darah',10),
('biologi_sma11_sel','Nefron','Unit struktural dan fungsional terkecil penyusun ginjal.','Tempat pembentukan urine',11),
('biologi_sma11_sel','Sinapsis','Tempat komunikasi antara neuron dengan neuron atau sel target.','Menggunakan neurotransmiter',12),

('kimia_sma12_reaksi','Oksidasi','Reaksi pelepasan elektron atau kenaikan bilangan oksidasi.','Terjadi di anode',1),
('kimia_sma12_reaksi','Reduksi','Reaksi penerimaan elektron atau penurunan bilangan oksidasi.','Terjadi di katode',2),
('kimia_sma12_reaksi','Oksidator','Zat yang mengoksidasi zat lain dan dirinya sendiri mengalami reduksi.','Penerima elektron',3),
('kimia_sma12_reaksi','Reduktor','Zat yang mereduksi zat lain dan dirinya sendiri mengalami oksidasi.','Pemberi elektron',4),
('kimia_sma12_reaksi','Sel volta','Sel elektrokimia yang mengubah energi kimia reaksi spontan menjadi energi listrik.','Anode negatif dan katode positif',5),
('kimia_sma12_reaksi','Elektrolisis','Penggunaan energi listrik untuk menjalankan reaksi redoks yang tidak spontan.','Berlangsung dalam sel elektrolisis',6),
('kimia_sma12_reaksi','Molalitas','Jumlah mol zat terlarut per kilogram pelarut.','m = mol zat terlarut / kg pelarut',7),
('kimia_sma12_reaksi','Fraksi mol','Perbandingan mol suatu komponen terhadap jumlah mol seluruh komponen.','Jumlah semua fraksi mol = 1',8),
('kimia_sma12_reaksi','Kenaikan titik didih','Sifat koligatif ketika titik didih larutan lebih tinggi daripada pelarut murni.','Bergantung jumlah partikel zat terlarut',9),
('kimia_sma12_reaksi','Polimer','Molekul besar yang tersusun dari pengulangan unit kecil bernama monomer.','Contoh: polietilena',10),
('kimia_sma12_reaksi','Alkana','Hidrokarbon jenuh dengan ikatan tunggal karbon-karbon.','Rumus umum CnH2n+2',11),
('kimia_sma12_reaksi','Gugus fungsi','Atom atau kelompok atom yang menentukan sifat khas senyawa organik.','-OH pada alkohol',12);

INSERT INTO learn_flashcard_cards(deck_id,front,back,hint,sort_order,is_active)
SELECT d.id,t.front,t.back,t.hint,t.sort_order,1 FROM tmp_flashcards_secondary t JOIN learn_flashcard_decks d ON d.code=t.code
ON DUPLICATE KEY UPDATE back=VALUES(back),hint=VALUES(hint),sort_order=VALUES(sort_order),is_active=1;
DROP TEMPORARY TABLE tmp_flashcards_secondary;
