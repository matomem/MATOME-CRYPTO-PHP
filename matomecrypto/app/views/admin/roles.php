<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Role Management</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class='bx bx-plus'></i> Add Role
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?= $role['id'] ?></td>
                            <td><?= htmlspecialchars($role['name']) ?></td>
                            <td><?= htmlspecialchars($role['description']) ?></td>
                            <td>
                                <?php foreach ($role['permissions'] as $permission): ?>
                                    <span class="badge bg-info"><?= htmlspecialchars($permission) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td><?= $role['user_count'] ?></td>
                            <td><?= date('Y-m-d', strtotime($role['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" 
                                            onclick="editRole(<?= $role['id'] ?>)">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning" 
                                            onclick="managePermissions(<?= $role['id'] ?>)">
                                        <i class='bx bx-lock-alt'></i>
                                    </button>
                                    <?php if ($role['name'] !== 'admin'): ?>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteRole(<?= $role['id'] ?>)">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/roles/add" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="row">
                            <?php foreach ($permissions as $permission): ?>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="permissions[]" value="<?= $permission['id'] ?>"
                                           id="perm_<?= $permission['id'] ?>">
                                    <label class="form-check-label" for="perm_<?= $permission['id'] ?>">
                                        <?= htmlspecialchars($permission['name']) ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editRole(roleId) {
    window.location.href = `/admin/roles/edit/${roleId}`;
}

function managePermissions(roleId) {
    window.location.href = `/admin/roles/permissions/${roleId}`;
}

function deleteRole(roleId) {
    if (confirm('Are you sure you want to delete this role?')) {
        fetch(`/admin/roles/delete/${roleId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }
}
</script> 