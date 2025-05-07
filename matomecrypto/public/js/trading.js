class TradingInterface {
    constructor() {
        this.currentPair = 'XBTZAR';
        this.updateInterval = 5000; // 5 seconds
        this.initializeEventListeners();
        this.startRealTimeUpdates();
    }

    initializeEventListeners() {
        // Trading pair change
        const tradingPair = document.getElementById('tradingPair');
        if (tradingPair) {
            tradingPair.addEventListener('change', (e) => {
                this.currentPair = e.target.value;
                this.updateAll();
            });
        }

        // Buy form submission
        const buyForm = document.querySelector('form:first-of-type');
        if (buyForm) {
            buyForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitOrder('BUY');
            });
        }

        // Sell form submission
        const sellForm = document.querySelector('form:last-of-type');
        if (sellForm) {
            sellForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitOrder('SELL');
            });
        }

        // Cancel order buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.cancel-order')) {
                const orderId = e.target.dataset.orderId;
                this.cancelOrder(orderId);
            }
        });
    }

    startRealTimeUpdates() {
        setInterval(() => {
            this.updateOrderBook();
            this.updateMarketStats();
            this.updateOpenOrders();
        }, this.updateInterval);
    }

    async updateAll() {
        await Promise.all([
            this.updateOrderBook(),
            this.updateMarketStats(),
            this.updateOpenOrders()
        ]);
    }

    async updateOrderBook() {
        try {
            const response = await fetch(`/api/trading/orderbook/${this.currentPair}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderOrderBook(data.data);
            }
        } catch (error) {
            console.error('Error updating order book:', error);
        }
    }

    async updateMarketStats() {
        try {
            const response = await fetch(`/api/trading/stats/${this.currentPair}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderMarketStats(data.data);
            }
        } catch (error) {
            console.error('Error updating market stats:', error);
        }
    }

    async updateOpenOrders() {
        try {
            const response = await fetch(`/api/trading/orders/${this.currentPair}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderOpenOrders(data.data);
            }
        } catch (error) {
            console.error('Error updating open orders:', error);
        }
    }

    async submitOrder(type) {
        const form = document.querySelector(`form:${type === 'BUY' ? 'first' : 'last'}-of-type`);
        const formData = new FormData(form);
        
        try {
            const response = await fetch('/api/trading/order', {
                method: 'POST',
                body: JSON.stringify({
                    pair: this.currentPair,
                    type: type,
                    price: formData.get('price'),
                    volume: formData.get('amount')
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Order placed successfully', 'success');
                form.reset();
                this.updateOpenOrders();
            } else {
                this.showNotification(data.error, 'error');
            }
        } catch (error) {
            console.error('Error submitting order:', error);
            this.showNotification('Error placing order', 'error');
        }
    }

    async cancelOrder(orderId) {
        try {
            const response = await fetch('/api/trading/order/cancel', {
                method: 'POST',
                body: JSON.stringify({ order_id: orderId }),
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Order cancelled successfully', 'success');
                this.updateOpenOrders();
            } else {
                this.showNotification(data.error, 'error');
            }
        } catch (error) {
            console.error('Error cancelling order:', error);
            this.showNotification('Error cancelling order', 'error');
        }
    }

    renderOrderBook(data) {
        const orderBookContainer = document.querySelector('.order-book');
        if (!orderBookContainer) return;

        // Render asks (sell orders)
        const asksHtml = data.asks.map(order => `
            <div class="flex justify-between text-sm text-red-600">
                <span>${parseFloat(order.price).toFixed(2)}</span>
                <span>${parseFloat(order.volume).toFixed(8)}</span>
                <span>${(parseFloat(order.price) * parseFloat(order.volume)).toFixed(2)}</span>
            </div>
        `).join('');

        // Render bids (buy orders)
        const bidsHtml = data.bids.map(order => `
            <div class="flex justify-between text-sm text-green-600">
                <span>${parseFloat(order.price).toFixed(2)}</span>
                <span>${parseFloat(order.volume).toFixed(8)}</span>
                <span>${(parseFloat(order.price) * parseFloat(order.volume)).toFixed(2)}</span>
            </div>
        `).join('');

        // Update the DOM
        orderBookContainer.innerHTML = `
            <div class="space-y-1">${asksHtml}</div>
            <div class="py-2 text-center">
                <span class="text-2xl font-bold text-secondary-900">${data.last_price}</span>
                <span class="text-sm text-secondary-500">ZAR</span>
            </div>
            <div class="space-y-1">${bidsHtml}</div>
        `;
    }

    renderMarketStats(data) {
        const statsContainer = document.querySelector('.market-stats');
        if (!statsContainer) return;

        const change = ((data.last_trade - data.open) / data.open * 100).toFixed(2);
        const changeClass = change >= 0 ? 'text-green-600' : 'text-red-600';

        statsContainer.innerHTML = `
            <div class="flex items-center space-x-2">
                <span class="text-sm text-secondary-500">24h Change:</span>
                <span class="text-sm font-medium ${changeClass}">${change}%</span>
            </div>
        `;
    }

    renderOpenOrders(data) {
        const ordersContainer = document.querySelector('tbody');
        if (!ordersContainer) return;

        ordersContainer.innerHTML = data.map(order => `
            <tr>
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium ${order.type === 'BUY' ? 'text-green-600' : 'text-red-600'}">${order.type}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">${parseFloat(order.price).toFixed(2)}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">${parseFloat(order.volume).toFixed(8)}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">${(parseFloat(order.price) * parseFloat(order.volume)).toFixed(2)}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">${order.state}</span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <button class="cancel-order text-red-600 hover:text-red-900" data-order-id="${order.order_id}">Cancel</button>
                </td>
            </tr>
        `).join('');
    }

    showNotification(message, type = 'info') {
        // Implement notification system
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

// Initialize trading interface when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.tradingInterface = new TradingInterface();
}); 