<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-xl">
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pembelajaran</div>
                <h2 class="page-title">Notifikasi &amp; Pengumuman</h2>
            </div>
            <div class="col-auto">
                <?php if ($can_create): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSend"><i class="ti ti-speakerphone me-1"></i>Kirim Pengumuman</button>
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

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-blue-lt avatar"><i class="ti ti-bell"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= number_format((int)$stats['total']); ?></div><div class="text-secondary">Total Notifikasi</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-orange-lt avatar"><i class="ti ti-bell-ringing"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= number_format((int)$stats['unread']); ?></div><div class="text-secondary">Belum Dibaca</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-purple-lt avatar"><i class="ti ti-speakerphone"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= number_format((int)$stats['broadcasts']); ?></div><div class="text-secondary">Pengumuman</div></div>
        </div></div></div></div>
        <div class="col-6 col-md-3"><div class="card card-sm"><div class="card-body"><div class="row align-items-center">
            <div class="col-auto"><span class="bg-green-lt avatar"><i class="ti ti-users"></i></span></div>
            <div class="col"><div class="fw-bold fs-3"><?= number_format((int)$stats['recipients']); ?></div><div class="text-secondary">Calon Penerima</div></div>
        </div></div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="ti ti-history me-2"></i>Riwayat Pengumuman</h3></div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Waktu</th><th>Judul</th><th>Pesan</th><th class="text-center">Penerima</th><th>Pengirim</th></tr></thead>
                <tbody>
                    <?php foreach ($broadcasts as $b): ?>
                    <tr>
                        <td class="text-secondary small"><?= date('d M Y H:i', strtotime($b['created_at'])); ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-sm" style="background:<?= html_escape($b['color']); ?>1a;color:<?= html_escape($b['color']); ?>"><i class="ti <?= html_escape($b['icon']); ?>"></i></span>
                                <span class="fw-medium"><?= html_escape($b['title']); ?></span>
                            </div>
                        </td>
                        <td class="text-secondary small" style="max-width:340px"><?= html_escape($b['message'] ?: '—'); ?></td>
                        <td class="text-center"><span class="badge bg-blue-lt text-blue"><?= number_format((int)$b['recipient_count']); ?></span></td>
                        <td class="text-secondary small"><?= html_escape($b['sender_name'] ?: 'Sistem'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($broadcasts)): ?>
                    <tr><td colspan="5" class="text-center text-secondary py-5"><i class="ti ti-speakerphone fs-1 d-block mb-2"></i>Belum ada pengumuman terkirim.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Kirim Pengumuman -->
<div class="modal modal-blur fade" id="modalSend" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <?= form_open('learn-notifications/send'); ?>
        <div class="modal-header"><h5 class="modal-title">Kirim Pengumuman ke Semua Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="alert alert-info py-2"><i class="ti ti-info-circle me-1"></i>Pengumuman dikirim sebagai notifikasi ke <strong><?= number_format((int)$stats['recipients']); ?></strong> member.</div>
            <div class="mb-3">
                <label class="form-label required">Judul</label>
                <input type="text" class="form-control" name="title" maxlength="180" placeholder="Kompetisi Matematika Dibuka!" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Pesan</label>
                <textarea class="form-control" name="message" rows="3" maxlength="500" placeholder="Detail pengumuman..."></textarea>
            </div>
            <div class="row">
                <div class="col-7 mb-3">
                    <label class="form-label">Ikon (Tabler)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="nt_icon_preview ti ti-speakerphone"></i></span>
                        <input type="text" class="form-control" name="icon" value="ti-speakerphone"
                               oninput="this.closest('.input-group').querySelector('.nt_icon_preview').className='nt_icon_preview ti '+this.value">
                    </div>
                </div>
                <div class="col-5 mb-3">
                    <label class="form-label">Warna</label>
                    <input type="color" class="form-control form-control-color w-100" name="color" value="#3b82f6">
                </div>
            </div>
            <div class="mb-1">
                <label class="form-label">Tautan (opsional)</label>
                <input type="text" class="form-control" name="url" placeholder="belajar/cerita atau quiz-competitions">
                <small class="text-secondary">Rute internal tanpa base_url. Kosongkan bila tak perlu.</small>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost-secondary me-auto" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i>Kirim ke Semua</button></div>
        <?= form_close(); ?>
    </div></div>
</div>
