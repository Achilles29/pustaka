<?php defined('BASEPATH') OR exit('No direct script access allowed');
$difficulty_labels = ['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit'];
$difficulty_colors = ['easy' => 'bg-success', 'medium' => 'bg-warning text-dark', 'hard' => 'bg-danger'];
$type_labels       = ['multiple_choice' => 'Pilihan Ganda', 'essay' => 'Essay'];
$type_icons        = ['multiple_choice' => 'ti-checkbox', 'essay' => 'ti-writing'];
$page_url = function($page) use ($filters) {
    return base_url('quiz-bank?' . http_build_query(array_merge($filters, ['page' => $page])));
};
?>
<style>
.bank-usage-bar { height: 4px; border-radius: 2px; background: var(--tblr-border-color); margin-top: 3px; }
.bank-usage-fill { height: 100%; border-radius: 2px; background: var(--tblr-primary); transition: width .3s; }
.bulk-toolbar {
    background: var(--tblr-primary); color: #fff; border-radius: .5rem .5rem 0 0;
    padding: .6rem 1rem; display: none; align-items: center; gap: 1rem;
    font-weight: 600; font-size: .875rem;
}
.bulk-toolbar.visible { display: flex; }
.bulk-toolbar .btn { color: #fff; border-color: rgba(255,255,255,.4); }
.bulk-toolbar .btn:hover { background: rgba(255,255,255,.15); }
tr.row-selected { background: var(--tblr-primary-lt) !important; }
.question-preview { max-width: 360px; }
.question-preview .q-text { font-size: .875rem; line-height: 1.4; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.usage-badge { font-size: .7rem; font-weight: 600; }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Modul Pembelajaran</div>
                <h1 class="page-title">Bank Soal</h1>
            </div>
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <a href="<?= base_url('quiz-bank/template'); ?>" class="btn btn-outline-secondary" title="Unduh template CSV">
                        <i class="ti ti-download me-1"></i>Template CSV
                    </a>
                    <a href="<?= base_url('quiz-bank/import'); ?>" class="btn btn-outline-primary">
                        <i class="ti ti-file-import me-1"></i>Import CSV
                    </a>
                    <?php if ($can_create): ?>
                    <a href="<?= base_url('quiz-bank/create'); ?>" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>Tambah Soal
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if ($s = $this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($s); ?></div>
        <?php endif; ?>
        <?php if ($e = $this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($e); ?></div>
        <?php endif; ?>

        <!-- Stats Ribbon -->
        <div class="row g-2 mb-3">
            <?php
            $stat_cards = [
                ['val' => $stats['total'], 'label' => 'Total Soal',     'icon' => 'ti-list-check',     'color' => 'primary'],
                ['val' => $stats['mc'],    'label' => 'Pilihan Ganda',  'icon' => 'ti-checkbox',       'color' => 'blue'],
                ['val' => $stats['essay'], 'label' => 'Essay',          'icon' => 'ti-writing',        'color' => 'purple'],
                ['val' => $stats['easy'],  'label' => 'Mudah',          'icon' => 'ti-mood-smile',     'color' => 'green'],
                ['val' => $stats['med'],   'label' => 'Sedang',         'icon' => 'ti-mood-neutral',   'color' => 'yellow'],
                ['val' => $stats['hard'],  'label' => 'Sulit',          'icon' => 'ti-mood-confuzed',  'color' => 'red'],
            ];
            foreach ($stat_cards as $sc): ?>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-<?= $sc['color']; ?> fs-2"><i class="ti <?= $sc['icon']; ?>"></i></span>
                            <div>
                                <div class="h3 mb-0"><?= number_format((int)$sc['val'], 0, ',', '.'); ?></div>
                                <div class="text-secondary small"><?= $sc['label']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card admin-card data-workspace">
            <!-- Filter Bar -->
            <div class="card-header workspace-header flex-wrap gap-2">
                <?= form_open('quiz-bank', ['method' => 'get', 'class' => 'd-flex flex-wrap gap-2 align-items-end w-100']); ?>
                <div style="min-width:180px;flex:1">
                    <input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>"
                           placeholder="Cari teks soal...">
                </div>
                <div style="min-width:130px">
                    <select class="form-select" name="subject_id">
                        <option value="">Semua Mapel</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id']; ?>" <?= (int)($filters['subject_id'] ?? 0) === (int)$s['id'] ? 'selected' : ''; ?>>
                                <?= html_escape($s['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:130px">
                    <select class="form-select" name="grade_level_id">
                        <option value="">Semua Jenjang</option>
                        <?php foreach ($grades as $g): ?>
                            <option value="<?= $g['id']; ?>" <?= (int)($filters['grade_level_id'] ?? 0) === (int)$g['id'] ? 'selected' : ''; ?>>
                                <?= html_escape($g['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:110px">
                    <select class="form-select" name="type">
                        <option value="">Semua Tipe</option>
                        <?php foreach ($type_labels as $k => $v): ?>
                            <option value="<?= $k; ?>" <?= ($filters['type'] ?? '') === $k ? 'selected' : ''; ?>><?= $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:100px">
                    <select class="form-select" name="difficulty">
                        <option value="">Semua Tingkat</option>
                        <?php foreach ($difficulty_labels as $k => $v): ?>
                            <option value="<?= $k; ?>" <?= ($filters['difficulty'] ?? '') === $k ? 'selected' : ''; ?>><?= $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:90px">
                    <select class="form-select" name="is_active">
                        <option value="">Semua Status</option>
                        <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
                <div style="min-width:80px">
                    <select class="form-select" name="per_page">
                        <?php foreach ([10, 25, 50, 100] as $pp): ?>
                            <option value="<?= $pp; ?>" <?= (int)($filters['per_page'] ?? 25) === $pp ? 'selected' : ''; ?>><?= $pp; ?>/hal</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i></button>
                    <a href="<?= base_url('quiz-bank'); ?>" class="btn btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
                <?= form_close(); ?>
            </div>

            <?php if ($can_delete): ?>
            <!-- Bulk Action Toolbar (hidden until rows selected) -->
            <div class="bulk-toolbar" id="bulk-toolbar">
                <i class="ti ti-checkbox fs-4"></i>
                <span id="bulk-count">0 soal dipilih</span>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="clearSelection()">
                        <i class="ti ti-x me-1"></i>Batalkan
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmBulkDelete()">
                        <i class="ti ti-trash me-1"></i>Hapus Terpilih
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <?php if ($can_delete): ?>
                            <th class="w-1 ps-3">
                                <input type="checkbox" id="select-all" class="form-check-input" title="Pilih semua">
                            </th>
                            <?php endif; ?>
                            <th class="text-secondary" style="width:50px">#</th>
                            <th>Soal</th>
                            <th style="width:120px">Mapel</th>
                            <th style="width:100px">Jenjang</th>
                            <th style="width:80px">Tipe</th>
                            <th style="width:80px">Tingkat</th>
                            <th style="width:90px" title="Berapa kali soal ini dipakai di sesi/kompetisi">
                                Pemakaian <i class="ti ti-info-circle text-muted" style="font-size:.75rem"></i>
                            </th>
                            <th style="width:60px">Aktif</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody id="bank-table-body">
                    <?php if (empty($questions)): ?>
                        <tr>
                            <td colspan="<?= $can_delete ? 10 : 9; ?>" class="text-center text-secondary py-5">
                                <i class="ti ti-inbox fs-1 d-block mb-2"></i>
                                Tidak ada soal ditemukan.<br>
                                <a href="<?= base_url('quiz-bank/create'); ?>" class="btn btn-sm btn-primary mt-2">
                                    <i class="ti ti-plus me-1"></i>Tambah Soal Pertama
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php
                    $max_usage = 0;
                    foreach ($questions as $q) { $max_usage = max($max_usage, (int)($q['usage_count'] ?? 0)); }
                    $max_usage = max(1, $max_usage);
                    foreach ($questions as $q):
                        $usage = (int)($q['usage_count'] ?? 0);
                        $usage_pct = min(100, round($usage / $max_usage * 100));
                    ?>
                    <tr data-id="<?= $q['id']; ?>" class="bank-row">
                        <?php if ($can_delete): ?>
                        <td class="ps-3">
                            <input type="checkbox" class="form-check-input row-check" value="<?= $q['id']; ?>"
                                   onchange="onRowCheck(this)">
                        </td>
                        <?php endif; ?>
                        <td class="text-secondary small"><?= $q['id']; ?></td>
                        <td class="question-preview">
                            <div class="q-text text-body" title="<?= html_escape(strip_tags($q['question_text'])); ?>">
                                <?= html_escape(mb_substr(strip_tags($q['question_text']), 0, 120)); ?>
                            </div>
                            <?php if (! empty($q['tags'])): ?>
                            <div class="mt-1">
                                <?php foreach (array_slice((array) explode(',', $q['tags'] ?? ''), 0, 3) as $tag): ?>
                                    <?php if (trim($tag)): ?>
                                    <span class="badge bg-azure-lt text-azure" style="font-size:.65rem"><?= html_escape(trim($tag)); ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (! empty($q['subject_name'])): ?>
                            <span class="badge text-white" style="background:<?= html_escape($q['subject_color'] ?? '#607d8b'); ?>; font-size:.72rem">
                                <?= html_escape($q['subject_name']); ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-secondary"><?= html_escape($q['grade_name'] ?? '—'); ?></td>
                        <td>
                            <span class="text-secondary" title="<?= $type_labels[$q['type']] ?? $q['type']; ?>">
                                <i class="ti <?= $type_icons[$q['type']] ?? 'ti-file'; ?> fs-4"></i>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $difficulty_colors[$q['difficulty']] ?? 'bg-secondary'; ?>" style="font-size:.72rem">
                                <?= $difficulty_labels[$q['difficulty']] ?? $q['difficulty']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <?php if ($usage > 0): ?>
                                <span class="usage-badge badge bg-blue-lt text-blue" title="Dipakai di <?= $usage; ?> sesi/kompetisi">
                                    <?= $usage; ?>x
                                </span>
                                <?php else: ?>
                                <span class="text-muted small">0x</span>
                                <?php endif; ?>
                            </div>
                            <div class="bank-usage-bar" style="width:60px">
                                <div class="bank-usage-fill" style="width:<?= $usage_pct; ?>%"></div>
                            </div>
                        </td>
                        <td>
                            <?php if ($q['is_active']): ?>
                                <span class="badge bg-success-lt text-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-lt text-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <?php if ($can_edit): ?>
                                <a href="<?= base_url('quiz-bank/edit/'.$q['id']); ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($can_delete): ?>
                                <a href="<?= base_url('quiz-bank/delete/'.$q['id']); ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   title="Hapus"
                                   onclick="return confirm('Hapus soal #<?= $q['id']; ?>? Tindakan ini tidak bisa dibatalkan.')">
                                    <i class="ti ti-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer / Pagination -->
            <div class="card-footer d-flex align-items-center flex-wrap gap-2">
                <p class="m-0 text-secondary small">
                    Menampilkan
                    <strong><?= number_format($pagination['offset'] + 1, 0, ',', '.'); ?></strong>–<strong><?= number_format(min($pagination['offset'] + $pagination['per_page'], $pagination['total_rows']), 0, ',', '.'); ?></strong>
                    dari <strong><?= number_format($pagination['total_rows'], 0, ',', '.'); ?></strong> soal
                </p>
                <?php if ($pagination['total_pages'] > 1): ?>
                <ul class="pagination m-0 ms-auto">
                    <?php if ($pagination['page'] > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= $page_url($pagination['page'] - 1); ?>"><i class="ti ti-chevron-left"></i></a></li>
                    <?php endif; ?>
                    <?php for ($p = max(1, $pagination['page'] - 2); $p <= min($pagination['total_pages'], $pagination['page'] + 2); $p++): ?>
                        <li class="page-item <?= $p === $pagination['page'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?= $page_url($p); ?>"><?= $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                        <li class="page-item"><a class="page-link" href="<?= $page_url($pagination['page'] + 1); ?>"><i class="ti ti-chevron-right"></i></a></li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Hidden bulk-delete form -->
<?php if ($can_delete): ?>
<form id="bulk-delete-form" action="<?= base_url('quiz-bank/bulk-delete'); ?>" method="POST" style="display:none">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <div id="bulk-ids-container"></div>
</form>
<?php endif; ?>

<script>
(function () {
    'use strict';

    var selected = new Set();
    var toolbar  = document.getElementById('bulk-toolbar');
    var countEl  = document.getElementById('bulk-count');
    var selectAll = document.getElementById('select-all');

    function updateToolbar() {
        if (!toolbar) return;
        var n = selected.size;
        if (n > 0) {
            toolbar.classList.add('visible');
            countEl.textContent = n + ' soal dipilih';
        } else {
            toolbar.classList.remove('visible');
        }
        if (selectAll) {
            var all = document.querySelectorAll('.row-check');
            selectAll.checked      = all.length > 0 && selected.size === all.length;
            selectAll.indeterminate = selected.size > 0 && selected.size < all.length;
        }
    }

    window.onRowCheck = function (cb) {
        var id = cb.value;
        var row = cb.closest('tr');
        if (cb.checked) { selected.add(id); row.classList.add('row-selected'); }
        else            { selected.delete(id); row.classList.remove('row-selected'); }
        updateToolbar();
    };

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(function (cb) {
                cb.checked = selectAll.checked;
                var row = cb.closest('tr');
                if (selectAll.checked) { selected.add(cb.value); row.classList.add('row-selected'); }
                else                   { selected.delete(cb.value); row.classList.remove('row-selected'); }
            });
            updateToolbar();
        });
    }

    window.clearSelection = function () {
        selected.clear();
        document.querySelectorAll('.row-check').forEach(function (cb) {
            cb.checked = false;
            cb.closest('tr').classList.remove('row-selected');
        });
        updateToolbar();
    };

    window.confirmBulkDelete = function () {
        if (selected.size === 0) return;
        if (!confirm('Hapus ' + selected.size + ' soal yang dipilih? Tindakan ini tidak bisa dibatalkan.')) return;

        var container = document.getElementById('bulk-ids-container');
        container.innerHTML = '';
        selected.forEach(function (id) {
            var input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'ids[]';
            input.value = id;
            container.appendChild(input);
        });

        document.getElementById('bulk-delete-form').submit();
    };
})();
</script>
