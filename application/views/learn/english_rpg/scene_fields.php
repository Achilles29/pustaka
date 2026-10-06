<?php $edit=!empty($scene);$opts=$choices??[];$type=$edit?($scene['challenge_type']??'choice'):'choice'; ?>
<?= form_open($action); ?>
<div class="row g-3">
  <div class="col-2"><label class="form-label">Urutan</label><input class="form-control" type="number" min="1" name="sort_order" value="<?= $edit?(int)$scene['sort_order']:count($scenes)+1; ?>"></div>
  <div class="col-4"><label class="form-label">Lokasi</label><input class="form-control" name="place" value="<?= $edit?html_escape($scene['place']):''; ?>" required></div>
  <div class="col-3"><label class="form-label">Karakter</label><input class="form-control" name="speaker" value="<?= $edit?html_escape($scene['speaker']):''; ?>" required></div>
  <div class="col-3"><label class="form-label">Tipe tantangan</label><select class="form-select" name="challenge_type"><option value="choice" <?= $type==='choice'?'selected':''; ?>>Pilihan ganda</option><option value="listening" <?= $type==='listening'?'selected':''; ?>>Listening</option><option value="sentence" <?= $type==='sentence'?'selected':''; ?>>Sentence Forge</option><option value="story" <?= $type==='story'?'selected':''; ?>>Pilihan cerita</option></select></div>
  <div class="col-2"><label class="form-label">Emoji</label><input class="form-control" name="emoji" value="<?= $edit?html_escape($scene['emoji']):'🧙'; ?>"></div>
  <div class="col-10"><label class="form-label">Dialog Inggris</label><textarea class="form-control" name="dialogue" required><?= $edit?html_escape($scene['dialogue']):''; ?></textarea></div>
  <div class="col-12"><label class="form-label">Terjemahan</label><textarea class="form-control" name="translation"><?= $edit?html_escape($scene['translation']):''; ?></textarea></div>
  <div class="col-12"><label class="form-label">Pertanyaan/perintah</label><input class="form-control" name="prompt" value="<?= $edit?html_escape($scene['prompt']):''; ?>" required></div>
  <div class="col-md-6"><label class="form-label">Teks audio (opsional)</label><textarea class="form-control" name="audio_text" rows="2"><?= $edit?html_escape($scene['audio_text']):''; ?></textarea></div>
  <div class="col-md-6"><label class="form-label">Jawaban Sentence Forge (opsional)</label><input class="form-control" name="sentence_answer" value="<?= $edit?html_escape($scene['sentence_answer']):''; ?>"><small class="form-hint">Tulis kalimat lengkap dalam bahasa Inggris.</small></div>
  <?php for($i=0;$i<3;$i++): $o=$opts[$i]??[]; ?><div class="col-md-7"><label class="form-label">Pilihan <?= chr(65+$i); ?></label><input class="form-control" name="choice_text[]" value="<?= html_escape($o['text']??''); ?>" <?= $i<2?'required':''; ?>></div><div class="col-md-5"><label class="form-label">Pembahasan <?= chr(65+$i); ?></label><input class="form-control" name="choice_feedback[]" value="<?= html_escape($o['feedback']??''); ?>"></div><?php endfor; ?>
  <div class="col-md-4"><label class="form-label">Jawaban benar</label><select class="form-select" name="correct_choice"><?php for($i=0;$i<3;$i++): ?><option value="<?= $i; ?>" <?= !empty($opts[$i]['correct'])?'selected':''; ?>><?= chr(65+$i); ?></option><?php endfor; ?></select><small class="form-hint">Untuk pilihan cerita, metadata pilihan lama tetap dipertahankan.</small></div>
  <div class="col-md-4"><label class="form-label">Kosakata</label><input class="form-control" name="vocabulary_word" value="<?= $edit?html_escape($scene['vocabulary_word']):''; ?>"></div>
  <div class="col-md-4"><label class="form-label">Arti</label><input class="form-control" name="vocabulary_meaning" value="<?= $edit?html_escape($scene['vocabulary_meaning']):''; ?>"></div>
  <div class="col-12"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?= !$edit||$scene['is_active']?'checked':''; ?>><span class="form-check-label">Aktif</span></label></div>
</div>
<button class="btn btn-primary mt-3">Simpan Adegan</button><?= form_close(); ?>
