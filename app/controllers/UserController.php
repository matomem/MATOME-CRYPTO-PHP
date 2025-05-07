<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Trade;
use App\Models\Bot;
use App\Models\Market;
use App\Models\AuditLog;
use App\Services\AuthService;
use App\Services\TwoFactorService;

class UserController extends BaseController
{
    private $userModel;
    private $tradeModel;
    private $botModel;
    private $marketModel;
    private $auditLogModel;
    private $authService;
    private $twoFactorService;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->tradeModel = new Trade();
        $this->botModel = new Bot();
        $this->marketModel = new Market();
        $this->auditLogModel = new AuditLog();
        $this->authService = new AuthService();
        $this->twoFactorService = new TwoFactorService();

        // Ensure user is logged in
        if (!$this->authService->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    public function dashboard()
    {
        $userId = $this->authService->getUserId();
        
        // Get user's total balance and PnL
        $totalBalance = $this->userModel->getTotalBalance($userId);
        $dailyPnL = $this->tradeModel->getDailyPnL($userId);
        $dailyPnLPercentage = $this->tradeModel->getDailyPnLPercentage($userId);
        
        // Get portfolio distribution
        $portfolioDistribution = $this->userModel->getPortfolioDistribution($userId);
        
        // Get recent trades
        $recentTrades = $this->tradeModel->getRecentTrades($userId, 5);
        
        // Get active bots
        $activeBots = $this->botModel->getActiveBots($userId);
        
        // Get market overview
        $marketOverview = $this->marketModel->getMarketOverview();
        
        // Get open orders count
        $openOrders = $this->tradeModel->getOpenOrdersCount($userId);
        
        // Get active bots count
        $activeBotsCount = count($activeBots);
        
        // Get default currency from settings
        $defaultCurrency = $this->getSetting('default_currency', 'USD');

        $this->view('user/dashboard', [
            'totalBalance' => $totalBalance,
            'dailyPnL' => $dailyPnL,
            'dailyPnLPercentage' => $dailyPnLPercentage,
            'portfolioDistribution' => $portfolioDistribution,
            'recentTrades' => $recentTrades,
            'activeBots' => $activeBots,
            'marketOverview' => $marketOverview,
            'openOrders' => $openOrders,
            'activeBots' => $activeBotsCount,
            'defaultCurrency' => $defaultCurrency
        ]);
    }

    public function profile()
    {
        $userId = $this->authService->getUserId();
        $user = $this->userModel->getById($userId);
        $loginHistory = $this->userModel->getLoginHistory($userId);

        $this->view('user/profile', [
            'user' => $user,
            'loginHistory' => $loginHistory
        ]);
    }

    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/profile');
        }

        $userId = $this->authService->getUserId();
        $data = [
            'email' => $_POST['email'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'phone' => $_POST['phone'] ?? ''
        ];

        try {
            $this->userModel->update($userId, $data);
            $this->auditLogModel->log($userId, 'profile_update', 'user', $userId, [
                'old' => $this->userModel->getById($userId),
                'new' => $data
            ]);
            $this->setFlash('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update profile: ' . $e->getMessage());
        }

        $this->redirect('/user/profile');
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/profile');
        }

        $userId = $this->authService->getUserId();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        try {
            if (!$this->authService->verifyPassword($userId, $currentPassword)) {
                throw new \Exception('Current password is incorrect');
            }

            if ($newPassword !== $confirmPassword) {
                throw new \Exception('New passwords do not match');
            }

            if (strlen($newPassword) < 8) {
                throw new \Exception('Password must be at least 8 characters long');
            }

            $this->userModel->updatePassword($userId, $newPassword);
            $this->auditLogModel->log($userId, 'password_change', 'user', $userId);
            $this->setFlash('success', 'Password changed successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to change password: ' . $e->getMessage());
        }

        $this->redirect('/user/profile');
    }

    public function enable2FA()
    {
        $userId = $this->authService->getUserId();
        $secret = $this->twoFactorService->generateSecret();
        $qrCode = $this->twoFactorService->getQRCode($secret, $this->userModel->getEmail($userId));

        $this->view('user/enable-2fa', [
            'secret' => $secret,
            'qrCode' => $qrCode
        ]);
    }

    public function verify2FA()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/profile');
        }

        $userId = $this->authService->getUserId();
        $secret = $_POST['secret'] ?? '';
        $code = $_POST['code'] ?? '';

        try {
            if (!$this->twoFactorService->verifyCode($secret, $code)) {
                throw new \Exception('Invalid verification code');
            }

            $this->userModel->enable2FA($userId, $secret);
            $this->auditLogModel->log($userId, '2fa_enable', 'user', $userId);
            $this->setFlash('success', 'Two-factor authentication enabled successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to enable 2FA: ' . $e->getMessage());
        }

        $this->redirect('/user/profile');
    }

    public function disable2FA()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/profile');
        }

        $userId = $this->authService->getUserId();
        $code = $_POST['code'] ?? '';

        try {
            if (!$this->twoFactorService->verifyCode($this->userModel->get2FASecret($userId), $code)) {
                throw new \Exception('Invalid verification code');
            }

            $this->userModel->disable2FA($userId);
            $this->auditLogModel->log($userId, '2fa_disable', 'user', $userId);
            $this->setFlash('success', 'Two-factor authentication disabled successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to disable 2FA: ' . $e->getMessage());
        }

        $this->redirect('/user/profile');
    }

    private function getSetting($key, $default = null)
    {
        $settings = new \App\Models\SystemSetting();
        return $settings->get($key) ?? $default;
    }
} 