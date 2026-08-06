<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pembelajaran</div>
                <h2 class="page-title">Story Quiz — Bacaan</h2>
            </div>
            <div class="col-auto">
                <?php if ($can_create): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddPassage"><i class="ti ti-plus me-1"></i>Tambah Bacaan</button>
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

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-cyan-lt avatar"><i class="ti ti-book"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['passages']; ?></div><div class="text-secondary">Bacaan</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-green-lt avatar"><i class="ti ti-circle-check"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['active']; ?></div><div class="text-secondary">Aktif</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-blue-lt avatar"><i class="ti ti-help-circle"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['questions']; ?></div><div class="text-secondary">Soal</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-orange-lt avatar"><i class="ti ti-player-play"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= number_format((int)$stats['attempts']); ?></div><div class="text-secondary">Dikerjakan</div></div>
        </div></div></div></div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Bacaan</th><th class="text-center">Soal</th><th>Mapel/Jenjang</th><th class="text-center">Estimasi</th><th class="text-center">Status</th><?php if ($can_edit||$can_delete): ?><th></th><?php endif; ?></tr></thead>
                <tbody>
                    <?php foreach ($passages as $p): ?>
                    <tr class="<?= !$p['is_active']?'opacity-60':''; ?>">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar" style="background:<?= html_escape($p['color']); ?>1a;color:<?= html_escape($p['color']); ?>"><i class="ti <?= html_escape($p['icon']); ?>"></i></span>
                                <div>
                                    <div class="fw-medium"><?= html_escape($p['title']); ?></div>
                                    <div class="text-secondary small"><code><?= html_escape($p['code']); ?></code></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-blue-lt text-blue"><?= (int)$p['question_count']; ?></span></td>
                        <td class="small text-secondary">
                            <?= html_escape($p['subject_name'] ?: 'Umum'); ?><?= !empty($p['grade_name']) ? ' · '.html_escape($p['grade_name']) : ''; ?>
                        </td>
                        <td class="text-center text-secondary small"><?= (int)$p['estimated_minutes']; ?> mnt</td>
                        <td class="text-center"><span class="badge <?= $p['is_active']?'bg-success-lt text-success':'bg-secondary-lt text-secondary'; ?>"><?= $p['is_active']?'Aktif':'Nonaktif'; ?></span></td>
                        <?php if ($can_edit||$can_delete): ?>
                        <td class="text-end">
                            <div class="btn-list flex-nowrap">
                                <a href="<?= base_url('learn-story/questions/'.$p['id']); ?>" class="btn btn-sm btn-primary"><i class="ti ti-help-circle me-1"></i>Soal</a>
                                <?php if ($can_edit): ?>
                                <a href="<?= base_url('learn-story/toggle_passage/'.$p['id']); ?>" class="btn btn-sm btn-ghost-secondary" title="<?= $p['is_active']?'Nonaktifkan':'Aktifkan'; ?>"><i class="ti <?= $p['is_active']?'ti-eye-off':'ti-eye'; ?>"></i></a>
                                <button class="btn btn-sm btn-ghost-secondary btn-edit-passage" data-bs-toggle="modal" data-bs-target="#modalEditPassage"
                                    data-id="<?= $p['id']; ?>" data-code="<?= html_escape($p['code']); ?>" data-title="<?= html_escape($p['title']); ?>"
                                    data-body="<?= html_escape($p['body']); ?>" data-summary="<?= html_escape($p['summary']); ?>"
                                    data-subject_id="<?= (int)$p['subject_id']; ?>" data-grade_level_id="<?= (int)$p['grade_level_id']; ?>"
                                    data-icon="<?= html_escape($p['icon']); ?>" data-color="<?= html_escape($p['color']); ?>"
                                    data-estimated_minutes="<?= (int)$p['estimated_minutes']; ?>" data-sort_order="<?= (int)$p['sort_order']; ?>" data-is_active="<?= (int)$p['is_active']; ?>">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($can_delete): ?>
                                <a href="<?= base_url('learn-story/delete_passage/'.$p['id']); ?>" class="btn btn-sm btn-ghost-danger" onclick="return confirm('Hapus bacaan ini beserta semua soalnya?')"><i class="ti ti-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($passages)): ?>
                    <tr><td colspan="<?= ($can_edit||$can_delete)?6:5; ?>" class="text-center text-secondary py-5"><i class="ti ti-book-off fs-1 d-block mb-2"></i>Belum ada bacaan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add Passage -->
<div class="modal modal-blur fade" id="modalAddPassage" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?= form_open('learn-story/store_passage'); ?>
            <div class="modal-header"><h5 class="modal-title">Tambah Bacaan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><?php include __DIR__ . '/_passage_fields.php'; ?></div>
            <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<!-- Modal Edit Passage -->
<div class="modal modal-blur fade" id="modalEditPassage" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="" id="formEditPassage">
            <div class="modal-header"><h5 class="modal-title">Edit Bacaan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><?php include __DIR__ . '/_passage_fields.php'; ?></div>
            <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Perbarui</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEditPassage').addEventListener('show.bs.modal', function (e) {
    var d = e.relatedTarget.dataset, f = document.getElementById('formEditPassage');
    f.action = '<?= base_url('learn-story/update_passage/'); ?>' + d.id;
    f.querySelector('[name=code]').value              = d.code;
    f.querySelector('[name=title]').value             = d.title;
    f.querySelector('[name=body]').value              = d.body;
    f.querySelector('[name=summary]').value           = d.summary || '';
    f.querySelector('[name=subject_id]').value        = d.subject_id !== '0' ? d.subject_id : '';
    f.querySelector('[name=grade_level_id]').value    = d.grade_level_id !== '0' ? d.grade_level_id : '';
    f.querySelector('[name=icon]').value              = d.icon;
    f.querySelector('[name=color]').value             = d.color;
    f.querySelector('[name=estimated_minutes]').value = d.estimated_minutes;
    f.querySelector('[name=sort_order]').value        = d.sort_order;
    f.querySelector('[name=is_active]').checked       = d.is_active === '1';
});
</script>
