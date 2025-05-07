<!DOCTYPE html>
<html>
<head><title>Wallet</title></head>
<body>
<h1>Wallet</h1>
<ul>
    <?php foreach ($balances as $asset => $amount): ?>
        <li><?php echo Utils::sanitize($asset); ?>: <?php echo Utils::sanitize($amount); ?></li>
    <?php endforeach; ?>
</ul>
<a href="/wallet/deposit">Deposit</a> | <a href="/wallet/withdraw">Withdraw</a> | <a href="/dashboard">Dashboard</a>
</body>
</html> 