<?php
declare(strict_types=1);

$path = $argv[1] ?? '/tmp/season1-scenes.tsv';
$handle = fopen($path, 'rb');
$types = [];
$bad = [];
$prompts = [];
$positions = [];

while (($line = fgets($handle)) !== false) {
    $fields = explode("\t", rtrim($line, "\n"));
    if (count($fields) < 9) { $bad[] = 'Baris tidak lengkap'; continue; }
    $code = $fields[0]; $sort = $fields[1]; $type = $fields[2];
    $choices = json_decode($fields[3], true);
    $types[$type] = ($types[$type] ?? 0) + 1;
    $key = mb_strtolower(trim($fields[5])); $prompts[$key] = ($prompts[$key] ?? 0) + 1;
    if ($type === 'sentence') {
        if (trim($fields[4]) === '') $bad[] = "$code#$sort sentence answer kosong";
        continue;
    }
    // Story scenes use narrative branch metadata rather than a single
    // correct-answer flag, so validate that the JSON is well formed but do
    // not require a sentence answer or a marked correct option.
    if ($type === 'story') {
        if (!is_array($choices) || count($choices) < 2) $bad[] = "$code#$sort pilihan story tidak valid";
        continue;
    }
    if (!is_array($choices) || count($choices) < 2) { $bad[] = "$code#$sort pilihan tidak valid"; continue; }
    $correct = 0; $texts = []; $correctPosition = null;
    foreach ($choices as $position => $choice) { if (!empty($choice['correct'])) { $correct++; $correctPosition = $position; } $texts[] = mb_strtolower(trim((string)($choice['text'] ?? ''))); }
    if ($correctPosition !== null) $positions[$correctPosition] = ($positions[$correctPosition] ?? 0) + 1;
    if ($correct !== 1) $bad[] = "$code#$sort jumlah kunci=$correct";
    if (count($texts) !== count(array_unique($texts))) $bad[] = "$code#$sort pilihan duplikat";
}
fclose($handle);

arsort($prompts);
echo 'Tipe: '.json_encode($types, JSON_UNESCAPED_UNICODE).PHP_EOL;
echo 'Distribusi posisi kunci (choice/listening): '.json_encode($positions).PHP_EOL;
echo 'Temuan fatal: '.count($bad).PHP_EOL;
foreach ($bad as $item) echo "- $item\n";
echo 'Prompt teratas (bukan otomatis salah):'.PHP_EOL;
foreach (array_slice($prompts, 0, 15, true) as $prompt => $count) echo "$count\t$prompt\n";
