<?php

namespace App\Controllers;

use App\Core\Security;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\SystemSetting;
use App\Models\AuditLog;

class AdminController extends BaseController
{
    protected $security;
    protected $user;
    protected $role;
    protected $permission;
    protected $systemSetting;
    protected $auditLog;

    public function __construct()
    {
        parent::__construct();
        $this->security = new Security();
        $this->user = new User();
        $this->role = new Role();
        $this->permission = new Permission();
        $this->systemSetting = new SystemSetting();
        $this->auditLog = new AuditLog();

        // Check if user is logged in and has admin access
        $this->checkAdminAccess();
    }

    protected function checkAdminAccess()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $userRoles = $this->user->getUserRoles($_SESSION['user_id']);
        if (!in_array('admin', $userRoles)) {
            $this->redirect('/dashboard');
        }
    }

    public function dashboard()
    {
        $data = [
            'title' => 'Admin Dashboard',
            'total_users' => $this->user->count(),
            'recent_users' => $this->user->getRecent(5),
            'recent_activities' => $this->auditLog->getRecent(10),
            'system_settings' => $this->systemSetting->getAll()
        ];

        $this->view('admin/dashboard', $data);
    }

    public function users()
    {
        $data = [
            'title' => 'User Management',
            'users' => $this->user->getAll(),
            'roles' => $this->role->getAll()
        ];

        $this->view('admin/users', $data);
    }

    public function roles()
    {
        $data = [
            'title' => 'Role Management',
            'roles' => $this->role->getAll(),
            'permissions' => $this->permission->getAll()
        ];

        $this->view('admin/roles', $data);
    }

    public function settings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSettings($_POST);
        }

        $data = [
            'title' => 'System Settings',
            'settings' => $this->systemSetting->getAll()
        ];

        $this->view('admin/settings', $data);
    }

    public function auditLogs()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 20;

        $data = [
            'title' => 'Audit Logs',
            'logs' => $this->auditLog->getPaginated($page, $perPage),
            'total_pages' => ceil($this->auditLog->count() / $perPage),
            'current_page' => $page
        ];

        $this->view('admin/audit-logs', $data);
    }

    protected function updateSettings($settings)
    {
        foreach ($settings as $key => $value) {
            $this->systemSetting->update($key, $value);
        }

        $this->auditLog->log(
            $_SESSION['user_id'],
            'update_settings',
            'system_settings',
            null,
            ['old' => $this->systemSetting->getAll(), 'new' => $settings]
        );

        $_SESSION['flash_message'] = 'Settings updated successfully';
        $_SESSION['flash_type'] = 'success';
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