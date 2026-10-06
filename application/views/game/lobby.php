<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?> — Pustaka Digital Rembang</title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260814a'); ?>">
</head>
<body class="learning-arena-page">
    <header class="learning-arena-topbar">
        <a href="<?= base_url($user ? 'user/dashboard' : ''); ?>" class="learning-arena-brand">
            <span class="learning-arena-brand-icon"><i class="ti ti-books"></i></span>
            <span>Pustaka Rembang</span>
        </a>
        <nav class="learning-arena-nav" aria-label="Navigasi belajar">
            <?php if ($user): ?>
            <a href="<?= base_url('belajar/raport'); ?>" class="learning-arena-nav-link">
                <i class="ti ti-chart-bar"></i><span>Raport</span>
            </a>
            <a href="<?= base_url('belajar/notifikasi'); ?>" class="learning-arena-nav-link position-relative">
                <i class="ti ti-bell"></i><span>Notifikasi</span>
                <?php if (! empty($unread_notif)): ?>
                <b class="learning-arena-notification"><?= (int) $unread_notif > 99 ? '99+' : (int) $unread_notif; ?></b>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            <a href="<?= base_url($user ? 'user/dashboard' : ''); ?>" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-arrow-left me-1"></i><span class="d-none d-sm-inline">Kembali</span>
            </a>
        </nav>
    </header>

    <main class="learning-arena-shell">
        <section class="learning-arena-hero">
            <div class="learning-arena-hero-copy">
                <span class="learning-arena-kicker"><i class="ti ti-sparkles"></i> Arena pembelajaran Pustaka Rembang</span>
                <h1>Belajar dengan arah.<br>Bertumbuh dengan seru.</h1>
                <p>Pilih latihan sesuai tujuanmu, lanjutkan progres yang sudah dimulai, lalu tukarkan poin menjadi manfaat baca digital.</p>
                <?php if (! $user): ?>
                <a href="<?= base_url('login'); ?>" class="btn btn-light learning-arena-login">
                    <i class="ti ti-login me-1"></i>Login untuk mengumpulkan poin
                </a>
                <?php else: ?>
                <a href="#aktivitas-belajar" class="btn btn-light learning-arena-login">
                    Mulai belajar <i class="ti ti-arrow-down ms-1"></i>
                </a>
                <?php endif; ?>
            </div>
            <div class="learning-arena-hero-visual">
                <?php if ($user && $learning_summary): ?>
                <aside class="learning-arena-passport" aria-label="Ringkasan progres belajar">
                    <div class="learning-arena-passport-head"><span class="learning-arena-avatar"><i class="ti ti-user-star"></i></span><div><small>PROGRES BELAJARMU</small><strong><?= html_escape($user['full_name'] ?? ($user['username'] ?? 'Pemelajar')); ?></strong></div></div>
                    <div class="learning-arena-passport-stats"><span><b><?= number_format($learning_summary['points'], 0, ',', '.'); ?></b><small>Poin</small></span><span><b><?= $learning_summary['quiz_attempts']; ?></b><small>Latihan</small></span><span><b><?= $learning_summary['badges']; ?></b><small>Lencana</small></span></div>
                    <a href="<?= base_url('belajar/raport'); ?>">Buka raport belajar <i class="ti ti-arrow-up-right"></i></a>
                </aside>
                <?php else: ?>
                <div class="learning-arena-discovery" aria-label="Pilihan aktivitas belajar"><span class="learning-orbit learning-orbit-one"><i class="ti ti-bulb"></i></span><span class="learning-orbit learning-orbit-two"><i class="ti ti-trophy"></i></span><span class="learning-orbit learning-orbit-three"><i class="ti ti-book-2"></i></span><div class="learning-arena-mascot"><i class="ti ti-mood-smile-beam"></i></div><small>Latihan · Cerita · Game · Kompetisi</small></div>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($user): ?>
        <section class="learning-arena-welcome">
            <div>
                <span class="learning-arena-eyebrow">BELAJAR HARI INI</span>
                <h2>Halo, <?= html_escape($user['full_name'] ?? ($user['username'] ?? 'Pemain')); ?>!</h2>
                <p>Setiap latihan kecil adalah kemajuan. Pilih satu aktivitas dan mulai dari sana.</p>
            </div>
            <div class="learning-arena-quick-links">
                <a href="<?= base_url('belajar/raport'); ?>"><i class="ti ti-file-analytics"></i> Lihat perkembangan</a>
                <a href="<?= base_url('belajar/tukar'); ?>"><i class="ti ti-gift"></i> Tukar poin</a>
            </div>
        </section>
        <section class="learning-arena-progress-strip" aria-label="Ringkasan aktivitas belajar">
            <div><i class="ti ti-cards"></i><span><b><?= number_format($learning_summary['flashcards_known'], 0, ',', '.'); ?></b><small>Kartu dikuasai</small></span></div>
            <div><i class="ti ti-device-gamepad-2"></i><span><b><?= number_format($learning_summary['games_played'], 0, ',', '.'); ?></b><small>Game selesai</small></span></div>
            <div><i class="ti ti-target-arrow"></i><span><b><?= number_format($learning_summary['quiz_attempts'], 0, ',', '.'); ?></b><small>Sesi latihan</small></span></div>
            <a href="<?= base_url('belajar/raport'); ?>">Detail progres <i class="ti ti-chevron-right"></i></a>
        </section>
        <?php endif; ?>

        <section id="aktivitas-belajar" class="learning-arena-section">
            <div class="learning-arena-section-heading">
                <div>
                    <span class="learning-arena-eyebrow">PILIH CARA BELAJAR</span>
                    <h2>Mulai dari tujuanmu</h2>
                </div>
                <p>Uji pemahaman sendiri atau ikuti tantangan resmi.</p>
            </div>
            <div class="learning-arena-feature-grid">
                <a href="<?= base_url('belajar/latihan'); ?>" class="learning-arena-feature-card is-blue">
                    <span class="learning-arena-feature-icon"><i class="ti ti-clipboard-list"></i></span>
                    <span class="learning-arena-feature-arrow"><i class="ti ti-arrow-up-right"></i></span>
                    <h3>Latihan Soal</h3>
                    <p>Filter berdasarkan jenjang dan mapel, lalu lihat hasil serta pembahasannya langsung.</p>
                    <small><i class="ti ti-coin"></i> +10 poin per sesi</small>
                </a>
                <a href="<?= base_url('quiz/login'); ?>" class="learning-arena-feature-card is-purple">
                    <span class="learning-arena-feature-icon"><i class="ti ti-trophy"></i></span>
                    <span class="learning-arena-feature-arrow"><i class="ti ti-arrow-up-right"></i></span>
                    <h3>Kompetisi</h3>
                    <p>Ikuti tantangan resmi dan ukur kemampuanmu bersama peserta lain.</p>
                    <small><i class="ti ti-award"></i> Hadiah poin spesial</small>
                </a>
            </div>
        </section>

        <a href="<?= base_url('belajar/tukar'); ?>" class="learning-arena-reward">
            <span class="learning-arena-reward-icon"><i class="ti ti-gift"></i></span>
            <span class="flex-fill">
                <b>Poinmu bisa jadi Token Baca</b>
                <small>Tukarkan poin untuk mendapatkan manfaat di koleksi digital.</small>
            </span>
            <i class="ti ti-chevron-right"></i>
        </a>

        <section class="learning-arena-section">
            <div class="learning-arena-section-heading">
                <div>
                    <span class="learning-arena-eyebrow">BELAJAR MANDIRI</span>
                    <h2>Pilih aktivitas hari ini</h2>
                </div>
                <p>Aktivitas singkat untuk membangun kebiasaan belajar setiap hari.</p>
            </div>
            <div class="learning-arena-activity-grid">
                <a href="<?= base_url('belajar/bahasa-inggris'); ?>" class="learning-arena-activity-card">
                    <span class="learning-arena-activity-icon is-teal"><i class="ti ti-language"></i></span>
                    <span><h3>Kelas Bahasa Inggris</h3><p>Belajar terarah dari Beginner sampai Expert dengan audio, hint, situasi nyata, dan tes tiap level.</p></span>
                    <b>COURSE <i class="ti ti-arrow-up-right"></i></b>
                </a>
                <a href="<?= base_url('belajar/english-quest'); ?>" class="learning-arena-activity-card">
                    <span class="learning-arena-activity-icon is-violet"><i class="ti ti-sword"></i></span>
                    <span><h3>English Quest</h3><p>Jelajahi dunia RPG, berbicara dengan karakter, dan kuasai bahasa Inggris dari cerita.</p></span>
                    <b>RPG <i class="ti ti-sparkles"></i></b>
                </a>
                <a href="<?= base_url('belajar/flashcard'); ?>" class="learning-arena-activity-card">
                    <span class="learning-arena-activity-icon is-violet"><i class="ti ti-cards"></i></span>
                    <span><h3>Flashcard</h3><p>Balik kartu, ingat istilah, dan tandai yang sudah dikuasai.</p></span>
                    <b>+5 <i class="ti ti-coin"></i></b>
                </a>
                <a href="<?= base_url('belajar/cerita'); ?>" class="learning-arena-activity-card">
                    <span class="learning-arena-activity-icon is-teal"><i class="ti ti-book"></i></span>
                    <span><h3>Story Quiz</h3><p>Baca cerita pendek lalu uji pemahaman bacaanmu.</p></span>
                    <b>+10 <i class="ti ti-coin"></i></b>
                </a>
                <a href="<?= base_url('belajar/battle'); ?>" class="learning-arena-activity-card">
                    <span class="learning-arena-activity-icon is-rose"><i class="ti ti-swords"></i></span>
                    <span><h3>Mode Battle</h3><p>Tantang teman dan adu cepat menjawab pertanyaan.</p></span>
                    <b>+20 <i class="ti ti-trophy"></i></b>
                </a>
                <a href="<?= base_url('belajar/matematika'); ?>" class="learning-arena-activity-card">
                    <span class="learning-arena-activity-icon is-teal"><i class="ti ti-map-2"></i></span>
                    <span><h3>Math Expedition</h3><p>Naik level matematika bertahap dengan hint, pembahasan, dan tips tiap soal.</p></span>
                    <b>LEVEL <i class="ti ti-arrow-up-right"></i></b>
                </a>
            </div>
        </section>

        <?php if (! empty($game_types)): ?>
        <section class="learning-arena-section learning-arena-mini-games">
            <div class="learning-arena-section-heading">
                <div>
                    <span class="learning-arena-eyebrow">SELANGI BELAJAR</span>
                    <h2>Mini game</h2>
                </div>
                <p>Belajar dengan cara yang lebih santai dan menyenangkan.</p>
            </div>
            <div class="learning-arena-mini-grid">
                <?php foreach ($game_types as $gt): ?>
                <a href="<?= base_url('belajar/pilih/' . $gt['code']); ?>" class="learning-arena-mini-card" style="--game-color:<?= html_escape($gt['color']); ?>">
                    <span class="learning-arena-mini-icon"><i class="<?= html_escape($gt['icon']); ?>"></i></span>
                    <h3><?= html_escape($gt['name']); ?></h3>
                    <p><?= html_escape(mb_substr($gt['description'], 0, 90)); ?></p>
                    <small><i class="ti ti-coin"></i> +5 poin per sesi</small>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
