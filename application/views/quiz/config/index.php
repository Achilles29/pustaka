<?php defined('BASEPATH') OR exit('No direct script access allowed');
$metrics = [
    ['key'=>'grades',   'label'=>'Jenjang Kelas', 'icon'=>'ti ti-school'],
    ['key'=>'subjects', 'label'=>'Mata Pelajaran', 'icon'=>'ti ti-books'],
    ['key'=>'questions','label'=>'Total Soal',     'icon'=>'ti ti-list-check'],
    ['key'=>'sessions', 'label'=>'Sesi Aktif',     'icon'=>'ti ti-calendar-event'],
];
$edu_labels = ['tk'=>'TK','sd'=>'SD','smp'=>'SMP','sma'=>'SMA','smk'=>'SMK','pt'=>'PT','umum'=>'Umum'];
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><div class="page-pretitle">Modul Belajar</div><h1 class="page-title">Konfigurasi Quiz</h1></div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if ($s = $this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($s); ?></div><?php endif; ?>
        <?php if ($e = $this->session->flashdata('error')): ?><div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($e); ?></div><?php endif; ?>

        <div class="metric-ribbon">
            <?php foreach ($metrics as $m): ?>
            <div class="metric-ribbon-item">
                <span class="metric-icon"><i class="<?= $m['icon']; ?>"></i></span>
                <div><div class="metric-value"><?= number_format((int)($stats[$m['key']]??0),0,',','.'); ?></div><div class="metric-label"><?= $m['label']; ?></div></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card admin-card">
            <div class="card-header workspace-header">
                <h2 class="card-title">Pengaturan Referensi</h2>
                <ul class="nav nav-tabs card-header-tabs workspace-tabs">
                    <li class="nav-item"><a href="?tab=grades"   class="nav-link <?= $active_tab==='grades'   ? 'active':'' ?>"><i class="ti ti-school me-1"></i>Jenjang Kelas</a></li>
                    <li class="nav-item"><a href="?tab=subjects" class="nav-link <?= $active_tab==='subjects' ? 'active':'' ?>"><i class="ti ti-books me-1"></i>Mata Pelajaran</a></li>
                </ul>
            </div>

            <?php if ($active_tab === 'grades'): ?>
            <div class="card-body pb-0">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-grade-add"><i class="ti ti-plus me-1"></i>Tambah Jenjang</button>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Level</th><th>Kelas</th><th>Urutan</th><th>Aktif</th><th class="w-1"></th></tr></thead>
                    <tbody>
                    <?php if(empty($grades)):?><tr><td colspan="7" class="text-center text-secondary py-4">Belum ada data.</td></tr><?php endif;?>
                    <?php foreach($grades as $g): ?>
                    <tr>
                        <td><code><?= html_escape($g['code']); ?></code></td>
                        <td class="fw-semibold"><?= html_escape($g['name']); ?></td>
                        <td><span class="badge bg-blue-lt"><?= $edu_labels[$g['education_level']]??$g['education_level']; ?></span></td>
                        <td><?= $g['grade_number'] ?: '—'; ?></td>
                        <td><?= $g['sort_order']; ?></td>
                        <td><?= $g['is_active'] ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>'; ?></td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-sm btn-outline-secondary" onclick="editGrade(<?= htmlspecialchars(json_encode($g)); ?>)"><i class="ti ti-edit"></i></button>
                                <a href="<?= base_url('quiz-config/delete_grade/'.$g['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus jenjang ini?')"><i class="ti ti-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php else: ?>
            <div class="card-body pb-0">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-subject-add"><i class="ti ti-plus me-1"></i>Tambah Mapel</button>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Icon</th><th>Warna</th><th>Urutan</th><th>Aktif</th><th class="w-1"></th></tr></thead>
                    <tbody>
                    <?php if(empty($subjects)):?><tr><td colspan="7" class="text-center text-secondary py-4">Belum ada data.</td></tr><?php endif;?>
                    <?php foreach($subjects as $s): ?>
                    <tr>
                        <td><code><?= html_escape($s['code']); ?></code></td>
                        <td><i class="<?= html_escape($s['icon']); ?> me-1" style="color:<?= html_escape($s['color']); ?>"></i><?= html_escape($s['name']); ?></td>
                        <td><code><?= html_escape($s['icon']); ?></code></td>
                        <td><span class="badge" style="background:<?= html_escape($s['color']); ?>"><?= html_escape($s['color']); ?></span></td>
                        <td><?= $s['sort_order']; ?></td>
                        <td><?= $s['is_active'] ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>'; ?></td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-sm btn-outline-secondary" onclick="editSubject(<?= htmlspecialchars(json_encode($s)); ?>)"><i class="ti ti-edit"></i></button>
                                <a href="<?= base_url('quiz-config/delete_subject/'.$s['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus mapel ini?')"><i class="ti ti-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Add Grade -->
<div class="modal fade" id="modal-grade-add" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= base_url('quiz-config/store_grade'); ?>">
    <?= csrf_field(); ?>
    <div class="modal-header"><h5 class="modal-title">Tambah Jenjang Kelas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label required">Kode</label><input type="text" name="code" class="form-control" placeholder="sd_4" required></div>
        <div class="mb-3"><label class="form-label required">Nama</label><input type="text" name="name" class="form-control" placeholder="SD Kelas 4" required></div>
        <div class="row g-2">
            <div class="col-6"><label class="form-label">Level</label>
                <select name="education_level" class="form-select">
                    <?php foreach($edu_labels as $k=>$v): ?><option value="<?=$k?>"><?=$v?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-3"><label class="form-label">No. Kelas</label><input type="number" name="grade_number" class="form-control" min="1" max="13" placeholder="4"></div>
            <div class="col-3"><label class="form-label">Urutan</label><input type="number" name="sort_order" class="form-control" value="0"></div>
        </div>
        <div class="mt-2"><label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" checked><span class="form-check-label">Aktif</span></label></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div></div>

<!-- Modal Edit Grade (populated by JS) -->
<div class="modal fade" id="modal-grade-edit" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="post" id="form-grade-edit">
    <?= csrf_field(); ?>
    <div class="modal-header"><h5 class="modal-title">Edit Jenjang Kelas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label required">Kode</label><input type="text" name="code" id="edit-grade-code" class="form-control" required></div>
        <div class="mb-3"><label class="form-label required">Nama</label><input type="text" name="name" id="edit-grade-name" class="form-control" required></div>
        <div class="row g-2">
            <div class="col-6"><label class="form-label">Level</label>
                <select name="education_level" id="edit-grade-level" class="form-select">
                    <?php foreach($edu_labels as $k=>$v): ?><option value="<?=$k?>"><?=$v?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-3"><label class="form-label">No. Kelas</label><input type="number" name="grade_number" id="edit-grade-num" class="form-control" min="1" max="13"></div>
            <div class="col-3"><label class="form-label">Urutan</label><input type="number" name="sort_order" id="edit-grade-sort" class="form-control"></div>
        </div>
        <div class="mt-2"><label class="form-check"><input type="checkbox" name="is_active" id="edit-grade-active" value="1" class="form-check-input"><span class="form-check-label">Aktif</span></label></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div></div>

<!-- Modal Add Subject -->
<div class="modal fade" id="modal-subject-add" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= base_url('quiz-config/store_subject'); ?>">
    <?= csrf_field(); ?>
    <div class="modal-header"><h5 class="modal-title">Tambah Mata Pelajaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label required">Kode</label><input type="text" name="code" class="form-control" placeholder="matematika" required></div>
        <div class="mb-3"><label class="form-label required">Nama</label><input type="text" name="name" class="form-control" required></div>
        <div class="row g-2">
            <div class="col-6"><label class="form-label">Icon (Tabler)</label><input type="text" name="icon" class="form-control" value="ti ti-book" placeholder="ti ti-book"></div>
            <div class="col-3"><label class="form-label">Warna</label><input type="color" name="color" class="form-control form-control-color" value="#4a90d9"></div>
            <div class="col-3"><label class="form-label">Urutan</label><input type="number" name="sort_order" class="form-control" value="0"></div>
        </div>
        <div class="mt-2"><label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" checked><span class="form-check-label">Aktif</span></label></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div></div>

<!-- Modal Edit Subject -->
<div class="modal fade" id="modal-subject-edit" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="post" id="form-subject-edit">
    <?= csrf_field(); ?>
    <div class="modal-header"><h5 class="modal-title">Edit Mata Pelajaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label required">Kode</label><input type="text" name="code" id="edit-sub-code" class="form-control" required></div>
        <div class="mb-3"><label class="form-label required">Nama</label><input type="text" name="name" id="edit-sub-name" class="form-control" required></div>
        <div class="row g-2">
            <div class="col-6"><label class="form-label">Icon</label><input type="text" name="icon" id="edit-sub-icon" class="form-control"></div>
            <div class="col-3"><label class="form-label">Warna</label><input type="color" name="color" id="edit-sub-color" class="form-control form-control-color"></div>
            <div class="col-3"><label class="form-label">Urutan</label><input type="number" name="sort_order" id="edit-sub-sort" class="form-control"></div>
        </div>
        <div class="mt-2"><label class="form-check"><input type="checkbox" name="is_active" id="edit-sub-active" value="1" class="form-check-input"><span class="form-check-label">Aktif</span></label></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div></div>

<script>
function editGrade(g) {
    document.getElementById('edit-grade-code').value = g.code;
    document.getElementById('edit-grade-name').value = g.name;
    document.getElementById('edit-grade-level').value = g.education_level;
    document.getElementById('edit-grade-num').value = g.grade_number || '';
    document.getElementById('edit-grade-sort').value = g.sort_order;
    document.getElementById('edit-grade-active').checked = g.is_active == 1;
    document.getElementById('form-grade-edit').action = '<?= base_url('quiz-config/update_grade/'); ?>' + g.id;
    new bootstrap.Modal(document.getElementById('modal-grade-edit')).show();
}
function editSubject(s) {
    document.getElementById('edit-sub-code').value = s.code;
    document.getElementById('edit-sub-name').value = s.name;
    document.getElementById('edit-sub-icon').value = s.icon;
    document.getElementById('edit-sub-color').value = s.color;
    document.getElementById('edit-sub-sort').value = s.sort_order;
    document.getElementById('edit-sub-active').checked = s.is_active == 1;
    document.getElementById('form-subject-edit').action = '<?= base_url('quiz-config/update_subject/'); ?>' + s.id;
    new bootstrap.Modal(document.getElementById('modal-subject-edit')).show();
}
</script>
