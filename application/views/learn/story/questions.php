<?php defined('BASEPATH') OR exit('No direct script access allowed');
$letters = ['A', 'B', 'C', 'D'];
?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= base_url('learn-story'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Bacaan</a></div>
            <div class="col">
                <div class="page-pretitle">Story Quiz · <?= html_escape($passage['code']); ?></div>
                <h2 class="page-title"><?= html_escape($passage['title']); ?></h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="<?= base_url('belajar/cerita/'.$passage['code']); ?>" target="_blank" class="btn btn-outline-primary"><i class="ti ti-eye me-1"></i>Pratinjau</a>
                <?php if ($can_create): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddQ"><i class="ti ti-plus me-1"></i>Tambah Soal</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible"><?= html_escape($this->session->flashdata('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible"><?= html_escape($this->session->flashdata('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-book me-2"></i>Isi Bacaan</h3></div>
                <div class="card-body" style="max-height:520px;overflow:auto">
                    <?php foreach (preg_split('/\n\s*\n/', $passage['body']) as $para): ?>
                        <?php if (trim($para) !== ''): ?><p><?= nl2br(html_escape(trim($para))); ?></p><?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <?php foreach ($questions as $i => $q): ?>
            <div class="card mb-2 <?= !$q['is_active']?'opacity-60':''; ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="fw-medium mb-2"><span class="text-secondary">#<?= $i+1; ?>.</span> <?= html_escape($q['question']); ?></div>
                        <div class="btn-list flex-nowrap">
                            <?php if ($can_edit): ?>
                            <button class="btn btn-sm btn-ghost-secondary btn-edit-q" data-bs-toggle="modal" data-bs-target="#modalEditQ"
                                data-id="<?= $q['id']; ?>" data-question="<?= html_escape($q['question']); ?>"
                                data-option_a="<?= html_escape($q['option_a']); ?>" data-option_b="<?= html_escape($q['option_b']); ?>"
                                data-option_c="<?= html_escape($q['option_c']); ?>" data-option_d="<?= html_escape($q['option_d']); ?>"
                                data-correct_option="<?= (int)$q['correct_option']; ?>" data-explanation="<?= html_escape($q['explanation']); ?>"
                                data-sort_order="<?= (int)$q['sort_order']; ?>" data-is_active="<?= (int)$q['is_active']; ?>"><i class="ti ti-pencil"></i></button>
                            <?php endif; ?>
                            <?php if ($can_delete): ?>
                            <a href="<?= base_url('learn-story/delete_question/'.$q['id']); ?>" class="btn btn-sm btn-ghost-danger" onclick="return confirm('Hapus soal ini?')"><i class="ti ti-trash"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row g-1">
                        <?php foreach (['option_a','option_b','option_c','option_d'] as $oi => $ok): if (!empty($q[$ok])): ?>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-1 small <?= (int)$q['correct_option']===$oi ? 'text-success fw-bold' : 'text-secondary'; ?>">
                                <?php if ((int)$q['correct_option']===$oi): ?><i class="ti ti-circle-check"></i><?php else: ?><span class="badge bg-secondary-lt"><?= $letters[$oi]; ?></span><?php endif; ?>
                                <?= html_escape($q[$ok]); ?>
                            </div>
                        </div>
                        <?php endif; endforeach; ?>
                    </div>
                    <?php if ($q['explanation']): ?><div class="text-secondary small mt-2"><i class="ti ti-info-circle me-1"></i><?= html_escape($q['explanation']); ?></div><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($questions)): ?>
            <div class="card"><div class="card-body text-center text-secondary py-5"><i class="ti ti-help-circle fs-1 d-block mb-2"></i>Belum ada soal. Tambahkan soal pemahaman.</div></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Add Question -->
<div class="modal modal-blur fade" id="modalAddQ" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <?= form_open('learn-story/store_question/'.$passage['id']); ?>
        <div class="modal-header"><h5 class="modal-title">Tambah Soal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><?php include __DIR__ . '/_question_fields.php'; ?></div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        <?= form_close(); ?>
    </div></div>
</div>

<!-- Modal Edit Question -->
<div class="modal modal-blur fade" id="modalEditQ" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="post" action="" id="formEditQ">
        <div class="modal-header"><h5 class="modal-title">Edit Soal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><?php include __DIR__ . '/_question_fields.php'; ?></div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Perbarui</button></div>
        </form>
    </div></div>
</div>

<script>
document.getElementById('modalEditQ').addEventListener('show.bs.modal', function (e) {
    var d = e.relatedTarget.dataset, f = document.getElementById('formEditQ');
    f.action = '<?= base_url('learn-story/update_question/'); ?>' + d.id;
    f.querySelector('[name=question]').value    = d.question;
    f.querySelector('[name=option_a]').value    = d.option_a;
    f.querySelector('[name=option_b]').value    = d.option_b;
    f.querySelector('[name=option_c]').value    = d.option_c || '';
    f.querySelector('[name=option_d]').value    = d.option_d || '';
    f.querySelector('[name=correct_option]').value = d.correct_option;
    f.querySelector('[name=explanation]').value = d.explanation || '';
    f.querySelector('[name=sort_order]').value  = d.sort_order;
    f.querySelector('[name=is_active]').checked = d.is_active === '1';
});
</script>
