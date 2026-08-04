<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = ! empty($question);
$options = $is_edit ? array_column($question['options'] ?? [], 'option_text', 'option_index') : [];
$correct = $is_edit ? (int)($question['correct_option_index'] ?? 0) : 0;
$tag_string = $is_edit ? implode(', ', array_column($question['tags']??[], 'name')) : '';
$letters = ['A','B','C','D','E'];
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= base_url('quiz-bank'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a></div>
            <div class="col"><div class="page-pretitle">Bank Soal</div><h1 class="page-title"><?= $title; ?></h1></div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if($e=$this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($e); ?></div><?php endif; ?>

        <?= form_open($action, ['id'=>'quiz-question-form']); ?>
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Pertanyaan</h3></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Teks Soal</label>
                            <textarea name="question_text" class="form-control" rows="5" required><?= html_escape($is_edit ? $question['question_text'] : ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Tipe Soal</label>
                            <div class="d-flex gap-3">
                                <label class="form-check"><input type="radio" name="type" value="multiple_choice" class="form-check-input" id="type-mc" <?= (!$is_edit || $question['type']==='multiple_choice')?'checked':''; ?> onchange="toggleType()"><span class="form-check-label">Pilihan Ganda</span></label>
                                <label class="form-check"><input type="radio" name="type" value="essay" class="form-check-input" id="type-essay" <?= ($is_edit&&$question['type']==='essay')?'checked':''; ?> onchange="toggleType()"><span class="form-check-label">Essay</span></label>
                            </div>
                        </div>

                        <!-- Multiple Choice Options -->
                        <div id="mc-section">
                            <div class="mb-3">
                                <label class="form-label required">Pilihan Jawaban</label>
                                <?php for($i=0;$i<4;$i++): ?>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">
                                        <input type="radio" name="correct_option_index" value="<?=$i?>" <?= $correct===$i?'checked':''; ?> title="Tandai sebagai jawaban benar">
                                        &nbsp;<strong><?= $letters[$i]; ?></strong>
                                    </span>
                                    <input type="text" name="options[<?=$i?>]" class="form-control" value="<?= html_escape($options[$i]??''); ?>" placeholder="Pilihan <?= $letters[$i]; ?>">
                                </div>
                                <?php endfor; ?>
                                <p class="form-hint">Klik radio button di kiri untuk menandai jawaban benar.</p>
                            </div>
                        </div>

                        <!-- Essay Section -->
                        <div id="essay-section" style="display:none">
                            <div class="mb-3">
                                <label class="form-label">Rubrik Penilaian</label>
                                <textarea name="essay_rubric" class="form-control" rows="3" placeholder="Kriteria penilaian untuk jawaban essay..."><?= html_escape($is_edit ? ($question['essay_rubric']??'') : ''); ?></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pembahasan / Kunci Jawaban</label>
                            <textarea name="explanation" class="form-control" rows="3" placeholder="Penjelasan jawaban yang benar..."><?= html_escape($is_edit ? ($question['explanation']??'') : ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Metadata</h3></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Mata Pelajaran</label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">— Pilih —</option>
                                <?php foreach($subjects as $s): ?>
                                <option value="<?=$s['id']?>" <?= ($is_edit&&(int)$question['subject_id']===$s['id'])?'selected':''; ?>><?= html_escape($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Jenjang Kelas</label>
                            <select name="grade_level_id" class="form-select" required>
                                <option value="">— Pilih —</option>
                                <?php foreach($grades as $g): ?>
                                <option value="<?=$g['id']?>" <?= ($is_edit&&(int)$question['grade_level_id']===$g['id'])?'selected':''; ?>><?= html_escape($g['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select">
                                <option value="easy" <?= ($is_edit&&$question['difficulty']==='easy')?'selected':''; ?>>Mudah</option>
                                <option value="medium" <?= (!$is_edit||$question['difficulty']==='medium')?'selected':''; ?>>Sedang</option>
                                <option value="hard" <?= ($is_edit&&$question['difficulty']==='hard')?'selected':''; ?>>Sulit</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bobot Skor</label>
                            <input type="number" name="score_weight" class="form-control" value="<?= $is_edit ? $question['score_weight'] : '1'; ?>" step="0.5" min="0.5" max="10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tag (pisah koma)</label>
                            <input type="text" name="tags" class="form-control" value="<?= html_escape($tag_string); ?>" placeholder="aritmatika, penjumlahan">
                            <?php if(!empty($tags)): ?>
                            <div class="mt-1 d-flex flex-wrap gap-1">
                                <?php foreach(array_slice($tags,0,15) as $t): ?>
                                <button type="button" class="badge bg-blue-lt border-0" onclick="addTag('<?= html_escape($t['name']); ?>')"><?= html_escape($t['name']); ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" <?= (!$is_edit||$question['is_active'])?'checked':''; ?>><span class="form-check-label">Soal Aktif</span></label>
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                    <a href="<?= base_url('quiz-bank'); ?>" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<script>
function toggleType() {
    const isEssay = document.getElementById('type-essay').checked;
    document.getElementById('mc-section').style.display = isEssay ? 'none' : '';
    document.getElementById('essay-section').style.display = isEssay ? '' : 'none';
}
function addTag(name) {
    const inp = document.querySelector('input[name="tags"]');
    const existing = inp.value.split(',').map(s=>s.trim()).filter(Boolean);
    if (!existing.includes(name)) { existing.push(name); inp.value = existing.join(', '); }
}
document.addEventListener('DOMContentLoaded', toggleType);
</script>
