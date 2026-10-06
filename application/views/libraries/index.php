<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$map_json = json_encode($map_payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
$query_base = array_intersect_key($filters, array_flip(['q','type_id','subtype_id','district_id','status']));
$query_base['per_page'] = $per_page;
$profiles = [];
foreach ($libraries as $library) {
	$profiles[$library['id']] = array_intersect_key($library, array_flip(['name','institution_name','code','npp','type_name','subtype_name','institution_status','address','district_name','district','village_name','village','manager_name','phone','email','website','opening_hours','latitude','longitude','service_radius_meters','facilities','description','status','is_verified','iplm_population_status']));
}
foreach($profiles as $id=>&$profile){$profile['survey']=$survey_profiles[$id]??['values'=>[],'version'=>0];$profile['iplm']=$iplm_profiles[$id]??[];} unset($profile);
$map_ids=array_column($map_payload,'id');
$status_labels = [
	'active' => 'Aktif',
	'pending' => 'Pending',
	'inactive' => 'Nonaktif',
];
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="<?= base_url('assets/vendor/leaflet-markercluster/MarkerCluster.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/libraries.css'); ?>?v=<?= filemtime(FCPATH . 'assets/css/libraries.css'); ?>">

<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Direktori Terintegrasi</div>
				<h1 class="page-title">Perpustakaan GIS</h1>
			</div>
			<?php if ($can_create): ?>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('libraries/create'); ?>" class="btn btn-primary">Tambah Perpustakaan</a>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?>
			<div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
		<?php endif; ?>

		<div class="card mb-3">
			<div class="card-body">
				<?= form_open('libraries', ['method' => 'get', 'id' => 'libraries-filters', 'class' => 'row g-2 align-items-end']); ?>
					<div class="col-md-4">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Nama perpustakaan/institusi, kode, wilayah">
					</div>
					<div class="col-md-3">
						<label class="form-label">Jenis</label>
						<select name="type_id" class="form-select">
							<option value="">Semua jenis</option>
							<?php foreach ($types as $type): ?>
								<option value="<?= (int) $type['id']; ?>" <?= (int) ($filters['type_id'] ?? 0) === (int) $type['id'] ? 'selected' : ''; ?>>
									<?= html_escape($type['name']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3"><label class="form-label">Subjenis</label><select name="subtype_id" class="form-select"><option value="">Semua subjenis</option><?php foreach(($subtypes??[]) as $sub): ?><option data-type="<?= (int)$sub['library_type_id']; ?>" value="<?= (int)$sub['id']; ?>" <?= (int)($filters['subtype_id']??0)===(int)$sub['id']?'selected':''; ?>><?= html_escape($sub['name']); ?></option><?php endforeach; ?></select></div>
					<div class="col-md-3">
						<label class="form-label">Kecamatan</label>
						<select name="district_id" class="form-select">
							<option value="">Semua kecamatan</option>
							<?php foreach ($districts as $district): ?>
								<option value="<?= (int) $district['id']; ?>" <?= (int) ($filters['district_id'] ?? 0) === (int) $district['id'] ? 'selected' : ''; ?>>
									<?= html_escape(($district['full_code'] ?: $district['code']) . ' - ' . $district['name']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Status</label>
						<select name="status" class="form-select">
							<option value="">Semua status</option>
							<?php foreach (['active' => 'Aktif', 'pending' => 'Pending', 'inactive' => 'Nonaktif'] as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= $label; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-outline-primary w-100">Filter</button>
					</div>
					<div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="<?= base_url('libraries'); ?>">Reset filter</a></div>
					<div class="col-12 text-secondary small">Jenis dan subjenis memfilter tabel serta peta. Pencarian, kecamatan, status, dan jumlah baris hanya memfilter tabel. Peta tidak dibatasi halaman tabel.</div>
				<?= form_close(); ?>
			</div>
		</div>

		<div class="row row-cards">
			<div class="col-lg-7">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Peta Lokasi</h2>
						<span class="badge bg-blue-lt ms-auto" id="libraries-map-count"><?= count($map_payload); ?> titik GPS</span>
					</div>
					<div class="card-body">
						<div id="libraries-map" class="leaflet-map" aria-label="Peta lokasi perpustakaan"></div>
						<div class="library-map-hint mt-2"><span class="library-cluster-hint">12</span><span>Angka menunjukkan jumlah lokasi berdekatan. Klik kelompok atau perbesar peta untuk melihat ikon perpustakaan, lalu klik ikon untuk detail.</span></div>
						<?php if (empty($map_payload)): ?><div class="text-secondary small mt-2">Belum ada titik GPS yang valid dalam cakupan akses Anda.</div><?php endif; ?>
						<?php if (! empty($map_missing_coordinates)): ?><div class="text-warning small mt-2"><?= (int) $map_missing_coordinates; ?> perpustakaan belum memiliki koordinat GPS yang valid.</div><?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col-lg-5">
				<div class="row row-cards">
					<div class="col-6">
						<div class="card stat-card">
							<div class="card-body">
								<div class="subheader">Total hasil</div>
								<div class="h1 mb-0"><?= number_format((int) $total_libraries, 0, ',', '.'); ?></div>
							</div>
						</div>
					</div>
					<div class="col-6">
						<div class="card stat-card">
							<div class="card-body">
								<div class="subheader">Jenis aktif</div>
								<div class="h1 mb-0"><?= number_format(count($types), 0, ',', '.'); ?></div>
							</div>
						</div>
					</div>
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h2 class="card-title">Legenda</h2>
							</div>
							<div class="library-legend-grid">
								<?php $all_query = $query_base; unset($all_query['type_id'], $all_query['subtype_id']); ?>
								<section aria-labelledby="library-legend-types-title"><h3 id="library-legend-types-title" class="library-legend-title">Jenis</h3><div class="library-legend-options">
								<a class="list-group-item list-group-item-action library-legend-filter <?= empty($filters['type_id']) && empty($filters['subtype_id']) ? 'active' : ''; ?>" href="<?= html_escape(base_url('libraries?'.http_build_query($all_query))); ?>">Semua jenis <span class="small float-end">Reset jenis</span></a>
								<?php foreach ($types as $type): ?>
									<?php $legend_query = array_merge($all_query, ['type_id' => (int)$type['id']]); $selected = (int)($filters['type_id']??0)===(int)$type['id']; ?>
									<a class="list-group-item list-group-item-action d-flex align-items-center library-legend-filter <?= $selected ? 'active' : ''; ?>" aria-current="<?= $selected ? 'true' : 'false'; ?>" href="<?= html_escape(base_url('libraries?'.http_build_query($legend_query))); ?>">
										<span class="library-type-legend me-2" data-library-type="<?= html_escape($type['code']); ?>" aria-hidden="true"></span>
										<span><?= html_escape($type['name']); ?></span>
									</a>
								<?php endforeach; ?>
								</div></section>
								<section aria-labelledby="library-legend-subtypes-title"><h3 id="library-legend-subtypes-title" class="library-legend-title">Subjenis</h3><div class="library-legend-options">
								<?php
								$types_by_id = array_column($types, null, 'id');
								$sub_reset = $all_query;
								if (!empty($filters['type_id'])) $sub_reset['type_id'] = (int)$filters['type_id'];
								$visible_subtypes = array_filter($subtypes, function ($sub) use ($filters, $types_by_id) { return isset($types_by_id[$sub['library_type_id']]) && (empty($filters['type_id']) || (int)$sub['library_type_id'] === (int)$filters['type_id']); });
								?>
								<a class="list-group-item list-group-item-action library-legend-subfilter <?= empty($filters['subtype_id']) ? 'active' : ''; ?>" href="<?= html_escape(base_url('libraries?'.http_build_query($sub_reset))); ?>">Semua subjenis</a>
								<?php foreach ($visible_subtypes as $sub): $parent = $types_by_id[$sub['library_type_id']]; $sub_query = array_merge($all_query, ['type_id'=>(int)$sub['library_type_id'], 'subtype_id'=>(int)$sub['id']]); $selected = (int)($filters['subtype_id']??0)===(int)$sub['id']; ?>
								<a class="list-group-item list-group-item-action library-legend-subfilter <?= $selected ? 'active' : ''; ?>" aria-current="<?= $selected ? 'true' : 'false'; ?>" href="<?= html_escape(base_url('libraries?'.http_build_query($sub_query))); ?>"><span><?= html_escape($sub['name']); ?></span><?php if (empty($filters['type_id'])): ?><small class="d-block text-secondary"><?= html_escape($parent['name']); ?></small><?php endif; ?></a>
								<?php endforeach; ?>
								<?php if (!$visible_subtypes): ?><p class="text-secondary small p-3 mb-0">Belum ada subjenis untuk jenis ini.</p><?php endif; ?>
								</div></section>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12">
				<div class="card mb-3 library-directory-tabs" id="library-directory-tabs">
					<div class="card-body py-3">
						<div class="library-filter-tab-row"><strong>Jenis</strong><nav class="library-filter-tabs" aria-label="Filter tabel dan peta menurut jenis">
							<a class="library-filter-tab <?= empty($filters['type_id']) && empty($filters['subtype_id']) ? 'active' : ''; ?>" href="<?= html_escape(base_url('libraries?'.http_build_query($all_query).'#library-directory-tabs')); ?>">Semua jenis</a>
							<?php foreach($types as$type): $tab_query=array_merge($all_query,['type_id'=>(int)$type['id']]); $selected=(int)($filters['type_id']??0)===(int)$type['id']; ?>
							<a class="library-filter-tab <?= $selected?'active':''; ?>" aria-current="<?= $selected?'true':'false'; ?>" href="<?= html_escape(base_url('libraries?'.http_build_query($tab_query).'#library-directory-tabs')); ?>"><?= html_escape($type['name']); ?></a>
							<?php endforeach; ?>
						</nav></div>
						<div class="library-filter-tab-row mt-3"><strong>Subjenis</strong><nav class="library-filter-tabs" aria-label="Filter tabel dan peta menurut subjenis">
							<a class="library-filter-tab <?= empty($filters['subtype_id'])?'active':''; ?>" href="<?= html_escape(base_url('libraries?'.http_build_query($sub_reset).'#library-directory-tabs')); ?>">Semua subjenis</a>
							<?php foreach($visible_subtypes as$sub): $tab_query=array_merge($all_query,['type_id'=>(int)$sub['library_type_id'],'subtype_id'=>(int)$sub['id']]); $selected=(int)($filters['subtype_id']??0)===(int)$sub['id']; ?>
							<a class="library-filter-tab <?= $selected?'active':''; ?>" aria-current="<?= $selected?'true':'false'; ?>" href="<?= html_escape(base_url('libraries?'.http_build_query($tab_query).'#library-directory-tabs')); ?>"><?= html_escape($sub['name']); ?></a>
							<?php endforeach; ?>
							<?php if(!$visible_subtypes): ?><span class="text-secondary small align-self-center">Belum ada subjenis untuk jenis ini.</span><?php endif; ?>
						</nav></div>
					</div>
				</div>
				<div class="card">
					<div class="card-header libraries-table-toolbar">
						<h2 class="card-title">Data Perpustakaan</h2>
						<div class="libraries-row-control ms-md-auto">
							<label for="libraries-per-page" class="text-secondary">Baris per halaman</label>
							<select id="libraries-per-page" name="per_page" form="libraries-filters" class="form-select form-select-sm">
								<?php foreach ([10, 25, 50, 100] as $option): ?>
									<option value="<?= $option; ?>" <?= (int) $per_page === $option ? 'selected' : ''; ?>><?= $option; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="text-secondary small">
							Halaman <?= number_format((int) $page, 0, ',', '.'); ?> dari <?= number_format((int) $total_pages, 0, ',', '.'); ?>
						</div>
					</div>
					<div class="table-responsive libraries-table-scroll">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Perpustakaan</th>
									<th>Nama institusi</th>
									<th>Jenis</th>
									<th>Wilayah</th>
									<th>Koordinat</th>
									<th>Status</th>
									<th class="w-1 text-center">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($libraries)): ?>
									<tr>
										<td colspan="7" class="text-center text-secondary py-4">Belum ada data perpustakaan.</td>
									</tr>
								<?php endif; ?>
								<?php foreach ($libraries as $library): ?>
									<tr>
										<td>
											<div class="fw-semibold"><?= html_escape($library['name']); ?></div>
											<div class="text-secondary small"><code><?= html_escape($library['code']); ?></code><?= (int) $library['is_verified'] === 1 ? ' - terverifikasi' : ' - perlu verifikasi'; ?></div>
										</td>
										<td><?= html_escape($library['institution_name'] ?: '—'); ?></td>
										<td><?= html_escape($library['type_name']); ?><small class="d-block text-secondary"><?= html_escape($library['subtype_name']??''); ?></small></td>
										<td><?= html_escape(trim(($library['village_name'] ?: $library['village'] ?: '-') . ', ' . ($library['district_name'] ?: $library['district'] ?: '-'), ', ')); ?></td>
										<td><?php if(in_array((int)$library['id'],$map_ids,true)): ?><button type="button" class="btn btn-sm btn-link library-coordinate" data-library-focus="<?= (int)$library['id']; ?>" aria-label="Lihat lokasi <?= html_escape($library['name']); ?> di peta"><i class="ti ti-map-pin" aria-hidden="true"></i> <?= html_escape($library['latitude'] . ', ' . $library['longitude']); ?></button><?php else: ?><span class="text-secondary">GPS belum tersedia</span><?php endif; ?></td>
										<td>
											<span class="badge <?= $library['status'] === 'active' ? 'bg-green-lt' : ($library['status'] === 'pending' ? 'bg-yellow-lt' : 'bg-red-lt'); ?>">
												<?= html_escape($status_labels[$library['status']] ?? ucfirst($library['status'])); ?>
											</span>
										</td>
										<td>
											<div class="btn-list flex-nowrap justify-content-center library-directory-actions">
												<button type="button" class="btn btn-sm btn-icon btn-outline-info" data-library-profile="<?= (int)$library['id']; ?>" title="Detail profil" aria-label="Detail profil"><i class="ti ti-id" aria-hidden="true"></i></button>
												<?php if (!empty($current_user['is_superadmin'])): ?><a class="btn btn-sm btn-icon btn-outline-primary" title="Operasional perpustakaan" aria-label="Operasional perpustakaan" href="<?= base_url('library-workspace?library_id='.(int)$library['id']); ?>"><i class="ti ti-building-community" aria-hidden="true"></i></a><?php endif; ?>
												<?php if ($can_edit): ?>
												<a class="btn btn-sm btn-icon btn-outline-primary" title="Edit perpustakaan" aria-label="Edit perpustakaan" href="<?= base_url('libraries/edit/' . (int) $library['id']); ?>"><i class="ti ti-edit" aria-hidden="true"></i></a>
												<?= form_open('libraries/toggle/' . (int) $library['id'], ['class' => 'd-inline']); ?>
													<input type="hidden" name="libraries_csrf" value="<?= html_escape($this->session->userdata('libraries_csrf')); ?>">
													<button type="submit" class="btn btn-sm btn-icon btn-outline-secondary" title="<?= $library['status'] === 'active' ? 'Nonaktifkan perpustakaan' : 'Aktifkan perpustakaan'; ?>" aria-label="<?= $library['status'] === 'active' ? 'Nonaktifkan perpustakaan' : 'Aktifkan perpustakaan'; ?>"><i class="ti <?= $library['status'] === 'active' ? 'ti-toggle-right' : 'ti-toggle-left'; ?>" aria-hidden="true"></i></button>
												<?= form_close(); ?>
												<?php endif; ?>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
						<div class="card-footer d-flex flex-wrap gap-2 align-items-center">
							<p class="m-0 text-secondary">
								Menampilkan <?= number_format(count($libraries), 0, ',', '.'); ?> dari <?= number_format((int) $total_libraries, 0, ',', '.'); ?> data.
							</p>
							<?php if ((int) $total_pages > 1): ?>
							<ul class="pagination m-0 ms-auto">
								<?php
								$prev_query = array_merge($query_base, ['page' => max(1, (int) $page - 1)]);
								$next_query = array_merge($query_base, ['page' => min((int) $total_pages, (int) $page + 1)]);
								?>
								<li class="page-item <?= (int) $page <= 1 ? 'disabled' : ''; ?>">
									<a class="page-link" href="<?= base_url('libraries?' . http_build_query($prev_query)); ?>">Sebelumnya</a>
								</li>
								<?php
								$start = max(1, (int) $page - 2);
								$end = min((int) $total_pages, (int) $page + 2);
								for ($i = $start; $i <= $end; $i++):
									$page_query = array_merge($query_base, ['page' => $i]);
								?>
									<li class="page-item <?= $i === (int) $page ? 'active' : ''; ?>">
										<a class="page-link" href="<?= base_url('libraries?' . http_build_query($page_query)); ?>"><?= $i; ?></a>
									</li>
								<?php endfor; ?>
								<li class="page-item <?= (int) $page >= (int) $total_pages ? 'disabled' : ''; ?>">
									<a class="page-link" href="<?= base_url('libraries?' . http_build_query($next_query)); ?>">Berikutnya</a>
								</li>
							</ul>
							<?php endif; ?>
						</div>
				</div>
			</div>
		</div>
	</div>
</div>

<dialog id="library-profile-dialog" class="library-profile-dialog" aria-labelledby="library-profile-title">
	<div class="library-profile-heading"><div><div class="small text-uppercase">Profil perpustakaan</div><h2 id="library-profile-title"></h2><p id="library-profile-institution" class="mb-0"></p></div><button type="button" class="btn btn-icon btn-light" data-profile-close aria-label="Tutup detail" title="Tutup detail"><i class="ti ti-x" aria-hidden="true"></i></button></div>
	<div class="library-profile-body"><div id="library-profile-badges" class="mb-3"></div><dl id="library-profile-fields" class="library-profile-fields"></dl><h3 class="library-profile-section">Pendataan tambahan</h3><p class="text-secondary small">Data pengelola lokal; tidak menjadi nilai IPLM.</p><dl id="library-profile-survey" class="library-profile-fields"></dl><div id="library-profile-iplm"></div></div>
	<div class="library-profile-footer"><button type="button" class="btn btn-primary" data-profile-close>Tutup</button></div>
</dialog>
<script type="application/json" id="libraries-survey-fields"><?= json_encode($survey_fields,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE); ?></script>
<script type="application/json" id="libraries-profile-data"><?= json_encode($profiles, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?></script>
<script src="<?= base_url('assets/libraries-directory.js'); ?>?v=<?= filemtime(FCPATH.'assets/libraries-directory.js'); ?>"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= base_url('assets/vendor/leaflet-markercluster/leaflet.markercluster.js'); ?>"></script>
<script type="application/json" id="libraries-map-data" data-can-edit="<?= $can_edit ? '1' : '0'; ?>"><?= $map_json ?: '[]'; ?></script>
<script src="<?= base_url('assets/libraries-map.js'); ?>?v=<?= filemtime(FCPATH . 'assets/libraries-map.js'); ?>"></script>
