<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = ! empty($session);
$v   = function($key, $default = '') use ($session, $is_edit) { return $is_edit ? ($session[$key] ?? $default) : $default; };
$chk = function($key, $default = false) use ($session, $is_edit) { return $is_edit ? (bool)($session[$key] ?? $default) : $default; };
$dt  = function($key) use ($session, $is_edit) {
    $val = $is_edit ? ($session[$key] ?? '') : '';
    return $val ? str_replace(' ', 'T', substr($val, 0, 16)) : '';
};
?>
<style>
.wizard-steps { display: flex; align-items: center; gap: 0; margin-bottom: 2rem; }
.wizard-step  { flex: 1; text-align: center; position: relative; }
.wizard-step + .wizard-step::before {
    content: ''; position: absolute; left: calc(-50% + 20px); right: calc(50% + 20px);
    top: 20px; height: 2px; background: var(--tblr-border-color); z-index: 0;
}
.wizard-step.done + .wizard-step::before,
.wizard-step.active + .wizard-step::before { background: var(--tblr-primary); }
.step-circle {
    width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--tblr-border-color);
    background: #fff; display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .85rem; position: relative; z-index: 1;
    color: var(--tblr-secondary); transition: all .25s;
}
.wizard-step.active .step-circle  { border-color: var(--tblr-primary); background: var(--tblr-primary); color: #fff; }
.wizard-step.done   .step-circle  { border-color: var(--tblr-success); background: var(--tblr-success); color: #fff; }
.step-label { font-size: .75rem; margin-top: .35rem; color: var(--tblr-secondary); font-weight: 500; }
.wizard-step.active .step-label { color: var(--tblr-primary); font-weight: 700; }
.wizard-step.done   .step-label { color: var(--tblr-success); }
.wizard-pane { display: none; }
.wizard-pane.active { display: block; }

.scoring-card {
    border: 2px solid var(--tblr-border-color); border-radius: .5rem; padding: 1rem;
    cursor: pointer; transition: all .2s; user-select: none;
}
.scoring-card:hover { border-color: var(--tblr-primary); }
.scoring-card.selected { border-color: var(--tblr-primary); background: var(--tblr-primary-lt); }
.scoring-card .score-badge { font-size: .75rem; font-weight: 700; border-radius: .35rem; padding: .15rem .5rem; }

.toggle-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
@media(max-width:576px) { .toggle-grid { grid-template-columns: 1fr; } }
.toggle-item {
    border: 1.5px solid var(--tblr-border-color); border-radius: .5rem; padding: .75rem 1rem;
    display: flex; align-items: center; gap: .75rem; cursor: pointer; transition: all .2s;
}
.toggle-item:hover { border-color: var(--tblr-primary-light); }
.toggle-item input[type=checkbox]:checked ~ .toggle-content .toggle-icon { color: var(--tblr-primary); }
.toggle-item:has(input:checked) { border-color: var(--tblr-primary); background: var(--tblr-primary-lt); }
.toggle-icon { font-size: 1.25rem; min-width: 1.5rem; }
.toggle-content small { font-size: .72rem; color: var(--tblr-secondary); display: block; }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="<?= base_url('quiz-competitions'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">Kompetisi & Lomba</div>
                <h1 class="page-title"><?= html_escape($title); ?></h1>
            </div>
            <?php if ($is_edit): ?>
            <div class="col-auto d-flex gap-2">
                <a href="<?= base_url('quiz-competitions/questions/'.$session['id']); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-list-check me-1"></i>Soal
                </a>
                <a href="<?= base_url('quiz-competitions/participants/'.$session['id']); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-users me-1"></i>Peserta
                </a>
                <a href="<?= base_url('quiz-competitions/results/'.$session['id']); ?>" class="btn btn-sm btn-outline-success">
                    <i class="ti ti-trophy me-1"></i>Hasil
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if ($e = $this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible mb-3">
                <i class="ti ti-alert-circle me-2"></i><?= html_escape($e); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= form_open($action, ['id' => 'wizard-form']); ?>

        <!-- Step Indicator -->
        <div class="wizard-steps mb-4">
            <div class="wizard-step active" id="step-nav-1">
                <div class="step-circle">1</div>
                <div class="step-label">Informasi Dasar</div>
            </div>
            <div class="wizard-step" id="step-nav-2">
                <div class="step-circle">2</div>
                <div class="step-label">Waktu & Penilaian</div>
            </div>
            <div class="wizard-step" id="step-nav-3">
                <div class="step-circle">3</div>
                <div class="step-label">Pengalaman Peserta</div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             STEP 1 — Informasi Dasar
             ═══════════════════════════════════════════════════════════ -->
        <div class="wizard-pane active" id="pane-1">
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-tournament me-2 text-primary"></i>Informasi Kompetisi</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Nama Kompetisi / Lomba</label>
                                <input type="text" name="title" id="f-title" class="form-control form-control-lg"
                                       value="<?= html_escape($v('title')); ?>"
                                       placeholder="e.g. Olimpiade Matematika SD 2026" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3"
                                          placeholder="Jelaskan tujuan dan gambaran umum kompetisi..."><?= html_escape($v('description')); ?></textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Petunjuk untuk Peserta</label>
                                <textarea name="instructions" class="form-control" rows="4"
                                          placeholder="Instruksi yang akan dibaca peserta sebelum memulai. Contoh: Baca soal dengan teliti. Tidak diperbolehkan membuka tab lain..."><?= html_escape($v('instructions')); ?></textarea>
                                <div class="form-hint">Teks ini ditampilkan di halaman mulai ujian peserta.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-filter me-2 text-purple"></i>Kategori Soal</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Mata Pelajaran</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">— Semua Mapel —</option>
                                    <?php foreach ($subjects as $s): ?>
                                        <option value="<?= $s['id']; ?>" <?= (int)$v('subject_id') === (int)$s['id'] ? 'selected' : ''; ?>>
                                            <?= html_escape($s['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jenjang Pendidikan</label>
                                <select name="grade_level_id" class="form-select">
                                    <option value="">— Semua Jenjang —</option>
                                    <?php foreach ($grades as $g): ?>
                                        <option value="<?= $g['id']; ?>" <?= (int)$v('grade_level_id') === (int)$g['id'] ? 'selected' : ''; ?>>
                                            <?= html_escape($g['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tingkat Kesulitan</label>
                                <select name="difficulty_filter" class="form-select">
                                    <option value="mixed"  <?= $v('difficulty_filter','mixed')==='mixed'  ? 'selected':''; ?>>Campuran</option>
                                    <option value="easy"   <?= $v('difficulty_filter')==='easy'   ? 'selected':''; ?>>Mudah</option>
                                    <option value="medium" <?= $v('difficulty_filter')==='medium' ? 'selected':''; ?>>Sedang</option>
                                    <option value="hard"   <?= $v('difficulty_filter')==='hard'   ? 'selected':''; ?>>Sulit</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Jumlah Soal</label>
                                <input type="number" name="question_count" class="form-control"
                                       value="<?= (int)$v('question_count', 20); ?>" min="1" max="200">
                                <div class="form-hint">Jumlah soal akan diambil dari bank atau yang ditambahkan manual.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-primary px-4" onclick="wizardNext(1)">
                    Lanjut: Waktu & Penilaian <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             STEP 2 — Waktu & Penilaian
             ═══════════════════════════════════════════════════════════ -->
        <div class="wizard-pane" id="pane-2">
            <div class="row g-3">
                <div class="col-lg-8">

                    <!-- Sistem Penilaian -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-calculator me-2 text-primary"></i>Sistem Penilaian</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="scoring-card <?= $v('scoring_system','standard')==='standard'?'selected':''; ?>"
                                         onclick="selectScoring('standard', this)">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="score-badge bg-blue-lt text-blue">STANDAR</span>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge bg-green-lt text-green">Benar +1</span>
                                            <span class="badge bg-red-lt text-red">Salah 0</span>
                                            <span class="badge bg-secondary-lt">Kosong 0</span>
                                        </div>
                                        <div class="text-secondary small mt-2">Cocok untuk latihan umum & kompetisi biasa.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="scoring-card <?= $v('scoring_system')==='tka'?'selected':''; ?>"
                                         onclick="selectScoring('tka', this)">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="score-badge bg-orange-lt text-orange">TKA / UTBK</span>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge bg-green-lt text-green">Benar +4</span>
                                            <span class="badge bg-red-lt text-red">Salah −1</span>
                                            <span class="badge bg-secondary-lt">Kosong 0</span>
                                        </div>
                                        <div class="text-secondary small mt-2">Mendorong akurasi — jawaban salah mengurangi poin.</div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="scoring_system" id="scoring-system-val"
                                   value="<?= html_escape($v('scoring_system','standard')); ?>">
                        </div>
                    </div>

                    <!-- Mode Waktu & Akses -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-clock me-2 text-cyan"></i>Mode Waktu & Akses</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mode Waktu</label>
                                    <div class="d-flex flex-column gap-2">
                                        <label class="form-check">
                                            <input class="form-check-input" type="radio" name="time_mode"
                                                   value="per_participant" <?= $v('time_mode','per_participant')==='per_participant'?'checked':''; ?>>
                                            <span class="form-check-label">
                                                <strong>Per Peserta</strong><br>
                                                <small class="text-secondary">Waktu mundur dimulai saat peserta klik "Mulai".</small>
                                            </span>
                                        </label>
                                        <label class="form-check">
                                            <input class="form-check-input" type="radio" name="time_mode"
                                                   value="simultaneous" <?= $v('time_mode')==='simultaneous'?'checked':''; ?>>
                                            <span class="form-check-label">
                                                <strong>Serentak</strong><br>
                                                <small class="text-secondary">Semua peserta mulai & selesai bersamaan sesuai jadwal.</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mode Akses Peserta</label>
                                    <div class="d-flex flex-column gap-2">
                                        <label class="form-check">
                                            <input class="form-check-input" type="radio" name="access_mode"
                                                   value="assigned" <?= $v('access_mode','assigned')==='assigned'?'checked':''; ?>>
                                            <span class="form-check-label">
                                                <strong>Assigned — Kode + PIN</strong><br>
                                                <small class="text-secondary">Peserta wajib punya kode & PIN dari admin.</small>
                                            </span>
                                        </label>
                                        <label class="form-check">
                                            <input class="form-check-input" type="radio" name="access_mode"
                                                   value="public" <?= $v('access_mode')==='public'?'checked':''; ?>>
                                            <span class="form-check-label">
                                                <strong>Public — Daftar Mandiri</strong><br>
                                                <small class="text-secondary">Siapapun bisa daftar dengan mengisi identitas.</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Durasi (menit)</label>
                                    <input type="number" name="time_limit_minutes" class="form-control"
                                           value="<?= (int)$v('time_limit_minutes', 90); ?>" min="5" max="600">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nilai Lulus (%)</label>
                                    <input type="number" name="passing_score" class="form-control"
                                           value="<?= (int)$v('passing_score', 60); ?>" min="0" max="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-calendar me-2 text-green"></i>Jadwal</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Pendaftaran Dibuka</label>
                                    <input type="datetime-local" name="registration_start" class="form-control"
                                           value="<?= $dt('registration_start'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pendaftaran Ditutup</label>
                                    <input type="datetime-local" name="registration_end" class="form-control"
                                           value="<?= $dt('registration_end'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kompetisi Dimulai</label>
                                    <input type="datetime-local" name="start_time" class="form-control"
                                           value="<?= $dt('start_time'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kompetisi Berakhir</label>
                                    <input type="datetime-local" name="end_time" class="form-control"
                                           value="<?= $dt('end_time'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card sticky-top" style="top:1rem;">
                        <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Panduan Sistem Penilaian</h3></div>
                        <div class="card-body small text-secondary">
                            <p><strong class="text-body">Standar</strong> — Dipakai untuk latihan harian atau kompetisi yang ingin mendorong partisipasi. Tidak ada penalti menjawab salah.</p>
                            <p class="mb-0"><strong class="text-body">TKA / UTBK</strong> — Mirip SBMPTN: benar +4, salah −1, kosong 0. Peserta harus cermat memilih jawaban karena tebakan berisiko.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <button type="button" class="btn btn-outline-secondary" onclick="wizardBack(2)">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </button>
                <button type="button" class="btn btn-primary px-4" onclick="wizardNext(2)">
                    Lanjut: Pengalaman Peserta <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             STEP 3 — Pengalaman Peserta
             ═══════════════════════════════════════════════════════════ -->
        <div class="wizard-pane" id="pane-3">
            <div class="row g-3">
                <div class="col-lg-8">

                    <!-- Toggle features -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-adjustments me-2 text-primary"></i>Pengalaman Peserta</h3>
                        </div>
                        <div class="card-body">
                            <div class="toggle-grid">
                                <label class="toggle-item">
                                    <input type="checkbox" name="show_leaderboard" value="1"
                                           <?= $chk('show_leaderboard') ? 'checked' : ''; ?> hidden>
                                    <i class="ti ti-chart-bar toggle-icon text-orange"></i>
                                    <div class="toggle-content">
                                        <span class="fw-semibold">Tampilkan Leaderboard</span>
                                        <small>Peserta bisa melihat ranking saat kompetisi berlangsung.</small>
                                    </div>
                                </label>

                                <label class="toggle-item">
                                    <input type="checkbox" name="shuffle_questions" value="1"
                                           <?= $chk('shuffle_questions', true) ? 'checked' : ''; ?> hidden>
                                    <i class="ti ti-arrows-shuffle toggle-icon text-blue"></i>
                                    <div class="toggle-content">
                                        <span class="fw-semibold">Acak Urutan Soal</span>
                                        <small>Setiap peserta mendapat urutan soal yang berbeda.</small>
                                    </div>
                                </label>

                                <label class="toggle-item">
                                    <input type="checkbox" name="shuffle_options" value="1"
                                           <?= $chk('shuffle_options', true) ? 'checked' : ''; ?> hidden>
                                    <i class="ti ti-list-numbers toggle-icon text-indigo"></i>
                                    <div class="toggle-content">
                                        <span class="fw-semibold">Acak Pilihan Jawaban</span>
                                        <small>Opsi A/B/C/D diacak per peserta.</small>
                                    </div>
                                </label>

                                <label class="toggle-item">
                                    <input type="checkbox" name="allow_review" value="1"
                                           <?= $chk('allow_review') ? 'checked' : ''; ?> hidden>
                                    <i class="ti ti-book-2 toggle-icon text-teal"></i>
                                    <div class="toggle-content">
                                        <span class="fw-semibold">Tampilkan Pembahasan</span>
                                        <small>Peserta bisa baca kunci jawaban setelah selesai.</small>
                                    </div>
                                </label>

                                <label class="toggle-item">
                                    <input type="checkbox" name="show_result_immediately" value="1"
                                           <?= $chk('show_result_immediately', false) ? 'checked' : ''; ?> hidden>
                                    <i class="ti ti-eye toggle-icon text-cyan"></i>
                                    <div class="toggle-content">
                                        <span class="fw-semibold">Hasil Langsung</span>
                                        <small>Tampilkan skor begitu peserta submit.</small>
                                    </div>
                                </label>

                                <label class="toggle-item">
                                    <input type="checkbox" name="announce_results" value="1"
                                           <?= $chk('announce_results', true) ? 'checked' : ''; ?> hidden>
                                    <i class="ti ti-speakerphone toggle-icon text-green"></i>
                                    <div class="toggle-content">
                                        <span class="fw-semibold">Umumkan Hasil</span>
                                        <small>Pengumuman pemenang setelah kompetisi selesai.</small>
                                    </div>
                                </label>

                                <label class="toggle-item">
                                    <input type="checkbox" name="has_certificate" value="1"
                                           <?= $chk('has_certificate') ? 'checked' : ''; ?> hidden>
                                    <i class="ti ti-certificate toggle-icon text-yellow"></i>
                                    <div class="toggle-content">
                                        <span class="fw-semibold">Sertifikat Peserta</span>
                                        <small>Peserta bisa unduh sertifikat keikutsertaan.</small>
                                    </div>
                                </label>

                                <label class="toggle-item">
                                    <input type="checkbox" name="allow_self_reset" value="1"
                                           <?= $chk('allow_self_reset') ? 'checked' : ''; ?> hidden>
                                    <i class="ti ti-refresh toggle-icon text-purple"></i>
                                    <div class="toggle-content">
                                        <span class="fw-semibold">Reset Mandiri</span>
                                        <small>Peserta bisa mengulang attempt tanpa minta admin.</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Anti-fraud -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-shield-check me-2 text-red"></i>Anti-Fraud</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="toggle-grid">
                                        <label class="toggle-item">
                                            <input type="checkbox" name="fraud_detect_tab_switch" value="1"
                                                   <?= $chk('fraud_detect_tab_switch', true) ? 'checked' : ''; ?> hidden>
                                            <i class="ti ti-external-link toggle-icon text-red"></i>
                                            <div class="toggle-content">
                                                <span class="fw-semibold">Deteksi Pindah Tab</span>
                                                <small>Catat setiap kali peserta meninggalkan tab ujian.</small>
                                            </div>
                                        </label>
                                        <label class="toggle-item">
                                            <input type="checkbox" name="fraud_detect_time_anomaly" value="1"
                                                   <?= $chk('fraud_detect_time_anomaly', true) ? 'checked' : ''; ?> hidden>
                                            <i class="ti ti-clock-x toggle-icon text-orange"></i>
                                            <div class="toggle-content">
                                                <span class="fw-semibold">Deteksi Anomali Waktu</span>
                                                <small>Tandai jika jawaban terlalu cepat dari normal.</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Maks. Pindah Tab</label>
                                    <input type="number" name="fraud_max_tab_switches" class="form-control"
                                           value="<?= (int)$v('fraud_max_tab_switches', 3); ?>" min="0">
                                    <div class="form-hint">0 = tidak dibatasi</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tindakan Fraud</label>
                                    <select name="fraud_action" class="form-select">
                                        <option value="warn"        <?= $v('fraud_action','flag')==='warn'        ? 'selected':''; ?>>Peringatkan saja</option>
                                        <option value="flag"        <?= $v('fraud_action','flag')==='flag'        ? 'selected':''; ?>>Tandai (flag)</option>
                                        <option value="disqualify"  <?= $v('fraud_action')==='disqualify'  ? 'selected':''; ?>>Diskualifikasi</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right sidebar: Status & Publikasi -->
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title"><i class="ti ti-settings me-2"></i>Status & Publikasi</h3></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Status Kompetisi</label>
                                <select name="status" class="form-select">
                                    <option value="draft"   <?= $v('status','draft')==='draft'   ? 'selected':''; ?>>Draft</option>
                                    <option value="open"    <?= $v('status')==='open'    ? 'selected':''; ?>>Buka Pendaftaran</option>
                                    <option value="ongoing" <?= $v('status')==='ongoing' ? 'selected':''; ?>>Berlangsung</option>
                                    <option value="closed"  <?= $v('status')==='closed'  ? 'selected':''; ?>>Selesai</option>
                                </select>
                            </div>
                            <hr>
                            <label class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_published" value="1"
                                       <?= $chk('is_published') ? 'checked' : ''; ?>>
                                <span class="form-check-label">
                                    <strong>Publikasikan</strong><br>
                                    <small class="text-secondary">Tampilkan di halaman publik perpustakaan.</small>
                                </span>
                            </label>
                            <label class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_paused" value="1"
                                       <?= $chk('is_paused') ? 'checked' : ''; ?>>
                                <span class="form-check-label">
                                    <strong>Jeda Kompetisi</strong><br>
                                    <small class="text-secondary">Peserta tidak bisa mulai ujian selama dijeda.</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <?php if ($is_edit): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="text-secondary small mb-2">Kelola kompetisi ini:</div>
                            <div class="list-group list-group-flush">
                                <a href="<?= base_url('quiz-competitions/questions/'.$session['id']); ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                    <i class="ti ti-list-check text-blue"></i>Soal Kompetisi
                                </a>
                                <a href="<?= base_url('quiz-competitions/participants/'.$session['id']); ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                    <i class="ti ti-users text-green"></i>Daftar Peserta
                                </a>
                                <a href="<?= base_url('quiz-competitions/results/'.$session['id']); ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                    <i class="ti ti-trophy text-yellow"></i>Hasil & Peringkat
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <button type="button" class="btn btn-outline-secondary" onclick="wizardBack(3)">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </button>
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="ti ti-device-floppy me-2"></i><?= $is_edit ? 'Simpan Perubahan' : 'Buat Kompetisi'; ?>
                </button>
            </div>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<script>
(function () {
    'use strict';

    let currentStep = 1;
    const TOTAL = 3;

    function showStep(n) {
        for (let i = 1; i <= TOTAL; i++) {
            const pane = document.getElementById('pane-' + i);
            const nav  = document.getElementById('step-nav-' + i);
            if (!pane || !nav) continue;
            pane.classList.toggle('active', i === n);
            nav.classList.remove('active', 'done');
            if (i < n)  nav.classList.add('done');
            if (i === n) nav.classList.add('active');
            // Replace number with checkmark for done steps
            const circle = nav.querySelector('.step-circle');
            if (i < n) circle.innerHTML = '<i class="ti ti-check"></i>';
            else        circle.textContent = i;
        }
        currentStep = n;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    window.wizardNext = function (from) {
        if (from === 1) {
            const title = document.getElementById('f-title');
            if (!title.value.trim()) { title.focus(); title.classList.add('is-invalid'); return; }
            title.classList.remove('is-invalid');
        }
        if (from < TOTAL) showStep(from + 1);
    };

    window.wizardBack = function (from) {
        if (from > 1) showStep(from - 1);
    };

    // Scoring card selection
    window.selectScoring = function (val, el) {
        document.querySelectorAll('.scoring-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('scoring-system-val').value = val;
    };

    // Toggle items: checkbox is hidden, click toggles checked + visual state
    document.querySelectorAll('.toggle-item').forEach(function (item) {
        item.addEventListener('click', function () {
            const cb = item.querySelector('input[type=checkbox]');
            cb.checked = !cb.checked;
        });
    });

    // If editing and step validation is already satisfied, allow direct step nav
    document.querySelectorAll('.wizard-step').forEach(function (nav, idx) {
        nav.addEventListener('click', function () {
            const targetStep = idx + 1;
            if (targetStep <= currentStep || <?= $is_edit ? 'true' : 'false'; ?>) {
                showStep(targetStep);
            }
        });
        nav.style.cursor = 'pointer';
    });

    // Init
    showStep(1);
})();
</script>
