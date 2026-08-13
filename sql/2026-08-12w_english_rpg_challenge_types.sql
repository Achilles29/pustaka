ALTER TABLE learn_english_rpg_scenes ADD COLUMN IF NOT EXISTS challenge_type ENUM('choice','listening','sentence') NOT NULL DEFAULT 'choice' AFTER prompt;
ALTER TABLE learn_english_rpg_scenes ADD COLUMN IF NOT EXISTS audio_text TEXT NULL AFTER challenge_type;
ALTER TABLE learn_english_rpg_scenes ADD COLUMN IF NOT EXISTS sentence_answer VARCHAR(255) NULL AFTER audio_text;
UPDATE learn_english_rpg_scenes SET challenge_type='listening',audio_text=dialogue WHERE sort_order IN(4,8,12);
UPDATE learn_english_rpg_scenes SET challenge_type='sentence',sentence_answer=CONCAT('I learned the word ',vocabulary_word),audio_text=NULL WHERE sort_order IN(5,10,15);
