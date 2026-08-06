<?php defined('BASEPATH') OR exit('No direct script access allowed');
$letters = ['A', 'B', 'C', 'D'];
$room_status = [
    'waiting'   => ['Menunggu', 'bg-yellow-lt text-yellow'],
    'playing'   => ['Berlangsung', 'bg-blue-lt text-blue'],
    'finished'  => ['Selesai', 'bg-green-lt text-green'],
    'abandoned' => ['Ditinggalkan', 'bg-secondary-lt text-secondary'],
];
?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pembelajaran</div>
                <h2 class="page-title">Mode Battle — Pool Soal</h2>
            </div>
            <div class="col-auto">
                <?php if ($can_create): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd"><i class="ti ti-plus me-1"></i>Tambah Soal</button>
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

    <?php if ((int)$stats['active'] < 3): ?>
    <div class="alert alert-warning"><i class="ti ti-alert-triangle me-1"></i>Pool soal aktif kurang dari 3. Mode Battle butuh minimal 3 soal aktif agar bisa dimainkan.</div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-red-lt avatar"><i class="ti ti-swords"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['questions']; ?></div><div class="text-secondary">Total Soal</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-green-lt avatar"><i class="ti ti-circle-check"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= (int)$stats['active']; ?></div><div class="text-secondary">Soal Aktif</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-blue-lt avatar"><i class="ti ti-device-gamepad-2"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= number_format((int)$stats['rooms']); ?></div><div class="text-secondary">Total Ronde</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-purple-lt avatar"><i class="ti ti-flag-checkered"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= number_format((int)$stats['finished']); ?></div><div class="text-secondary">Selesai</div></div>
        </div></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-list me-2"></i>Pool Soal</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead><tr><th>Pertanyaan</th><th>Kategori</th><th class="text-center">Kunci</th><th class="text-center">Status</th><?php if ($can_edit||$can_delete): ?><th></th><?php endif; ?></tr></thead>
                        <tbody>
                            <?php foreach ($questions as $q): ?>
                            <tr class="<?= !$q['is_active']?'opacity-60':''; ?>">
                                <td style="max-width:340px"><?= html_escape($q['question']); ?></td>
                                <td><?php if ($q['category']): ?><span class="badge bg-azure-lt text-azure"><?= html_escape($q['category']); ?></span><?php else: ?><span class="text-secondary">—</span><?php endif; ?></td>
                                <td class="text-center"><span class="badge bg-green-lt text-green"><?= $letters[$q['correct_option']] ?? '?'; ?></span></td>
                                <td class="text-center"><span class="badge <?= $q['is_active']?'bg-success-lt text-success':'bg-secondary-lt text-secondary'; ?>"><?= $q['is_active']?'Aktif':'Nonaktif'; ?></span></td>
                                <?php if ($can_edit||$can_delete): ?>
                                <td class="text-end">
                                    <div class="btn-list flex-nowrap">
                                        <?php if ($can_edit): ?>
                                        <button class="btn btn-sm btn-ghost-secondary btn-edit-q" data-bs-toggle="modal" data-bs-target="#modalEdit"
                                            data-id="<?= $q['id']; ?>" data-question="<?= html_escape($q['question']); ?>"
                                            data-option_a="<?= html_escape($q['option_a']); ?>" data-option_b="<?= html_escape($q['option_b']); ?>"
                                            data-option_c="<?= html_escape($q['option_c']); ?>" data-option_d="<?= html_escape($q['option_d']); ?>"
                                            data-correct_option="<?= (int)$q['correct_option']; ?>" data-category="<?= html_escape($q['category']); ?>"
                                            data-is_active="<?= (int)$q['is_active']; ?>"><i class="ti ti-pencil"></i></button>
                                        <?php endif; ?>
                                        <?php if ($can_delete): ?>
                                        <a href="<?= base_url('learn-battle/delete/'.$q['id']); ?>" class="btn btn-sm btn-ghost-danger" onclick="return confirm('Hapus soal ini?')"><i class="ti ti-trash"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($questions)): ?>
                            <tr><td colspan="<?= ($can_edit||$can_delete)?5:4; ?>" class="text-center text-secondary py-5"><i class="ti ti-swords fs-1 d-block mb-2"></i>Belum ada soal battle.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="ti ti-history me-2"></i>Ronde Terbaru</h3></div>
                <div class="list-group list-group-flush">
                    <?php foreach ($rooms as $r): $st = $room_status[$r['status']] ?? ['?','bg-secondary-lt']; ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <code class="fw-bold"><?= html_escape($r['code']); ?></code>
                                <span class="badge <?= $st[1]; ?> ms-1"><?= $st[0]; ?></span>
                            </div>
                            <span class="text-secondary small"><?= (int)$r['host_score']; ?>–<?= (int)$r['guest_score']; ?></span>
                        </div>
                        <div class="text-secondary small mt-1">
                            <?= html_escape($r['host_name'] ?: 'Host'); ?> vs <?= html_escape($r['guest_name'] ?: '—'); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($rooms)): ?>
                    <div class="list-group-item text-center text-secondary py-4">Belum ada ronde.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add -->
<div class="modal modal-blur fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <?= form_open('learn-battle/store'); ?>
        <div class="modal-header"><h5 class="modal-title">Tambah Soal Battle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><?php include __DIR__ . '/_fields.php'; ?></div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        <?= form_close(); ?>
    </div></div>
</div>

<!-- Modal Edit -->
<div class="modal modal-blur fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="post" action="" id="formEdit">
        <div class="modal-header"><h5 class="modal-title">Edit Soal Battle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><?php include __DIR__ . '/_fields.php'; ?></div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Perbarui</button></div>
        </form>
    </div></div>
</div>

<script>
document.getElementById('modalEdit').addEventListener('show.bs.modal', function (e) {
    var d = e.relatedTarget.dataset, f = document.getElementById('formEdit');
    f.action = '<?= base_url('learn-battle/update/'); ?>' + d.id;
    f.querySelector('[name=question]').value       = d.question;
    f.querySelector('[name=option_a]').value       = d.option_a;
    f.querySelector('[name=option_b]').value       = d.option_b;
    f.querySelector('[name=option_c]').value       = d.option_c || '';
    f.querySelector('[name=option_d]').value       = d.option_d || '';
    f.querySelector('[name=correct_option]').value = d.correct_option;
    f.querySelector('[name=category]').value       = d.category || '';
    f.querySelector('[name=is_active]').checked    = d.is_active === '1';
});
</script>
