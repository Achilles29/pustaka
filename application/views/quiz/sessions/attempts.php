<?php defined('BASEPATH') OR exit('No direct script access allowed');
$status_labels = ['submitted'=>'Selesai','in_progress'=>'Berlangsung','timed_out'=>'Timeout','disqualified'=>'Diskualifikasi','abandoned'=>'Dibatalkan'];
$status_colors = ['submitted'=>'bg-success','in_progress'=>'bg-blue','timed_out'=>'bg-warning','disqualified'=>'bg-danger','abandoned'=>'bg-secondary'];
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= base_url('quiz-sessions'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a></div>
            <div class="col"><div class="page-pretitle">Latihan Soal</div><h1 class="page-title"><?= html_escape($session['title']); ?></h1></div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if(!empty($pending_grades)): ?>
        <div class="alert alert-warning"><i class="ti ti-alert-triangle me-1"></i><?= count($pending_grades); ?> attempt memiliki essay yang belum dinilai. <a href="#pending-grading" class="alert-link">Lihat di bawah.</a></div>
        <?php endif; ?>

        <!-- Leaderboard -->
        <div class="card admin-card">
            <div class="card-header"><h3 class="card-title"><i class="ti ti-trophy me-1"></i>Hasil Terbaik</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>#</th><th>Nama</th><th>Kode</th><th>Skor</th><th>%</th><th>Lulus</th><th>Waktu</th></tr></thead>
                    <tbody>
                    <?php if(empty($results)): ?><tr><td colspan="7" class="text-center text-secondary py-4">Belum ada hasil.</td></tr><?php endif; ?>
                    <?php foreach($results as $rank=>$r): ?>
                    <tr>
                        <td><?= $rank+1; ?></td>
                        <td class="fw-semibold"><?= html_escape($r['full_name']); ?></td>
                        <td><code><?= html_escape($r['registration_code']); ?></code></td>
                        <td><?= number_format((float)$r['total_score'],1); ?> / <?= number_format((float)$r['max_possible_score'],1); ?></td>
                        <td><strong><?= number_format((float)$r['percentage'],1); ?>%</strong></td>
                        <td><?= $r['is_passed']===null ? '<span class="badge bg-secondary">Pending</span>' : ($r['is_passed'] ? '<span class="badge bg-success">Lulus</span>' : '<span class="badge bg-danger">Tidak Lulus</span>'); ?></td>
                        <td><?= $r['time_spent_seconds'] ? gmdate('i:s',(int)$r['time_spent_seconds']) : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- All Attempts -->
        <div class="card admin-card mt-3">
            <div class="card-header"><h3 class="card-title">Semua Attempt</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>ID</th><th>Peserta</th><th>Percobaan ke-</th><th>%</th><th>Status</th><th>Fraud</th><th>Mulai</th><th class="w-1"></th></tr></thead>
                    <tbody>
                    <?php if(empty($attempts)): ?><tr><td colspan="8" class="text-center text-secondary py-4">Belum ada attempt.</td></tr><?php endif; ?>
                    <?php foreach($attempts as $a): ?>
                    <tr>
                        <td><?= $a['id']; ?></td>
                        <td><?= html_escape($a['full_name']); ?></td>
                        <td><?= $a['attempt_number']; ?></td>
                        <td><?= $a['percentage'] !== null ? number_format((float)$a['percentage'],1).'%' : '—'; ?></td>
                        <td><span class="badge <?= $status_colors[$a['status']]??'bg-secondary'; ?>"><?= $status_labels[$a['status']]??$a['status']; ?></span><?= $a['is_flagged'] ? ' <span class="badge bg-danger">Flagged</span>' : ''; ?></td>
                        <td><span class="badge <?= $a['fraud_score']>0?'bg-warning':'bg-secondary'; ?>"><?= $a['fraud_score']; ?></span></td>
                        <td><?= substr($a['started_at'],0,16); ?></td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <a href="<?= base_url('quiz/review/'.$a['id']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="ti ti-eye"></i></a>
                                <?php if($can_grade && !$a['essay_graded'] && $a['status']==='submitted'): ?>
                                <a href="<?= base_url('quiz-sessions/grade/'.$a['id']); ?>" class="btn btn-sm btn-outline-primary"><i class="ti ti-pencil"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if(!empty($pending_grades)): ?>
        <div id="pending-grading" class="card admin-card mt-3">
            <div class="card-header"><h3 class="card-title text-warning"><i class="ti ti-pencil me-1"></i>Essay Menunggu Penilaian</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Peserta</th><th>Waktu Submit</th><th class="w-1"></th></tr></thead>
                    <tbody>
                    <?php foreach($pending_grades as $a): ?>
                    <tr>
                        <td class="fw-semibold"><?= html_escape($a['full_name']); ?></td>
                        <td><?= html_escape($a['submitted_at']??'—'); ?></td>
                        <td><a href="<?= base_url('quiz-sessions/grade/'.$a['id']); ?>" class="btn btn-sm btn-primary"><i class="ti ti-pencil me-1"></i>Nilai</a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
