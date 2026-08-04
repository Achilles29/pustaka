<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $can_manage = $this->can('learn_games.index', 'create'); ?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="<?= base_url('learn-games'); ?>">Kelola Game</a>
                    <span class="mx-1">›</span>
                    <a href="<?= base_url('learn-games/content/' . $set['category_id']); ?>">Set Konten</a>
                    <span class="mx-1">›</span> Item
                </div>
                <h2 class="page-title"><?= html_escape($set['name']); ?></h2>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('learn-games/content/' . $set['category_id']); ?>" class="btn btn-outline-secondary me-2">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
                <?php if ($can_manage): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddItem">
                    <i class="ti ti-plus me-1"></i>Tambah Item
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

    <?php if (count($items) < 4): ?>
    <div class="alert alert-warning mb-3">
        <i class="ti ti-alert-triangle me-1"></i>
        Untuk Memory Match minimal dibutuhkan <strong>4 item</strong>. Saat ini baru ada <?= count($items); ?> item.
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= count($items); ?> Item dalam Set Ini</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Istilah / Sisi A</th>
                        <th>Definisi / Sisi B</th>
                        <th class="text-center" width="120">Urutan</th>
                        <?php if ($can_manage): ?><th width="100"></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td class="text-secondary"><?= $i + 1; ?></td>
                        <td>
                            <div class="fw-medium"><?= html_escape($item['term']); ?></div>
                            <?php if ($item['term_image_url']): ?>
                            <img src="<?= html_escape($item['term_image_url']); ?>" alt="term" class="mt-1" style="max-height:40px;border-radius:4px">
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?= html_escape($item['definition']); ?></div>
                            <?php if ($item['def_image_url']): ?>
                            <img src="<?= html_escape($item['def_image_url']); ?>" alt="def" class="mt-1" style="max-height:40px;border-radius:4px">
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $item['sort_order']; ?></td>
                        <?php if ($can_manage): ?>
                        <td class="text-end">
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-sm btn-ghost-secondary"
                                    data-bs-toggle="modal" data-bs-target="#modalEditItem"
                                    data-id="<?= $item['id']; ?>"
                                    data-term="<?= html_escape($item['term']); ?>"
                                    data-definition="<?= html_escape($item['definition']); ?>"
                                    data-sort_order="<?= $item['sort_order']; ?>">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <a href="<?= base_url('learn-games/delete_item/' . $item['id']); ?>"
                                   class="btn btn-sm btn-ghost-danger"
                                   onclick="return confirm('Hapus item ini?')">
                                    <i class="ti ti-trash"></i>
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                    <tr><td colspan="5" class="text-center text-secondary py-5">Belum ada item. Klik "Tambah Item" untuk mulai.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Tambah Item -->
<div class="modal modal-blur fade" id="modalAddItem" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open('learn-games/store_item/' . $set['id']); ?>
            <div class="modal-header"><h5 class="modal-title">Tambah Pasangan Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Sisi A — Istilah / Pertanyaan</label>
                    <textarea class="form-control" name="term" rows="2" placeholder="Contoh: Fotosintesis" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Sisi B — Definisi / Jawaban</label>
                    <textarea class="form-control" name="definition" rows="2" placeholder="Contoh: Proses tumbuhan mengubah sinar matahari menjadi energi" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Urutan (opsional)</label>
                    <input type="number" class="form-control" name="sort_order" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<!-- Modal: Edit Item -->
<div class="modal modal-blur fade" id="modalEditItem" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" id="formEditItem">
            <div class="modal-header"><h5 class="modal-title">Edit Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Sisi A</label>
                    <textarea class="form-control" name="term" id="ei_term" rows="2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Sisi B</label>
                    <textarea class="form-control" name="definition" id="ei_definition" rows="2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Urutan</label>
                    <input type="number" class="form-control" name="sort_order" id="ei_sort_order" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Perbarui</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEditItem').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('formEditItem').action = '<?= base_url('learn-games/update_item/'); ?>' + btn.dataset.id;
    document.getElementById('ei_term').value        = btn.dataset.term;
    document.getElementById('ei_definition').value  = btn.dataset.definition;
    document.getElementById('ei_sort_order').value  = btn.dataset.sort_order;
});
</script>
