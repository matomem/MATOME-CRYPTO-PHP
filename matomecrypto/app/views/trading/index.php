<?php $title = 'Trading - MatomeCrypto'; ?>

<div class="space-y-6">
    <!-- Trading Pair Selector -->
    <div class="flex items-center space-x-4">
        <select id="tradingPair" class="input max-w-xs">
            <option value="XBTZAR">BTC/ZAR</option>
            <option value="ETHZAR">ETH/ZAR</option>
            <option value="XRPZAR">XRP/ZAR</option>
        </select>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-secondary-500">24h Change:</span>
            <span class="text-sm font-medium text-green-600">+2.5%</span>
        </div>
    </div>

    <!-- Main Trading Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Book -->
        <div class="card lg:col-span-1">
            <h2 class="text-lg font-medium text-secondary-900 mb-4">Order Book</h2>
            <div class="space-y-2">
                <!-- Asks (Sell Orders) -->
                <div class="space-y-1">
                    <div class="flex justify-between text-sm font-medium text-secondary-500">
                        <span>Price (ZAR)</span>
                        <span>Amount (BTC)</span>
                        <span>Total</span>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm text-red-600">
                            <span>45,000.00</span>
                            <span>0.1</span>
                            <span>4,500.00</span>
                        </div>
                        <div class="flex justify-between text-sm text-red-600">
                            <span>44,950.00</span>
                            <span>0.2</span>
                            <span>8,990.00</span>
                        </div>
                    </div>
                </div>

                <!-- Current Price -->
                <div class="py-2 text-center">
                    <span class="text-2xl font-bold text-secondary-900">44,900.00</span>
                    <span class="text-sm text-secondary-500">ZAR</span>
                </div>

                <!-- Bids (Buy Orders) -->
                <div class="space-y-1">
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm text-green-600">
                            <span>44,850.00</span>
                            <span>0.15</span>
                            <span>6,727.50</span>
                        </div>
                        <div class="flex justify-between text-sm text-green-600">
                            <span>44,800.00</span>
                            <span>0.3</span>
                            <span>13,440.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Chart -->
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-medium text-secondary-900">Price Chart</h2>
                <div class="flex space-x-2">
                    <button class="btn-secondary text-sm">1H</button>
                    <button class="btn-secondary text-sm">1D</button>
                    <button class="btn-secondary text-sm">1W</button>
                    <button class="btn-secondary text-sm">1M</button>
                </div>
            </div>
            <div class="h-96 bg-secondary-50 rounded-lg flex items-center justify-center">
                <p class="text-secondary-500">Chart will be implemented with TradingView</p>
            </div>
        </div>
    </div>

    <!-- Order Forms -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Buy Form -->
        <div class="card">
            <h2 class="text-lg font-medium text-secondary-900 mb-4">Buy BTC</h2>
            <form class="space-y-4">
                <div>
                    <label class="label">Price (ZAR)</label>
                    <input type="number" class="input" placeholder="0.00">
                </div>
                <div>
                    <label class="label">Amount (BTC)</label>
                    <input type="number" class="input" placeholder="0.00">
                </div>
                <div>
                    <label class="label">Total (ZAR)</label>
                    <input type="number" class="input" placeholder="0.00" readonly>
                </div>
                <button type="submit" class="btn-primary w-full">Buy BTC</button>
            </form>
        </div>

        <!-- Sell Form -->
        <div class="card">
            <h2 class="text-lg font-medium text-secondary-900 mb-4">Sell BTC</h2>
            <form class="space-y-4">
                <div>
                    <label class="label">Price (ZAR)</label>
                    <input type="number" class="input" placeholder="0.00">
                </div>
                <div>
                    <label class="label">Amount (BTC)</label>
                    <input type="number" class="input" placeholder="0.00">
                </div>
                <div>
                    <label class="label">Total (ZAR)</label>
                    <input type="number" class="input" placeholder="0.00" readonly>
                </div>
                <button type="submit" class="btn-primary w-full bg-red-600 hover:bg-red-700">Sell BTC</button>
            </form>
        </div>
    </div>

    <!-- Open Orders -->
    <div class="card">
        <h2 class="text-lg font-medium text-secondary-900 mb-4">Open Orders</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-300">
                <thead>
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-secondary-900">Type</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Price</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Amount</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Total</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Status</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-200">
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-green-600">Buy</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">44,800.00</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">0.1</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">4,480.00</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Pending</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <button class="text-red-600 hover:text-red-900">Cancel</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trading pair change handler
    const tradingPair = document.getElementById('tradingPair');
    tradingPair.addEventListener('change', function() {
        // Update order book and chart
        updateOrderBook(this.value);
        updateChart(this.value);
    });

    // Form calculations
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const priceInput = form.querySelector('input[placeholder="0.00"]');
        const amountInput = form.querySelectorAll('input[placeholder="0.00"]')[1];
        const totalInput = form.querySelector('input[readonly]');

        [priceInput, amountInput].forEach(input => {
            input.addEventListener('input', function() {
                const price = parseFloat(priceInput.value) || 0;
                const amount = parseFloat(amountInput.value) || 0;
                totalInput.value = (price * amount).toFixed(2);
            });
        });
    });
});

function updateOrderBook(pair) {
    // Implement order book update logic
    console.log('Updating order book for pair:', pair);
}

function updateChart(pair) {
    // Implement chart update logic
    console.log('Updating chart for pair:', pair);
}
</script> 