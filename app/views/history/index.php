<!DOCTYPE html>
<html>
<head><title>Transaction History</title></head>
<body>
<h1>Transaction History</h1>
<ul>
    <?php foreach ($history as $tx): ?>
        <li><?php echo Utils::sanitize($tx['type'] ?? $tx['row_index'] ?? ''); ?> - <?php echo Utils::sanitize($tx['amount'] ?? ''); ?> <?php echo Utils::sanitize($tx['currency'] ?? $tx['account_id'] ?? ''); ?> (<?php echo Utils::sanitize($tx['timestamp'] ?? $tx['created_at'] ?? ''); ?>)</li>
    <?php endforeach; ?>
</ul>
<a href="/dashboard">Dashboard</a>
</body>
</html> 