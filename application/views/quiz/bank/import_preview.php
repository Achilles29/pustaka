<?php defined('BASEPATH') OR exit('No direct script access allowed');
$letters = ['A', 'B', 'C', 'D', 'E'];
$diff_label = ['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit'];
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= base_url('quiz-bank/import'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Unggah Ulang</a></div>
            <div class="col">
                <div class="page-pretitle">Bank Soal · Analisa Import</div>
                <h1 class="page-title">Review Soal — <?= html_escape($filename); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- Ringkasan analisa -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="bg-blue-lt avatar"><i class="ti ti-list-numbers"></i></span>
                <div><div class="h2 mb-0"><?= (int)$summary['total']; ?></div><div class="text-secondary small">Total Terbaca</div></div>
            </div></div></div>
            <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="bg-green-lt avatar"><i class="ti ti-circle-check"></i></span>
                <div><div class="h2 mb-0 text-success"><?= (int)$summary['valid']; ?></div><div class="text-secondary small">Siap Import</div></div>
            </div></div></div>
            <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
                <span class="bg-red-lt avatar"><i class="ti ti-alert-triangle"></i></span>
                <div><div class="h2 mb-0 text-danger"><?= (int)$summary['invalid']; ?></div><div class="text-secondary small">Bermasalah</div></div>
            </div></div></div>
            <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body d-flex align-items-center gap-2">
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
                Review tiap soal di bawah. Saat kamu klik <strong>Konfirmasi &amp; Import</strong>, hanya <strong><?= (int)$summary['valid']; ?> soal valid</strong> yang akan dimasukkan.
                <?php if ((int)$summary['invalid'] > 0): ?>Soal <span class="text-danger fw-bold">bermasalah</span> dilewati — perbaiki di file lalu unggah ulang bila perlu.<?php endif; ?>
            </div>
        </div>

        <div class="row g-2 mb-4">
            <?php foreach ($questions as $qi => $q): ?>
            <div class="col-12">
                <div class="card <?= $q['status'] === 'ok' ? '' : 'border-danger'; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <span class="badge bg-secondary">#<?= $qi + 1; ?></span>
                                <?php if ($q['status'] === 'ok'): ?>
                                <span class="badge bg-success-lt text-success"><i class="ti ti-check me-1"></i>Valid</span>
                                <?php else: ?>
                                <span class="badge bg-danger-lt text-danger"><i class="ti ti-alert-triangle me-1"></i>Bermasalah</span>
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
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Bar aksi -->
        <div class="card sticky-bottom">
            <div class="card-body d-flex align-items-center flex-wrap gap-2">
                <div class="text-secondary">
                    <strong class="text-success"><?= (int)$summary['valid']; ?></strong> soal siap diimport
                    <?php if ((int)$summary['invalid'] > 0): ?>· <strong class="text-danger"><?= (int)$summary['invalid']; ?></strong> dilewati<?php endif; ?>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <a href="<?= base_url('quiz-bank/import'); ?>" class="btn btn-outline-secondary">Batal</a>
                    <?php if ((int)$summary['valid'] > 0): ?>
                    <?= form_open('quiz-bank/commit-import', ['class' => 'd-inline']); ?>
                    <button type="submit" class="btn btn-primary"
                            data-confirm="Import <?= (int)$summary['valid']; ?> soal valid ke bank soal?"
                            data-confirm-title="Konfirmasi Import" data-confirm-variant="primary" data-confirm-ok="Ya, Import">
                        <i class="ti ti-database-import me-1"></i>Konfirmasi &amp; Import <?= (int)$summary['valid']; ?> Soal
                    </button>
                    <?= form_close(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>
</div>
