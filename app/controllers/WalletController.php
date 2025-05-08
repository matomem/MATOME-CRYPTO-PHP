<?php

namespace App\Controllers;

use App\Services\LunoService;
use App\Models\Trade;

class WalletController
{
    private $lunoService;
    private $tradeModel;
    private $isProduction;

    public function __construct()
    {
        $this->lunoService = new LunoService();
        $this->tradeModel = new Trade();
        $this->isProduction = getenv('APP_ENV') === 'production';
    }

    public function index()
    {
        if (!Auth::check()) {
            Utils::redirect('/login');
        }

        try {
            // Get detailed balance information
            $balances = $this->lunoService->getDetailedBalance();
            
            // Get recent transactions
            $transactions = $this->lunoService->getTransactionHistory(null, 0, 10);
            
            // Get pending transactions
            $pendingTransactions = $this->lunoService->getPendingTransactions();

            // Sync orders in production
            if ($this->isProduction) {
                $this->tradeModel->syncOrders(Auth::id());
            }

            return [
                'balances' => $balances,
                'transactions' => $transactions,
                'pendingTransactions' => $pendingTransactions
            ];
        } catch (\Exception $e) {
            error_log('Wallet Error: ' . $e->getMessage());
            return [
                'error' => 'Failed to load wallet information. Please try again later.',
                'balances' => [],
                'transactions' => [],
                'pendingTransactions' => []
            ];
        }
    }

    public function getBalance($asset = null)
    {
        if (!Auth::check()) {
            return json_encode(['error' => 'Unauthorized']);
        }

        try {
            $balance = $asset 
                ? $this->lunoService->getAssetBalance($asset)
                : $this->lunoService->getDetailedBalance();

            if ($this->isProduction && !$balance) {
                throw new \Exception('Failed to retrieve balance information');
            }

            return json_encode([
                'success' => true,
                'data' => $balance
            ]);
        } catch (\Exception $e) {
            error_log('Get Balance Error: ' . $e->getMessage());
            return json_encode([
                'success' => false,
                'error' => 'Failed to retrieve balance information'
            ]);
        }
    }

    public function getTransactions($asset = null)
    {
        if (!Auth::check()) {
            return json_encode(['error' => 'Unauthorized']);
        }

        try {
            $transactions = $this->lunoService->getTransactionHistory($asset);
            
            if ($this->isProduction && !isset($transactions['transactions'])) {
                throw new \Exception('Failed to retrieve transaction history');
            }

            return json_encode([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            error_log('Get Transactions Error: ' . $e->getMessage());
            return json_encode([
                'success' => false,
                'error' => 'Failed to retrieve transaction history'
            ]);
        }
    }

    public function getPendingTransactions($asset = null)
    {
        if (!Auth::check()) {
            return json_encode(['error' => 'Unauthorized']);
        }

        try {
            $pendingTransactions = $this->lunoService->getPendingTransactions($asset);
            
            if ($this->isProduction && !isset($pendingTransactions['pending'])) {
                throw new \Exception('Failed to retrieve pending transactions');
            }

            return json_encode([
                'success' => true,
                'data' => $pendingTransactions
            ]);
        } catch (\Exception $e) {
            error_log('Get Pending Transactions Error: ' . $e->getMessage());
            return json_encode([
                'success' => false,
                'error' => 'Failed to retrieve pending transactions'
            ]);
        }
    }

    public function deposit()
    {
        if (!Auth::check()) {
            Utils::redirect('/login');
        }

        try {
            // Get available assets for deposit
            $balances = $this->lunoService->getDetailedBalance();
            $assets = array_keys($balances);

            if ($this->isProduction && empty($assets)) {
                throw new \Exception('No assets available for deposit');
            }

            return [
                'assets' => $assets,
                'balances' => $balances
            ];
        } catch (\Exception $e) {
            error_log('Deposit Error: ' . $e->getMessage());
            return [
                'error' => 'Failed to load deposit information. Please try again later.',
                'assets' => [],
                'balances' => []
            ];
        }
    }

    public function withdraw()
    {
        if (!Auth::check()) {
            Utils::redirect('/login');
        }

        try {
            // Get available assets for withdrawal
            $balances = $this->lunoService->getDetailedBalance();
            $assets = array_keys($balances);

            if ($this->isProduction && empty($assets)) {
                throw new \Exception('No assets available for withdrawal');
            }

            return [
                'assets' => $assets,
                'balances' => $balances
            ];
        } catch (\Exception $e) {
            error_log('Withdraw Error: ' . $e->getMessage());
            return [
                'error' => 'Failed to load withdrawal information. Please try again later.',
                'assets' => [],
                'balances' => []
            ];
        }
    }
} 