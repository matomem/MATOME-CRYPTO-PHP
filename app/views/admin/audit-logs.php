<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Audit Logs</h5>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form action="/admin/audit-logs" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">User</label>
                            <input type="text" class="form-control" name="user" 
                                   value="<?= htmlspecialchars($_GET['user'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <input type="text" class="form-control" name="action" 
                                   value="<?= htmlspecialchars($_GET['action'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from" 
                                   value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to" 
                                   value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="/admin/audit-logs" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Logs Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity Type</th>
                            <th>Entity ID</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                            <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                            <td><?= htmlspecialchars($log['entity_type'] ?? '-') ?></td>
                            <td><?= $log['entity_id'] ?? '-' ?></td>
                            <td><?= htmlspecialchars($log['ip_address']) ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        onclick="showDetails(<?= htmlspecialchars(json_encode($log)) ?>)">
                                    <i class='bx bx-info-circle'></i> View
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $current_page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $current_page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Old Values</h6>
                        <pre id="oldValues" class="bg-light p-3"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6>New Values</h6>
                        <pre id="newValues" class="bg-light p-3"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDetails(log) {
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const oldValues = document.getElementById('oldValues');
    const newValues = document.getElementById('newValues');

    oldValues.textContent = JSON.stringify(log.old_values || {}, null, 2);
    newValues.textContent = JSON.stringify(log.new_values || {}, null, 2);

    modal.show();
}
</script> 