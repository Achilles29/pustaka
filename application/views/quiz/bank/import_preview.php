<?php defined('BASEPATH') OR exit('No direct script access allowed');
$letters = ['A', 'B', 'C', 'D', 'E'];
$diff_label = ['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit'];
$ready_count = (int) ($summary['ready'] ?? $summary['valid'] ?? 0);
$warning_count = (int) ($summary['warnings'] ?? 0);
$valid_count = (int) ($summary['valid'] ?? 0);
$can_curriculum_override = ! empty($can_curriculum_override);
$session_scope = $session_scope ?? null;
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= $session_scope ? base_url('quiz-bank/import?session_id=' . (int) $session_scope['id']) : base_url('quiz-bank/import'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Unggah Ulang</a></div>
            <div class="col">
                <div class="page-pretitle"><?= $session_scope ? 'Sesi Latihan · Analisa Soal Khusus' : 'Bank Soal · Analisa Import'; ?></div>
                <h1 class="page-title">Review Soal — <?= html_escape($filename); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Ringkasan analisa -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md"><div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="bg-blue-lt avatar"><i class="ti ti-list-numbers"></i></span>
                <div><div class="h2 mb-0"><?= (int)$summary['total']; ?></div><div class="text-secondary small">Total Terbaca</div></div>
            </div></div></div>
            <div class="col-6 col-md"><div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="bg-green-lt avatar"><i class="ti ti-circle-check"></i></span>
                <div><div class="h2 mb-0 text-success" id="import-ready-count"><?= $ready_count; ?></div><div class="text-secondary small">Siap Import</div></div>
            </div></div></div>
            <div class="col-6 col-md"><div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="bg-yellow-lt avatar"><i class="ti ti-alert-circle"></i></span>
                <div><div class="h2 mb-0 text-warning"><?= $warning_count; ?></div><div class="text-secondary small">Peringatan Matriks</div></div>
            </div></div></div>
            <div class="col-6 col-md"><div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="bg-red-lt avatar"><i class="ti ti-alert-triangle"></i></span>
                <div><div class="h2 mb-0 text-danger"><?= (int)$summary['invalid']; ?></div><div class="text-secondary small">Bermasalah</div></div>
            </div></div></div>
            <div class="col-6 col-md"><div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="bg-azure-lt avatar text-uppercase fw-bold" style="font-size:.8rem"><?= html_escape($format); ?></span>
                <div><div class="small text-secondary">Format file</div></div>
            </div></div></div>
        </div>

        <?php if ((int)$summary['total'] === 0): ?>
        <div class="alert alert-warning"><i class="ti ti-alert-triangle me-1"></i>Tidak ada soal terbaca dari file. Periksa format file lalu unggah ulang.</div>
        <?php else: ?>

        <div class="alert <?= (int)$summary['invalid'] > 0 ? 'alert-warning' : 'alert-success'; ?> d-flex align-items-center">
            <i class="ti ti-eye-check fs-2 me-2"></i>
            <div>
                Review tiap soal di bawah. Secara default, hanya <strong id="import-ready-text"><?= $ready_count; ?> soal valid tanpa peringatan</strong> yang akan dimasukkan.
                <?php if ((int)$summary['invalid'] > 0): ?>Soal <span class="text-danger fw-bold">bermasalah</span> dilewati — perbaiki di file lalu unggah ulang bila perlu.<?php endif; ?>
            </div>
        </div>

        <?php if ($warning_count > 0): ?>
        <div class="alert alert-warning">
            <div class="d-flex align-items-start gap-2">
                <i class="ti ti-shield-exclamation fs-2"></i>
                <div>
                    <div class="fw-semibold">Override matriks Kurikulum Merdeka</div>
                    <div class="small">Ada <?= $warning_count; ?> soal yang strukturnya valid, tetapi kombinasi mapel–jenjangnya belum tercakup pada matriks. Gunakan hanya untuk soal pengayaan atau materi level lebih lanjut.</div>
                    <?php if ($can_curriculum_override): ?>
                        <label class="form-check mt-2 mb-0">
                            <input class="form-check-input" type="checkbox" name="override_curriculum_scope" value="1" id="override-curriculum-scope" form="commit-import-form">
                            <span class="form-check-label">Saya sudah meninjau soal dan mengizinkan import <?= $warning_count; ?> soal di luar matriks.</span>
                        </label>
                    <?php else: ?>
                        <div class="small mt-2">Override hanya tersedia untuk admin dengan izin <strong>ubah Bank Soal</strong>.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-2 mb-4">
            <?php foreach ($questions as $qi => $q): ?>
            <div class="col-12">
                <div class="card <?= $q['status'] === 'ok' ? (! empty($q['warnings']) ? 'border-warning' : '') : 'border-danger'; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <span class="badge bg-secondary">#<?= $qi + 1; ?></span>
                                <?php if ($q['status'] === 'ok'): ?>
                                <span class="badge bg-success-lt text-success"><i class="ti ti-check me-1"></i>Valid</span>
                                <?php else: ?>
                                <span class="badge bg-danger-lt text-danger"><i class="ti ti-alert-triangle me-1"></i>Bermasalah</span>
                                <?php endif; ?>
                                <?php if (! empty($q['warnings'])): ?>
                                <span class="badge bg-warning-lt text-warning"><i class="ti ti-alert-circle me-1"></i>Perlu Override</span>
                                <?php endif; ?>
                                <span class="badge bg-blue-lt text-blue"><?= html_escape($q['subject_label']); ?></span>
                                <span class="badge bg-cyan-lt text-cyan"><?= html_escape($q['grade_label']); ?></span>
                                <span class="badge bg-orange-lt text-orange"><?= $diff_label[$q['difficulty']] ?? $q['difficulty']; ?></span>
                                <span class="badge bg-secondary-lt"><?= $q['type'] === 'essay' ? 'Essay' : 'Pilihan Ganda'; ?></span>
                            </div>
                        </div>

                        <div class="fw-medium mb-2"><?= html_escape($q['question_text']); ?></div>

                        <?php if ($q['type'] === 'multiple_choice'): ?>
                        <div class="row g-1">
                            <?php foreach ($q['options'] as $oi => $opt): $text = is_array($opt) ? ($opt['text'] ?? '') : $opt; ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 px-2 py-1 rounded <?= (int)$q['correct_index'] === (int)$oi ? 'bg-success-lt' : ''; ?>">
                                    <span class="badge <?= (int)$q['correct_index'] === (int)$oi ? 'bg-success text-white' : 'bg-secondary-lt'; ?>"><?= $letters[$oi] ?? $oi; ?></span>
                                    <span class="<?= (int)$q['correct_index'] === (int)$oi ? 'fw-semibold text-success' : ''; ?>"><?= html_escape($text); ?></span>
                                    <?php if ((int)$q['correct_index'] === (int)$oi): ?><i class="ti ti-circle-check text-success ms-auto"></i><?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="small text-secondary mt-1">Kunci jawaban: <strong class="text-success"><?= html_escape($q['correct_letter'] ?: '—'); ?></strong></div>
                        <?php endif; ?>

                        <?php if (! empty($q['explanation'])): ?>
                        <div class="text-secondary small mt-2"><i class="ti ti-bulb me-1"></i><?= html_escape($q['explanation']); ?></div>
                        <?php endif; ?>

                        <?php if (! empty($q['issues'])): ?>
                        <div class="mt-2">
                            <?php foreach ($q['issues'] as $iss): ?>
                            <div class="text-danger small"><i class="ti ti-alert-circle me-1"></i><?= html_escape($iss); ?></div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (! empty($q['warnings'])): ?>
                        <div class="mt-2">
                            <?php foreach ($q['warnings'] as $warning): ?>
                            <div class="text-warning small"><i class="ti ti-alert-circle me-1"></i><?= html_escape($warning); ?></div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Bar aksi -->
        <div class="card sticky-bottom">
            <div class="card-body d-flex align-items-center flex-wrap gap-2">
                <div class="text-secondary">
                    <strong class="text-success" id="import-ready-count-footer"><?= $ready_count; ?></strong> soal siap diimport
                    <?php if ((int)$summary['invalid'] > 0): ?>· <strong class="text-danger"><?= (int)$summary['invalid']; ?></strong> dilewati<?php endif; ?>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <a href="<?= base_url('quiz-bank/import'); ?>" class="btn btn-outline-secondary">Batal</a>
                    <?php if ($valid_count > 0): ?>
                    <?= form_open('quiz-bank/commit-import', ['class' => 'd-inline', 'id' => 'commit-import-form']); ?>
                    <button type="submit" class="btn btn-primary" id="commit-import-button" <?= $ready_count === 0 ? 'disabled' : ''; ?>
                            data-confirm="Import <?= $ready_count; ?> soal valid ke bank soal?"
                            data-confirm-title="Konfirmasi Import" data-confirm-variant="primary" data-confirm-ok="Ya, Import">
                        <i class="ti ti-database-import me-1"></i><span id="commit-import-label">Konfirmasi &amp; Import <?= $ready_count; ?> Soal</span>
                    </button>
                    <?= form_close(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>
</div>
<?php if ($warning_count > 0 && $can_curriculum_override): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('override-curriculum-scope');
    var button = document.getElementById('commit-import-button');
    var label = document.getElementById('commit-import-label');
    var count = document.getElementById('import-ready-count');
    var footerCount = document.getElementById('import-ready-count-footer');
    var text = document.getElementById('import-ready-text');
    var normal = <?= $ready_count; ?>;
    var overridden = <?= $valid_count; ?>;
    function refresh() {
        var total = toggle.checked ? overridden : normal;
        button.disabled = total === 0;
        label.textContent = 'Konfirmasi & Import ' + total + ' Soal';
        button.dataset.confirm = 'Import ' + total + ' soal ' + (toggle.checked ? 'termasuk override matriks kurikulum' : 'valid') + ' ke bank soal?';
        count.textContent = total;
        footerCount.textContent = total;
        text.textContent = total + ' soal ' + (toggle.checked ? 'valid (termasuk override matriks)' : 'valid tanpa peringatan');
    }
    toggle.addEventListener('change', refresh);
});
</script>
<?php endif; ?>
