-- ============================================================
-- Perbaikan Akses — Cabut izin admin dari role USER (pemustaka)
-- Pustaka Digital Rembang — 2026-08-07
-- ------------------------------------------------------------
-- Halaman /quiz-* dan /learn-* adalah panel ADMIN (kelola).
-- Pemustaka mengakses pembelajaran lewat /belajar (arena) dan
-- /quiz/* (Quiz_play, controller publik) — TANPA izin RBAC.
-- Migrasi ini memastikan role USER (id 3) tidak punya izin apa pun
-- pada halaman admin modul pembelajaran. Idempotent.
-- ============================================================

UPDATE `auth_role_permission` rp
JOIN `sys_page` p ON p.`id` = rp.`page_id`
SET rp.`can_view`   = 0,
    rp.`can_create` = 0,
    rp.`can_edit`   = 0,
    rp.`can_delete` = 0,
    rp.`can_export` = 0,
    rp.`can_approve`= 0
WHERE rp.`role_id` = 3
  AND p.`code` IN (
    'quiz_config.index', 'quiz_bank.index', 'quiz_sessions.index', 'quiz_competitions.index',
    'learn_config.index', 'learn_games.index', 'learn_rewards.index', 'learn_flashcards.index',
    'learn_story.index', 'learn_notifications.index', 'learn_battle.index', 'learn_reports.index'
  );
