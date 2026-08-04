<?php defined('BASEPATH') OR exit('No direct script access allowed');
$status_labels = ['registered'=>'Terdaftar','confirmed'=>'Konfirmasi','disqualified'=>'Diskualifikasi','withdrawn'=>'Mundur'];
$status_colors = ['registered'=>'bg-blue','confirmed'=>'bg-success','disqualified'=>'bg-danger','withdrawn'=>'bg-secondary'];
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= base_url('quiz-competitions'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a></div>
            <div class="col"><div class="page-pretitle">Kompetisi — <?= html_escape($session['title']); ?></div><h1 class="page-title">Manajemen Peserta</h1></div>
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <a href="<?= base_url('quiz-competitions/export_participants/'.$session['id']); ?>" class="btn btn-outline-secondary"><i class="ti ti-download me-1"></i>Export CSV</a>
                    <?php if($can_edit): ?>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-import"><i class="ti ti-file-import me-1"></i>Import</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-participant"><i class="ti ti-plus me-1"></i>Tambah Peserta</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if($s=$this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= $s; ?></div><?php endif; ?>
        <?php if($e=$this->session->flashdata('error')): ?><div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($e); ?></div><?php endif; ?>

        <div class="card admin-card data-workspace">
            <div class="card-header workspace-header">
                <div><h2 class="card-title">Peserta (<?= $pagination['total_rows']; ?>)</h2></div>
            </div>
            <div class="card-body workspace-filter py-2">
                <?= form_open('quiz-competitions/participants/'.$session['id'],['method'=>'get','class'=>'row g-2 align-items-end']); ?>
                <div class="col-md-4"><input type="text" class="form-control" name="q" value="<?= html_escape($filters['q']??''); ?>" placeholder="Cari nama, kode, sekolah..."></div>
                <div class="col-md-2">
                    <select class="form-select" name="status"><option value="">Semua Status</option>
                    <?php foreach($status_labels as $k=>$v): ?><option value="<?=$k?>" <?= ($filters['status']??'')===$k?'selected':''; ?>><?=$v?></option><?php endforeach; ?></select>
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i></button><a href="<?= base_url('quiz-competitions/participants/'.$session['id']); ?>" class="btn btn-outline-secondary ms-1"><i class="ti ti-x"></i></a></div>
                <?= form_close(); ?>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Kode</th><th>PIN</th><th>Nama</th><th>Sekolah/Kelas</th><th>Attempt</th><th>Skor Terbaik</th><th>Status</th><th class="w-1"></th></tr></thead>
                    <tbody>
                    <?php if(empty($participants)): ?><tr><td colspan="8" class="text-center text-secondary py-4">Belum ada peserta.</td></tr><?php endif; ?>
                    <?php foreach($participants as $p): ?>
                    <tr>
                        <td><code class="fw-bold"><?= html_escape($p['registration_code']); ?></code></td>
                        <td><code><?= html_escape($p['registration_pin']); ?></code></td>
                        <td class="fw-semibold"><?= html_escape($p['full_name']); ?></td>
                        <td>
                            <div><?= html_escape($p['school_name']??'—'); ?></div>
                            <div class="text-secondary small"><?= html_escape($p['grade_class']??''); ?></div>
                        </td>
                        <td><?= (int)$p['attempt_count']; ?></td>
                        <td><?= $p['best_score']!==null ? number_format((float)$p['best_score'],1).'%' : '—'; ?></td>
                        <td><span class="badge <?= $status_colors[$p['registration_status']]??'bg-secondary'; ?>"><?= $status_labels[$p['registration_status']]??$p['registration_status']; ?></span></td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <?php if($can_edit): ?>
                                <button class="btn btn-sm btn-outline-secondary" onclick="editParticipant(<?= htmlspecialchars(json_encode($p)); ?>)"><i class="ti ti-edit"></i></button>
                                <?php endif; ?>
                                <?php if($can_delete): ?>
                                <a href="<?= base_url('quiz-competitions/delete_participant/'.$p['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus peserta?')"><i class="ti ti-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Participant -->
<div class="modal fade" id="modal-add-participant" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="post" action="<?= base_url('quiz-competitions/add_participant/'.$session['id']); ?>">
    <?= csrf_field(); ?>
    <div class="modal-header"><h5 class="modal-title">Tambah Peserta</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="row g-2">
            <div class="col-md-6"><label class="form-label required">Nama Lengkap</label><input type="text" name="full_name" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">No. Identitas</label><input type="text" name="identity_number" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Jenis Identitas</label><input type="text" name="identity_type" class="form-control" placeholder="NIK, NIS, dll."></div>
            <div class="col-md-5"><label class="form-label">Nama Sekolah</label><input type="text" name="school_name" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Kelas/Jurusan</label><input type="text" name="grade_class" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Jenis Kelamin</label>
                <select name="gender" class="form-select"><option value="">—</option><option value="L">Laki-laki</option><option value="P">Perempuan</option></select>
            </div>
            <div class="col-md-2"><label class="form-label">Tgl Lahir</label><input type="date" name="birth_date" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-control"></div>
            <div class="col-12"><label class="form-label">Catatan</label><input type="text" name="notes" class="form-control"></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Tambah</button></div>
    </form>
</div></div></div>

<!-- Modal Edit Participant (populated by JS) -->
<div class="modal fade" id="modal-edit-participant" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="post" id="form-edit-participant">
    <?= csrf_field(); ?>
    <div class="modal-header"><h5 class="modal-title">Edit Peserta</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="row g-2">
            <div class="col-md-6"><label class="form-label required">Nama Lengkap</label><input type="text" name="full_name" id="ep-name" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">No. Identitas</label><input type="text" name="identity_number" id="ep-idno" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Jenis Identitas</label><input type="text" name="identity_type" id="ep-idtype" class="form-control"></div>
            <div class="col-md-5"><label class="form-label">Nama Sekolah</label><input type="text" name="school_name" id="ep-school" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Kelas</label><input type="text" name="grade_class" id="ep-class" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" id="ep-email" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Telepon</label><input type="text" name="phone" id="ep-phone" class="form-control"></div>
            <div class="col-12"><label class="form-label">Catatan</label><input type="text" name="notes" id="ep-notes" class="form-control"></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
</div></div></div>

<!-- Modal Import -->
<div class="modal fade" id="modal-import" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= base_url('quiz-competitions/import_participants/'.$session['id']); ?>" enctype="multipart/form-data">
    <?= csrf_field(); ?>
    <div class="modal-header"><h5 class="modal-title">Import Peserta CSV</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="text-secondary small">Format header: <code>full_name,identity_number,identity_type,school_name,grade_class,phone,email,gender</code></p>
        <input type="file" name="participant_file" class="form-control" accept=".csv" required>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Upload & Import</button></div>
    </form>
</div></div></div>

<script>
function editParticipant(p) {
    document.getElementById('ep-name').value = p.full_name||'';
    document.getElementById('ep-idno').value = p.identity_number||'';
    document.getElementById('ep-idtype').value = p.identity_type||'';
    document.getElementById('ep-school').value = p.school_name||'';
    document.getElementById('ep-class').value = p.grade_class||'';
    document.getElementById('ep-email').value = p.email||'';
    document.getElementById('ep-phone').value = p.phone||'';
    document.getElementById('ep-notes').value = p.notes||'';
    document.getElementById('form-edit-participant').action = '<?= base_url('quiz-competitions/edit_participant/'); ?>' + p.id;
    new bootstrap.Modal(document.getElementById('modal-edit-participant')).show();
}
</script>
