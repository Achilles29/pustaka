<?php defined('BASEPATH') OR exit('No direct script access allowed');
$unit_labels = ['minutes' => 'menit', 'pages' => 'halaman', 'books' => 'buku'];
?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pembelajaran</div>
                <h2 class="page-title">Tukar Poin — Katalog Hadiah</h2>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <a href="<?= base_url('learn-rewards/redemptions'); ?>" class="btn btn-outline-primary">
                        <i class="ti ti-history me-1"></i>Riwayat Penukaran
                    </a>
                    <?php if ($can_create): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddReward">
                        <i class="ti ti-plus me-1"></i>Tambah Hadiah
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

    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="ti ti-info-circle fs-2 me-2"></i>
        <div>
            Member menukar <strong>poin belajar</strong> mereka dengan <strong>token baca digital</strong>.
            Saat ditukar, poin berkurang otomatis dan sebuah token baca aktif langsung terbit untuk member tersebut.
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body"><div class="row align-items-center">
                <div class="col-auto"><span class="bg-blue-lt avatar"><i class="ti ti-gift"></i></span></div>
                <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['rewards']; ?></div><div class="text-secondary">Total Hadiah</div></div>
            </div></div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body"><div class="row align-items-center">
                <div class="col-auto"><span class="bg-green-lt avatar"><i class="ti ti-circle-check"></i></span></div>
                <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['active_rewards']; ?></div><div class="text-secondary">Hadiah Aktif</div></div>
            </div></div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body"><div class="row align-items-center">
                <div class="col-auto"><span class="bg-purple-lt avatar"><i class="ti ti-arrows-exchange"></i></span></div>
                <div class="col"><div class="fw-bold fs-3"><?= number_format((int)$stats['redemptions']); ?></div><div class="text-secondary">Penukaran</div></div>
            </div></div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-sm"><div class="card-body"><div class="row align-items-center">
                <div class="col-auto"><span class="bg-orange-lt avatar"><i class="ti ti-coins"></i></span></div>
                <div class="col"><div class="fw-bold fs-3"><?= number_format((int)$stats['points_spent']); ?></div><div class="text-secondary">Poin Ditukar</div></div>
            </div></div></div>
        </div>
    </div>

    <!-- Catalog -->
    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title"><i class="ti ti-gift me-2"></i>Katalog Hadiah</h3></div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Hadiah</th>
                        <th class="text-center">Biaya Poin</th>
                        <th class="text-center">Kuota Token</th>
                        <th class="text-center">Berlaku</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Batas/User</th>
                        <th class="text-center">Ditukar</th>
                        <th class="text-center">Status</th>
                        <?php if ($can_edit || $can_delete): ?><th></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($catalog as $r): ?>
                    <tr class="<?= !$r['is_active'] ? 'opacity-60' : ''; ?>">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar" style="background:<?= html_escape($r['color']); ?>1a;color:<?= html_escape($r['color']); ?>">
                                    <i class="ti <?= html_escape($r['icon']); ?>"></i>
                                </span>
                                <div>
                                    <div class="fw-medium"><?= html_escape($r['name']); ?></div>
                                    <div class="text-secondary small"><code><?= html_escape($r['code']); ?></code></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-orange-lt text-orange fw-bold"><?= number_format((int)$r['cost_points']); ?> poin</span></td>
                        <td class="text-center"><?= (int)$r['quota_amount']; ?> <?= $unit_labels[$r['quota_unit']] ?? $r['quota_unit']; ?></td>
                        <td class="text-center"><?= (int)$r['token_validity_days'] > 0 ? (int)$r['token_validity_days'].' hari' : '∞'; ?></td>
                        <td class="text-center"><?= $r['stock'] === null ? '<span class="text-secondary">∞</span>' : (int)$r['stock']; ?></td>
                        <td class="text-center"><?= (int)$r['per_user_limit'] > 0 ? (int)$r['per_user_limit'].'x' : '<span class="text-secondary">∞</span>'; ?></td>
                        <td class="text-center"><?= number_format((int)$r['redeemed_count']); ?></td>
                        <td class="text-center">
                            <span class="badge <?= $r['is_active'] ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary'; ?>">
                                <?= $r['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                            </span>
                        </td>
                        <?php if ($can_edit || $can_delete): ?>
                        <td class="text-end">
                            <div class="btn-list flex-nowrap">
                                <?php if ($can_edit): ?>
                                <a href="<?= base_url('learn-rewards/toggle/'.$r['id']); ?>" class="btn btn-sm btn-ghost-secondary" title="<?= $r['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                    <i class="ti <?= $r['is_active'] ? 'ti-eye-off' : 'ti-eye'; ?>"></i>
                                </a>
                                <button class="btn btn-sm btn-ghost-secondary btn-edit-reward"
                                    data-bs-toggle="modal" data-bs-target="#modalEditReward"
                                    data-id="<?= $r['id']; ?>"
                                    data-code="<?= html_escape($r['code']); ?>"
                                    data-name="<?= html_escape($r['name']); ?>"
                                    data-description="<?= html_escape($r['description']); ?>"
                                    data-icon="<?= html_escape($r['icon']); ?>"
                                    data-color="<?= html_escape($r['color']); ?>"
                                    data-cost_points="<?= (int)$r['cost_points']; ?>"
                                    data-quota_amount="<?= (int)$r['quota_amount']; ?>"
                                    data-quota_unit="<?= html_escape($r['quota_unit']); ?>"
                                    data-token_validity_days="<?= (int)$r['token_validity_days']; ?>"
                                    data-stock="<?= $r['stock'] === null ? '' : (int)$r['stock']; ?>"
                                    data-per_user_limit="<?= (int)$r['per_user_limit']; ?>"
                                    data-sort_order="<?= (int)$r['sort_order']; ?>"
                                    data-is_active="<?= (int)$r['is_active']; ?>">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($can_delete): ?>
                                <a href="<?= base_url('learn-rewards/delete/'.$r['id']); ?>" class="btn btn-sm btn-ghost-danger"
                                   onclick="return confirm('Hapus hadiah <?= html_escape(addslashes($r['name'])); ?>? Riwayat penukaran tetap tersimpan.')">
                                    <i class="ti ti-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($catalog)): ?>
                    <tr><td colspan="<?= ($can_edit || $can_delete) ? 9 : 8; ?>" class="text-center text-secondary py-5">
                        <i class="ti ti-gift-off fs-1 d-block mb-2"></i>Belum ada hadiah. Tambahkan hadiah pertama.
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent redemptions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-history me-2"></i>Penukaran Terbaru</h3>
            <div class="card-actions">
                <a href="<?= base_url('learn-rewards/redemptions'); ?>" class="btn btn-sm btn-ghost-primary">Lihat semua</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr><th>Waktu</th><th>Member</th><th>Hadiah</th><th class="text-center">Poin</th><th>Token</th><th class="text-center">Status Token</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($redemptions as $rd): ?>
                    <tr>
                        <td class="text-secondary small"><?= date('d M Y H:i', strtotime($rd['created_at'])); ?></td>
                        <td>
                            <div class="fw-medium"><?= html_escape($rd['user_name'] ?: $rd['username'] ?: '—'); ?></div>
                            <?php if (!empty($rd['member_no'])): ?><div class="text-secondary small"><?= html_escape($rd['member_no']); ?></div><?php endif; ?>
                        </td>
                        <td><?= html_escape($rd['reward_name']); ?> <span class="text-secondary small">(<?= (int)$rd['quota_amount']; ?> <?= $unit_labels[$rd['quota_unit']] ?? $rd['quota_unit']; ?>)</span></td>
                        <td class="text-center"><span class="badge bg-orange-lt text-orange">−<?= number_format((int)$rd['cost_points']); ?></span></td>
                        <td><code class="small"><?= html_escape($rd['token_code']); ?></code></td>
                        <td class="text-center">
                            <?php
                            $ts = $rd['token_status'] ?? null;
                            $map = ['active'=>'bg-success-lt text-success','used'=>'bg-blue-lt text-blue','expired'=>'bg-secondary-lt text-secondary','revoked'=>'bg-red-lt text-red'];
                            ?>
                            <span class="badge <?= $map[$ts] ?? 'bg-secondary-lt'; ?>"><?= html_escape($ts ?: '—'); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($redemptions)): ?>
                    <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada penukaran.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Modal: Tambah Hadiah ─────────────────────────────── -->
<div class="modal modal-blur fade" id="modalAddReward" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?= form_open('learn-rewards/store'); ?>
            <div class="modal-header"><h5 class="modal-title">Tambah Hadiah</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?php include __DIR__ . '/_form_fields.php'; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<!-- ── Modal: Edit Hadiah ───────────────────────────────── -->
<div class="modal modal-blur fade" id="modalEditReward" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="" id="formEditReward">
            <div class="modal-header"><h5 class="modal-title">Edit Hadiah</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?php $edit = true; include __DIR__ . '/_form_fields.php'; ?>
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
document.getElementById('modalEditReward').addEventListener('show.bs.modal', function (e) {
    var b = e.relatedTarget, d = b.dataset;
    var f = document.getElementById('formEditReward');
    f.action = '<?= base_url('learn-rewards/update/'); ?>' + d.id;
    f.querySelector('[name=code]').value                = d.code;
    f.querySelector('[name=name]').value                = d.name;
    f.querySelector('[name=description]').value         = d.description || '';
    f.querySelector('[name=icon]').value                = d.icon;
    f.querySelector('[name=color]').value               = d.color;
    f.querySelector('[name=cost_points]').value         = d.cost_points;
    f.querySelector('[name=quota_amount]').value        = d.quota_amount;
    f.querySelector('[name=quota_unit]').value          = d.quota_unit;
    f.querySelector('[name=token_validity_days]').value = d.token_validity_days;
    f.querySelector('[name=stock]').value               = d.stock;
    f.querySelector('[name=per_user_limit]').value      = d.per_user_limit;
    f.querySelector('[name=sort_order]').value          = d.sort_order;
    f.querySelector('[name=is_active]').checked         = d.is_active === '1';
});
</script>
