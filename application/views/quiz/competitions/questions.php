<?php defined('BASEPATH') OR exit('No direct script access allowed');
$assigned_ids = array_column($session_questions, 'question_id');
$diff_labels = ['easy'=>'Mudah','medium'=>'Sedang','hard'=>'Sulit'];
$diff_colors = ['easy'=>'bg-success','medium'=>'bg-warning','hard'=>'bg-danger'];
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-auto"><a href="<?= base_url('quiz-competitions'); ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali</a></div>
            <div class="col"><div class="page-pretitle">Kompetisi</div><h1 class="page-title"><?= html_escape($title); ?></h1></div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <?php if($s=$this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= html_escape($s); ?></div><?php endif; ?>

        <div class="row g-3">
            <!-- Soal Kompetisi (kiri) -->
            <div class="col-lg-5">
                <div class="card admin-card">
                    <div class="card-header"><h3 class="card-title"><i class="ti ti-list-check me-1"></i>Soal Kompetisi (<?= count($session_questions); ?>)</h3></div>
                    <div class="table-responsive" style="max-height:600px;overflow-y:auto">
                        <table class="table table-vcenter card-table table-sm">
                            <thead><tr><th>#</th><th>Soal</th><th>Tingkat</th><th class="w-1"></th></tr></thead>
                            <tbody id="assigned-tbody">
                            <?php if(empty($session_questions)): ?>
                            <tr id="empty-row"><td colspan="4" class="text-center text-secondary py-3">Belum ada soal. Tambahkan dari bank soal.</td></tr>
                            <?php endif; ?>
                            <?php foreach($session_questions as $i=>$sq): ?>
                            <tr id="sq-row-<?= $sq['question_id']; ?>">
                                <td><?= $i+1; ?></td>
                                <td style="max-width:220px"><div class="text-truncate small"><?= html_escape(mb_substr(strip_tags($sq['question_text']),0,60)); ?></div><span class="text-secondary small"><?= html_escape($sq['subject_name']??''); ?></span></td>
                                <td><span class="badge <?= $diff_colors[$sq['difficulty']]??''; ?>"><?= $diff_labels[$sq['difficulty']]??''; ?></span></td>
                                <td><?php if($can_edit): ?><button class="btn btn-sm btn-outline-danger" onclick="removeQuestion(<?= $sq['question_id']; ?>)"><i class="ti ti-x"></i></button><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bank Soal (kanan) -->
            <div class="col-lg-7">
                <div class="card admin-card">
                    <div class="card-header workspace-header">
                        <h3 class="card-title"><i class="ti ti-database me-1"></i>Bank Soal</h3>
                    </div>
                    <div class="card-body workspace-filter py-2">
                        <?= form_open('quiz-competitions/questions/'.$session['id'],['method'=>'get','class'=>'row g-1 align-items-end']); ?>
                        <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="q" value="<?= html_escape($filters['q']??''); ?>" placeholder="Cari soal..."></div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" name="subject_id"><option value="">Semua Mapel</option>
                            <?php foreach($subjects as $s): ?><option value="<?=$s['id']?>" <?= (int)($filters['subject_id']??0)===$s['id']?'selected':''; ?>><?= html_escape($s['name']); ?></option><?php endforeach; ?></select>
                        </div>
                        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter"></i></button><a href="<?= base_url('quiz-competitions/questions/'.$session['id']); ?>" class="btn btn-sm btn-outline-secondary ms-1"><i class="ti ti-x"></i></a></div>
                        <?= form_close(); ?>
                    </div>
                    <div class="table-responsive" style="max-height:560px;overflow-y:auto">
                        <table class="table table-vcenter card-table table-sm">
                            <thead><tr><th>Soal</th><th>Mapel</th><th>Tingkat</th><th class="w-1"></th></tr></thead>
                            <tbody>
                            <?php if(empty($bank_questions)): ?><tr><td colspan="4" class="text-center text-secondary py-3">Tidak ada soal.</td></tr><?php endif; ?>
                            <?php foreach($bank_questions as $q): ?>
                            <?php $assigned = in_array($q['id'], $assigned_ids); ?>
                            <tr id="bank-row-<?= $q['id']; ?>" class="<?= $assigned?'table-secondary':''; ?>">
                                <td style="max-width:260px"><div class="text-truncate small"><?= html_escape(mb_substr(strip_tags($q['question_text']),0,80)); ?></div></td>
                                <td><span class="badge" style="background:<?= html_escape($q['subject_color']??'#999'); ?>"><?= html_escape($q['subject_name']??''); ?></span></td>
                                <td><span class="badge <?= $diff_colors[$q['difficulty']]??''; ?>"><?= $diff_labels[$q['difficulty']]??''; ?></span></td>
                                <td>
                                    <?php if($can_edit): ?>
                                    <button class="btn btn-sm <?= $assigned?'btn-secondary':'btn-outline-primary'; ?>" id="btn-add-<?= $q['id']; ?>" <?= $assigned?'disabled':''; ?> onclick="addQuestion(<?= $q['id']; ?>, <?= htmlspecialchars(json_encode(mb_substr(strip_tags($q['question_text']),0,60))); ?>)">
                                        <?= $assigned ? '<i class="ti ti-check"></i>' : '<i class="ti ti-plus"></i>'; ?>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= base_url(); ?>';
const SESSION_ID = <?= $session['id']; ?>;
let assignedCount = <?= count($session_questions); ?>;

async function addQuestion(qid, text) {
    const btn = document.getElementById('btn-add-' + qid);
    btn.disabled = true;
    const fd = new FormData();
    fd.append('question_id', qid);
    fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    const r = await fetch(BASE + 'quiz-competitions/add_question/' + SESSION_ID, {method:'POST', body:fd});
    const d = await r.json();
    if (d.ok) {
        btn.className = 'btn btn-sm btn-secondary';
        btn.innerHTML = '<i class="ti ti-check"></i>';
        document.getElementById('bank-row-' + qid).className = 'table-secondary';
        // add to left panel
        const empty = document.getElementById('empty-row');
        if(empty) empty.remove();
        assignedCount++;
        const tbody = document.getElementById('assigned-tbody');
        const tr = document.createElement('tr');
        tr.id = 'sq-row-' + qid;
        tr.innerHTML = `<td>${assignedCount}</td><td><div class="text-truncate small">${text}</div></td><td></td><td><button class="btn btn-sm btn-outline-danger" onclick="removeQuestion(${qid})"><i class="ti ti-x"></i></button></td>`;
        tbody.appendChild(tr);
    }
}

async function removeQuestion(qid) {
    if (!confirm('Hapus soal ini dari kompetisi?')) return;
    const fd = new FormData();
    fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    const r = await fetch(BASE + 'quiz-competitions/remove_question/' + SESSION_ID + '/' + qid, {method:'POST', body:fd});
    const d = await r.json();
    if(d.ok) {
        const row = document.getElementById('sq-row-' + qid);
        if(row) row.remove();
        const btn = document.getElementById('btn-add-' + qid);
        if(btn) { btn.disabled=false; btn.className='btn btn-sm btn-outline-primary'; btn.innerHTML='<i class="ti ti-plus"></i>'; }
        const bankRow = document.getElementById('bank-row-' + qid);
        if(bankRow) bankRow.className='';
    }
}
</script>
