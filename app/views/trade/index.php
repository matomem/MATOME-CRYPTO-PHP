<!DOCTYPE html>
<html>
<head><title>Trade</title></head>
<body>
<h1>Trade</h1>
<form method="post" action="/trade/buy">
    <h2>Buy</h2>
    Pair: <input type="text" name="pair" value="XBTZAR" required>
    Amount: <input type="number" step="any" name="amount" required>
    <button type="submit">Buy</button>
</form>
<form method="post" action="/trade/sell">
    <h2>Sell</h2>
    Pair: <input type="text" name="pair" value="XBTZAR" required>
    Amount: <input type="number" step="any" name="amount" required>
    <button type="submit">Sell</button>
</form>
<?php if (isset($result)): ?>
    <p><?php echo Utils::sanitize($result['message']); ?></p>
<?php endif; ?>
<a href="/dashboard">Dashboard</a>
</body>
</html> 