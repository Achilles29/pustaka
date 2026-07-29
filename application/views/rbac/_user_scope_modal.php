<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($edit_user);
$user_role_ids = $is_edit ? array_map(function ($role) {
	return (int) $role['id'];
}, $edit_user['roles']) : [];
?>
<div class="modal fade" id="user-scope-modal" tabindex="-1" aria-hidden="true"<?= $is_edit ? ' data-pustaka-open-modal="1"' : ''; ?>>
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<?php if ($is_edit): ?>
				<?= form_open('rbac/users/roles/' . (int) $edit_user['id']); ?>
					<div class="modal-header">
						<h2 class="modal-title">Edit Role & Scope User</h2>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="mb-3">
							<div class="user-row-main">
								<span class="avatar avatar-md bg-blue-lt text-blue"><?= html_escape(strtoupper(substr($edit_user['full_name'], 0, 2))); ?></span>
								<div>
									<div class="fw-semibold"><?= html_escape($edit_user['full_name']); ?></div>
									<div class="text-secondary"><?= html_escape($edit_user['username']); ?><?= $edit_user['email'] ? ' - ' . html_escape($edit_user['email']) : ''; ?></div>
								</div>
							</div>
						</div>
						<div class="mb-3">
							<label class="form-label">Role</label>
							<div class="role-check-grid">
								<?php foreach ($roles as $role): ?>
									<label class="form-check">
										<input class="form-check-input" type="checkbox" name="role_ids[]" value="<?= (int) $role['id']; ?>" <?= in_array((int) $role['id'], $user_role_ids, true) ? 'checked' : ''; ?>>
										<span class="form-check-label"><?= html_escape($role['code'] . ' - ' . $role['name']); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="mb-3">
							<label class="form-label">Scope Perpustakaan</label>
							<select class="form-select" name="library_id">
								<option value="">Global/tidak dibatasi</option>
								<?php foreach ($libraries as $library): ?>
									<option value="<?= (int) $library['id']; ?>" <?= (int) $edit_user['library_id'] === (int) $library['id'] ? 'selected' : ''; ?>>
										<?= html_escape($library['name']); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
						<button type="submit" class="btn btn-primary">Simpan Role & Scope</button>
					</div>
				<?= form_close(); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
