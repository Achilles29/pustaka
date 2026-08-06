<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="<?= base_url('learn-flashcards'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Deck</a>
            </div>
            <div class="col">
                <div class="page-pretitle">Flashcard · <?= html_escape($deck['code']); ?></div>
                <h2 class="page-title">
                    <span class="avatar avatar-sm me-2" style="background:<?= html_escape($deck['color']); ?>1a;color:<?= html_escape($deck['color']); ?>"><i class="ti <?= html_escape($deck['icon']); ?>"></i></span>
                    <?= html_escape($deck['name']); ?>
                </h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="<?= base_url('belajar/flashcard/'.$deck['code']); ?>" target="_blank" class="btn btn-outline-primary">
                    <i class="ti ti-eye me-1"></i>Pratinjau Belajar
                </a>
                <?php if ($can_create): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddCard"><i class="ti ti-plus me-1"></i>Tambah Kartu</button>
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

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr><th style="width:50px">#</th><th>Depan (Istilah)</th><th>Belakang (Definisi)</th><th>Petunjuk</th><th class="text-center">Status</th><?php if ($can_edit || $can_delete): ?><th></th><?php endif; ?></tr>
                </thead>
                <tbody>
                    <?php foreach ($cards as $i => $c): ?>
                    <tr class="<?= !$c['is_active'] ? 'opacity-60' : ''; ?>">
                        <td class="text-secondary"><?= $i + 1; ?></td>
                        <td class="fw-medium"><?= html_escape($c['front']); ?></td>
                        <td class="text-secondary" style="max-width:360px"><?= html_escape($c['back']); ?></td>
                        <td class="text-secondary small"><?= html_escape($c['hint'] ?: '—'); ?></td>
                        <td class="text-center"><span class="badge <?= $c['is_active']?'bg-success-lt text-success':'bg-secondary-lt text-secondary'; ?>"><?= $c['is_active']?'Aktif':'Nonaktif'; ?></span></td>
                        <?php if ($can_edit || $can_delete): ?>
                        <td class="text-end">
                            <div class="btn-list flex-nowrap">
                                <?php if ($can_edit): ?>
                                <button class="btn btn-sm btn-ghost-secondary btn-edit-card"
                                    data-bs-toggle="modal" data-bs-target="#modalEditCard"
                                    data-id="<?= $c['id']; ?>"
                                    data-front="<?= html_escape($c['front']); ?>"
                                    data-back="<?= html_escape($c['back']); ?>"
                                    data-hint="<?= html_escape($c['hint']); ?>"
                                    data-sort_order="<?= (int)$c['sort_order']; ?>"
                                    data-is_active="<?= (int)$c['is_active']; ?>"><i class="ti ti-pencil"></i></button>
                                <?php endif; ?>
                                <?php if ($can_delete): ?>
                                <a href="<?= base_url('learn-flashcards/delete_card/'.$c['id']); ?>" class="btn btn-sm btn-ghost-danger" onclick="return confirm('Hapus kartu ini?')"><i class="ti ti-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cards)): ?>
                    <tr><td colspan="<?= ($can_edit||$can_delete)?6:5; ?>" class="text-center text-secondary py-5"><i class="ti ti-stack-2 fs-1 d-block mb-2"></i>Belum ada kartu. Tambahkan kartu pertama.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add Card -->
<div class="modal modal-blur fade" id="modalAddCard" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open('learn-flashcards/store_card/'.$deck['id']); ?>
            <div class="modal-header"><h5 class="modal-title">Tambah Kartu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><?php include __DIR__ . '/_card_fields.php'; ?></div>
            <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<!-- Modal Edit Card -->
<div class="modal modal-blur fade" id="modalEditCard" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" id="formEditCard">
            <div class="modal-header"><h5 class="modal-title">Edit Kartu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><?php include __DIR__ . '/_card_fields.php'; ?></div>
            <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Perbarui</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEditCard').addEventListener('show.bs.modal', function (e) {
    var d = e.relatedTarget.dataset, f = document.getElementById('formEditCard');
    f.action = '<?= base_url('learn-flashcards/update_card/'); ?>' + d.id;
    f.querySelector('[name=front]').value       = d.front;
    f.querySelector('[name=back]').value        = d.back;
    f.querySelector('[name=hint]').value        = d.hint || '';
    f.querySelector('[name=sort_order]').value  = d.sort_order;
    f.querySelector('[name=is_active]').checked = d.is_active === '1';
});
</script>
