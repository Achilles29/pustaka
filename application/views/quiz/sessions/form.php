<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = ! empty($session);
$v = function($key,$default='') use ($session,$is_edit) { return $is_edit ? ($session[$key]??$default) : $default; };
$chk = function($key,$default=true) use ($session,$is_edit) { return !$is_edit ? $default : (bool)($session[$key]??$default); };
$active_tab = $active_tab ?? 'settings';
$session_questions = $session_questions ?? [];
$bank_questions = $bank_questions ?? [];
$selected_subject_id = (int) $v('subject_id', 0);
$selected_grade_level_id = (int) $v('grade_level_id', 0);
$selected_ids = [];
$special_questions = [];
foreach ($session_questions as $question) {
    if (! empty($question['is_session_only'])) $special_questions[] = $question;
    else $selected_ids[] = (int) $question['question_id'];
}
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
        <?php if($s=$this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($s); ?></div><?php endif; ?>
        <?php if ($is_edit): ?>
        <div class="nav workspace-tabs quiz-session-tabs mb-3" role="tablist" aria-label="Editor sesi latihan">
            <a class="nav-link <?= $active_tab === 'settings' ? 'active' : ''; ?>" href="<?= base_url('quiz-sessions/edit/' . (int) $session['id']); ?>"><i class="ti ti-settings"></i><span>Pengaturan Sesi</span></a>
            <a class="nav-link <?= $active_tab === 'questions' ? 'active' : ''; ?>" href="<?= base_url('quiz-sessions/edit/' . (int) $session['id'] . '?tab=questions'); ?>"><i class="ti ti-clipboard-list"></i><span>Bank Soal</span><span class="badge quiz-session-tab-count"><?= count($session_questions); ?></span></a>
        </div>
        <?php endif; ?>
        <?php if ($active_tab !== 'questions'): ?>
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
                                <?php foreach($subjects as $s): ?><option value="<?= (int) $s['id']; ?>" <?= $selected_subject_id === (int) $s['id'] ? 'selected' : ''; ?>><?= html_escape($s['name']); ?><?= empty($s['is_active']) ? ' (nonaktif)' : ''; ?></option><?php endforeach; ?></select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Jenjang Kelas</label>
                                <select name="grade_level_id" class="form-select"><option value="">Semua Jenjang</option>
                                <?php foreach($grades as $g): ?><option value="<?= (int) $g['id']; ?>" <?= $selected_grade_level_id === (int) $g['id'] ? 'selected' : ''; ?>><?= html_escape($g['name']); ?><?= empty($g['is_active']) ? ' (nonaktif)' : ''; ?></option><?php endforeach; ?></select>
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
                        <div class="row g-2 mt-2">
                            <div class="col-12"><div class="form-label mb-0"><i class="ti ti-calendar-clock me-1"></i>Jadwal Buka (opsional)</div><small class="text-secondary">Di luar rentang ini, member tidak bisa mengikuti. Kosongkan salah satu/keduanya untuk tanpa batas.</small></div>
                            <div class="col-md-4"><label class="form-label">Dibuka Mulai</label><input type="datetime-local" name="start_time" class="form-control" value="<?= str_replace(' ','T',substr((string)$v('start_time',''),0,16)); ?>"></div>
                            <div class="col-md-4"><label class="form-label">Ditutup Pada</label><input type="datetime-local" name="end_time" class="form-control" value="<?= str_replace(' ','T',substr((string)$v('end_time',''),0,16)); ?>"></div>
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
        <?php endif; ?>

        <?php if ($is_edit && $active_tab === 'questions'): ?>
        <div class="card admin-card">
            <div class="card-header"><div><h3 class="card-title">Sumber Soal Sesi</h3><div class="text-secondary small">Pilih cara sesi mengambil soal. Soal khusus sesi tidak muncul pada bank soal umum.</div></div><a href="<?= base_url('quiz-bank/import?session_id=' . (int) $session['id']); ?>" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import Soal Khusus</a></div>
            <div class="card-body">
                <?= form_open('quiz-sessions/questions/update/' . (int) $session['id']); ?>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-selectgroup-item"><input type="radio" name="question_source" value="bank_all" class="form-selectgroup-input" <?= $v('question_source','bank_all') === 'bank_all' ? 'checked' : ''; ?>><span class="form-selectgroup-label"><span class="form-selectgroup-title">Semua Bank Soal</span><span class="form-selectgroup-extra">Acak dari bank sesuai mapel, jenjang, kesulitan, dan jumlah soal pada sesi.</span></span></label></div>
                    <div class="col-md-4"><label class="form-selectgroup-item"><input type="radio" name="question_source" value="selected" class="form-selectgroup-input" <?= $v('question_source') === 'selected' ? 'checked' : ''; ?>><span class="form-selectgroup-label"><span class="form-selectgroup-title">Pilih Soal Tertentu</span><span class="form-selectgroup-extra">Gunakan hanya soal bank yang dicentang di bawah.</span></span></label></div>
                    <div class="col-md-4"><label class="form-selectgroup-item"><input type="radio" name="question_source" value="session_only" class="form-selectgroup-input" <?= $v('question_source') === 'session_only' ? 'checked' : ''; ?>><span class="form-selectgroup-label"><span class="form-selectgroup-title">Bank Khusus Sesi</span><span class="form-selectgroup-extra">Gunakan soal yang diimpor khusus untuk sesi ini.</span></span></label></div>
                </div>
                <div class="alert alert-info mt-3 mb-2 py-2 small"><i class="ti ti-info-circle me-1"></i>Jumlah soal tetap mengikuti pengaturan sesi: <strong><?= (int) $session['question_count']; ?> soal</strong>. Jika pilihan kurang, peserta hanya mendapat soal yang tersedia.</div>
                <div class="border rounded mt-3 p-3" id="bank-selected-list">
                    <div class="d-flex justify-content-between align-items-center mb-2"><strong>Soal Bank yang Dipilih</strong><span class="text-secondary small">Ditampilkan maksimal 100 soal sesuai filter sesi.</span></div>
                    <?php if (empty($bank_questions)): ?><div class="text-secondary">Tidak ada soal bank yang sesuai filter sesi. Longgarkan filter pada Pengaturan Sesi atau gunakan import soal khusus.</div><?php endif; ?>
                    <?php foreach ($bank_questions as $question): ?><label class="form-check py-1 border-bottom d-block"><input class="form-check-input" type="checkbox" name="question_ids[]" value="<?= (int) $question['id']; ?>" <?= in_array((int) $question['id'], $selected_ids, true) ? 'checked' : ''; ?>><span class="form-check-label"><strong>#<?= (int) $question['id']; ?></strong> <?= html_escape($question['question_text']); ?><span class="text-secondary small"> · <?= html_escape($question['subject_name']); ?> / <?= html_escape($question['grade_name']); ?> · <?= html_escape($question['difficulty']); ?><?= empty($question['is_active']) ? ' · draft' : ''; ?></span></span></label><?php endforeach; ?>
                </div>
                <div class="border rounded mt-3 p-3"><div class="d-flex justify-content-between align-items-center"><strong>Soal Khusus Sesi</strong><span class="badge bg-purple-lt"><?= count($special_questions); ?> soal</span></div><?php if (empty($special_questions)): ?><div class="text-secondary small mt-2">Belum ada. Gunakan tombol Import Soal Khusus untuk unggah CSV, XLSX, atau TXT dengan pratinjau dan validasi yang sama seperti Bank Soal.</div><?php else: ?><ul class="mb-0 mt-2 ps-3"><?php foreach ($special_questions as $question): ?><li><?= html_escape($question['question_text']); ?></li><?php endforeach; ?></ul><?php endif; ?></div>
                <div class="mt-3 d-flex gap-2"><button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Sumber Soal</button><a href="<?= base_url('quiz-sessions'); ?>" class="btn btn-outline-secondary">Selesai</a></div>
                <?= form_close(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
