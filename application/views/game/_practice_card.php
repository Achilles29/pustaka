<?php
$not_open_yet = ! empty($s['start_time']) && $now < $s['start_time'];
$subject_color = preg_match('/^#[0-9a-f]{3,8}$/i', (string) ($s['subject_color'] ?? '')) ? $s['subject_color'] : '#1677c8';
$difficulty = $difficulty_labels[$s['difficulty_filter']] ?? 'Campuran';
$subject_icon = ! empty($s['subject_icon']) ? $s['subject_icon'] : 'ti ti-book-2';
?>
<article class="practice-session-card <?= ! empty($s['is_recommended']) ? 'is-recommended' : ''; ?>" style="--subject-color:<?= html_escape($subject_color); ?>">
    <div class="practice-session-card-top">
        <span class="practice-subject-icon"><i class="<?= html_escape($subject_icon); ?>"></i></span>
        <div class="practice-session-badges">
            <?php if (! empty($s['grade_name'])): ?><span><?= html_escape($s['grade_name']); ?></span><?php endif; ?>
            <span class="practice-difficulty difficulty-<?= html_escape($s['difficulty_filter']); ?>"><?= html_escape($difficulty); ?></span>
        </div>
    </div>
    <?php if (! empty($s['subject_name'])): ?><p class="practice-subject-name"><?= html_escape($s['subject_name']); ?></p><?php endif; ?>
    <h3><?= html_escape($s['title']); ?></h3>
    <div class="practice-session-meta">
        <span><i class="ti ti-list-numbers"></i><?= (int) $s['question_count']; ?> soal</span>
        <?php if ((int) $s['time_limit_minutes'] > 0): ?><span><i class="ti ti-clock"></i><?= (int) $s['time_limit_minutes']; ?> menit</span><?php endif; ?>
        <?php if ((int) $s['passing_score'] > 0): ?><span><i class="ti ti-award"></i>Target <?= (int) $s['passing_score']; ?></span><?php endif; ?>
    </div>
    <?php if (! empty($s['start_time']) || ! empty($s['end_time'])): ?>
    <p class="practice-schedule"><i class="ti ti-calendar-event"></i><?php if ($not_open_yet): ?>Buka <?= date('d M Y, H:i', strtotime($s['start_time'])); ?><?php elseif (! empty($s['end_time'])): ?>Tersedia sampai <?= date('d M Y, H:i', strtotime($s['end_time'])); ?><?php else: ?>Sudah tersedia<?php endif; ?></p>
    <?php endif; ?>
    <div class="practice-session-action">
        <?php if (! $user): ?>
        <a href="<?= base_url('login'); ?>" class="btn btn-outline-primary w-100">Login untuk mengerjakan</a>
        <?php elseif ($not_open_yet): ?>
        <button class="btn btn-light w-100" disabled><i class="ti ti-lock"></i> Belum dibuka</button>
        <?php else: ?>
        <a href="<?= base_url('quiz/practice/' . rawurlencode($s['code'])); ?>" class="btn btn-primary w-100"><i class="ti ti-player-play"></i> Mulai latihan</a>
        <?php endif; ?>
    </div>
</article>
