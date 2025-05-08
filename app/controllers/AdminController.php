<?php

namespace App\Controllers;

use App\Core\Security;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use App\Models\Trade;
use App\Services\LunoService;

class AdminController extends BaseController
{
    protected $security;
    protected $user;
    protected $role;
    protected $permission;
    protected $systemSetting;
    protected $auditLog;
    protected $tradeModel;
    protected $lunoService;
    protected $isProduction;

    public function __construct()
    {
        parent::__construct();
        $this->security = new Security();
        $this->user = new User();
        $this->role = new Role();
        $this->permission = new Permission();
        $this->systemSetting = new SystemSetting();
        $this->auditLog = new AuditLog();
        $this->tradeModel = new Trade();
        $this->lunoService = new LunoService();
        $this->isProduction = getenv('APP_ENV') === 'production';

        // Check if user is logged in and has admin access
        $this->checkAdminAccess();
    }

    protected function checkAdminAccess()
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }

        $userRoles = $this->user->getUserRoles(Auth::id());
        if (!in_array('admin', $userRoles)) {
            $this->redirect('/dashboard');
        }
    }

    public function dashboard()
    {
        try {
            // Get system statistics
            $totalUsers = $this->user->count();
            $recentUsers = $this->user->getRecent(5);
            $recentActivities = $this->auditLog->getRecent(10);
            $systemSettings = $this->systemSetting->getAll();

            // Get trading statistics
            $activeTrades = $this->tradeModel->getOpenOrdersCount();
            $totalVolume = $this->calculateTotalVolume();
            $maintenanceMode = $this->systemSetting->get('maintenance_mode')['value'] ?? 'false';

            // Get Luno API status
            $lunoStatus = $this->checkLunoAPIStatus();

            $data = [
                'title' => 'Admin Dashboard',
                'total_users' => $totalUsers,
                'recent_users' => $recentUsers,
                'recent_activities' => $recentActivities,
                'system_settings' => $systemSettings,
                'active_trades' => $activeTrades,
                'total_volume' => $totalVolume,
                'maintenance_mode' => $maintenanceMode,
                'luno_status' => $lunoStatus
            ];

            $this->view('admin/dashboard', $data);
        } catch (\Exception $e) {
            error_log('Admin Dashboard Error: ' . $e->getMessage());
            $this->view('admin/dashboard', [
                'error' => 'Failed to load dashboard information. Please try again later.',
                'total_users' => 0,
                'recent_users' => [],
                'recent_activities' => [],
                'system_settings' => [],
                'active_trades' => 0,
                'total_volume' => 0,
                'maintenance_mode' => 'false',
                'luno_status' => 'error'
            ]);
        }
    }

    private function calculateTotalVolume()
    {
        try {
            $query = "SELECT SUM(volume * price) as total_volume 
                     FROM trades 
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchColumn() ?? 0;
        } catch (\Exception $e) {
            error_log('Calculate Total Volume Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function checkLunoAPIStatus()
    {
        try {
            if ($this->isProduction) {
                // Check Luno API connectivity
                $this->lunoService->getMarkets();
                return 'connected';
            }
            return 'development';
        } catch (\Exception $e) {
            error_log('Luno API Status Check Error: ' . $e->getMessage());
            return 'error';
        }
    }

    public function users()
    {
        try {
            $data = [
                'title' => 'User Management',
                'users' => $this->user->getAll(),
                'roles' => $this->role->getAll()
            ];

            $this->view('admin/users', $data);
        } catch (\Exception $e) {
            error_log('User Management Error: ' . $e->getMessage());
            $this->view('admin/users', [
                'error' => 'Failed to load user information. Please try again later.',
                'users' => [],
                'roles' => []
            ]);
        }
    }

    public function roles()
    {
        try {
            $data = [
                'title' => 'Role Management',
                'roles' => $this->role->getAll(),
                'permissions' => $this->permission->getAll()
            ];

            $this->view('admin/roles', $data);
        } catch (\Exception $e) {
            error_log('Role Management Error: ' . $e->getMessage());
            $this->view('admin/roles', [
                'error' => 'Failed to load role information. Please try again later.',
                'roles' => [],
                'permissions' => []
            ]);
        }
    }

    public function settings()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->updateSettings($_POST);
            }

            $data = [
                'title' => 'System Settings',
                'settings' => $this->systemSetting->getAll()
            ];

            $this->view('admin/settings', $data);
        } catch (\Exception $e) {
            error_log('Settings Error: ' . $e->getMessage());
            $this->view('admin/settings', [
                'error' => 'Failed to load settings. Please try again later.',
                'settings' => []
            ]);
        }
    }

    public function auditLogs()
    {
        try {
            $page = $_GET['page'] ?? 1;
            $perPage = 20;

            $data = [
                'title' => 'Audit Logs',
                'logs' => $this->auditLog->getPaginated($page, $perPage),
                'total_pages' => ceil($this->auditLog->count() / $perPage),
                'current_page' => $page
            ];

            $this->view('admin/audit-logs', $data);
        } catch (\Exception $e) {
            error_log('Audit Logs Error: ' . $e->getMessage());
            $this->view('admin/audit-logs', [
                'error' => 'Failed to load audit logs. Please try again later.',
                'logs' => [],
                'total_pages' => 0,
                'current_page' => 1
            ]);
        }
    }

    protected function updateSettings($settings)
    {
        try {
            foreach ($settings as $key => $value) {
                $this->systemSetting->update($key, $value);
            }

            $this->auditLog->log(
                Auth::id(),
                'update_settings',
                'system_settings',
                null,
                ['old' => $this->systemSetting->getAll(), 'new' => $settings]
            );

            $_SESSION['flash_message'] = 'Settings updated successfully';
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            error_log('Update Settings Error: ' . $e->getMessage());
            $_SESSION['flash_message'] = 'Failed to update settings';
            $_SESSION['flash_type'] = 'error';
        }
    }

    protected function view($view, $data = [])
    {
        extract($data);
        ob_start();
        include_once __DIR__ . "/../views/{$view}.php";
        $content = ob_get_clean();
        include_once __DIR__ . "/../views/layouts/admin.php";
    }
} 