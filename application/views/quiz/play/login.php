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
    <style>
        * { box-sizing:border-box; }
        body { position:relative; overflow-x:hidden; background:radial-gradient(circle at 10% 12%,rgba(96,165,250,.34),transparent 27rem),radial-gradient(circle at 90% 88%,rgba(45,212,191,.2),transparent 24rem),linear-gradient(135deg,#071a3d 0%,#0b4d91 54%,#0f766e 100%); min-height:100vh; font-family:'Plus Jakarta Sans',sans-serif; }
        body::before,body::after{content:'';position:fixed;border:1px solid rgba(255,255,255,.13);border-radius:50%;pointer-events:none}body::before{width:540px;height:540px;top:-330px;right:-130px}body::after{width:360px;height:360px;left:-210px;bottom:-190px}
        .quiz-login-card { position:relative; z-index:1; max-width:510px; margin:auto; padding:20px 0; }
        .quiz-branding { text-align:center; padding: .25rem 0 1.35rem; }.quiz-branding .brand-mark{width:66px;height:66px;display:grid;place-items:center;margin:0 auto .85rem;border:1px solid rgba(255,255,255,.35);border-radius:19px;background:rgba(255,255,255,.12);box-shadow:0 14px 28px rgba(0,0,0,.16)}.quiz-branding img { max-width:45px;max-height:47px; }.quiz-branding h1 { margin:0;color:#fff;font-size:1.65rem;font-weight:900;letter-spacing:-.035em; }.quiz-branding p { margin:.35rem 0 0;color:rgba(236,254,255,.8);font-size:.84rem; }
        .quiz-branding .eyebrow{display:block;margin-bottom:.45rem;color:#a5f3fc;font-size:.68rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
        .quiz-login-card .card { border:1px solid rgba(255,255,255,.65); border-radius:24px; background:rgba(255,255,255,.96); box-shadow:0 26px 64px rgba(1,26,64,.36); overflow:hidden; }.quiz-login-card .card-body{padding:30px!important}.login-icon{display:grid;place-items:center;width:52px;height:52px;margin:0 auto 13px;border-radius:16px;background:#e0f2fe;color:#0369a1;font-size:1.6rem}.quiz-login-card h2{font-weight:900;color:#102a4f;letter-spacing:-.035em}.quiz-login-card .form-label{font-size:.78rem;font-weight:800;letter-spacing:.045em;text-transform:uppercase;color:#475569}.quiz-login-card .form-control{min-height:51px;border:1px solid #cbd5e1;border-radius:13px;font-weight:700;box-shadow:none}.quiz-login-card .form-control:focus{border-color:#0284c7;box-shadow:0 0 0 4px rgba(14,165,233,.12)}.quiz-login-card .btn-primary{min-height:52px;border:0;border-radius:13px;background:linear-gradient(135deg,#0369a1,#0e7490);box-shadow:0 10px 20px rgba(3,105,161,.25)}.quiz-login-card .btn-primary:hover{background:linear-gradient(135deg,#075985,#0f766e)}.session-list{max-height:160px;overflow:auto;padding-right:3px}.session-item{border-color:#cceeff!important;border-radius:12px!important;background:#f0f9ff!important}.help-note{display:flex;align-items:center;justify-content:center;gap:7px;color:rgba(236,254,255,.78);font-size:.77rem}.help-note i{font-size:1rem}@media(max-width:575.98px){body{align-items:flex-start!important;padding:20px 14px!important}.quiz-login-card{padding:6px 0 20px}.quiz-branding{padding-bottom:1rem}.quiz-branding .brand-mark{width:56px;height:56px;border-radius:16px}.quiz-branding img{max-width:38px;max-height:40px}.quiz-branding h1{font-size:1.4rem}.quiz-login-card .card{border-radius:20px}.quiz-login-card .card-body{padding:23px 19px!important}.session-list{max-height:140px}}
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-4">
    <div class="quiz-login-card w-100">
        <div class="quiz-branding">
            <div class="brand-mark"><img src="<?= base_url('img/logo_pemkab.png'); ?>" alt="Logo Pemerintah Kabupaten Rembang" onerror="this.parentElement.style.display='none'"></div>
            <span class="eyebrow">Arena kompetisi</span>
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

                <div class="login-icon"><i class="ti ti-trophy"></i></div>
                <h2 class="card-title text-center mb-1">Login Peserta</h2>
                <p class="text-secondary text-center small mb-4">Masukkan kode dan PIN yang kamu terima dari panitia.</p>

                <?php if(!empty($sessions)): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kompetisi yang Tersedia</label>
                    <div class="session-list">
                    <?php foreach($sessions as $sess): ?>
                    <div class="session-item d-flex align-items-center p-2 border rounded mb-2">
                        <i class="ti ti-tournament me-2 text-blue"></i>
                        <div class="flex-fill">
                            <div class="fw-semibold small"><?= html_escape($sess['title']); ?></div>
                            <div class="text-secondary" style="font-size:.75rem">Kode: <code><?= html_escape($sess['code']); ?></code><?= $sess['start_time'] ? ' · Mulai: '.substr($sess['start_time'],0,16) : ''; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>
                <hr>
                <?php endif; ?>

                <form method="post" action="<?= base_url('quiz/do_login'); ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
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

        <p class="help-note mt-3 mb-0"><i class="ti ti-help-circle"></i>Lupa kode atau PIN? Hubungi panitia kompetisi.</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
