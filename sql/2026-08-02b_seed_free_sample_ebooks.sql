-- Seed 10 legal/free sample ebooks for reader testing.
-- Source texts are Project Gutenberg public-domain/catalog ebooks rendered to PDF locally for testing.

SET @seed_user_id := (SELECT id FROM auth_user ORDER BY id LIMIT 1);
SET @cat_fiction := (SELECT id FROM book_content_categories WHERE code = 'fiksi' LIMIT 1);
SET @cat_non_fiction := (SELECT id FROM book_content_categories WHERE code = 'non-fiksi' LIMIT 1);
SET @cat_children := (SELECT id FROM book_content_categories WHERE code = 'anak-remaja' LIMIT 1);
SET @class_social := (SELECT id FROM book_classification_masters WHERE code = '300' LIMIT 1);
SET @class_literature := (SELECT id FROM book_classification_masters WHERE code = '800' LIMIT 1);

INSERT INTO books (source_system, source_id, title, statement_responsibility, publish_place, publisher, publish_year, classification, content_category_id, content_classification_id, call_number, language, abstract, status, created_by, updated_by)
VALUES
('sample_free_pdf', 'gutenberg-11', 'Alice''s Adventures in Wonderland', 'Lewis Carroll', 'Project Gutenberg', 'Project Gutenberg', '1865', '823', @cat_children, @class_literature, '823 CAR a', 'English', 'Sampel ebook public domain untuk pengujian reader PDF, katalog publik, dan kebijakan akses digital.', 'published', @seed_user_id, @seed_user_id),
('sample_free_pdf', 'gutenberg-1342', 'Pride and Prejudice', 'Jane Austen', 'Project Gutenberg', 'Project Gutenberg', '1813', '823', @cat_fiction, @class_literature, '823 AUS p', 'English', 'Sampel ebook public domain untuk pengujian reader PDF dan metadata katalog.', 'published', @seed_user_id, @seed_user_id),
('sample_free_pdf', 'gutenberg-84', 'Frankenstein', 'Mary Wollstonecraft Shelley', 'Project Gutenberg', 'Project Gutenberg', '1818', '823', @cat_fiction, @class_literature, '823 SHE f', 'English', 'Sampel ebook public domain untuk pengujian akses location_only dan sesi baca.', 'published', @seed_user_id, @seed_user_id),
('sample_free_pdf', 'gutenberg-1661', 'The Adventures of Sherlock Holmes', 'Arthur Conan Doyle', 'Project Gutenberg', 'Project Gutenberg', '1892', '823', @cat_fiction, @class_literature, '823 DOY a', 'English', 'Sampel ebook public domain untuk pengujian reader PDF dan pencarian subjek.', 'published', @seed_user_id, @seed_user_id),
('sample_free_pdf', 'gutenberg-2701', 'Moby-Dick', 'Herman Melville', 'Project Gutenberg', 'Project Gutenberg', '1851', '813', @cat_fiction, @class_literature, '813 MEL m', 'English', 'Sampel ebook public domain untuk uji file PDF berukuran menengah.', 'published', @seed_user_id, @seed_user_id),
('sample_free_pdf', 'gutenberg-98', 'A Tale of Two Cities', 'Charles Dickens', 'Project Gutenberg', 'Project Gutenberg', '1859', '823', @cat_fiction, @class_literature, '823 DIC t', 'English', 'Sampel ebook public domain untuk pengujian katalog dan reader.', 'published', @seed_user_id, @seed_user_id),
('sample_free_pdf', 'gutenberg-74', 'The Adventures of Tom Sawyer', 'Mark Twain', 'Project Gutenberg', 'Project Gutenberg', '1876', '813', @cat_children, @class_literature, '813 TWA a', 'English', 'Sampel ebook public domain untuk pengujian katalog anak dan remaja.', 'published', @seed_user_id, @seed_user_id),
('sample_free_pdf', 'gutenberg-345', 'Dracula', 'Bram Stoker', 'Project Gutenberg', 'Project Gutenberg', '1897', '823', @cat_fiction, @class_literature, '823 STO d', 'English', 'Sampel ebook public domain untuk pengujian akses digital.', 'published', @seed_user_id, @seed_user_id),
('sample_free_pdf', 'gutenberg-1400', 'Great Expectations', 'Charles Dickens', 'Project Gutenberg', 'Project Gutenberg', '1861', '823', @cat_fiction, @class_literature, '823 DIC g', 'English', 'Sampel ebook public domain untuk uji katalog dan reader PDF.', 'published', @seed_user_id, @seed_user_id),
('sample_free_pdf', 'gutenberg-1232', 'The Prince', 'Niccolo Machiavelli', 'Project Gutenberg', 'Project Gutenberg', '1532', '320', @cat_non_fiction, @class_social, '320 MAC p', 'English', 'Sampel ebook public domain untuk pengujian kategori non-fiksi.', 'published', @seed_user_id, @seed_user_id)
ON DUPLICATE KEY UPDATE
	title = VALUES(title),
	statement_responsibility = VALUES(statement_responsibility),
	publish_place = VALUES(publish_place),
	publisher = VALUES(publisher),
	publish_year = VALUES(publish_year),
	classification = VALUES(classification),
	content_category_id = VALUES(content_category_id),
	content_classification_id = VALUES(content_classification_id),
	call_number = VALUES(call_number),
	language = VALUES(language),
	abstract = VALUES(abstract),
	status = VALUES(status),
	updated_by = VALUES(updated_by),
	deleted_at = NULL,
	updated_at = CURRENT_TIMESTAMP;

DELETE ba FROM book_authors ba INNER JOIN books b ON b.id = ba.book_id WHERE b.source_system = 'sample_free_pdf';
DELETE bs FROM book_subjects bs INNER JOIN books b ON b.id = bs.book_id WHERE b.source_system = 'sample_free_pdf';
DELETE da FROM digital_assets da INNER JOIN books b ON b.id = da.book_id WHERE b.source_system = 'sample_free_pdf';

INSERT INTO book_authors (book_id, name, role, sort_order)
SELECT id, statement_responsibility, 'Author', 10 FROM books WHERE source_system = 'sample_free_pdf';

INSERT INTO book_subjects (book_id, subject)
SELECT id, subject FROM (
	SELECT b.id, 'Public domain' AS subject FROM books b WHERE b.source_system = 'sample_free_pdf'
	UNION ALL SELECT b.id, 'Sample ebook' FROM books b WHERE b.source_system = 'sample_free_pdf'
	UNION ALL SELECT b.id, cc.name FROM books b INNER JOIN book_content_categories cc ON cc.id = b.content_category_id WHERE b.source_system = 'sample_free_pdf'
) x;

INSERT INTO digital_assets (book_id, source_system, source_id, source_path, migration_status, migrated_at, file_original_name, file_path, mime_type, file_size, access_policy, is_downloadable, status, uploaded_by)
SELECT b.id, 'sample_free_pdf', 'gutenberg-11', 'https://www.gutenberg.org/cache/epub/11/pg11-images.html', 'copied', CURRENT_TIMESTAMP, 'alice-adventures-wonderland.pdf', 'storage/ebooks/free_samples/alice-adventures-wonderland.pdf', 'application/pdf', 825536, 'download_allowed', 1, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-11'
UNION ALL SELECT b.id, 'sample_free_pdf', 'gutenberg-1342', 'https://www.gutenberg.org/cache/epub/1342/pg1342-images.html', 'copied', CURRENT_TIMESTAMP, 'pride-and-prejudice.pdf', 'storage/ebooks/free_samples/pride-and-prejudice.pdf', 'application/pdf', 21670174, 'location_only', 0, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-1342'
UNION ALL SELECT b.id, 'sample_free_pdf', 'gutenberg-84', 'https://www.gutenberg.org/cache/epub/84/pg84-images.html', 'copied', CURRENT_TIMESTAMP, 'frankenstein.pdf', 'storage/ebooks/free_samples/frankenstein.pdf', 'application/pdf', 1160556, 'location_only', 0, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-84'
UNION ALL SELECT b.id, 'sample_free_pdf', 'gutenberg-1661', 'https://www.gutenberg.org/cache/epub/1661/pg1661-images.html', 'copied', CURRENT_TIMESTAMP, 'adventures-sherlock-holmes.pdf', 'storage/ebooks/free_samples/adventures-sherlock-holmes.pdf', 'application/pdf', 1931531, 'location_only', 0, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-1661'
UNION ALL SELECT b.id, 'sample_free_pdf', 'gutenberg-2701', 'https://www.gutenberg.org/cache/epub/2701/pg2701-images.html', 'copied', CURRENT_TIMESTAMP, 'moby-dick.pdf', 'storage/ebooks/free_samples/moby-dick.pdf', 'application/pdf', 3217835, 'location_only', 0, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-2701'
UNION ALL SELECT b.id, 'sample_free_pdf', 'gutenberg-98', 'https://www.gutenberg.org/cache/epub/98/pg98-images.html', 'copied', CURRENT_TIMESTAMP, 'tale-of-two-cities.pdf', 'storage/ebooks/free_samples/tale-of-two-cities.pdf', 'application/pdf', 4026550, 'location_only', 0, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-98'
UNION ALL SELECT b.id, 'sample_free_pdf', 'gutenberg-74', 'https://www.gutenberg.org/cache/epub/74/pg74-images.html', 'copied', CURRENT_TIMESTAMP, 'adventures-tom-sawyer.pdf', 'storage/ebooks/free_samples/adventures-tom-sawyer.pdf', 'application/pdf', 11514941, 'location_only', 0, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-74'
UNION ALL SELECT b.id, 'sample_free_pdf', 'gutenberg-345', 'https://www.gutenberg.org/cache/epub/345/pg345-images.html', 'copied', CURRENT_TIMESTAMP, 'dracula.pdf', 'storage/ebooks/free_samples/dracula.pdf', 'application/pdf', 2788770, 'location_only', 0, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-345'
UNION ALL SELECT b.id, 'sample_free_pdf', 'gutenberg-1400', 'https://www.gutenberg.org/cache/epub/1400/pg1400-images.html', 'copied', CURRENT_TIMESTAMP, 'great-expectations.pdf', 'storage/ebooks/free_samples/great-expectations.pdf', 'application/pdf', 12900452, 'location_only', 0, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-1400'
UNION ALL SELECT b.id, 'sample_free_pdf', 'gutenberg-1232', 'https://www.gutenberg.org/cache/epub/1232/pg1232-images.html', 'copied', CURRENT_TIMESTAMP, 'prince.pdf', 'storage/ebooks/free_samples/prince.pdf', 'application/pdf', 903922, 'download_allowed', 1, 'active', @seed_user_id FROM books b WHERE b.source_system = 'sample_free_pdf' AND b.source_id = 'gutenberg-1232';

INSERT INTO book_items (book_id, source_system, source_id, item_code, barcode, call_number, location_name, location_library_name, location_room_name, rule_name, collection_type, category_name, media_name, source_name, inventory_number, status, status_label, is_public)
SELECT b.id, 'sample_free_pdf', CONCAT(b.source_id, '-digital-item'), CONCAT('EBOOK-', REPLACE(b.source_id, 'gutenberg-', 'PG-')), CONCAT('EBOOK-', REPLACE(b.source_id, 'gutenberg-', 'PG-')), b.call_number, 'Koleksi Digital', 'Pustaka Digital Rembang', 'Reader Online', 'Baca digital', 'Ebook', 'Digital Public Domain', 'PDF', 'Project Gutenberg', CONCAT('INV-', REPLACE(b.source_id, 'gutenberg-', 'PG-')), 'available', 'Tersedia digital', 1
FROM books b
WHERE b.source_system = 'sample_free_pdf'
ON DUPLICATE KEY UPDATE
	book_id = VALUES(book_id),
	call_number = VALUES(call_number),
	location_name = VALUES(location_name),
	location_library_name = VALUES(location_library_name),
	location_room_name = VALUES(location_room_name),
	rule_name = VALUES(rule_name),
	collection_type = VALUES(collection_type),
	category_name = VALUES(category_name),
	media_name = VALUES(media_name),
	source_name = VALUES(source_name),
	inventory_number = VALUES(inventory_number),
	status = VALUES(status),
	status_label = VALUES(status_label),
	is_public = VALUES(is_public),
	deleted_at = NULL,
	updated_at = CURRENT_TIMESTAMP;

UPDATE books
SET
	cover_local_path = CASE source_id
		WHEN 'gutenberg-11' THEN 'assets/uploads/sample_ebooks/covers/alice-adventures-wonderland.png'
		WHEN 'gutenberg-1342' THEN 'assets/uploads/sample_ebooks/covers/pride-and-prejudice.png'
		WHEN 'gutenberg-84' THEN 'assets/uploads/sample_ebooks/covers/frankenstein.png'
		WHEN 'gutenberg-1661' THEN 'assets/uploads/sample_ebooks/covers/adventures-sherlock-holmes.png'
		WHEN 'gutenberg-2701' THEN 'assets/uploads/sample_ebooks/covers/moby-dick.png'
		WHEN 'gutenberg-98' THEN 'assets/uploads/sample_ebooks/covers/tale-of-two-cities.png'
		WHEN 'gutenberg-74' THEN 'assets/uploads/sample_ebooks/covers/adventures-tom-sawyer.png'
		WHEN 'gutenberg-345' THEN 'assets/uploads/sample_ebooks/covers/dracula.png'
		WHEN 'gutenberg-1400' THEN 'assets/uploads/sample_ebooks/covers/great-expectations.png'
		WHEN 'gutenberg-1232' THEN 'assets/uploads/sample_ebooks/covers/prince.png'
		ELSE cover_local_path
	END,
	cover_migration_status = 'copied',
	cover_migrated_at = CURRENT_TIMESTAMP
WHERE source_system = 'sample_free_pdf';
