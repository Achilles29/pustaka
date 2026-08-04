<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$can_manage = $this->can('learn_games.index', 'create');
$difficulty_labels = ['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit'];
?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pembelajaran</div>
                <h2 class="page-title">Kelola Konten Game</h2>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('belajar'); ?>" class="btn btn-outline-primary me-2" target="_blank">
                    <i class="ti ti-external-link me-1"></i>Lihat Arena Belajar
                </a>
                <?php if ($can_manage): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
                    <i class="ti ti-plus me-1"></i>Tambah Kategori
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible"><<?= html_escape($this->session->flashdata('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible"><?= html_escape($this->session->flashdata('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php
        $stat_items = [
            ['val' => $stats['game_types'], 'label' => 'Tipe Game Aktif', 'icon' => 'ti-puzzle', 'bg' => 'bg-purple-lt'],
            ['val' => $stats['categories'],  'label' => 'Kategori Konten',  'icon' => 'ti-folder', 'bg' => 'bg-blue-lt'],
            ['val' => $stats['sets'],        'label' => 'Set Konten',       'icon' => 'ti-stack', 'bg' => 'bg-green-lt'],
            ['val' => $stats['sessions'],    'label' => 'Sesi Dimainkan',   'icon' => 'ti-joystick', 'bg' => 'bg-yellow-lt'],
        ];
        ?>
        <?php foreach ($stat_items as $s): ?>
        <div class="col-6 col-md-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar <?= $s['bg']; ?>"><i class="ti <?= $s['icon']; ?>"></i></span></div>
                        <div class="col"><div class="fw-bold fs-3"><?= number_format($s['val']); ?></div><div class="text-secondary"><?= $s['label']; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tipe Game -->
    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title"><i class="ti ti-puzzle me-2"></i>Tipe Game</h3></div>
        <div class="row g-0">
            <?php foreach ($game_types as $gt): ?>
            <div class="col-12 col-md-4 border-end border-bottom">
                <div class="p-3 d-flex gap-3 align-items-center">
                    <div class="avatar avatar-lg" style="background:<?= html_escape($gt['color']); ?>1a;color:<?= html_escape($gt['color']); ?>">
                        <i class="<?= html_escape($gt['icon']); ?> fs-2"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-bold"><?= html_escape($gt['name']); ?></div>
                        <div class="text-secondary small"><?= html_escape(mb_substr($gt['description'], 0, 70)); ?></div>
                        <span class="badge mt-1 <?= $gt['is_active'] ? 'bg-success-lt text-success' : 'bg-secondary-lt'; ?>">
                            <?= $gt['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                        </span>
                    </div>
                    <?php if ($can_manage): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-ghost-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="<?= base_url('learn-games/toggle/' . $gt['id']); ?>"
                               onclick="return confirm('Ubah status tipe game?')">
                                <i class="ti ti-toggle-left me-1"></i><?= $gt['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kategori Konten -->
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title me-auto"><i class="ti ti-folder me-2"></i>Kategori Konten</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter table-sm">
                <thead>
                    <tr>
                        <th>Nama Kategori</th>
                        <th>Tipe Game</th>
                        <th>Jenjang</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <a href="<?= base_url('learn-games/content/' . $cat['id']); ?>" class="fw-medium text-decoration-none">
                                <?= html_escape($cat['name']); ?>
                            </a>
                            <?php if ($cat['description']): ?>
                            <div class="text-secondary small"><?= html_escape(mb_substr($cat['description'], 0, 60)); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= html_escape($cat['game_type_name']); ?></td>
                        <td><?= html_escape($cat['grade_label'] ?: '—'); ?></td>
                        <td><?= html_escape($cat['subject_name'] ?: '—'); ?></td>
                        <td class="text-center">
                            <span class="badge <?= $cat['is_active'] ? 'bg-success-lt text-success' : 'bg-secondary-lt'; ?>">
                                <?= $cat['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($can_manage): ?>
                            <div class="btn-list flex-nowrap">
                                <a href="<?= base_url('learn-games/content/' . $cat['id']); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-stack me-1"></i>Konten
                                </a>
                                <button class="btn btn-sm btn-ghost-secondary"
                                    data-bs-toggle="modal" data-bs-target="#modalEditCategory"
                                    data-id="<?= $cat['id']; ?>"
                                    data-game_type_id="<?= $cat['game_type_id']; ?>"
                                    data-grade_level_id="<?= $cat['grade_level_id']; ?>"
                                    data-subject_id="<?= $cat['subject_id']; ?>"
                                    data-name="<?= html_escape($cat['name']); ?>"
                                    data-description="<?= html_escape($cat['description']); ?>"
                                    data-is_active="<?= $cat['is_active']; ?>">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <a href="<?= base_url('learn-games/delete_category/' . $cat['id']); ?>"
                                   class="btn btn-sm btn-ghost-danger"
                                   onclick="return confirm('Hapus kategori ini? Semua set dan item akan ikut terhapus.')">
                                    <i class="ti ti-trash"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="6" class="text-center text-secondary py-5">Belum ada kategori. Tambah kategori untuk mulai membuat konten game.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Tambah Kategori -->
<div class="modal modal-blur fade" id="modalAddCategory" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <?= form_open('learn-games/store_category'); ?>
            <div class="modal-header"><h5 class="modal-title">Tambah Kategori Konten</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Tipe Game</label>
                    <select class="form-select" name="game_type_id" required>
                        <option value="">— Pilih Tipe Game —</option>
                        <?php foreach ($game_types as $gt): ?>
                        <option value="<?= $gt['id']; ?>"><?= html_escape($gt['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Nama Kategori</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="description" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Jenjang Kelas</label>
                        <select class="form-select" name="grade_level_id">
                            <option value="">— Semua Jenjang —</option>
                            <?php foreach ($grades as $g): ?>
                            <option value="<?= $g['id']; ?>"><?= html_escape($g['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Mata Pelajaran</label>
                        <select class="form-select" name="subject_id">
                            <option value="">— Semua Mapel —</option>
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id']; ?>"><?= html_escape($s['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                    <label class="form-check-label">Aktif</label>
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

<!-- Modal: Edit Kategori -->
<div class="modal modal-blur fade" id="modalEditCategory" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form method="post" action="" id="formEditCategory">
            <div class="modal-header"><h5 class="modal-title">Edit Kategori</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Tipe Game</label>
                    <select class="form-select" name="game_type_id" id="ec_game_type_id" required>
                        <?php foreach ($game_types as $gt): ?>
                        <option value="<?= $gt['id']; ?>"><?= html_escape($gt['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Nama</label>
                    <input type="text" class="form-control" name="name" id="ec_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="description" id="ec_description" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Jenjang</label>
                        <select class="form-select" name="grade_level_id" id="ec_grade">
                            <option value="">— Semua —</option>
                            <?php foreach ($grades as $g): ?>
                            <option value="<?= $g['id']; ?>"><?= html_escape($g['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Mapel</label>
                        <select class="form-select" name="subject_id" id="ec_subject">
                            <option value="">— Semua —</option>
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id']; ?>"><?= html_escape($s['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="ec_active">
                    <label class="form-check-label" for="ec_active">Aktif</label>
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
document.getElementById('modalEditCategory').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('formEditCategory').action = '<?= base_url('learn-games/update_category/'); ?>' + btn.dataset.id;
    document.getElementById('ec_game_type_id').value   = btn.dataset.game_type_id;
    document.getElementById('ec_name').value           = btn.dataset.name;
    document.getElementById('ec_description').value    = btn.dataset.description;
    document.getElementById('ec_grade').value          = btn.dataset.grade_level_id || '';
    document.getElementById('ec_subject').value        = btn.dataset.subject_id || '';
    document.getElementById('ec_active').checked       = btn.dataset.is_active === '1';
});
</script>
