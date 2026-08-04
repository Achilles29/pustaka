<?php defined('BASEPATH') OR exit('No direct script access allowed');
$unit_labels = ['minutes' => 'menit', 'pages' => 'halaman', 'books' => 'buku'];
$status_map  = ['active'=>'bg-success-lt text-success','used'=>'bg-blue-lt text-blue','expired'=>'bg-secondary-lt text-secondary','revoked'=>'bg-red-lt text-red'];
$page_url = function($p) { return base_url('learn-rewards/redemptions?page=' . $p); };
?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="<?= base_url('learn-rewards'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
            </div>
            <div class="col">
                <div class="page-pretitle">Pembelajaran · Tukar Poin</div>
                <h2 class="page-title">Riwayat Penukaran Poin</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>#</th><th>Waktu</th><th>Member</th><th>Hadiah</th>
                        <th class="text-center">Poin</th><th class="text-center">Kuota</th>
                        <th>Token</th><th class="text-center">Status</th><th>Kadaluarsa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($redemptions as $rd): ?>
                    <tr>
                        <td class="text-secondary"><?= (int)$rd['id']; ?></td>
                        <td class="text-secondary small"><?= date('d M Y H:i', strtotime($rd['created_at'])); ?></td>
                        <td>
                            <div class="fw-medium"><?= html_escape($rd['user_name'] ?: $rd['username'] ?: '—'); ?></div>
                            <?php if (!empty($rd['member_no'])): ?><div class="text-secondary small"><?= html_escape($rd['member_no']); ?></div><?php endif; ?>
                        </td>
                        <td><?= html_escape($rd['reward_name']); ?></td>
                        <td class="text-center"><span class="badge bg-orange-lt text-orange">−<?= number_format((int)$rd['cost_points']); ?></span></td>
                        <td class="text-center"><?= (int)$rd['quota_amount']; ?> <?= $unit_labels[$rd['quota_unit']] ?? $rd['quota_unit']; ?></td>
                        <td><code class="small"><?= html_escape($rd['token_code']); ?></code></td>
                        <td class="text-center">
                            <span class="badge <?= $status_map[$rd['token_status'] ?? ''] ?? 'bg-secondary-lt'; ?>"><?= html_escape($rd['token_status'] ?: '—'); ?></span>
                        </td>
                        <td class="text-secondary small"><?= !empty($rd['expires_at']) ? date('d M Y', strtotime($rd['expires_at'])) : '∞'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($redemptions)): ?>
                    <tr><td colspan="9" class="text-center text-secondary py-5"><i class="ti ti-inbox fs-1 d-block mb-2"></i>Belum ada penukaran.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary small">Total <?= number_format($pagination['total_rows']); ?> penukaran</p>
            <ul class="pagination m-0 ms-auto">
                <?php if ($pagination['page'] > 1): ?><li class="page-item"><a class="page-link" href="<?= $page_url($pagination['page']-1); ?>"><i class="ti ti-chevron-left"></i></a></li><?php endif; ?>
                <?php for ($p = max(1,$pagination['page']-2); $p <= min($pagination['total_pages'],$pagination['page']+2); $p++): ?>
                    <li class="page-item <?= $p===$pagination['page']?'active':''; ?>"><a class="page-link" href="<?= $page_url($p); ?>"><?= $p; ?></a></li>
                <?php endfor; ?>
                <?php if ($pagination['page'] < $pagination['total_pages']): ?><li class="page-item"><a class="page-link" href="<?= $page_url($pagination['page']+1); ?>"><i class="ti ti-chevron-right"></i></a></li><?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>
