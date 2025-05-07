<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">System Settings</h5>
        </div>
        <div class="card-body">
            <form action="/admin/settings/update" method="POST">
                <div class="row">
                    <!-- General Settings -->
                    <div class="col-md-6">
                        <h6 class="mb-3">General Settings</h6>
                        <div class="mb-3">
                            <label class="form-label">Site Name</label>
                            <input type="text" class="form-control" name="site_name" 
                                   value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Default Currency</label>
                            <select class="form-select" name="default_currency">
                                <option value="USD" <?= ($settings['default_currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                                <option value="EUR" <?= ($settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                                <option value="GBP" <?= ($settings['default_currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" 
                                       value="true" <?= ($settings['maintenance_mode'] ?? '') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label">Maintenance Mode</label>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="col-md-6">
                        <h6 class="mb-3">Security Settings</h6>
                        <div class="mb-3">
                            <label class="form-label">Max Login Attempts</label>
                            <input type="number" class="form-control" name="max_login_attempts" 
                                   value="<?= htmlspecialchars($settings['max_login_attempts'] ?? '5') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lockout Duration (seconds)</label>
                            <input type="number" class="form-control" name="lockout_duration" 
                                   value="<?= htmlspecialchars($settings['lockout_duration'] ?? '900') ?>">
                        </div>
                    </div>

                    <!-- Trading Settings -->
                    <div class="col-md-6 mt-4">
                        <h6 class="mb-3">Trading Settings</h6>
                        <div class="mb-3">
                            <label class="form-label">Trading Fee (%)</label>
                            <input type="number" step="0.0001" class="form-control" name="trading_fee" 
                                   value="<?= htmlspecialchars($settings['trading_fee'] ?? '0.0025') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Minimum Trade Amount</label>
                            <input type="number" step="0.01" class="form-control" name="min_trade_amount" 
                                   value="<?= htmlspecialchars($settings['min_trade_amount'] ?? '10') ?>">
                        </div>
                    </div>

                    <!-- API Settings -->
                    <div class="col-md-6 mt-4">
                        <h6 class="mb-3">API Settings</h6>
                        <div class="mb-3">
                            <label class="form-label">Luno API Key</label>
                            <input type="password" class="form-control" name="luno_api_key" 
                                   value="<?= htmlspecialchars($settings['luno_api_key'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Luno API Secret</label>
                            <input type="password" class="form-control" name="luno_api_secret" 
                                   value="<?= htmlspecialchars($settings['luno_api_secret'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any necessary JavaScript for settings management
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate numeric inputs
        const numericInputs = form.querySelectorAll('input[type="number"]');
        let isValid = true;
        
        numericInputs.forEach(input => {
            if (input.value < 0) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (isValid) {
            form.submit();
        }
    });
});
</script> 