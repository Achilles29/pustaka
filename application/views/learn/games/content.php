<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$can_manage = $this->can('learn_games.index', 'create');
$difficulty_labels = ['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit'];
$difficulty_colors = ['easy' => 'bg-success-lt text-success', 'medium' => 'bg-yellow-lt text-yellow', 'hard' => 'bg-red-lt text-red'];
?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="<?= base_url('learn-games'); ?>">Kelola Game</a>
                    <span class="mx-1">›</span> Set Konten
                </div>
                <h2 class="page-title"><?= html_escape($category['name']); ?></h2>
                <div class="text-secondary"><?= html_escape($category['game_type_name']); ?></div>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('learn-games'); ?>" class="btn btn-outline-secondary me-2">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
                <?php if ($can_manage): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddSet">
                    <i class="ti ti-plus me-1"></i>Tambah Set
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

    <div class="row g-3">
        <?php foreach ($sets as $set): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 <?= !$set['is_active'] ? 'opacity-60' : ''; ?>">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <div class="flex-fill">
                            <h4 class="mb-1"><?= html_escape($set['name']); ?></h4>
                            <span class="badge <?= $difficulty_colors[$set['difficulty']] ?? 'bg-secondary-lt'; ?>">
                                <?= $difficulty_labels[$set['difficulty']] ?? $set['difficulty']; ?>
                            </span>
                            <?php if (!$set['is_active']): ?>
                            <span class="badge bg-secondary-lt ms-1">Nonaktif</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-secondary small mb-3">
                        <i class="ti ti-list me-1"></i><?= (int)$set['item_count']; ?> item
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('learn-games/items/' . $set['id']); ?>" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="ti ti-list me-1"></i>Kelola Item
                        </a>
                        <?php if ($can_manage): ?>
                        <button class="btn btn-sm btn-ghost-secondary"
                            data-bs-toggle="modal" data-bs-target="#modalEditSet"
                            data-id="<?= $set['id']; ?>"
                            data-name="<?= html_escape($set['name']); ?>"
                            data-difficulty="<?= $set['difficulty']; ?>"
                            data-is_active="<?= $set['is_active']; ?>">
                            <i class="ti ti-pencil"></i>
                        </button>
                        <a href="<?= base_url('learn-games/delete_set/' . $set['id']); ?>"
                           class="btn btn-sm btn-ghost-danger"
                           onclick="return confirm('Hapus set ini dan semua itemnya?')">
                            <i class="ti ti-trash"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($sets)): ?>
        <div class="col-12">
            <div class="empty py-5">
                <div class="empty-icon"><i class="ti ti-stack-2 fs-1 text-secondary"></i></div>
                <p class="empty-title">Belum ada set konten</p>
                <p class="empty-subtitle">Tambah set konten untuk mulai membuat pasangan kata atau item game.</p>
                <?php if ($can_manage): ?>
                <div class="empty-action">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddSet">
                        <i class="ti ti-plus me-1"></i>Tambah Set Pertama
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Tambah Set -->
<div class="modal modal-blur fade" id="modalAddSet" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open('learn-games/store_set/' . $category['id']); ?>
            <div class="modal-header"><h5 class="modal-title">Tambah Set Konten</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Nama Set</label>
                    <input type="text" class="form-control" name="name" placeholder="Contoh: Kosakata Tubuh Manusia" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tingkat Kesulitan</label>
                    <select class="form-select" name="difficulty">
                        <?php foreach ($difficulty_labels as $k => $v): ?>
                        <option value="<?= $k; ?>"><?= $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                    <label class="form-check-label">Aktif (langsung bisa dimainkan)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Set &rarr; Tambah Item</button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<!-- Modal: Edit Set -->
<div class="modal modal-blur fade" id="modalEditSet" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" id="formEditSet">
            <div class="modal-header"><h5 class="modal-title">Edit Set</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Nama</label>
                    <input type="text" class="form-control" name="name" id="es_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kesulitan</label>
                    <select class="form-select" name="difficulty" id="es_difficulty">
                        <?php foreach ($difficulty_labels as $k => $v): ?>
                        <option value="<?= $k; ?>"><?= $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="es_active">
                    <label class="form-check-label" for="es_active">Aktif</label>
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
document.getElementById('modalEditSet').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('formEditSet').action = '<?= base_url('learn-games/update_set/'); ?>' + btn.dataset.id;
    document.getElementById('es_name').value       = btn.dataset.name;
    document.getElementById('es_difficulty').value = btn.dataset.difficulty;
    document.getElementById('es_active').checked   = btn.dataset.is_active === '1';
});
</script>
