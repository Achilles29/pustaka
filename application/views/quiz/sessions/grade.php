<?php defined('BASEPATH') OR exit('No direct script access allowed');
$letters = ['A','B','C','D','E'];
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a></div>
            <div class="col"><div class="page-pretitle">Penilaian</div><h1 class="page-title"><?= html_escape($title); ?></h1></div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if($s=$this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($s); ?></div><?php endif; ?>

        <div class="alert alert-info mb-3">
            <strong><?= html_escape($attempt['full_name']); ?></strong> — Sesi: <?= html_escape($attempt['session_title']); ?>
            <?php if($attempt['percentage']!==null): ?> | Skor saat ini: <strong><?= number_format((float)$attempt['percentage'],1); ?>%</strong><?php endif; ?>
        </div>

        <?= form_open(''); ?>
        <?php foreach($review['answers'] as $i=>$ans): ?>
        <?php if($ans['type']!=='essay') continue; ?>
        <div class="card mb-3">
            <div class="card-header"><h4 class="card-title">Soal <?= $i+1; ?></h4></div>
            <div class="card-body">
                <div class="mb-3 p-3 bg-light rounded"><strong>Pertanyaan:</strong><br><?= nl2br(html_escape($ans['question_text'])); ?></div>
                <?php if($ans['explanation']): ?>
                <div class="mb-3 p-3 bg-success-lt rounded"><strong><i class="ti ti-key me-1"></i>Kunci Jawaban:</strong><br><?= nl2br(html_escape($ans['explanation'])); ?></div>
                <?php endif; ?>
                <div class="mb-3 p-3 bg-blue-lt rounded"><strong>Jawaban Peserta:</strong><br><?= nl2br(html_escape($ans['essay_answer']??'(kosong)')); ?></div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label required">Skor (0 – <?= $ans['score_weight']; ?>)</label>
                        <input type="number" name="grade[<?= $ans['id']; ?>][score]" class="form-control" value="<?= $ans['essay_score']??0; ?>" min="0" max="<?= $ans['score_weight']; ?>" step="0.5" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Feedback</label>
                        <input type="text" name="grade[<?= $ans['id']; ?>][feedback]" class="form-control" value="<?= html_escape($ans['essay_feedback']??''); ?>" placeholder="Komentar untuk peserta (opsional)">
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Penilaian</button>
        </div>
        <?= form_close(); ?>
    </div>
</div>
