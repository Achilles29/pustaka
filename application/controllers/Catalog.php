<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model(['Catalog_model', 'Loan_model']);
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

	public function detail($id)
	{
		$this->require_permission('catalog.index', 'view');

		$book = $this->Catalog_model->get_book((int) $id, $this->current_library_scope_id());
		if (! $book) {
			show_404();
			return;
		}

		$this->render('catalog/detail', [
			'title' => 'Detail Buku',
			'book' => $book,
			'authors' => $this->Catalog_model->get_book_authors((int) $id),
			'subjects' => $this->Catalog_model->get_book_subjects((int) $id),
			'items' => $this->Catalog_model->get_book_items((int) $id, 50, $this->current_library_scope_id()),
			'digital_assets' => $this->Catalog_model->get_book_digital_assets((int) $id),
			'collection_types' => $this->Catalog_model->get_collection_types(true),
			'reference_options' => $this->book_item_reference_options(),
			'can_create_item' => $this->can('catalog.index', 'create'),
			'can_edit_item' => $this->can('catalog.index', 'edit'),
			'can_delete_item' => $this->can('catalog.index', 'delete'),
		]);
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

	public function store()
	{
		$this->require_permission('catalog.index', 'create');

		try {
			$book_id = $this->Catalog_model->create_book($this->book_input(), (int) ($this->current_user['id'] ?? 0));
			$this->Catalog_model->save_book_textbook_tags($book_id, (array) $this->input->post('textbook_grade_ids'), (array) $this->input->post('textbook_subject_ids'));
			$this->audit_event('catalog.create', 'books', $book_id, null, $this->book_input());
			$this->session->set_flashdata('success', 'Katalog baru berhasil disimpan.');
			redirect('catalog/detail/' . $book_id);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('catalog/create');
		}
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
			$this->Catalog_model->update_book((int) $id, $this->book_input(), (int) ($this->current_user['id'] ?? 0));
			$this->Catalog_model->save_book_textbook_tags((int) $id, (array) $this->input->post('textbook_grade_ids'), (array) $this->input->post('textbook_subject_ids'));
			$this->audit_event('catalog.update', 'books', (int) $id, $before, $this->book_input());
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
			'can_create_loan' => $this->can('catalog.requests', 'create'),
			'can_return_loan' => $this->can('catalog.requests', 'approve'),
		]);
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
			$this->Catalog_model->update_book_request_status(
				(int) $id,
				(string) $this->input->post('status', true),
				$this->input->post('admin_note', true),
				(int) ($this->current_user['id'] ?? 0)
			);
			$this->audit_event('catalog.request_update', 'book_requests', (int) $id, null, $this->input->post(null, true));
			$this->session->set_flashdata('success', 'Status request buku diperbarui.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/requests');
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
