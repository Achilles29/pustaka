ALTER TABLE learn_english_rpg_scenes MODIFY challenge_type ENUM('choice','listening','sentence','story') NOT NULL DEFAULT 'choice';
UPDATE learn_english_rpg_scenes SET challenge_type='story',
 prompt=CASE WHEN sort_order=7 THEN 'A traveler needs your help. How will your hero respond?' ELSE 'The final guardian offers two paths. Which one will you choose?' END,
 choices_json=CASE WHEN sort_order=7 THEN JSON_ARRAY(
   JSON_OBJECT('text','I will stop and help the traveler.','choice_code','compassion','reputation_delta',2,'feedback','The traveler smiles. Your kindness inspires everyone nearby.'),
   JSON_OBJECT('text','I will scout ahead and make the road safe.','choice_code','courage','reputation_delta',1,'feedback','You move ahead bravely and discover a safer route.'),
   JSON_OBJECT('text','I will study the clues before we decide.','choice_code','wisdom','reputation_delta',1,'feedback','Your careful thinking reveals a clue others missed.'))
 ELSE JSON_ARRAY(
   JSON_OBJECT('text','We should forgive the guardian and work together.','choice_code','mercy','reputation_delta',2,'feedback','Mercy changes an enemy into a valuable ally.'),
   JSON_OBJECT('text','I will face the danger and protect my friends.','choice_code','valor','reputation_delta',1,'feedback','Your friends gather behind you, strengthened by your courage.'),
   JSON_OBJECT('text','Let us solve the ancient riddle instead of fighting.','choice_code','insight','reputation_delta',1,'feedback','The riddle opens a peaceful path through the final gate.')) END
WHERE sort_order IN(7,14);
