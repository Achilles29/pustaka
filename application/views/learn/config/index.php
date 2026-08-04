<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $can_manage = $this->can('learn_config.index', 'create'); ?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pembelajaran</div>
                <h2 class="page-title">Konfigurasi Poin &amp; Lencana</h2>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <a href="<?= base_url('learn-config/leaderboard'); ?>" class="btn btn-outline-primary">
                        <i class="ti ti-trophy me-1"></i>Leaderboard
                    </a>
                    <?php if ($can_manage): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRule">
                        <i class="ti ti-plus me-1"></i>Tambah Aturan Poin
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <?= html_escape($this->session->flashdata('success')); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <?= html_escape($this->session->flashdata('error')); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-blue-lt avatar"><i class="ti ti-coin"></i></span></div>
                        <div class="col">
                            <div class="fw-bold fs-3"><?= $stats['rules']; ?></div>
                            <div class="text-secondary">Aturan Poin</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-yellow-lt avatar"><i class="ti ti-award"></i></span></div>
                        <div class="col">
                            <div class="fw-bold fs-3"><?= $stats['badges']; ?></div>
                            <div class="text-secondary">Jenis Lencana</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-green-lt avatar"><i class="ti ti-coins"></i></span></div>
                        <div class="col">
                            <div class="fw-bold fs-3"><?= number_format($stats['awarded']); ?></div>
                            <div class="text-secondary">Poin Dibagikan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-purple-lt avatar"><i class="ti ti-rosette"></i></span></div>
                        <div class="col">
                            <div class="fw-bold fs-3"><?= number_format($stats['badges_awarded']); ?></div>
                            <div class="text-secondary">Lencana Diperoleh</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-0" id="configTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-rules"><i class="ti ti-coin me-1"></i>Aturan Poin</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-badges" id="badges-tab-link"><i class="ti ti-award me-1"></i>Lencana</a></li>
    </ul>
    <div class="tab-content border border-top-0 rounded-bottom p-3 bg-white mb-4">

        <!-- ── TAB: Aturan Poin ─── -->
        <div class="tab-pane active" id="tab-rules">
            <div class="d-flex justify-content-end mb-2">
                <?php if ($can_manage): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRule">
                    <i class="ti ti-plus me-1"></i>Tambah
                </button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter table-sm">
                    <thead>
                        <tr>
                            <th>Kode Aksi</th>
                            <th>Label</th>
                            <th class="text-center">Poin</th>
                            <th class="text-center">Cooldown</th>
                            <th class="text-center">Status</th>
                            <?php if ($can_manage): ?><th></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rules as $rule): ?>
                        <tr>
                            <td><code><?= html_escape($rule['action_code']); ?></code></td>
                            <td>
                                <div class="fw-medium"><?= html_escape($rule['label']); ?></div>
                                <?php if ($rule['description']): ?>
                                <div class="text-secondary small"><?= html_escape($rule['description']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><span class="badge bg-blue-lt text-blue fw-bold"><?= (int)$rule['points']; ?> poin</span></td>
                            <td class="text-center"><?= $rule['cooldown_hours'] > 0 ? $rule['cooldown_hours'].' jam' : '—'; ?></td>
                            <td class="text-center">
                                <span class="badge <?= $rule['is_active'] ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary'; ?>">
                                    <?= $rule['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <?php if ($can_manage): ?>
                            <td class="text-end">
                                <div class="btn-list flex-nowrap">
                                    <button class="btn btn-sm btn-ghost-secondary"
                                        data-bs-toggle="modal" data-bs-target="#modalEditRule"
                                        data-id="<?= $rule['id']; ?>"
                                        data-action_code="<?= html_escape($rule['action_code']); ?>"
                                        data-label="<?= html_escape($rule['label']); ?>"
                                        data-description="<?= html_escape($rule['description']); ?>"
                                        data-points="<?= $rule['points']; ?>"
                                        data-cooldown_hours="<?= $rule['cooldown_hours']; ?>"
                                        data-is_active="<?= $rule['is_active']; ?>">
                                        <i class="ti ti-pencil"></i>
                                    </button>
                                    <a href="<?= base_url('learn-config/delete_rule/' . $rule['id']); ?>"
                                       class="btn btn-sm btn-ghost-danger"
                                       onclick="return confirm('Hapus aturan poin ini?')">
                                        <i class="ti ti-trash"></i>
                                    </a>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rules)): ?>
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada aturan poin.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── TAB: Lencana ─── -->
        <div class="tab-pane" id="tab-badges">
            <div class="d-flex justify-content-end mb-3">
                <?php if ($can_manage): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddBadge">
                    <i class="ti ti-plus me-1"></i>Tambah Lencana
                </button>
                <?php endif; ?>
            </div>
            <div class="row g-3">
                <?php foreach ($badges as $badge): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card card-sm <?= !$badge['is_active'] ? 'opacity-50' : ''; ?>">
                        <div class="card-body d-flex gap-3 align-items-start">
                            <div class="avatar avatar-lg" style="background:<?= html_escape($badge['color']); ?>1a;color:<?= html_escape($badge['color']); ?>">
                                <i class="<?= html_escape($badge['icon']); ?> fs-2"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="fw-bold"><?= html_escape($badge['name']); ?></div>
                                <div class="text-secondary small mb-1"><?= html_escape($badge['description']); ?></div>
                                <div class="d-flex gap-1 flex-wrap">
                                    <span class="badge bg-blue-lt text-blue"><?= html_escape($badge_criteria_labels[$badge['criteria_type']] ?? $badge['criteria_type']); ?></span>
                                    <span class="badge bg-orange-lt text-orange">&ge; <?= $badge['criteria_value']; ?></span>
                                    <?php if (!$badge['is_active']): ?>
                                    <span class="badge bg-secondary-lt">Nonaktif</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($can_manage): ?>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-ghost-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <button class="dropdown-item"
                                        data-bs-toggle="modal" data-bs-target="#modalEditBadge"
                                        data-id="<?= $badge['id']; ?>"
                                        data-code="<?= html_escape($badge['code']); ?>"
                                        data-name="<?= html_escape($badge['name']); ?>"
                                        data-description="<?= html_escape($badge['description']); ?>"
                                        data-icon="<?= html_escape($badge['icon']); ?>"
                                        data-color="<?= html_escape($badge['color']); ?>"
                                        data-criteria_type="<?= html_escape($badge['criteria_type']); ?>"
                                        data-criteria_value="<?= $badge['criteria_value']; ?>"
                                        data-is_active="<?= $badge['is_active']; ?>">
                                        <i class="ti ti-pencil me-1"></i>Edit
                                    </button>
                                    <a class="dropdown-item text-danger" href="<?= base_url('learn-config/delete_badge/' . $badge['id']); ?>"
                                       onclick="return confirm('Hapus lencana ini?')">
                                        <i class="ti ti-trash me-1"></i>Hapus
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($badges)): ?>
                <div class="col-12 text-center text-secondary py-5">Belum ada lencana.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal: Tambah Aturan Poin ─────────────────────── -->
<div class="modal modal-blur fade" id="modalAddRule" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <?= form_open('learn-config/store_rule'); ?>
            <div class="modal-header"><h5 class="modal-title">Tambah Aturan Poin</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Kode Aksi</label>
                    <input type="text" class="form-control font-monospace" name="action_code" placeholder="quiz.pass" required>
                    <small class="text-secondary">Huruf kecil, titik sebagai pemisah. Contoh: <code>quiz.pass</code>, <code>game.complete</code></small>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Label</label>
                    <input type="text" class="form-control" name="label" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="description" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label required">Jumlah Poin</label>
                        <input type="number" class="form-control" name="points" value="10" min="0" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Cooldown (jam)</label>
                        <input type="number" class="form-control" name="cooldown_hours" value="0" min="0">
                        <small class="text-secondary">0 = tidak ada batas</small>
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="ar_active" checked>
                    <label class="form-check-label" for="ar_active">Aktif</label>
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

<!-- ── Modal: Edit Aturan Poin ───────────────────────── -->
<div class="modal modal-blur fade" id="modalEditRule" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form method="post" action="" id="formEditRule">
            <div class="modal-header"><h5 class="modal-title">Edit Aturan Poin</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Kode Aksi</label>
                    <input type="text" class="form-control font-monospace" name="action_code" id="er_action_code" required>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Label</label>
                    <input type="text" class="form-control" name="label" id="er_label" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="description" id="er_description" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label required">Poin</label>
                        <input type="number" class="form-control" name="points" id="er_points" min="0" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Cooldown (jam)</label>
                        <input type="number" class="form-control" name="cooldown_hours" id="er_cooldown" min="0">
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="er_active">
                    <label class="form-check-label" for="er_active">Aktif</label>
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

<!-- ── Modal: Tambah Lencana ─────────────────────────── -->
<div class="modal modal-blur fade" id="modalAddBadge" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <?= form_open('learn-config/store_badge'); ?>
            <div class="modal-header"><h5 class="modal-title">Tambah Lencana</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label required">Kode</label>
                        <input type="text" class="form-control font-monospace" name="code" placeholder="quiz_pass_5" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label required">Nama</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="description" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Ikon (Tabler)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ab_icon ti-award"></i></span>
                            <input type="text" class="form-control" name="icon" id="ab_icon_input" value="ti-award"
                                   oninput="document.querySelector('.ab_icon').className='ab_icon ti '+this.value">
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Warna</label>
                        <input type="color" class="form-control form-control-color w-100" name="color" value="#3b82f6">
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label required">Kriteria</label>
                        <select class="form-select" name="criteria_type" required>
                            <?php foreach ($badge_criteria_labels as $k => $v): ?>
                            <option value="<?= $k; ?>"><?= html_escape($v); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label required">Threshold</label>
                        <input type="number" class="form-control" name="criteria_value" value="1" min="1" required>
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

<!-- ── Modal: Edit Lencana ───────────────────────────── -->
<div class="modal modal-blur fade" id="modalEditBadge" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form method="post" action="" id="formEditBadge">
            <div class="modal-header"><h5 class="modal-title">Edit Lencana</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label required">Kode</label>
                        <input type="text" class="form-control font-monospace" name="code" id="eb_code" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label required">Nama</label>
                        <input type="text" class="form-control" name="name" id="eb_name" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="description" id="eb_description" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Ikon</label>
                        <input type="text" class="form-control" name="icon" id="eb_icon">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Warna</label>
                        <input type="color" class="form-control form-control-color w-100" name="color" id="eb_color">
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label required">Kriteria</label>
                        <select class="form-select" name="criteria_type" id="eb_criteria_type" required>
                            <?php foreach ($badge_criteria_labels as $k => $v): ?>
                            <option value="<?= $k; ?>"><?= html_escape($v); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label required">Threshold</label>
                        <input type="number" class="form-control" name="criteria_value" id="eb_criteria_value" min="1" required>
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="eb_active">
                    <label class="form-check-label" for="eb_active">Aktif</label>
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
// Activate badge tab if URL hash = #badges
if (window.location.hash === '#badges') {
    document.getElementById('badges-tab-link').click();
}

// Edit Rule modal populate
document.getElementById('modalEditRule').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    var id  = btn.dataset.id;
    document.getElementById('formEditRule').action = '<?= base_url('learn-config/update_rule/'); ?>' + id;
    document.getElementById('er_action_code').value   = btn.dataset.action_code;
    document.getElementById('er_label').value         = btn.dataset.label;
    document.getElementById('er_description').value   = btn.dataset.description;
    document.getElementById('er_points').value        = btn.dataset.points;
    document.getElementById('er_cooldown').value      = btn.dataset.cooldown_hours;
    document.getElementById('er_active').checked      = btn.dataset.is_active === '1';
});

// Edit Badge modal populate
document.getElementById('modalEditBadge').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    var id  = btn.dataset.id;
    document.getElementById('formEditBadge').action       = '<?= base_url('learn-config/update_badge/'); ?>' + id;
    document.getElementById('eb_code').value              = btn.dataset.code;
    document.getElementById('eb_name').value              = btn.dataset.name;
    document.getElementById('eb_description').value       = btn.dataset.description;
    document.getElementById('eb_icon').value              = btn.dataset.icon;
    document.getElementById('eb_color').value             = btn.dataset.color;
    document.getElementById('eb_criteria_type').value     = btn.dataset.criteria_type;
    document.getElementById('eb_criteria_value').value    = btn.dataset.criteria_value;
    document.getElementById('eb_active').checked          = btn.dataset.is_active === '1';
});
</script>
