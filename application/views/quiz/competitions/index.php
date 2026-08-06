<?php defined('BASEPATH') OR exit('No direct script access allowed');
$status_labels = ['draft'=>'Draft','open'=>'Terbuka','ongoing'=>'Berlangsung','closed'=>'Selesai','archived'=>'Arsip'];
$status_colors = ['draft'=>'bg-secondary','open'=>'bg-success','ongoing'=>'bg-blue','closed'=>'bg-muted','archived'=>'bg-muted'];
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><div class="page-pretitle">Modul Belajar</div><h1 class="page-title">Kompetisi</h1></div>
            <div class="col-auto ms-auto">
                <?php if($can_create): ?><a href="<?= base_url('quiz-competitions/create'); ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Buat Kompetisi</a><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if($s=$this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($s); ?></div><?php endif; ?>
        <?php if($e=$this->session->flashdata('error')): ?><div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($e); ?></div><?php endif; ?>

        <div class="metric-ribbon">
            <?php foreach([['key'=>'total','label'=>'Total','icon'=>'ti ti-tournament'],['key'=>'open','label'=>'Terbuka','icon'=>'ti ti-door-enter'],['key'=>'attempts','label'=>'Pengerjaan','icon'=>'ti ti-clipboard-check']] as $m): ?>
            <div class="metric-ribbon-item"><span class="metric-icon"><i class="<?=$m['icon']?>"></i></span><div><div class="metric-value"><?= number_format((int)($stats[$m['key']]??0),0,',','.'); ?></div><div class="metric-label"><?=$m['label']?></div></div></div>
            <?php endforeach; ?>
        </div>

        <?php foreach($sessions as $sess): ?>
        <div class="card admin-card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge <?= $status_colors[$sess['status']]??'bg-secondary'; ?>"><?= $status_labels[$sess['status']]??$sess['status']; ?></span>
                            <code class="small"><?= html_escape($sess['code']); ?></code>
                            <?= $sess['is_flagged']??false ? '<span class="badge bg-danger">Ada pelanggaran</span>' : ''; ?>
                        </div>
                        <h3 class="m-0 fw-bold"><?= html_escape($sess['title']); ?></h3>
                        <div class="text-secondary small mt-1">
                            <?= $sess['question_count']; ?> soal · <?= $sess['time_limit_minutes']; ?> menit ·
                            <?= $sess['participant_count']; ?> peserta ·
                            <?= $sess['attempt_count']; ?> attempt
                            <?php if($sess['start_time']): ?> · Mulai: <?= substr($sess['start_time'],0,16); ?><?php endif; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <a href="<?= base_url('quiz-competitions/questions/'.$sess['id']); ?>" class="btn btn-sm btn-outline-secondary"><i class="ti ti-list-check me-1"></i>Soal</a>
                            <a href="<?= base_url('quiz-competitions/participants/'.$sess['id']); ?>" class="btn btn-sm btn-outline-secondary"><i class="ti ti-users me-1"></i>Peserta</a>
                            <a href="<?= base_url('quiz-competitions/results/'.$sess['id']); ?>" class="btn btn-sm btn-outline-primary"><i class="ti ti-trophy me-1"></i>Hasil</a>
                            <?php if($can_edit): ?>
                            <!-- Status quick-switch -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-settings"></i></button>
                                <div class="dropdown-menu">
                                    <?php foreach(['draft'=>'Draft','open'=>'Buka','ongoing'=>'Mulai','closed'=>'Tutup','archived'=>'Arsip'] as $st=>$stl): ?>
                                    <?php if($sess['status']!==$st): ?>
                                    <?= form_open('quiz-competitions/set_status/'.$sess['id'],['class'=>'d-inline']); ?>
                                    <input type="hidden" name="status" value="<?=$st?>">
                                    <button type="submit" class="dropdown-item"><?=$stl?></button>
                                    <?= form_close(); ?>
                                    <?php endif; endforeach; ?>
                                    <div class="dropdown-divider"></div>
                                    <?php if($can_edit): ?><a href="<?= base_url('quiz-competitions/announce/'.$sess['id']); ?>" class="dropdown-item" onclick="return confirm('Umumkan kompetisi ini sebagai notifikasi ke semua member?')"><i class="ti ti-speakerphone me-1"></i>Umumkan ke Member</a><?php endif; ?>
                                    <a href="<?= base_url('quiz-competitions/edit/'.$sess['id']); ?>" class="dropdown-item"><i class="ti ti-edit me-1"></i>Edit</a>
                                    <?php if($can_delete): ?><a href="<?= base_url('quiz-competitions/delete/'.$sess['id']); ?>" class="dropdown-item text-danger" onclick="return confirm('Hapus kompetisi ini?')"><i class="ti ti-trash me-1"></i>Hapus</a><?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if(empty($sessions)): ?>
        <div class="card"><div class="card-body text-center text-secondary py-5"><i class="ti ti-tournament fs-1 d-block mb-2"></i>Belum ada kompetisi. <a href="<?= base_url('quiz-competitions/create'); ?>">Buat sekarang.</a></div></div>
        <?php endif; ?>
    </div>
</div>
