<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pembelajaran</div>
                <h2 class="page-title">Leaderboard Poin</h2>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('learn-config'); ?>" class="btn btn-outline-secondary me-2">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
                <div class="btn-group">
                    <?php foreach ([7 => '7 Hari', 30 => '30 Hari', 90 => '3 Bulan'] as $d => $label): ?>
                    <a href="?days=<?= $d; ?>" class="btn btn-sm <?= $days === $d ? 'btn-primary' : 'btn-outline-primary'; ?>"><?= $label; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top <?= count($leaderboard); ?> Pemustaka — <?= $days; ?> Hari Terakhir</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                    <tr>
                        <th class="text-center" width="60">Rank</th>
                        <th>Nama</th>
                        <th class="text-center">Total Poin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $i => $row): ?>
                    <tr>
                        <td class="text-center">
                            <?php if ($i === 0): ?>
                            <span class="text-yellow fw-bold fs-4">🥇</span>
                            <?php elseif ($i === 1): ?>
                            <span class="text-secondary fw-bold fs-4">🥈</span>
                            <?php elseif ($i === 2): ?>
                            <span class="text-orange fw-bold fs-4">🥉</span>
                            <?php else: ?>
                            <span class="text-secondary"><?= $i + 1; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-medium"><?= html_escape($row['full_name'] ?: $row['username']); ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-blue-lt text-blue fw-bold fs-6"><?= number_format($row['total_points']); ?> poin</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($leaderboard)): ?>
                    <tr><td colspan="3" class="text-center text-secondary py-5">Belum ada data poin untuk periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
