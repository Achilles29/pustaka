<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= base_url('quiz-bank'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a></div>
            <div class="col"><div class="page-pretitle">Bank Soal</div><h1 class="page-title">Import Bank Soal</h1></div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if($s=$this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($s); ?></div><?php endif; ?>
        <?php if($e=$this->session->flashdata('error')): ?><div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($e); ?></div><?php endif; ?>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="ti ti-file-import me-1"></i>Upload File CSV</h3></div>
                    <div class="card-body">
                        <?= form_open_multipart('quiz-bank/do_import'); ?>
                        <div class="mb-3">
                            <label class="form-label required">File CSV</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv" required>
                            <small class="form-hint">Maksimum 5 MB. Format: CSV UTF-8.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-upload me-1"></i>Upload & Import</button>
                            <a href="<?= base_url('quiz-bank/template'); ?>" class="btn btn-outline-secondary"><i class="ti ti-download me-1"></i>Download Template</a>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-1"></i>Format CSV</h3></div>
                    <div class="card-body">
                        <p class="text-secondary small">Kolom yang diperlukan (sesuai urutan header):</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr><th>Kolom</th><th>Nilai Valid</th></tr></thead>
                                <tbody>
                                    <tr><td><code>question_text</code></td><td>Teks soal (wajib)</td></tr>
                                    <tr><td><code>type</code></td><td><code>multiple_choice</code> | <code>essay</code></td></tr>
                                    <tr><td><code>difficulty</code></td><td><code>easy</code> | <code>medium</code> | <code>hard</code></td></tr>
                                    <tr><td><code>subject_code</code></td><td>Kode mapel (contoh: <code>matematika</code>)</td></tr>
                                    <tr><td><code>grade_code</code></td><td>Kode jenjang (contoh: <code>sd_4</code>)</td></tr>
                                    <tr><td><code>option_a</code> – <code>option_d</code></td><td>Teks pilihan (MC saja)</td></tr>
                                    <tr><td><code>correct_answer</code></td><td><code>A</code> | <code>B</code> | <code>C</code> | <code>D</code></td></tr>
                                    <tr><td><code>explanation</code></td><td>Pembahasan / kunci jawaban</td></tr>
                                    <tr><td><code>tags</code></td><td>Tag dipisah koma</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <strong>Kode Mapel:</strong>
                            <?php foreach($subjects as $s): ?><code><?= html_escape($s['code']); ?></code> <?php endforeach; ?>
                        </div>
                        <div class="mt-1">
                            <strong>Kode Jenjang:</strong>
                            <?php foreach($grades as $g): ?><code><?= html_escape($g['code']); ?></code> <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!empty($batches)): ?>
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Riwayat Import</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Waktu</th><th>File</th><th>Berhasil</th><th>Skip</th><th>Error</th><th>Diimport oleh</th></tr></thead>
                    <tbody>
                    <?php foreach($batches as $b): ?>
                    <tr>
                        <td><?= html_escape($b['created_at']); ?></td>
                        <td><?= html_escape($b['filename']); ?></td>
                        <td><span class="badge bg-success"><?= $b['imported']; ?></span></td>
                        <td><span class="badge bg-secondary"><?= $b['skipped']; ?></span></td>
                        <td>
                            <?php if($b['errors']>0): ?>
                            <button class="badge bg-danger border-0" data-bs-toggle="modal" data-bs-target="#modal-err-<?=$b['id']?>"><?= $b['errors']; ?></button>
                            <div class="modal fade" id="modal-err-<?=$b['id']?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                                <div class="modal-header"><h5 class="modal-title">Error Detail</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body"><pre class="small"><?= html_escape($b['error_log']??''); ?></pre></div>
                            </div></div></div>
                            <?php else: ?><span class="badge bg-secondary">0</span><?php endif; ?>
                        </td>
                        <td><?= html_escape($b['imported_by_name']??'—'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
