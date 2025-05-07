<?php $title = 'Dashboard - MatomeCrypto'; ?>

<div class="space-y-6">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Balance -->
        <div class="card bg-gradient-to-br from-primary-500 to-primary-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-100">Total Balance</p>
                    <p class="text-2xl font-semibold">$24,500.00</p>
                </div>
                <div class="rounded-full bg-primary-400/20 p-3">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-primary-100">+2.5% from last month</span>
            </div>
        </div>

        <!-- BTC Balance -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-secondary-500">BTC Balance</p>
                    <p class="text-2xl font-semibold text-secondary-900">0.5234 BTC</p>
                </div>
                <div class="rounded-full bg-secondary-100 p-3">
                    <svg class="h-6 w-6 text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-secondary-500">≈ $25,000.00</span>
            </div>
        </div>

        <!-- 24h Volume -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-secondary-500">24h Volume</p>
                    <p class="text-2xl font-semibold text-secondary-900">$12,345.67</p>
                </div>
                <div class="rounded-full bg-secondary-100 p-3">
                    <svg class="h-6 w-6 text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-secondary-500">+15.3% from yesterday</span>
            </div>
        </div>

        <!-- Open Orders -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-secondary-500">Open Orders</p>
                    <p class="text-2xl font-semibold text-secondary-900">5</p>
                </div>
                <div class="rounded-full bg-secondary-100 p-3">
                    <svg class="h-6 w-6 text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-secondary-500">2 buy, 3 sell orders</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <h2 class="text-lg font-medium text-secondary-900">Recent Activity</h2>
        <div class="mt-6 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <table class="min-w-full divide-y divide-secondary-300">
                        <thead>
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-secondary-900 sm:pl-0">Type</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Amount</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Price</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Total</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-secondary-900">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary-200">
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-secondary-900 sm:pl-0">Buy BTC</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">0.1 BTC</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">$45,000.00</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">$4,500.00</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Completed</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">2024-02-20 14:30</td>
                            </tr>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-secondary-900 sm:pl-0">Sell ETH</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">2.5 ETH</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">$2,800.00</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">$7,000.00</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Pending</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-secondary-500">2024-02-20 13:15</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <button class="card hover:bg-primary-50 transition-colors duration-200">
            <div class="flex items-center space-x-4">
                <div class="rounded-full bg-primary-100 p-3">
                    <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div class="text-left">
                    <h3 class="text-lg font-medium text-secondary-900">Buy Crypto</h3>
                    <p class="text-sm text-secondary-500">Purchase cryptocurrency instantly</p>
                </div>
            </div>
        </button>

        <button class="card hover:bg-primary-50 transition-colors duration-200">
            <div class="flex items-center space-x-4">
                <div class="rounded-full bg-primary-100 p-3">
                    <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div class="text-left">
                    <h3 class="text-lg font-medium text-secondary-900">Send Crypto</h3>
                    <p class="text-sm text-secondary-500">Transfer to another wallet</p>
                </div>
            </div>
        </button>

        <button class="card hover:bg-primary-50 transition-colors duration-200">
            <div class="flex items-center space-x-4">
                <div class="rounded-full bg-primary-100 p-3">
                    <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="text-left">
                    <h3 class="text-lg font-medium text-secondary-900">View Charts</h3>
                    <p class="text-sm text-secondary-500">Analyze market trends</p>
                </div>
            </div>
        </button>
    </div>
</div> 