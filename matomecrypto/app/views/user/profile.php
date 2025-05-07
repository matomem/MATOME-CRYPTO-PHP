<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form action="/user/profile/update" method="POST" id="profileForm">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" 
                                   value="<?= htmlspecialchars($user['username']) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" 
                                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Security Settings</h5>
                </div>
                <div class="card-body">
                    <!-- Change Password -->
                    <form action="/user/profile/change-password" method="POST" id="passwordForm">
                        <h6 class="mb-3">Change Password</h6>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>

                    <hr class="my-4">

                    <!-- Two-Factor Authentication -->
                    <div class="mb-4">
                        <h6 class="mb-3">Two-Factor Authentication</h6>
                        <?php if ($user['2fa_enabled']): ?>
                            <p class="text-success">
                                <i class='bx bx-check-circle'></i> Two-factor authentication is enabled
                            </p>
                            <form action="/user/profile/disable-2fa" method="POST">
                                <button type="submit" class="btn btn-danger">Disable 2FA</button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted">
                                <i class='bx bx-lock-alt'></i> Two-factor authentication is not enabled
                            </p>
                            <a href="/user/profile/enable-2fa" class="btn btn-primary">Enable 2FA</a>
                        <?php endif; ?>
                    </div>

                    <!-- Login History -->
                    <div>
                        <h6 class="mb-3">Recent Login History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>IP Address</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($loginHistory as $login): ?>
                                    <tr>
                                        <td><?= date('Y-m-d H:i:s', strtotime($login['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($login['ip_address']) ?></td>
                                        <td><?= htmlspecialchars($login['location'] ?? 'Unknown') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile form validation
    const profileForm = document.getElementById('profileForm');
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Add any necessary validation
        this.submit();
    });

    // Password form validation
    const passwordForm = document.getElementById('passwordForm');
    passwordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const newPassword = this.querySelector('[name="new_password"]').value;
        const confirmPassword = this.querySelector('[name="confirm_password"]').value;

        if (newPassword !== confirmPassword) {
            alert('New passwords do not match!');
            return;
        }

        if (newPassword.length < 8) {
            alert('Password must be at least 8 characters long!');
            return;
        }

        this.submit();
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 