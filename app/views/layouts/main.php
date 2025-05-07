<!DOCTYPE html>
<html lang="en" class="h-full bg-secondary-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MatomeCrypto' ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full">
    <div x-data="{ mobileMenuOpen: false }" class="min-h-full">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex flex-shrink-0 items-center">
                            <img class="h-8 w-auto" src="/images/logo.svg" alt="MatomeCrypto">
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="/" class="inline-flex items-center border-b-2 border-primary-500 px-1 pt-1 text-sm font-medium text-secondary-900">Dashboard</a>
                            <a href="/trading" class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-secondary-500 hover:border-secondary-300 hover:text-secondary-700">Trading</a>
                            <a href="/wallet" class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-secondary-500 hover:border-secondary-300 hover:text-secondary-700">Wallet</a>
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <button type="button" class="rounded-full bg-white p-1 text-secondary-400 hover:text-secondary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            <span class="sr-only">View notifications</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </button>
                        <div class="relative ml-3">
                            <div>
                                <button type="button" class="flex rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" id="user-menu-button">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="inline-flex items-center justify-center rounded-md p-2 text-secondary-400 hover:bg-secondary-100 hover:text-secondary-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500">
                            <span class="sr-only">Open main menu</span>
                            <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div x-show="mobileMenuOpen" class="sm:hidden">
                <div class="space-y-1 pb-3 pt-2">
                    <a href="/" class="block border-l-4 border-primary-500 bg-primary-50 py-2 pl-3 pr-4 text-base font-medium text-primary-700">Dashboard</a>
                    <a href="/trading" class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-secondary-500 hover:border-secondary-300 hover:bg-secondary-50 hover:text-secondary-700">Trading</a>
                    <a href="/wallet" class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-secondary-500 hover:border-secondary-300 hover:bg-secondary-50 hover:text-secondary-700">Wallet</a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
            <div class="mt-8 md:mt-0">
                <p class="text-center text-base text-secondary-400">&copy; 2024 MatomeCrypto. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html> 