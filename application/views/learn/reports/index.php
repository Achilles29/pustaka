<?php defined('BASEPATH') OR exit('No direct script access allowed');
$page_url = function($p) use ($q) { return base_url('learn-reports?' . http_build_query(['q' => $q, 'page' => $p])); };
?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pembelajaran</div>
                <h2 class="page-title">Raport Belajar Member</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <?= form_open('learn-reports', ['method' => 'get', 'class' => 'd-flex gap-2']); ?>
            <input type="text" name="q" class="form-control" value="<?= html_escape($q); ?>" placeholder="Cari nama, no. anggota, atau username...">
            <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Cari</button>
            <?php if ($q !== ''): ?><a href="<?= base_url('learn-reports'); ?>" class="btn btn-outline-secondary">Reset</a><?php endif; ?>
            <?= form_close(); ?>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Nama</th><th>No. Anggota</th><th>Username</th><th class="w-1"></th></tr></thead>
                <tbody>
                    <?php foreach ($members as $m): ?>
                    <tr>
                        <td class="fw-medium"><?= html_escape($m['full_name']); ?></td>
                        <td><?= html_escape($m['member_no'] ?: '—'); ?></td>
                        <td class="text-secondary"><?= html_escape($m['username']); ?></td>
                        <td>
                            <a href="<?= base_url('learn-reports/view/'.$m['user_id']); ?>" target="_blank" class="btn btn-sm btn-primary">
                                <i class="ti ti-report me-1"></i>Lihat Raport
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($members)): ?>
                    <tr><td colspan="4" class="text-center text-secondary py-5">
                        <i class="ti ti-user-search fs-1 d-block mb-2"></i>
                        <?= $q !== '' ? 'Tidak ada member cocok dengan pencarian.' : 'Ketik kata kunci untuk mencari member.'; ?>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary small"><?= number_format($pagination['total_rows']); ?> member</p>
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
