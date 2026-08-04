<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= base_url('quiz-competitions'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a></div>
            <div class="col"><div class="page-pretitle">Kompetisi</div><h1 class="page-title"><?= html_escape($session['title']); ?> — Hasil</h1></div>
            <div class="col-auto ms-auto"><button class="btn btn-outline-secondary" onclick="window.print()"><i class="ti ti-printer me-1"></i>Cetak</button></div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if(!empty($pending)): ?>
        <div class="alert alert-warning"><i class="ti ti-alert-triangle me-1"></i><?= count($pending); ?> essay belum dinilai — skor bisa berubah setelah penilaian selesai.</div>
        <?php endif; ?>

        <div class="card admin-card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-trophy me-1 text-warning"></i>Papan Peringkat</h3>
                <div class="card-options text-secondary small"><?= count($results); ?> peserta selesai</div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Peringkat</th><th>Nama</th><th>Sekolah/Kelas</th><th>Skor</th><th>%</th><th>Lulus</th><th>Waktu</th><th>Flagged</th><?php if(!empty($pending)): ?><th class="w-1"></th><?php endif; ?></tr></thead>
                    <tbody>
                    <?php if(empty($results)): ?><tr><td colspan="9" class="text-center text-secondary py-5"><i class="ti ti-inbox d-block fs-1 mb-2"></i>Belum ada hasil.</td></tr><?php endif; ?>
                    <?php foreach($results as $rank=>$r): ?>
                    <?php $medal = ['🥇','🥈','🥉'][$rank]??($rank+1); ?>
                    <tr class="<?= $r['is_passed']?'':'table-warning-lt'; ?>">
                        <td class="fw-bold text-center fs-4"><?= $medal; ?></td>
                        <td>
                            <div class="fw-semibold"><?= html_escape($r['full_name']); ?></div>
                            <code class="text-secondary small"><?= html_escape($r['registration_code']); ?></code>
                        </td>
                        <td>
                            <div><?= html_escape($r['school_name']??'—'); ?></div>
                            <div class="text-secondary small"><?= html_escape($r['grade_class']??''); ?></div>
                        </td>
                        <td><?= number_format((float)$r['total_score'],1); ?> / <?= number_format((float)$r['max_possible_score'],1); ?></td>
                        <td class="fw-bold"><?= number_format((float)$r['percentage'],1); ?>%</td>
                        <td>
                            <?php if($r['is_passed']===null): ?><span class="badge bg-warning">Pending</span>
                            <?php elseif($r['is_passed']): ?><span class="badge bg-success">Lulus</span>
                            <?php else: ?><span class="badge bg-danger">Tidak</span><?php endif; ?>
                        </td>
                        <td><?= $r['time_spent_seconds'] ? gmdate('i\ms\s',(int)$r['time_spent_seconds']) : '—'; ?></td>
                        <td><?= $r['is_flagged'] ? '<span class="badge bg-danger"><i class="ti ti-alert-triangle"></i> Flagged</span>' : '<span class="badge bg-success-lt">Bersih</span>'; ?></td>
                        <?php if(!empty($pending)): ?>
                        <td>
                            <?php if(!$r['essay_graded'] && $r['status']==='submitted'): ?>
                            <a href="<?= base_url('quiz-competitions/grade/'.$r['id']); ?>" class="btn btn-sm btn-primary"><i class="ti ti-pencil me-1"></i>Nilai</a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
