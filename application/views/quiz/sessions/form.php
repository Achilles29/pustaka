<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = ! empty($session);
$v = function($key,$default='') use ($session,$is_edit) { return $is_edit ? ($session[$key]??$default) : $default; };
$chk = function($key,$default=true) use ($session,$is_edit) { return !$is_edit ? $default : (bool)($session[$key]??$default); };
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= base_url('quiz-sessions'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a></div>
            <div class="col"><div class="page-pretitle">Latihan Soal</div><h1 class="page-title"><?= $title; ?></h1></div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if($e=$this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($e); ?></div><?php endif; ?>
        <?= form_open($action); ?>
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Informasi Sesi</h3></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label required">Judul Sesi</label><input type="text" name="title" class="form-control" value="<?= html_escape($v('title')); ?>" required></div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="2"><?= html_escape($v('description')); ?></textarea></div>
                        <div class="mb-3"><label class="form-label">Petunjuk Pengerjaan</label><textarea name="instructions" class="form-control" rows="3" placeholder="Petunjuk yang muncul sebelum soal dimulai..."><?= html_escape($v('instructions')); ?></textarea></div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header"><h3 class="card-title">Pengaturan Soal</h3></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4"><label class="form-label">Mata Pelajaran</label>
                                <select name="subject_id" class="form-select"><option value="">Semua Mapel</option>
                                <?php foreach($subjects as $s): ?><option value="<?=$s['id']?>" <?= (int)$v('subject_id')===$s['id']?'selected':''; ?>><?= html_escape($s['name']); ?></option><?php endforeach; ?></select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Jenjang Kelas</label>
                                <select name="grade_level_id" class="form-select"><option value="">Semua Jenjang</option>
                                <?php foreach($grades as $g): ?><option value="<?=$g['id']?>" <?= (int)$v('grade_level_id')===$g['id']?'selected':''; ?>><?= html_escape($g['name']); ?></option><?php endforeach; ?></select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Tingkat Kesulitan</label>
                                <select name="difficulty_filter" class="form-select">
                                    <?php foreach(['mixed'=>'Campuran','easy'=>'Mudah','medium'=>'Sedang','hard'=>'Sulit'] as $k=>$lbl): ?>
                                    <option value="<?=$k?>" <?= $v('difficulty_filter','mixed')===$k?'selected':''; ?>><?=$lbl?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Jumlah Soal</label><input type="number" name="question_count" class="form-control" value="<?= $v('question_count',10); ?>" min="1" max="200"></div>
                            <div class="col-md-4"><label class="form-label">Batas Waktu (menit, 0=tanpa batas)</label><input type="number" name="time_limit_minutes" class="form-control" value="<?= $v('time_limit_minutes',30); ?>" min="0"></div>
                            <div class="col-md-4"><label class="form-label">Maks Percobaan (0=∞)</label><input type="number" name="max_attempts" class="form-control" value="<?= $v('max_attempts',0); ?>" min="0"></div>
                            <div class="col-md-4"><label class="form-label">Nilai Lulus (%)</label><input type="number" name="passing_score" class="form-control" value="<?= $v('passing_score',60); ?>" min="0" max="100"></div>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-auto"><label class="form-check"><input type="checkbox" name="shuffle_questions" value="1" class="form-check-input" <?= $chk('shuffle_questions')?'checked':''; ?>><span class="form-check-label">Acak urutan soal</span></label></div>
                            <div class="col-auto"><label class="form-check"><input type="checkbox" name="shuffle_options" value="1" class="form-check-input" <?= $chk('shuffle_options')?'checked':''; ?>><span class="form-check-label">Acak pilihan jawaban</span></label></div>
                            <div class="col-auto"><label class="form-check"><input type="checkbox" name="show_result_immediately" value="1" class="form-check-input" <?= $chk('show_result_immediately')?'checked':''; ?>><span class="form-check-label">Tampilkan hasil langsung</span></label></div>
                            <div class="col-auto"><label class="form-check"><input type="checkbox" name="allow_review" value="1" class="form-check-input" <?= $chk('allow_review')?'checked':''; ?>><span class="form-check-label">Izinkan pembahasan</span></label></div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header"><h3 class="card-title"><i class="ti ti-shield-check me-1"></i>Anti-Fraud</h3></div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-auto"><label class="form-check"><input type="checkbox" name="fraud_detect_tab_switch" value="1" class="form-check-input" <?= $chk('fraud_detect_tab_switch')?'checked':''; ?>><span class="form-check-label">Deteksi pindah tab</span></label></div>
                            <div class="col-auto"><label class="form-check"><input type="checkbox" name="fraud_detect_time_anomaly" value="1" class="form-check-input" <?= $chk('fraud_detect_time_anomaly')?'checked':''; ?>><span class="form-check-label">Deteksi anomali waktu</span></label></div>
                            <div class="col-md-3"><label class="form-label">Maks pindah tab</label><input type="number" name="fraud_max_tab_switches" class="form-control" value="<?= $v('fraud_max_tab_switches',3); ?>" min="1"></div>
                            <div class="col-md-3"><label class="form-label">Tindakan jika curang</label>
                                <select name="fraud_action" class="form-select">
                                    <option value="warn" <?= $v('fraud_action','flag')==='warn'?'selected':''; ?>>Peringatkan</option>
                                    <option value="flag" <?= $v('fraud_action','flag')==='flag'?'selected':''; ?>>Tandai (flag)</option>
                                    <option value="disqualify" <?= $v('fraud_action')==='disqualify'?'selected':''; ?>>Diskualifikasi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Status</h3></div>
                    <div class="card-body">
                        <select name="status" class="form-select">
                            <?php foreach(['draft'=>'Draft','open'=>'Buka','closed'=>'Ditutup'] as $k=>$lbl): ?>
                            <option value="<?=$k?>" <?= $v('status','draft')===$k?'selected':''; ?>><?=$lbl?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint mt-1">Setel ke "Buka" agar bisa dikerjakan anggota.</p>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                    <a href="<?= base_url('quiz-sessions'); ?>" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>
