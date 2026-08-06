<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pembelajaran</div>
                <h2 class="page-title">Flashcard — Deck</h2>
            </div>
            <div class="col-auto">
                <?php if ($can_create): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddDeck">
                    <i class="ti ti-plus me-1"></i>Tambah Deck
                </button>
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

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-purple-lt avatar"><i class="ti ti-cards"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['decks']; ?></div><div class="text-secondary">Total Deck</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-green-lt avatar"><i class="ti ti-circle-check"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['active']; ?></div><div class="text-secondary">Deck Aktif</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-blue-lt avatar"><i class="ti ti-stack-2"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['cards']; ?></div><div class="text-secondary">Total Kartu</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-orange-lt avatar"><i class="ti ti-users"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['learners']; ?></div><div class="text-secondary">Pembelajar</div></div>
        </div></div></div></div>
    </div>

    <div class="row g-3">
        <?php foreach ($decks as $d): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card <?= !$d['is_active'] ? 'opacity-60' : ''; ?>">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <span class="avatar avatar-lg" style="background:<?= html_escape($d['color']); ?>1a;color:<?= html_escape($d['color']); ?>">
                            <i class="ti <?= html_escape($d['icon']); ?> fs-2"></i>
                        </span>
                        <div class="flex-fill">
                            <div class="fw-bold"><?= html_escape($d['name']); ?></div>
                            <div class="text-secondary small mb-2"><code><?= html_escape($d['code']); ?></code></div>
                            <div class="d-flex gap-1 flex-wrap">
                                <span class="badge bg-blue-lt text-blue"><?= (int)$d['card_count']; ?> kartu</span>
                                <?php if (!empty($d['subject_name'])): ?><span class="badge bg-cyan-lt text-cyan"><?= html_escape($d['subject_name']); ?></span><?php endif; ?>
                                <?php if (!empty($d['grade_name'])): ?><span class="badge bg-secondary-lt"><?= html_escape($d['grade_name']); ?></span><?php endif; ?>
                                <?php if (!$d['is_active']): ?><span class="badge bg-secondary-lt text-secondary">Nonaktif</span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($d['description']): ?>
                    <p class="text-secondary small mt-2 mb-0"><?= html_escape($d['description']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer d-flex gap-2">
                    <a href="<?= base_url('learn-flashcards/cards/'.$d['id']); ?>" class="btn btn-sm btn-primary flex-fill">
                        <i class="ti ti-stack-2 me-1"></i>Kelola Kartu
                    </a>
                    <?php if ($can_edit): ?>
                    <a href="<?= base_url('learn-flashcards/toggle_deck/'.$d['id']); ?>" class="btn btn-sm btn-ghost-secondary" title="<?= $d['is_active']?'Nonaktifkan':'Aktifkan'; ?>">
                        <i class="ti <?= $d['is_active']?'ti-eye-off':'ti-eye'; ?>"></i>
                    </a>
                    <button class="btn btn-sm btn-ghost-secondary btn-edit-deck"
                        data-bs-toggle="modal" data-bs-target="#modalEditDeck"
                        data-id="<?= $d['id']; ?>"
                        data-code="<?= html_escape($d['code']); ?>"
                        data-name="<?= html_escape($d['name']); ?>"
                        data-description="<?= html_escape($d['description']); ?>"
                        data-subject_id="<?= (int)$d['subject_id']; ?>"
                        data-grade_level_id="<?= (int)$d['grade_level_id']; ?>"
                        data-icon="<?= html_escape($d['icon']); ?>"
                        data-color="<?= html_escape($d['color']); ?>"
                        data-sort_order="<?= (int)$d['sort_order']; ?>"
                        data-is_active="<?= (int)$d['is_active']; ?>">
                        <i class="ti ti-pencil"></i>
                    </button>
                    <?php endif; ?>
                    <?php if ($can_delete): ?>
                    <a href="<?= base_url('learn-flashcards/delete_deck/'.$d['id']); ?>" class="btn btn-sm btn-ghost-danger"
                       onclick="return confirm('Hapus deck ini beserta semua kartunya?')"><i class="ti ti-trash"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($decks)): ?>
        <div class="col-12 text-center text-secondary py-5"><i class="ti ti-cards fs-1 d-block mb-2"></i>Belum ada deck flashcard.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Add Deck -->
<div class="modal modal-blur fade" id="modalAddDeck" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?= form_open('learn-flashcards/store_deck'); ?>
            <div class="modal-header"><h5 class="modal-title">Tambah Deck</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><?php include __DIR__ . '/_deck_fields.php'; ?></div>
            <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<!-- Modal Edit Deck -->
<div class="modal modal-blur fade" id="modalEditDeck" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="" id="formEditDeck">
            <div class="modal-header"><h5 class="modal-title">Edit Deck</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><?php include __DIR__ . '/_deck_fields.php'; ?></div>
            <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Perbarui</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEditDeck').addEventListener('show.bs.modal', function (e) {
    var d = e.relatedTarget.dataset, f = document.getElementById('formEditDeck');
    f.action = '<?= base_url('learn-flashcards/update_deck/'); ?>' + d.id;
    f.querySelector('[name=code]').value           = d.code;
    f.querySelector('[name=name]').value           = d.name;
    f.querySelector('[name=description]').value     = d.description || '';
    f.querySelector('[name=subject_id]').value      = d.subject_id !== '0' ? d.subject_id : '';
    f.querySelector('[name=grade_level_id]').value  = d.grade_level_id !== '0' ? d.grade_level_id : '';
    f.querySelector('[name=icon]').value            = d.icon;
    f.querySelector('[name=color]').value           = d.color;
    f.querySelector('[name=sort_order]').value      = d.sort_order;
    f.querySelector('[name=is_active]').checked     = d.is_active === '1';
});
</script>
