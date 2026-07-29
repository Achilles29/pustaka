<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="modal fade" id="user-modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<?= form_open('rbac/users/store'); ?>
				<div class="modal-header">
					<h2 class="modal-title">Tambah User</h2>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Username</label>
						<input type="text" class="form-control" name="username" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Nama lengkap</label>
						<input type="text" class="form-control" name="full_name" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Email</label>
						<input type="email" class="form-control" name="email">
					</div>
					<div class="mb-3">
						<label class="form-label">Password awal</label>
						<input type="password" class="form-control" name="password" minlength="6" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Scope Perpustakaan</label>
						<select class="form-select" name="library_id">
							<option value="">Global/tidak dibatasi</option>
							<?php foreach ($libraries as $library): ?>
								<option value="<?= (int) $library['id']; ?>"><?= html_escape($library['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Role</label>
						<div class="stacked-checks">
							<?php foreach ($roles as $role): ?>
								<label class="form-check">
									<input class="form-check-input" type="checkbox" name="role_ids[]" value="<?= (int) $role['id']; ?>">
									<span class="form-check-label"><?= html_escape($role['code'] . ' - ' . $role['name']); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary">Buat User</button>
				</div>
			<?= form_close(); ?>
		</div>
	</div>
</div>
