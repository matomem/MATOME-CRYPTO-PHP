<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Trading Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Balance</h6>
                    <h3 class="mb-0"><?= number_format($totalBalance, 2) ?> <?= $defaultCurrency ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">24h Profit/Loss</h6>
                    <h3 class="mb-0"><?= number_format($dailyPnL, 2) ?> <?= $defaultCurrency ?></h3>
                    <small><?= $dailyPnLPercentage ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Open Orders</h6>
                    <h3 class="mb-0"><?= $openOrders ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Active Bots</h6>
                    <h3 class="mb-0"><?= $activeBots ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Portfolio Distribution -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Portfolio Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="portfolioChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Trades -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Trades</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Pair</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Amount</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTrades as $trade): ?>
                                <tr>
                                    <td><?= htmlspecialchars($trade['pair']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $trade['type'] === 'buy' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($trade['type']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($trade['price'], 8) ?></td>
                                    <td><?= number_format($trade['amount'], 8) ?></td>
                                    <td><?= number_format($trade['total'], 2) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($trade['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Active Bots -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Active Trading Bots</h5>
                    <a href="/user/bots/create" class="btn btn-primary btn-sm">Create Bot</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Strategy</th>
                                    <th>Pair</th>
                                    <th>Status</th>
                                    <th>Profit/Loss</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeBots as $bot): ?>
                                <tr>
                                    <td><?= htmlspecialchars($bot['name']) ?></td>
                                    <td><?= htmlspecialchars($bot['strategy']) ?></td>
                                    <td><?= htmlspecialchars($bot['pair']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $bot['status'] === 'active' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($bot['status']) ?>
                                        </span>
                                    </td>
                                    <td class="<?= $bot['pnl'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($bot['pnl'], 2) ?>%
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/user/bots/edit/<?= $bot['id'] ?>" class="btn btn-sm btn-info">
                                                <i class='bx bx-edit'></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="stopBot(<?= $bot['id'] ?>)">
                                                <i class='bx bx-stop'></i>
                                            </button>
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

        <!-- Market Overview -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Market Overview</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Pair</th>
                                    <th>Price</th>
                                    <th>24h Change</th>
                                    <th>24h Volume</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($marketOverview as $market): ?>
                                <tr>
                                    <td><?= htmlspecialchars($market['pair']) ?></td>
                                    <td><?= number_format($market['price'], 8) ?></td>
                                    <td class="<?= $market['change_24h'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($market['change_24h'], 2) ?>%
                                    </td>
                                    <td><?= number_format($market['volume_24h'], 2) ?></td>
                                    <td>
                                        <a href="/user/trade/<?= $market['pair'] ?>" class="btn btn-sm btn-primary">
                                            Trade
                                        </a>
                                    </td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Portfolio Chart
    const portfolioCtx = document.getElementById('portfolioChart').getContext('2d');
    new Chart(portfolioCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($portfolioDistribution, 'asset')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($portfolioDistribution, 'value')) ?>,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});

function stopBot(botId) {
    if (confirm('Are you sure you want to stop this bot?')) {
        fetch(`/user/bots/stop/${botId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to stop bot: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while stopping the bot');
        });
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 