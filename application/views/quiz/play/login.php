<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?> — Pustaka Digital Rembang</title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a56a7 0%, #0d3d7a 100%); min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; }
        .quiz-login-card { max-width: 480px; margin: auto; }
        .quiz-branding { text-align:center; padding: 2rem 0 1rem; }
        .quiz-branding img { height: 60px; margin-bottom: .75rem; }
        .quiz-branding h1 { color: #fff; font-size: 1.5rem; font-weight: 700; }
        .quiz-branding p { color: rgba(255,255,255,.7); font-size: .9rem; }
        .card { border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-4">
    <div class="quiz-login-card w-100">
        <div class="quiz-branding">
            <img src="<?= base_url('img/logo_pemkab.png'); ?>" alt="Logo" onerror="this.style.display='none'">
            <h1>Kompetisi Belajar</h1>
            <p>Pustaka Digital Kabupaten Rembang</p>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <?= html_escape($error); ?>
                </div>
                <?php endif; ?>

                <h2 class="card-title text-center mb-1">Login Peserta</h2>
                <p class="text-secondary text-center small mb-4">Masukkan kode dan PIN yang kamu terima dari panitia.</p>

                <?php if(!empty($sessions)): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kompetisi yang Tersedia</label>
                    <?php foreach($sessions as $sess): ?>
                    <div class="d-flex align-items-center p-2 border rounded mb-1 bg-blue-lt">
                        <i class="ti ti-tournament me-2 text-blue"></i>
                        <div class="flex-fill">
                            <div class="fw-semibold small"><?= html_escape($sess['title']); ?></div>
                            <div class="text-secondary" style="font-size:.75rem">Kode: <code><?= html_escape($sess['code']); ?></code><?= $sess['start_time'] ? ' · Mulai: '.substr($sess['start_time'],0,16) : ''; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <?php endif; ?>

                <form method="post" action="<?= base_url('quiz/do_login'); ?>">
                    <?= csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold required">Kode Registrasi</label>
                        <input type="text" name="registration_code" class="form-control form-control-lg text-uppercase" value="<?= html_escape($presel_code??''); ?>" placeholder="PST1234567" autocomplete="off" autofocus style="letter-spacing:.1em">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold required">PIN</label>
                        <input type="password" name="registration_pin" class="form-control form-control-lg text-center" placeholder="••••••" maxlength="10" autocomplete="off" style="letter-spacing:.3em">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold">
                        <i class="ti ti-login me-1"></i>Mulai Kompetisi
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center mt-3 text-white-50 small">Lupa kode atau PIN? Hubungi panitia kompetisi.</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
