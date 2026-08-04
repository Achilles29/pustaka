<?php defined('BASEPATH') OR exit('No direct script access allowed');
$status_labels = ['draft'=>'Draft','open'=>'Buka','ongoing'=>'Berlangsung','closed'=>'Ditutup','archived'=>'Arsip'];
$status_colors = ['draft'=>'bg-secondary','open'=>'bg-success','ongoing'=>'bg-blue','closed'=>'bg-warning','archived'=>'bg-muted'];
$page_url = function($p) use ($filters) { return base_url('quiz-sessions?'.http_build_query(array_merge($filters,['page'=>$p]))); };
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><div class="page-pretitle">Modul Belajar</div><h1 class="page-title">Latihan Soal</h1></div>
            <div class="col-auto ms-auto">
                <?php if($can_create): ?><a href="<?= base_url('quiz-sessions/create'); ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Buat Sesi Latihan</a><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if($s=$this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($s); ?></div><?php endif; ?>
        <?php if($e=$this->session->flashdata('error')): ?><div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($e); ?></div><?php endif; ?>

        <div class="metric-ribbon">
            <?php foreach([['key'=>'total','label'=>'Total Sesi','icon'=>'ti ti-calendar'],['key'=>'open','label'=>'Sesi Terbuka','icon'=>'ti ti-door-enter'],['key'=>'draft','label'=>'Draft','icon'=>'ti ti-pencil'],['key'=>'attempts','label'=>'Total Pengerjaan','icon'=>'ti ti-clipboard-check']] as $m): ?>
            <div class="metric-ribbon-item">
                <span class="metric-icon"><i class="<?=$m['icon']?>"></i></span>
                <div><div class="metric-value"><?= number_format((int)($stats[$m['key']]??0),0,',','.'); ?></div><div class="metric-label"><?=$m['label']?></div></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card admin-card data-workspace">
            <div class="card-header workspace-header">
                <h2 class="card-title">Sesi Latihan</h2>
            </div>
            <div class="card-body workspace-filter">
                <?= form_open('quiz-sessions',['method'=>'get','class'=>'row g-2 align-items-end']); ?>
                <div class="col-md-4"><label class="form-label">Cari</label><input type="text" class="form-control" name="q" value="<?= html_escape($filters['q']??''); ?>" placeholder="Judul sesi..."></div>
                <div class="col-md-2"><label class="form-label">Status</label>
                    <select class="form-select" name="status"><option value="">Semua</option>
                    <?php foreach($status_labels as $k=>$v): ?><option value="<?=$k?>" <?= ($filters['status']??'')===$k?'selected':''; ?>><?=$v?></option><?php endforeach; ?></select>
                </div>
                <div class="col-md-3"><label class="form-label">Mapel</label>
                    <select class="form-select" name="subject_id"><option value="">Semua</option>
                    <?php foreach($subjects as $s): ?><option value="<?=$s['id']?>" <?= (int)($filters['subject_id']??0)===$s['id']?'selected':''; ?>><?= html_escape($s['name']); ?></option><?php endforeach; ?></select>
                </div>
                <div class="col-auto"><label class="form-label">&nbsp;</label><div class="d-flex gap-1"><button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i></button><a href="<?= base_url('quiz-sessions'); ?>" class="btn btn-outline-secondary"><i class="ti ti-x"></i></a></div></div>
                <?= form_close(); ?>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Kode</th><th>Judul</th><th>Mapel/Jenjang</th><th>Soal</th><th>Waktu</th><th>Percobaan</th><th>Status</th><th class="w-1"></th></tr></thead>
                    <tbody>
                    <?php if(empty($sessions)): ?><tr><td colspan="8" class="text-center text-secondary py-4">Belum ada sesi latihan.</td></tr><?php endif; ?>
                    <?php foreach($sessions as $sess): ?>
                    <tr>
                        <td><code><?= html_escape($sess['code']); ?></code></td>
                        <td>
                            <div class="fw-semibold"><?= html_escape($sess['title']); ?></div>
                            <div class="text-secondary small"><?= html_escape($sess['creator_name']??''); ?></div>
                        </td>
                        <td>
                            <div><?= html_escape($sess['subject_name']??'Semua Mapel'); ?></div>
                            <div class="text-secondary small"><?= html_escape($sess['grade_name']??'Semua Jenjang'); ?></div>
                        </td>
                        <td><?= $sess['question_count']; ?> soal</td>
                        <td><?= $sess['time_limit_minutes'] ? $sess['time_limit_minutes'].' mnt' : 'Tanpa batas'; ?></td>
                        <td>
                            <a href="<?= base_url('quiz-sessions/attempts/'.$sess['id']); ?>" class="badge bg-blue-lt"><?= number_format((int)$sess['attempt_count'],0,',','.'); ?> attempt</a>
                        </td>
                        <td><span class="badge <?= $status_colors[$sess['status']]??'bg-secondary'; ?>"><?= $status_labels[$sess['status']]??$sess['status']; ?></span></td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <?php if($can_edit): ?>
                                <a href="<?= base_url('quiz-sessions/toggle_status/'.$sess['id']); ?>" class="btn btn-sm <?= $sess['status']==='open'?'btn-outline-warning':'btn-outline-success'; ?>" title="<?= $sess['status']==='open'?'Tutup':'Buka'; ?>"><i class="ti ti-<?= $sess['status']==='open'?'lock':'lock-open'; ?>"></i></a>
                                <a href="<?= base_url('quiz-sessions/edit/'.$sess['id']); ?>" class="btn btn-sm btn-outline-secondary"><i class="ti ti-edit"></i></a>
                                <?php endif; ?>
                                <?php if($can_delete): ?><a href="<?= base_url('quiz-sessions/delete/'.$sess['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus sesi latihan ini?')"><i class="ti ti-trash"></i></a><?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if($pagination['total_pages']>1): ?>
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-secondary"><?= $pagination['total_rows']; ?> sesi</p>
                <ul class="pagination m-0 ms-auto">
                    <?php if($pagination['page']>1): ?><li class="page-item"><a class="page-link" href="<?= $page_url($pagination['page']-1); ?>">‹</a></li><?php endif; ?>
                    <?php for($p=max(1,$pagination['page']-2);$p<=min($pagination['total_pages'],$pagination['page']+2);$p++): ?>
                    <li class="page-item <?= $p===$pagination['page']?'active':''; ?>"><a class="page-link" href="<?= $page_url($p); ?>"><?=$p?></a></li>
                    <?php endfor; ?>
                    <?php if($pagination['page']<$pagination['total_pages']): ?><li class="page-item"><a class="page-link" href="<?= $page_url($pagination['page']+1); ?>">›</a></li><?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
