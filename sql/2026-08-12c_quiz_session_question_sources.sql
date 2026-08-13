-- Pilihan sumber soal untuk setiap sesi latihan.
ALTER TABLE `quiz_sessions`
  ADD COLUMN IF NOT EXISTS `question_source` enum('bank_all','selected','session_only') NOT NULL DEFAULT 'bank_all' AFTER `question_count`;

ALTER TABLE `quiz_questions`
  ADD COLUMN IF NOT EXISTS `is_session_only` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_active`,
  ADD COLUMN IF NOT EXISTS `session_scope_id` bigint(20) unsigned DEFAULT NULL AFTER `is_session_only`;

CREATE INDEX IF NOT EXISTS `idx_quiz_questions_session_scope`
  ON `quiz_questions` (`is_session_only`, `session_scope_id`);
