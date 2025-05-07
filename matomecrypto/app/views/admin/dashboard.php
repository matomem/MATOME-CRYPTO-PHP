<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="card-text"><?= $total_users ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Trades</h5>
                    <h2 class="card-text"><?= $active_trades ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Volume</h5>
                    <h2 class="card-text"><?= number_format($total_volume ?? 0, 2) ?> USD</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">System Status</h5>
                    <h2 class="card-text"><?= $system_settings['maintenance_mode'] === 'true' ? 'Maintenance' : 'Active' ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Users -->
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i', strtotime($activity['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($activity['username']) ?></td>
                                    <td><?= htmlspecialchars($activity['action']) ?></td>
                                    <td><?= htmlspecialchars($activity['details']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Users</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($recent_users as $user): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?= htmlspecialchars($user['username']) ?></h6>
                                <small><?= date('Y-m-d', strtotime($user['created_at'])) ?></small>
                            </div>
                            <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="btn-group">
                        <a href="/admin/users" class="btn btn-primary">Manage Users</a>
                        <a href="/admin/roles" class="btn btn-info">Manage Roles</a>
                        <a href="/admin/settings" class="btn btn-success">System Settings</a>
                        <a href="/admin/audit-logs" class="btn btn-warning">View Audit Logs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add any necessary JavaScript for charts or real-time updates
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any charts or real-time updates here
});
</script> 