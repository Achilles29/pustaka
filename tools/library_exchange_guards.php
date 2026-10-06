<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
/** Database holds protect even imports/synchronizers outside normal circulation models. */
function install_library_exchange_guards($db)
{
    foreach(['legacy'=>['book_items','books'],'network'=>['network_items','network_books']] as $source=>$tables){
        [$items,$books]=$tables;
        $lookup="DECLARE hold_id BIGINT DEFAULT NULL; DECLARE CONTINUE HANDLER FOR NOT FOUND SET hold_id=NULL; SELECT id INTO hold_id FROM inter_library_loans WHERE source='$source' AND item_id=OLD.id AND active_slot=1 LIMIT 1 FOR UPDATE;";
        $label=$source==='legacy'?"SET NEW.status_label='Dipinjam antarlembaga';":'';
        $body="$lookup IF hold_id IS NOT NULL THEN IF NOT(NEW.library_id<=>OLD.library_id) OR NOT(NEW.book_id<=>OLD.book_id) OR NOT(NEW.barcode<=>OLD.barcode) OR NOT(NEW.deleted_at<=>OLD.deleted_at) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Eksemplar masih dipinjam antarlembaga'; END IF; SET NEW.status='loaned'; $label END IF;";
        network_query($db,"CREATE TRIGGER IF NOT EXISTS pustaka_ill_{$source}_item_update BEFORE UPDATE ON $items FOR EACH ROW BEGIN $body END");
        network_query($db,"CREATE TRIGGER IF NOT EXISTS pustaka_ill_{$source}_item_delete BEFORE DELETE ON $items FOR EACH ROW BEGIN $lookup IF hold_id IS NOT NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Eksemplar masih dipinjam antarlembaga'; END IF; END");
        $bookLookup="DECLARE hold_id BIGINT DEFAULT NULL; DECLARE CONTINUE HANDLER FOR NOT FOUND SET hold_id=NULL; SELECT id INTO hold_id FROM inter_library_loans WHERE source='$source' AND book_id=OLD.id AND active_slot=1 LIMIT 1 FOR UPDATE;";
        network_query($db,"CREATE TRIGGER IF NOT EXISTS pustaka_ill_{$source}_book_update BEFORE UPDATE ON $books FOR EACH ROW BEGIN $bookLookup IF hold_id IS NOT NULL AND NOT(NEW.deleted_at<=>OLD.deleted_at) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Katalog masih dipinjam antarlembaga'; END IF; END");
        network_query($db,"CREATE TRIGGER IF NOT EXISTS pustaka_ill_{$source}_book_delete BEFORE DELETE ON $books FOR EACH ROW BEGIN $bookLookup IF hold_id IS NOT NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Katalog masih dipinjam antarlembaga'; END IF; END");
    }
}
