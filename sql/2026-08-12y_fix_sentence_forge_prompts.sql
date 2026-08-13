UPDATE learn_english_rpg_scenes
SET prompt=CONCAT('Arrange the words into a correct sentence using “',vocabulary_word,'” (',vocabulary_meaning,').'),
    translation=CONCAT('Susun kata-kata menjadi kalimat bahasa Inggris yang benar menggunakan kata “',vocabulary_word,'” yang berarti “',vocabulary_meaning,'”.')
WHERE challenge_type='sentence';
