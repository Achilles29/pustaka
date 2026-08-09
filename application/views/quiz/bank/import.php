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
        <?php if ($s = $this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($s); ?></div><?php endif; ?>
        <?php if ($e = $this->session->flashdata('error')): ?><div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($e); ?></div><?php endif; ?>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="ti ti-file-import me-1"></i>Unggah File</h3></div>
                    <div class="card-body">
                        <?= form_open_multipart('quiz-bank/analyze'); ?>
                        <div class="mb-3">
                            <label class="form-label required">File Soal</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv,.txt,.xlsx" required>
                            <small class="form-hint">Format: <strong>CSV</strong>, <strong>Excel (.xlsx)</strong>, atau <strong>TXT</strong> (naskah soal). Maks 5 MB.</small>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Mata Pelajaran</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">— dari file —</option>
                                    <?php foreach ($subjects as $s): ?><option value="<?= $s['id']; ?>"><?= html_escape($s['name']); ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Jenjang</label>
                                <select name="grade_level_id" class="form-select">
                                    <option value="">— dari file —</option>
                                    <?php foreach ($grades as $g): ?><option value="<?= $g['id']; ?>"><?= html_escape($g['name']); ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12"><small class="form-hint">Untuk file <strong>TXT/Excel tanpa kode</strong>, pilih mapel &amp; jenjang di atas (berlaku untuk semua soal). Untuk CSV/Excel dengan kolom <code>subject_code</code>/<code>grade_code</code>, kolom itu yang dipakai.</small></div>
                        </div>
                        <div class="alert alert-info py-2 mb-3">
                            <i class="ti ti-eye-check me-1"></i>Setelah memilih file, klik <strong>Analisa</strong>. Sistem akan mengecek & menampilkan seluruh soal untuk kamu review dulu sebelum benar-benar diimport.
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-eye-search me-1"></i>Analisa File</button>
                            <span class="text-secondary small ms-1">Template:</span>
                            <a href="<?= base_url('quiz-bank/template/xlsx'); ?>" class="btn btn-outline-success btn-sm"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
                            <a href="<?= base_url('quiz-bank/template/csv'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-file-text me-1"></i>CSV</a>
                            <a href="<?= base_url('quiz-bank/template/txt'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-file-description me-1"></i>TXT</a>
                        </div>
                        <p class="form-hint mt-2 mb-0"><i class="ti ti-bulb me-1"></i>Paling mudah: unduh <strong>Template Excel</strong>, isi di aplikasi spreadsheet, simpan sebagai <code>.xlsx</code>, lalu unggah di sini.</p>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="ti ti-info-circle me-1"></i>Format Kolom (CSV / Excel)</h3></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-2">
                                <thead class="table-light"><tr><th>Kolom</th><th>Nilai</th></tr></thead>
                                <tbody>
                                    <tr><td><code>question_text</code></td><td>Teks soal (wajib)</td></tr>
                                    <tr><td><code>type</code></td><td><code>multiple_choice</code> | <code>essay</code></td></tr>
                                    <tr><td><code>difficulty</code></td><td><code>easy</code> | <code>medium</code> | <code>hard</code></td></tr>
                                    <tr><td><code>subject_code</code>, <code>grade_code</code></td><td>Kode mapel &amp; jenjang (boleh kosong bila dipilih di atas)</td></tr>
                                    <tr><td><code>option_a</code> – <code>option_e</code></td><td>Teks pilihan (maks 5)</td></tr>
                                    <tr><td><code>correct_answer</code></td><td><code>A</code> | <code>B</code> | <code>C</code> | <code>D</code> | <code>E</code></td></tr>
                                    <tr><td><code>explanation</code>, <code>tags</code></td><td>Pembahasan &amp; tag (opsional)</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-secondary small mb-1"><strong>Excel (.xlsx)</strong>: gunakan baris pertama sebagai header kolom yang sama seperti di atas.</p>
                        <p class="text-secondary small mb-1"><strong>TXT (naskah)</strong>: format <code>1. soal</code>, opsi <code>a. ...</code>, dan blok <code>KUNCI JAWABAN</code> di akhir. Bagian <em>MUDAH/SEDANG/SULIT</em> menentukan tingkat kesulitan.</p>
                        <div class="mt-2">
                            <strong class="small">Kode Mapel:</strong> <?php foreach ($subjects as $s): ?><code><?= html_escape($s['code']); ?></code> <?php endforeach; ?>
                        </div>
                        <div class="mt-1">
                            <strong class="small">Kode Jenjang:</strong> <?php foreach ($grades as $g): ?><code><?= html_escape($g['code']); ?></code> <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (! empty($batches)): ?>
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Riwayat Import</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Waktu</th><th>File</th><th>Format</th><th>Berhasil</th><th>Dilewati</th><th>Oleh</th></tr></thead>
                    <tbody>
                    <?php foreach ($batches as $b): ?>
                    <tr>
                        <td class="text-secondary small"><?= html_escape($b['created_at']); ?></td>
                        <td><?= html_escape($b['filename']); ?></td>
                        <td><span class="badge bg-azure-lt text-azure text-uppercase"><?= html_escape($b['format'] ?? '—'); ?></span></td>
                        <td><span class="badge bg-success"><?= (int)$b['imported']; ?></span></td>
                        <td><span class="badge bg-secondary"><?= (int)$b['skipped']; ?></span></td>
                        <td class="text-secondary small"><?= html_escape($b['imported_by_name'] ?? '—'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
