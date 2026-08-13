UPDATE learn_english_rpg_scenes SET sentence_answer=CASE vocabulary_word
WHEN 'table' THEN 'The book is on the table' WHEN 'return' THEN 'Please return the book tomorrow' WHEN 'wisdom' THEN 'Wisdom grows when we keep learning'
WHEN 'coin' THEN 'I found a coin near the bakery' WHEN 'change' THEN 'The seller gave me the correct change' WHEN 'trade' THEN 'People trade fresh food at the market'
WHEN 'river' THEN 'We crossed the river before sunset' WHEN 'rescue' THEN 'The rangers will rescue the baby deer' WHEN 'wildlife' THEN 'We must protect the wildlife in this forest'
WHEN 'helmet' THEN 'Every astronaut must wear a helmet' WHEN 'signal' THEN 'The station received a weak signal' WHEN 'landing' THEN 'The crew prepared for a safe landing'
WHEN 'costume' THEN 'Mira wore a colorful costume on stage' WHEN 'celebrate' THEN 'Our class will celebrate after the show' WHEN 'applause' THEN 'The audience gave us loud applause'
WHEN 'coral' THEN 'A small fish hides behind the coral' WHEN 'pearl' THEN 'The purple shell contains a pearl' WHEN 'surface' THEN 'The diver returned safely to the surface'
WHEN 'lever' THEN 'Pull the silver lever very slowly' WHEN 'midnight' THEN 'The castle clock rings at midnight' WHEN 'alarm' THEN 'The loud alarm woke every guard'
WHEN 'compass' THEN 'Use the compass to find the northern trail' WHEN 'descend' THEN 'We should descend before the storm arrives' WHEN 'survive' THEN 'Warm clothes help climbers survive the cold'
WHEN 'flame' THEN 'The young dragon can breathe a blue flame' WHEN 'victory' THEN 'Courage and practice brought us victory' WHEN 'courage' THEN 'A true hero shows courage in danger'
WHEN 'map' THEN 'The captain placed the map on the deck' WHEN 'chest' THEN 'We buried the treasure chest under a tree' WHEN 'aboard' THEN 'All sailors are safely aboard the ship'
WHEN 'ancient' THEN 'This ancient statue moved during the night' WHEN 'history' THEN 'The museum teaches us about local history' WHEN 'restore' THEN 'Experts will restore the damaged painting'
WHEN 'charge' THEN 'We need to charge the robot now' WHEN 'future' THEN 'Clean energy will power our future' WHEN 'connect' THEN 'Use this cable to connect the two devices'
WHEN 'storm' THEN 'Dark clouds warn us about the storm' WHEN 'sky' THEN 'The airship sailed across the bright sky' WHEN 'airship' THEN 'The royal airship landed above the clouds'
WHEN 'stone' THEN 'The secret message is carved into the stone' WHEN 'emerald' THEN 'An emerald shines inside the hidden temple' WHEN 'discover' THEN 'Explorers discover new paths through the jungle'
WHEN 'scarf' THEN 'She wrapped a warm scarf around her neck' WHEN 'village' THEN 'Snow covered every roof in the village' WHEN 'together' THEN 'The neighbors worked together to ring the bell'
ELSE CONCAT('We used the word ',vocabulary_word,' in our adventure') END
WHERE challenge_type='sentence';

UPDATE learn_english_rpg_scenes SET
prompt=CASE MOD(id,5)
 WHEN 0 THEN CONCAT('Build the sentence that uses “',vocabulary_word,'” correctly.')
 WHEN 1 THEN CONCAT('Put these words in order to describe the quest with “',vocabulary_word,'”.')
 WHEN 2 THEN CONCAT('Rearrange the words into a natural English sentence about “',vocabulary_word,'”.')
 WHEN 3 THEN CONCAT('Complete the Sentence Forge challenge using “',vocabulary_word,'”.')
 ELSE CONCAT('Make the correct sentence from the word pieces. Key word: “',vocabulary_word,'”.') END,
translation=CASE MOD(id,4)
 WHEN 0 THEN CONCAT('Susun kalimat bahasa Inggris yang menggunakan kata “',vocabulary_word,'” (',vocabulary_meaning,') dengan tepat.')
 WHEN 1 THEN CONCAT('Urutkan potongan kata menjadi kalimat alami dengan kata kunci “',vocabulary_word,'” yang berarti “',vocabulary_meaning,'”.')
 WHEN 2 THEN CONCAT('Bentuk kalimat yang benar dari kata-kata berikut. Gunakan “',vocabulary_word,'” (',vocabulary_meaning,').')
 ELSE CONCAT('Selesaikan susunan kalimat tentang petualangan ini dengan kosakata “',vocabulary_word,'” = “',vocabulary_meaning,'”.') END
WHERE challenge_type='sentence';
