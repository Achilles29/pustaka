<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model(['Catalog_model', 'Loan_model', 'Digital_donation_model', 'Reader_model']);
	}

	public function index()
	{
		$this->require_permission('catalog.index', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'content_category_id' => $this->input->get('content_category_id', true),
			'content_classification_id' => $this->input->get('content_classification_id', true),
			'source_system' => $this->input->get('source_system', true),
			'collection_type' => $this->input->get('collection_type', true),
			'category' => $this->input->get('category', true),
			'media' => $this->input->get('media', true),
			'rule' => $this->input->get('rule', true),
			'location_library' => $this->input->get('location_library', true),
			'availability' => $this->input->get('availability', true),
			'publish_year' => $this->input->get('publish_year', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Catalog_model->count_books($filters, $this->current_library_scope_id());
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;
		$stats = $this->Catalog_model->stats($this->current_library_scope_id());

		$this->render('catalog/index', [
			'title' => 'Katalog Buku',
			'stats' => $stats,
			'local_stats' => [
				['label' => 'Judul buku lokal', 'value' => $stats['books'] ?? 0],
				['label' => 'Eksemplar lokal', 'value' => $stats['items'] ?? 0],
				['label' => 'Aset digital', 'value' => $stats['digital_assets'] ?? 0],
				['label' => 'Riwayat sinkronisasi', 'value' => $stats['sync_runs'] ?? 0],
			],
			'books' => $this->Catalog_model->get_books($filters, $per_page, $offset, $this->current_library_scope_id()),
			'sync_runs' => $this->Catalog_model->recent_sync_runs(),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
				'content_category_id' => $filters['content_category_id'],
				'content_classification_id' => $filters['content_classification_id'],
				'source_system' => $filters['source_system'],
				'collection_type' => $filters['collection_type'],
				'category' => $filters['category'],
				'media' => $filters['media'],
				'rule' => $filters['rule'],
				'location_library' => $filters['location_library'],
				'availability' => $filters['availability'],
				'publish_year' => $filters['publish_year'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'content_categories' => $this->Catalog_model->get_content_categories(true),
			'classification_masters' => $this->Catalog_model->get_classification_masters(true),
			'filter_options' => $this->Catalog_model->admin_filter_options($this->current_library_scope_id()),
			'pagination' => [
				'total_rows' => $total_rows,
				'total_pages' => $total_pages,
				'page' => $page,
				'per_page' => $per_page,
				'offset' => $offset,
			],
		]);
	}

	private function report_filters()
	{
		$filters = [];
		foreach (['q','status','content_category_id','content_classification_id','source_system','collection_type','category','media','rule','location_library','availability','publish_year'] as $key) {
			$value = $this->input->get($key, true);
			$filters[$key] = is_scalar($value) ? trim((string) $value) : '';
		}
		return $filters;
	}

	private function csv_stream($filename)
	{
		if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Cache-Control: private, no-store');
		header('X-Content-Type-Options: nosniff');
		$stream = fopen('php://output', 'w');
		fwrite($stream, "\xEF\xBB\xBF");
		return $stream;
	}

	private function csv_row($stream, array $row)
	{
		$row = array_map(function ($value) {
			if (is_int($value) || is_float($value)) return $value;
			$value = (string) ($value ?? '');
			return preg_match('/^[\x00-\x20]*[=+@-]|^[\t\r\n]/u', $value) ? "'".$value : $value;
		}, $row);
		fputcsv($stream, $row, ',', '"', '');
	}

	public function export()
	{
		$this->require_permission('catalog.index', 'view');
		$this->require_permission('catalog.index', 'export');
		$filters = $this->report_filters(); $scope = $this->current_library_scope_id();
		$fields = ['id'=>'ID katalog','title'=>'Judul','subtitle'=>'Subjudul','authors'=>'Pengarang',
			'statement_responsibility'=>'Penanggung jawab','edition'=>'Edisi','publish_place'=>'Kota terbit',
			'publisher'=>'Penerbit','publish_year'=>'Tahun terbit','isbn'=>'ISBN','classification'=>'Klasifikasi',
			'content_category_name'=>'Kategori konten','content_classification_name'=>'Klasifikasi konten',
			'call_number'=>'Nomor panggil','language'=>'Bahasa','physical_description'=>'Deskripsi fisik',
			'subjects'=>'Subjek','abstract'=>'Abstrak','status'=>'Status','source_system'=>'Sumber data',
			'source_id'=>'ID sumber','collection_types'=>'Jenis koleksi','libraries'=>'Perpustakaan',
			'item_count'=>'Jumlah eksemplar sesuai filter','available_count'=>'Eksemplar tersedia sesuai filter',
			'created_at'=>'Tanggal masuk aplikasi','updated_at'=>'Terakhir diperbarui'];
		$rows = $this->catalog_export_rows($fields, $filters, $scope);
		if ($this->input->get('format') === 'xlsx') {
			$this->download_excel('katalog-detail-'.date('Y-m-d').'.xlsx','Katalog',array_values($fields),$rows);
			return;
		}
		$rows->rewind(); // Fetch the first batch before sending CSV response headers.
		$stream = $this->csv_stream('katalog-detail-'.date('Y-m-d').'.csv');
		$this->csv_row($stream, array_values($fields));
		foreach ($rows as $row) $this->csv_row($stream, $row);
		fclose($stream);
	}

	private function catalog_export_rows(array $fields, array $filters, $scope)
	{
		$batch = $this->Catalog_model->export_batch($filters, $scope);
		while ($batch) {
			foreach ($batch as $book) {
				$row = [];
				foreach ($fields as $key=>$label) {
					$value = $book[$key] ?? '';
					$row[] = in_array($key,['id','item_count','available_count'],true) ? (int)$value : $value;
				}
				yield $row; $last_id = (int) $book['id'];
			}
			if (connection_aborted()) break;
			$batch = $this->Catalog_model->export_batch($filters, $scope, $last_id);
		}
	}

	private function download_excel($filename, $sheet_name, array $headers, iterable $rows)
	{
		if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
		$this->load->library('Catalog_xlsx');
		try {
			$path = $this->catalog_xlsx->build($sheet_name,$headers,$rows);
		} catch (Throwable $e) {
			log_message('error','Catalog Excel export: '.$e->getMessage());
			show_error('Unduhan Excel belum dapat dibuat. Silakan coba kembali atau gunakan CSV.',500);
			return;
		}
		try {
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Content-Length: '.filesize($path));
			header('Cache-Control: private, no-store');
			header('X-Content-Type-Options: nosniff');
			readfile($path);
		} finally { unlink($path); }
	}

	public function annual_report()
	{
		$this->require_permission('catalog.index', 'view');
		$filters = $this->report_filters();
		$format = $this->input->get('format');
		if (in_array($format,['csv','xlsx'],true)) $this->require_permission('catalog.index', 'export');
		$report = $this->Catalog_model->annual_development($filters, $this->current_library_scope_id());
		if (in_array($format,['csv','xlsx'],true)) {
			$headers = ['Tahun masuk katalog','Total judul unik','Judul fisik (termasuk keduanya)','Judul digital (termasuk keduanya)',
				'Judul fisik & digital','Judul belum teridentifikasi','Eksemplar fisik saat ini','Seluruh rekaman item saat ini',
				'Kumulatif judul unik','Kumulatif judul fisik','Kumulatif judul digital','Perubahan total judul tahunan (%)'];
			$rows = [];
			foreach ($report['rows'] as $row) $rows[] = [$row['year'],$row['titles'],$row['physical_titles'],$row['digital_titles'],
				$row['hybrid_titles'],$row['unclassified_titles'],$row['physical_copies'],$row['copies'],
				$row['cumulative'],$row['physical_cumulative'],$row['digital_cumulative'],$row['growth']];
			if ($report['unknown']['titles']) {
				$r = $report['unknown'];
				$rows[] = ['Tanggal tidak diketahui',(int)$r['titles'],(int)$r['physical_titles'],(int)$r['digital_titles'],
					(int)$r['hybrid_titles'],(int)$r['unclassified_titles'],(int)$r['physical_copies'],(int)$r['copies'],null,null,null,null];
			}
			if ($format === 'xlsx') {
				$this->download_excel('perkembangan-katalog-'.date('Y-m-d').'.xlsx','Perkembangan tahunan',$headers,$rows);
				return;
			}
			$stream = $this->csv_stream('perkembangan-katalog-'.date('Y-m-d').'.csv');
			$this->csv_row($stream, $headers);
			foreach ($rows as $row) $this->csv_row($stream, $row);
			fclose($stream); return;
		}
		$this->render('catalog/annual_report', ['title'=>'Perkembangan Katalog per Tahun', 'report'=>$report, 'filters'=>$filters]);
	}

	public function detail($id)
	{
		$this->require_permission('catalog.index', 'view');

		$book = $this->Catalog_model->get_book((int) $id, $this->current_library_scope_id());
		if (! $book) {
			show_404();
			return;
		}

		$items=$this->Catalog_model->get_book_items((int) $id, 50, $this->current_library_scope_id());
		$this->render('catalog/detail', [
			'title' => 'Detail Buku',
			'book' => $book,
			'authors' => $this->Catalog_model->get_book_authors((int) $id),
			'subjects' => $this->Catalog_model->get_book_subjects((int) $id),
			'items' => $items,
			'digital_assets' => $this->Catalog_model->get_book_digital_assets((int) $id),
			'collection_types' => $this->Catalog_model->get_collection_types(true),
			'reference_options' => $this->book_item_reference_options(),
			'can_create_item' => $this->can('catalog.index', 'create'),
			'can_edit_item' => $this->can('catalog.index', 'edit'),
			'can_delete_item' => $this->can('catalog.index', 'delete'),
		]);
		$this->output->set_output($this->decorate_catalog_qr($this->output->get_output(),$book,$items));
	}

	public function print_item_labels($book_id,$item_id=null)
	{
		$this->require_permission('catalog.index','view');$book=$this->Catalog_model->get_book((int)$book_id,$this->current_library_scope_id());if(!$book){show_404();return;}$items=$item_id!==null?array_filter([$this->Catalog_model->get_book_item((int)$item_id,(int)$book_id,$this->current_library_scope_id())]):$this->Catalog_model->get_book_items((int)$book_id,1000,$this->current_library_scope_id());if(!$items){show_error('Belum ada eksemplar yang dapat dicetak.',404,'Label Eksemplar');return;}$labels='';$scripts='';foreach(array_values($items) as $index=>$item){$qrId='item-qr-'.$index;$url=base_url('katalog/eksemplar/'.(int)$item['id']);$identity=$item['barcode']?:($item['inventory_number']?:$item['item_code']);$call=$item['call_number']?:'';$location=$item['location_room_name']?:($item['location_name']?:$item['location_library_name']);$labels.='<article class="label" data-item-id="'.(int)$item['id'].'"><div class="brand">PUSTAKA DIGITAL REMBANG · ITEM '.(int)$item['id'].'</div><div class="content"><div id="'.$qrId.'" class="qr"></div><div class="copy"><h2>'.html_escape($book['title']).'</h2><div class="call">'.html_escape($call?:'No. panggil belum diisi').'</div><div class="identity">'.html_escape($identity).'</div><div class="meta">Kode item: '.html_escape($item['item_code']?:'-').'<br>No. induk: '.html_escape($item['inventory_number']?:'-').'<br>Lokasi: '.html_escape($location?:'-').'</div></div></div></article>';$scripts.='new QRCode(document.getElementById('.json_encode($qrId).'),{text:'.json_encode($url).',width:92,height:92,colorDark:"#061a40",colorLight:"#fff",correctLevel:QRCode.CorrectLevel.M});';}$html='<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Label Eksemplar - '.html_escape($book['title']).'</title><style>@page{size:A4;margin:10mm}*{box-sizing:border-box}body{margin:0;background:#eef2f7;color:#061a40;font-family:Arial,sans-serif}.toolbar{position:sticky;top:0;z-index:2;display:flex;justify-content:center;gap:8px;padding:12px;background:#fff;border-bottom:1px solid #d8e1ec}.toolbar button,.toolbar a{border:1px solid #1767aa;border-radius:8px;padding:8px 14px;background:#fff;color:#075da7;text-decoration:none;font-weight:700}.toolbar button{background:#075da7;color:#fff}.sheet{display:grid;grid-template-columns:repeat(3,62mm);gap:5mm;margin:10mm auto;width:196mm}.label{width:62mm;height:40mm;padding:4mm;border:1px dashed #8ca2b8;border-radius:2mm;background:#fff;break-inside:avoid}.brand{margin-bottom:2mm;font-size:6.5pt;font-weight:800;letter-spacing:.06em}.content{display:flex;gap:3mm}.qr{flex:none;width:25mm;height:25mm;padding:1mm;border:1px solid #dbe4ed}.qr img{width:100%!important;height:100%!important}.copy{min-width:0;flex:1}.copy h2{display:-webkit-box;overflow:hidden;margin:0 0 1mm;font-size:7.5pt;line-height:1.15;-webkit-box-orient:vertical;-webkit-line-clamp:2}.call{font:800 9pt monospace}.identity{overflow-wrap:anywhere;margin-top:.7mm;font:700 7pt monospace}.meta{margin-top:.7mm;color:#52677e;font-size:5.6pt;line-height:1.2}@media(max-width:760px){.sheet{grid-template-columns:62mm;width:62mm}.toolbar{flex-wrap:wrap}}@media print{body{background:#fff}.toolbar{display:none}.sheet{margin:0;width:196mm}}</style></head><body><div class="toolbar"><a href="'.base_url('catalog/detail/'.(int)$book_id).'">Kembali</a><button onclick="window.print()">Cetak '.count($items).' Label Eksemplar</button></div><main class="sheet">'.$labels.'</main><script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script><script>'.$scripts.'</script></body></html>';$this->output->set_output($html);
	}

	public function create()
	{
		$this->require_permission('catalog.index', 'create');

		$this->render('catalog/form', [
			'title' => 'Tambah Katalog',
			'action' => 'catalog/store',
			'book' => null,
			'authors' => [],
			'subjects' => [],
			'content_categories' => $this->Catalog_model->get_content_categories(true),
			'classification_masters' => $this->Catalog_model->get_classification_masters(true),
			'collection_types' => $this->Catalog_model->get_collection_types(true),
			'textbook_grade_levels' => $this->Catalog_model->get_textbook_grade_levels(),
			'textbook_subject_options' => $this->Catalog_model->get_textbook_subjects(),
			'textbook_tags' => ['grades' => [], 'subjects' => []],
		]);
	}

	public function create_from_donation($id)
	{
		$this->require_permission('catalog.index', 'create');
		$donation = $this->Digital_donation_model->find((int) $id);
		if (! $donation) { show_404(); return; }
		if ($donation['status'] !== 'accepted') {
			$this->session->set_flashdata('error', 'Donasi harus berstatus Diterima sebelum dimasukkan ke katalog.');
			redirect('digital-donations'); return;
		}
		if (! empty($donation['catalog_book_id'])) { redirect('catalog/detail/' . (int) $donation['catalog_book_id']); return; }
		$book = ['id'=>0,'title'=>$donation['title'],'statement_responsibility'=>$donation['creator_names'],'publish_year'=>$donation['publication_year'],'language'=>$donation['language'],'abstract'=>$donation['description'],'status'=>'draft'];
		$this->render('catalog/form', [
			'title'=>'Katalogkan Donasi Digital','action'=>'catalog/store_from_donation/' . (int) $id,'book'=>$book,'authors'=>[],'subjects'=>[],'donation'=>$donation,
			'content_categories'=>$this->Catalog_model->get_content_categories(true),'classification_masters'=>$this->Catalog_model->get_classification_masters(true),'collection_types'=>$this->Catalog_model->get_collection_types(true),
			'textbook_grade_levels'=>$this->Catalog_model->get_textbook_grade_levels(),'textbook_subject_options'=>$this->Catalog_model->get_textbook_subjects(),'textbook_tags'=>['grades'=>[],'subjects'=>[]],
		]);
	}

	public function store()
	{
		$this->require_permission('catalog.index', 'create');
		try {
			$input = $this->book_input();
			$input = $this->attach_uploaded_cover($input);
			$book_id = $this->Catalog_model->create_book($input, (int) ($this->current_user['id'] ?? 0));
			$this->Catalog_model->save_book_textbook_tags($book_id, (array) $this->input->post('textbook_grade_ids'), (array) $this->input->post('textbook_subject_ids'));
			$this->audit_event('catalog.create', 'books', $book_id, null, $input);
			$this->session->set_flashdata('success', 'Katalog baru berhasil disimpan.');
			redirect('catalog/detail/' . $book_id);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('catalog/create');
		}
	}

	public function store_from_donation($id)
	{
		$this->require_permission('catalog.index', 'create');
		$donation = $this->Digital_donation_model->find((int) $id);
		try {
			if (! $donation || $donation['status'] !== 'accepted') throw new RuntimeException('Donasi tidak valid atau belum diterima.');
			if (! empty($donation['catalog_book_id'])) throw new RuntimeException('Donasi ini sudah dimasukkan ke katalog.');
			$this->db->trans_begin();
			$input = $this->attach_uploaded_cover($this->book_input());
			$input['status'] = 'draft';
			$book_id = $this->Catalog_model->create_book($input, (int) ($this->current_user['id'] ?? 0));
			$this->Catalog_model->save_book_textbook_tags($book_id, (array) $this->input->post('textbook_grade_ids'), (array) $this->input->post('textbook_subject_ids'));
			$this->Digital_donation_model->link_catalog((int) $id, $book_id, (int) ($this->current_user['id'] ?? 0));
			if (! empty($donation['file_path'])) {
				if (strtolower((string)$donation['file_mime_type']) === 'application/pdf') $this->create_donation_reader_asset($donation, $book_id, (int)$id);
			}
			$this->db->trans_complete();
			if (! $this->db->trans_status()) throw new RuntimeException('Gagal menautkan donasi dengan katalog.');
			$this->audit_event('digital_donation.catalog', 'books', $book_id, null, ['digital_donation_id'=>(int)$id] + $input);
			$this->session->set_flashdata('success', 'Donasi berhasil dimasukkan sebagai draft katalog.');
			redirect('catalog/detail/' . $book_id);
		} catch (Throwable $e) {
			$this->db->trans_rollback();
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('digital-donations/catalog/' . (int) $id);
		}
	}

	private function create_donation_reader_asset(array $donation, $book_id, $donation_id)
	{
		$license_urls=['cc0'=>'https://creativecommons.org/publicdomain/zero/1.0/','cc_by'=>'https://creativecommons.org/licenses/by/4.0/','cc_by_sa'=>'https://creativecommons.org/licenses/by-sa/4.0/','public_domain'=>'https://creativecommons.org/publicdomain/mark/1.0/'];
		$this->Reader_model->create_asset(['book_id'=>(int)$book_id,'source_system'=>'digital_donation','source_id'=>(string)$donation_id,'source_path'=>$donation['file_path'],'file_original_name'=>$donation['file_original_name'],'file_path'=>$donation['file_path'],'mime_type'=>'application/pdf','file_size'=>$donation['file_size'],'reader_audience'=>'internal','pdf_delivery'=>'render_locked','status'=>'draft','rights_basis'=>$donation['license_code']==='public_domain'?'public_domain':'licensed','rights_holder'=>$donation['donor_name'],'license_url'=>$license_urls[$donation['license_code']]??null,'permission_reference'=>$donation['rights_statement'],'access_notes'=>'Berasal dari donasi digital #' . (int)$donation_id], (int) ($this->current_user['id'] ?? 0));
	}

	public function edit($id)
	{
		$this->require_permission('catalog.index', 'edit');

		$book = $this->Catalog_model->get_book((int) $id, $this->current_library_scope_id());
		if (! $book) {
			show_404();
			return;
		}

		$this->render('catalog/form', [
			'title' => 'Edit Katalog',
			'action' => 'catalog/update/' . (int) $id,
			'book' => $book,
			'authors' => $this->Catalog_model->get_book_authors((int) $id),
			'subjects' => $this->Catalog_model->get_book_subjects((int) $id),
			'content_categories' => $this->Catalog_model->get_content_categories(true),
			'classification_masters' => $this->Catalog_model->get_classification_masters(true),
			'collection_types' => $this->Catalog_model->get_collection_types(true),
			'textbook_grade_levels' => $this->Catalog_model->get_textbook_grade_levels(),
			'textbook_subject_options' => $this->Catalog_model->get_textbook_subjects(),
			'textbook_tags' => $this->Catalog_model->get_book_textbook_tags((int) $id),
		]);
	}

	public function update($id)
	{
		$this->require_permission('catalog.index', 'edit');

		$before = $this->Catalog_model->get_book((int) $id, $this->current_library_scope_id());
		if (! $before) {
			show_404();
			return;
		}

		try {
			$input = $this->book_input();
			$input = $this->attach_uploaded_cover($input);
			$this->Catalog_model->update_book((int) $id, $input, (int) ($this->current_user['id'] ?? 0));
			$this->Catalog_model->save_book_textbook_tags((int) $id, (array) $this->input->post('textbook_grade_ids'), (array) $this->input->post('textbook_subject_ids'));
			$this->audit_event('catalog.update', 'books', (int) $id, $before, $input);
			$this->session->set_flashdata('success', 'Katalog berhasil diperbarui.');
			redirect('catalog/detail/' . (int) $id);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('catalog/edit/' . (int) $id);
		}
	}

	public function delete($id)
	{
		$this->require_permission('catalog.index', 'delete');

		$before = $this->Catalog_model->get_book((int) $id, $this->current_library_scope_id());
		if (! $before) {
			show_404();
			return;
		}

		$this->Catalog_model->soft_delete_book((int) $id, (int) ($this->current_user['id'] ?? 0));
		$this->audit_event('catalog.delete', 'books', (int) $id, $before, ['deleted_at' => date('Y-m-d H:i:s')]);
		$this->session->set_flashdata('success', 'Katalog disembunyikan dari data aktif.');
		redirect('catalog');
	}

	public function highlights()
	{
		$this->require_permission('catalog.highlights', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'target_type' => $this->input->get('target_type', true),
			'highlight_type' => $this->input->get('highlight_type', true),
			'status' => $this->input->get('status', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Catalog_model->count_highlights($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;
		$edit_id = (int) $this->input->get('edit_id', true);
		$edit_highlight = $edit_id > 0 ? $this->Catalog_model->get_highlight($edit_id) : null;

		$this->render('catalog/highlights', [
			'title' => 'Highlight Katalog',
			'highlights' => $this->Catalog_model->get_highlights($filters, $per_page, $offset),
			'edit_highlight' => $edit_highlight,
			'selected_book' => $edit_highlight
				? $this->Catalog_model->get_highlight_book((int) ($edit_highlight['book_id'] ?? 0))
				: null,
			'content_categories' => $this->Catalog_model->get_content_categories(true),
			'filters' => [
				'q' => $filters['q'],
				'target_type' => $filters['target_type'],
				'highlight_type' => $filters['highlight_type'],
				'status' => $filters['status'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'pagination' => [
				'total_rows' => $total_rows,
				'total_pages' => $total_pages,
				'page' => $page,
				'per_page' => $per_page,
				'offset' => $offset,
			],
			'can_create_highlight' => $this->can('catalog.highlights', 'create'),
			'can_edit_highlight' => $this->can('catalog.highlights', 'edit'),
			'can_delete_highlight' => $this->can('catalog.highlights', 'delete'),
		]);
	}

	public function highlight_book_search()
	{
		$this->require_permission('catalog.highlights', 'view');

		$kind = (string) $this->input->get('kind', true);
		$books = $this->Catalog_model->search_highlight_books(
			$this->input->get('q', true),
			in_array($kind, ['digital', 'non_digital'], true) ? $kind : 'all',
			12
		);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(['items' => $books], JSON_UNESCAPED_UNICODE));
	}

	public function store_highlight()
	{
		$this->require_permission('catalog.highlights', 'create');

		try {
			$highlight_id = $this->Catalog_model->save_highlight($this->highlight_input(), null, (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('catalog.highlight_create', 'catalog_highlights', $highlight_id, null, $this->highlight_input());
			$this->session->set_flashdata('success', 'Highlight katalog berhasil ditambahkan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('catalog/highlights');
	}

	public function update_highlight($id)
	{
		$this->require_permission('catalog.highlights', 'edit');

		$before = $this->Catalog_model->get_highlight((int) $id);
		if (! $before) {
			show_404();
			return;
		}

		try {
			$this->Catalog_model->save_highlight($this->highlight_input(), (int) $id, (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('catalog.highlight_update', 'catalog_highlights', (int) $id, $before, $this->Catalog_model->get_highlight((int) $id));
			$this->session->set_flashdata('success', 'Highlight katalog berhasil diperbarui.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('catalog/highlights?edit_id=' . (int) $id);
			return;
		}

		redirect('catalog/highlights');
	}

	public function delete_highlight($id)
	{
		$this->require_permission('catalog.highlights', 'delete');

		$before = $this->Catalog_model->get_highlight((int) $id);
		if (! $before) {
			show_404();
			return;
		}

		$this->Catalog_model->delete_highlight((int) $id);
		$this->audit_event('catalog.highlight_delete', 'catalog_highlights', (int) $id, $before, null);
		$this->session->set_flashdata('success', 'Highlight katalog berhasil dihapus.');
		redirect('catalog/highlights');
	}

	public function store_item($book_id)
	{
		$this->require_permission('catalog.index', 'create');

		$book = $this->Catalog_model->get_book((int) $book_id, $this->current_library_scope_id());
		if (! $book) {
			show_404();
			return;
		}

		try {
			$item_id = $this->Catalog_model->create_book_item((int) $book_id, $this->book_item_input(), $this->current_library_scope_id());
			$this->audit_event('catalog.item_create', 'book_items', $item_id, null, $this->book_item_input());
			$this->session->set_flashdata('success', 'Eksemplar berhasil ditambahkan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('catalog/detail/' . (int) $book_id);
	}

	public function update_item($book_id, $item_id)
	{
		$this->require_permission('catalog.index', 'edit');

		$before = $this->Catalog_model->get_book_item((int) $item_id, (int) $book_id, $this->current_library_scope_id());
		if (! $before) {
			show_404();
			return;
		}

		try {
			$this->Catalog_model->update_book_item((int) $item_id, (int) $book_id, $this->book_item_input(), $this->current_library_scope_id());
			$this->audit_event('catalog.item_update', 'book_items', (int) $item_id, $before, $this->book_item_input());
			$this->session->set_flashdata('success', 'Eksemplar berhasil diperbarui.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('catalog/detail/' . (int) $book_id);
	}

	public function delete_item($book_id, $item_id)
	{
		$this->require_permission('catalog.index', 'delete');

		$before = $this->Catalog_model->get_book_item((int) $item_id, (int) $book_id, $this->current_library_scope_id());
		if (! $before) {
			show_404();
			return;
		}

		$this->Catalog_model->soft_delete_book_item((int) $item_id, (int) $book_id, $this->current_library_scope_id());
		$this->audit_event('catalog.item_delete', 'book_items', (int) $item_id, $before, ['deleted_at' => date('Y-m-d H:i:s')]);
		$this->session->set_flashdata('success', 'Eksemplar dinonaktifkan dari data aktif.');
		redirect('catalog/detail/' . (int) $book_id);
	}

	public function sync()
	{
		$this->require_permission('catalog.sync', 'view');
		$this->render('catalog/sync', [
			'title' => 'Sinkronisasi Katalog',
			'source_stats' => $this->Catalog_model->source_stats(),
			'sync_runs' => $this->Catalog_model->recent_sync_runs(20),
			'can_run_sync' => $this->can('catalog.sync', 'create'),
		]);
	}

	public function run_sync()
	{
		$this->require_permission('catalog.sync', 'create');

		$limit = (int) $this->input->post('limit', true);
		$mode = (string) $this->input->post('mode', true);
		$result = $this->Catalog_model->run_manual_sync((int) ($this->current_user['id'] ?? 0), $limit ?: 500, $mode);

		$this->audit_event('catalog.sync_run', 'catalog_sync_runs', (int) $result['run_id'], null, $result);
		$this->session->set_flashdata('success', $result['message']);
		redirect('catalog/sync');
	}

	public function requests()
	{
		$this->require_permission('catalog.requests', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'request_type' => $this->input->get('request_type', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Catalog_model->count_book_requests($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('catalog/requests', [
			'title' => 'Request Buku',
			'requests' => $this->Catalog_model->get_book_requests($filters, $per_page, $offset),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
				'request_type' => $filters['request_type'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'pagination' => [
				'total_rows' => $total_rows,
				'total_pages' => $total_pages,
				'page' => $page,
				'per_page' => $per_page,
				'offset' => $offset,
			],
			'loan_settings' => $this->Loan_model->get_settings(),
			'loan_stats' => $this->Loan_model->stats(),
			'can_create_loan' => $this->can('catalog.requests', 'create'),
			'can_manage_loan_settings' => $this->can('catalog.index', 'edit'),
		]);
	}

	/** Daftar transaksi sirkulasi: transaksi aplikasi dan histori sinkron berada dalam satu data. */
	public function loans()
	{
		$this->require_permission('catalog.requests', 'view');
		$this->load->add_package_path(APPPATH . 'controllers');
		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'source' => $this->input->get('source', true),
			'date_from' => $this->input->get('date_from', true),
			'date_to' => $this->input->get('date_to', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Loan_model->count_loans($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);

		$this->render('catalog/loans', [
			'title' => 'Transaksi Peminjaman',
			'loans' => $this->Loan_model->get_loans($filters, $per_page, ($page - 1) * $per_page),
			'filters' => array_merge($filters, ['per_page' => $per_page, 'page' => $page]),
			'pagination' => ['total_rows' => $total_rows, 'total_pages' => $total_pages, 'page' => $page, 'per_page' => $per_page, 'offset' => ($page - 1) * $per_page],
			'stats' => $this->Loan_model->stats(),
			'loan_settings' => $this->Loan_model->get_settings(),
			'can_create_loan' => $this->can('catalog.requests', 'create'),
			'can_return_loan' => $this->can('catalog.requests', 'approve'),
			'can_renew_loan' => $this->can('catalog.requests', 'approve'),
			'can_send_overdue_wa' => $this->can('catalog.requests', 'approve') && $this->db->table_exists('wa_outbox'),
		]);
	}

	public function manual_loan_lookup()
	{
		$this->require_permission('catalog.requests', 'create');
		$type = (string) $this->input->get('type', true);
		$query = trim((string) $this->input->get('q', true));
		$data = $type === 'member' ? $this->Loan_model->search_members($query) : ($type === 'item' ? $this->Loan_model->search_loan_items($query) : []);
		$settings = $this->Loan_model->get_settings();
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'ok'=>true,'data'=>$data,'default_due_date'=>date('Y-m-d',strtotime('+' . max(1,min(60,(int)$settings['default_loan_days'])) . ' days')),
		], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
	}

	public function issue_manual_loan()
	{
		$this->require_permission('catalog.requests', 'create');
		try {
			$result = $this->Loan_model->issue_manual_loan(
				$this->input->post('member_lookup', true),
				$this->input->post('item_lookup', true),
				$this->input->post('due_date', true),
				(int) ($this->current_user['id'] ?? 0)
			);
			$this->audit_event('catalog.loan.issue', 'loan_transaction_items', (int) $result['loan_item_id'], null, [
				'reference' => $result['reference'], 'member_id' => $result['member']['id'], 'book_item_id' => $result['item']['id'], 'due_date' => $result['due_date'],
			]);
			$this->session->set_flashdata('success', 'Peminjaman berhasil dicatat. Referensi: ' . $result['reference'] . '. Jatuh tempo: ' . date('d M Y', strtotime($result['due_date'])) . '.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/loans');
	}

	/** Serahkan eksemplar yang sebelumnya telah disiapkan dari request member. */
	public function issue_request_loan($request_id)
	{
		$this->require_permission('catalog.requests', 'approve');
		try {
			$result = $this->Loan_model->issue_book_request(
				(int) $request_id,
				$this->input->post('due_date', true),
				(int) ($this->current_user['id'] ?? 0)
			);
			$this->audit_event('catalog.request_issue', 'loan_transaction_items', (int) $result['loan_item_id'], null, [
				'request_id' => (int) $request_id,
				'request_code' => $result['request_code'],
				'reference' => $result['reference'],
				'member_id' => $result['member']['id'],
				'book_item_id' => $result['item']['id'],
				'due_date' => $result['due_date'],
			]);
			$this->queue_request_status_whatsapp((int) $request_id, 'Buku diserahkan dan sedang dipinjam', null, (int) ($this->current_user['id'] ?? 0), $result['due_date']);
			$this->session->set_flashdata('success', 'Buku diserahkan dan transaksi peminjaman dicatat. Referensi: ' . $result['reference'] . '.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/requests');
	}

	public function return_loan($loan_item_id)
	{
		$this->require_permission('catalog.requests', 'approve');
		try {
			$result = $this->Loan_model->return_loan((int) $loan_item_id, (int) ($this->current_user['id'] ?? 0), $this->input->post('return_note', true));
			$this->audit_event('catalog.loan.return', 'loan_transaction_items', (int) $loan_item_id, null, ['late_days' => $result['late_days'], 'legacy_local_override' => ! empty($result['is_legacy']), 'return_note' => $this->input->post('return_note', true)]);
			$prefix = ! empty($result['is_legacy']) ? 'Pengembalian legacy dicatat secara lokal' : 'Pengembalian';
			$this->session->set_flashdata('success', $prefix . ' untuk ' . $result['title'] . ' berhasil disimpan' . ($result['late_days'] > 0 ? '. Terlambat ' . $result['late_days'] . ' hari.' : '.') );
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/loans');
	}

	/** Perpanjang jatuh tempo tanpa mengubah data mentah dari INLISLite. */
	public function renew_loan($loan_item_id)
	{
		$this->require_permission('catalog.requests', 'approve');
		try {
			$result = $this->Loan_model->renew_loan(
				(int) $loan_item_id,
				$this->input->post('new_due_date', true),
				(int) ($this->current_user['id'] ?? 0),
				$this->input->post('renewal_note', true)
			);
			$this->audit_event('catalog.loan.renew', 'loan_transaction_items', (int) $loan_item_id, ['due_date'=>$result['old_due_date']], ['due_date'=>$result['new_due_date'],'renewal_count'=>$result['renewal_count'],'note'=>$result['note']]);
			$this->session->set_flashdata('success', 'Peminjaman “' . $result['title'] . '” berhasil diperpanjang sampai ' . date('d M Y', strtotime($result['new_due_date'])) . '.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/loans');
	}

	/** Pesan keterlambatan manual dari daftar transaksi. */
	public function notify_overdue_loan($loan_item_id)
	{
		$this->require_permission('catalog.requests', 'approve');
		try {
			if (! $this->db->table_exists('wa_outbox')) throw new RuntimeException('WA Center belum dipasang.');
			$this->load->model('Whatsapp_model');
			$result = $this->Whatsapp_model->queue_overdue_loan((int) $loan_item_id, (int) ($this->current_user['id'] ?? 0), true);
			$this->audit_event('catalog.loan.overdue_whatsapp', 'loan_transaction_items', (int) $loan_item_id, null, $result);
			$this->session->set_flashdata('success', $result['message']);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/loans?status=overdue');
	}

	/** Cocokkan kembali status semua eksemplar dengan peminjaman efektif. */
	public function reconcile_loans()
	{
		$this->require_permission('catalog.requests', 'approve');
		try {
			$result = $this->Loan_model->reconcile_item_availability();
			$this->audit_event('catalog.loan.reconcile_availability', 'book_items', null, null, $result);
			$this->session->set_flashdata('success', 'Ketersediaan eksemplar diselaraskan: ' . (int) $result['loaned'] . ' ditandai dipinjam, ' . (int) $result['available'] . ' dibuka kembali.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/loans');
	}

	public function save_loan_settings()
	{
		$this->require_permission('catalog.index', 'edit');
		try {
			$settings = $this->Loan_model->save_settings([
				'is_loan_enabled' => $this->input->post('is_loan_enabled'),
				'default_loan_days' => $this->input->post('default_loan_days', true),
				'max_active_loans' => $this->input->post('max_active_loans', true),
			], (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('catalog.loan.settings', 'library_loan_settings', 1, null, $settings);
			$this->session->set_flashdata('success', 'Pengaturan peminjaman diperbarui.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/requests');
	}

	public function update_request($id)
	{
		$this->require_permission('catalog.requests', 'approve');

		try {
			$new_status = (string) $this->input->post('status', true);
			$admin_note = $this->input->post('admin_note', true);
			$this->Catalog_model->update_book_request_status(
				(int) $id,
				$new_status,
				$admin_note,
				(int) ($this->current_user['id'] ?? 0)
			);
			$this->queue_request_status_whatsapp((int) $id, $this->request_status_label($new_status), $admin_note, (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('catalog.request_update', 'book_requests', (int) $id, null, $this->input->post(null, true));
			$this->session->set_flashdata('success', 'Status request buku diperbarui.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/requests');
	}

	private function queue_request_status_whatsapp($requestId, $status, $note = null, $createdBy = null, $dueDate = null)
	{
		if (! $this->db->table_exists('wa_outbox')) return;
		$this->load->model('Whatsapp_model');
		$row = $this->db->select('br.request_code,br.requester_name,br.requester_phone,b.title,m.id AS member_id,m.full_name,m.phone')
			->from('book_requests br')->join('books b','b.id=br.book_id','left')->join('members m','m.id=br.member_id','left')->where('br.id',(int)$requestId)->get()->row_array();
		if (! $row) return;
		$this->Whatsapp_model->queue_template('loan_request_status', $row['phone'] ?: $row['requester_phone'], [
			'member_name' => $row['full_name'] ?: $row['requester_name'], 'book_title' => $row['title'] ?: 'Koleksi perpustakaan',
			'request_code' => $row['request_code'], 'status' => $status, 'staff_note' => trim((string) $note) ?: '',
			'due_date' => $dueDate ? date('d M Y', strtotime($dueDate)) : '',
		], (int) ($row['member_id'] ?? 0) ?: null, $createdBy ?: null);
	}

	private function request_status_label($status)
	{
		$labels=['approved'=>'Buku sedang disiapkan','rejected'=>'Ditolak','cancelled'=>'Dibatalkan','fulfilled'=>'Buku diserahkan'];
		return $labels[$status] ?? ucfirst((string) $status);
	}

	private function book_input()
	{
		return [
			'title' => $this->input->post('title', true),
			'subtitle' => $this->input->post('subtitle', true),
			'statement_responsibility' => $this->input->post('statement_responsibility', true),
			'authors' => $this->input->post('authors', true),
			'subjects' => $this->input->post('subjects', true),
			'edition' => $this->input->post('edition', true),
			'publish_place' => $this->input->post('publish_place', true),
			'publisher' => $this->input->post('publisher', true),
			'publish_year' => $this->input->post('publish_year', true),
			'isbn' => $this->input->post('isbn', true),
			'classification' => $this->input->post('classification', true),
			'content_category_id' => $this->input->post('content_category_id', true),
			'content_classification_id' => $this->input->post('content_classification_id', true),
			'collection_type' => $this->input->post('collection_type', true),
			'call_number' => $this->input->post('call_number', true),
			'language' => $this->input->post('language', true),
			'physical_description' => $this->input->post('physical_description', true),
			'abstract' => $this->input->post('abstract', true),
			'cover_path' => $this->input->post('cover_path', true),
			'status' => $this->input->post('status', true),
		];
	}

	/** Simpan cover manual secara aman; hanya gambar valid yang dapat diakses publik. */
	private function attach_uploaded_cover(array $input)
	{
		$file = $_FILES['cover_file'] ?? [];
		if (empty($file['name']) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return $input;
		if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
			$messages = [UPLOAD_ERR_INI_SIZE => 'Ukuran cover melebihi batas server.', UPLOAD_ERR_FORM_SIZE => 'Ukuran cover melebihi batas formulir.', UPLOAD_ERR_PARTIAL => 'Upload cover belum selesai. Coba unggah ulang.', UPLOAD_ERR_NO_FILE => 'Pilih berkas cover terlebih dahulu.'];
			throw new RuntimeException($messages[(int) $file['error']] ?? 'Upload cover gagal. Kode: ' . (int) $file['error']);
		}
		if (empty($file['tmp_name']) || ! is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Cover tidak dapat dibaca dari upload. Pilih ulang berkas gambar.');
		if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > 5 * 1024 * 1024) throw new RuntimeException('Ukuran cover maksimal 5 MB.');
		$info = @getimagesize($file['tmp_name']);
		$mime = (string) ($info['mime'] ?? '');
		$extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
		if (! isset($extensions[$mime])) throw new RuntimeException('Cover harus berupa gambar JPG, PNG, atau WebP yang valid.');
		if ((int) ($info[0] ?? 0) < 80 || (int) ($info[1] ?? 0) < 80) throw new RuntimeException('Ukuran cover minimal 80 × 80 piksel.');
		$relative = 'assets/uploads/catalog/covers/' . date('Y/m') . '/';
		$directory = FCPATH . $relative;
		if (! is_dir($directory) && ! mkdir($directory, 0755, true)) throw new RuntimeException('Folder cover tidak dapat dibuat. Periksa permission folder upload.');
		$name = 'cover-' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
		if (! move_uploaded_file($file['tmp_name'], $directory . $name)) throw new RuntimeException('Server tidak dapat menyimpan cover. Periksa permission folder upload.');
		$input['uploaded_cover_path'] = $relative . $name;
		return $input;
	}

	private function decorate_catalog_qr($html,array $book,array $items=[])
	{
		$published=($book['status']??'')==='published';$allLabels=base_url('catalog/detail/'.(int)$book['id'].'/labels');
		$card='<div class="card admin-card mb-3"><div class="card-body d-flex flex-wrap align-items-center gap-3"><div class="avatar avatar-lg bg-blue-lt text-blue"><i class="ti ti-qrcode fs-1"></i></div><div class="flex-fill"><div class="section-kicker">QR katalog per eksemplar</div><h2 class="h3 mb-1">Satu eksemplar, satu QR unik</h2><p class="text-secondary mb-2">QR tampil pada identitas setiap eksemplar dan memuat tujuan yang berbeda. '.(!$published?'Tautan publik aktif setelah judul diterbitkan dan eksemplar masuk OPAC.':'Saat dipindai, katalog menyorot identitas, lokasi, dan status eksemplar terkait.').'</p>'.($items?'<a class="btn btn-primary btn-sm" target="_blank" href="'.html_escape($allLabels).'"><i class="ti ti-printer me-1"></i>Cetak Semua Label Eksemplar</a>':'<span class="badge bg-secondary-lt">Belum ada eksemplar</span>').'</div></div></div>';
		foreach($items as $item){
			$barcode='<code>'.html_escape($item['barcode']?:'-').'</code>';
			$target='<div class="fw-semibold">'.$barcode.'</div>';
			$itemUrl=base_url('katalog/eksemplar/'.(int)$item['id']);
			$labelUrl=base_url('catalog/detail/'.(int)$book['id'].'/label/'.(int)$item['id']);
			$identity=$item['barcode']?:($item['inventory_number']?:$item['item_code']);
			$qr='<div class="catalog-item-qr-wrap"><div class="catalog-item-qr" data-catalog-item-qr data-url="'.html_escape($itemUrl).'" aria-label="QR eksemplar '.html_escape($identity).'"></div><a class="btn btn-sm btn-outline-primary" target="_blank" href="'.html_escape($labelUrl).'" title="Cetak label eksemplar"><i class="ti ti-printer me-1"></i>Cetak QR</a>'.((int)($item['is_public']??0)!==1?'<span class="text-secondary small">Aktif setelah masuk OPAC</span>':'').'</div>';
			$html=preg_replace_callback('/'.preg_quote($target,'/').'/',function()use($target,$qr){return str_replace('class="fw-semibold"','class="fw-semibold catalog-item-identity"',$target).$qr;},$html,1);
		}
		$html=preg_replace('/<div class="card admin-card mb-3">/',$card.'<div class="card admin-card mb-3">',$html,1);
		$script='<style>.catalog-item-qr-wrap{display:flex;flex-direction:column;align-items:flex-start;gap:5px;margin-top:8px}.catalog-item-qr{width:76px;height:76px;padding:4px;border:1px solid #d8e3ee;border-radius:8px;background:#fff}.catalog-item-qr img,.catalog-item-qr canvas{display:block;width:66px!important;height:66px!important}</style><script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script><script>(function(){if(!window.QRCode)return;document.querySelectorAll("[data-catalog-item-qr]").forEach(function(el){if(el.dataset.rendered)return;new QRCode(el,{text:el.dataset.url,width:66,height:66,colorDark:"#062d62",colorLight:"#ffffff",correctLevel:QRCode.CorrectLevel.M});el.dataset.rendered="1";});})();</script>';
		return str_replace('</body>',$script.'</body>',$html);
	}

	private function book_item_input()
	{
		return [
			'barcode' => $this->input->post('barcode', true),
			'item_code' => $this->input->post('item_code', true),
			'inventory_number' => $this->input->post('inventory_number', true),
			'call_number' => $this->input->post('call_number', true),
			'collection_type' => $this->input->post('collection_type', true),
			'source_location_library_id' => $this->input->post('source_location_library_id', true),
			'source_location_id' => $this->input->post('source_location_id', true),
			'source_rule_id' => $this->input->post('source_rule_id', true),
			'source_category_id' => $this->input->post('source_category_id', true),
			'source_media_id' => $this->input->post('source_media_id', true),
			'source_collection_source_id' => $this->input->post('source_collection_source_id', true),
			'source_status_id' => $this->input->post('source_status_id', true),
			'status' => $this->input->post('status', true),
			'is_public' => $this->input->post('is_public') ? 1 : 0,
			'is_loanable' => $this->input->post('is_loanable') ? 1 : 0,
		];
	}

	private function highlight_input()
	{
		return [
			'target_type' => $this->input->post('target_type', true),
			'book_id' => $this->input->post('book_id', true),
			'content_category_id' => $this->input->post('content_category_id', true),
			'highlight_type' => $this->input->post('highlight_type', true),
			'label' => $this->input->post('label', true),
			'title_override' => $this->input->post('title_override', true),
			'summary' => $this->input->post('summary', true),
			'sort_order' => $this->input->post('sort_order', true),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'starts_at' => $this->input->post('starts_at', true),
			'ends_at' => $this->input->post('ends_at', true),
		];
	}

	private function book_item_reference_options()
	{
		return [
			'location_libraries' => $this->Catalog_model->get_master_references('location_library'),
			'locations' => $this->Catalog_model->get_master_references('locations'),
			'rules' => $this->Catalog_model->get_master_references('collectionrules'),
			'categories' => $this->Catalog_model->get_master_references('collectioncategorys'),
			'medias' => $this->Catalog_model->get_master_references('collectionmedias'),
			'sources' => $this->Catalog_model->get_master_references('collectionsources'),
			'statuses' => $this->Catalog_model->get_master_references('collectionstatus'),
		];
	}
}
