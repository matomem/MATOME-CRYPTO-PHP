<?php

namespace App\Models;

use PDO;

class Role extends BaseModel
{
    public function getAll()
    {
        $query = "SELECT r.*, 
                    GROUP_CONCAT(p.name) as permissions,
                    (SELECT COUNT(*) FROM user_roles WHERE role_id = r.id) as user_count
                 FROM roles r
                 LEFT JOIN role_permissions rp ON r.id = rp.role_id
                 LEFT JOIN permissions p ON rp.permission_id = p.id
                 GROUP BY r.id
                 ORDER BY r.name";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format permissions as array
        foreach ($roles as &$role) {
            $role['permissions'] = $role['permissions'] ? explode(',', $role['permissions']) : [];
        }

        return $roles;
    }

    public function getById($id)
    {
        $query = "SELECT r.*, 
                    GROUP_CONCAT(p.id) as permission_ids,
                    GROUP_CONCAT(p.name) as permissions
                 FROM roles r
                 LEFT JOIN role_permissions rp ON r.id = rp.role_id
                 LEFT JOIN permissions p ON rp.permission_id = p.id
                 WHERE r.id = :id
                 GROUP BY r.id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($role) {
            $role['permissions'] = $role['permissions'] ? explode(',', $role['permissions']) : [];
            $role['permission_ids'] = $role['permission_ids'] ? explode(',', $role['permission_ids']) : [];
        }

        return $role;
    }

    public function create($data)
    {
        $this->db->beginTransaction();

        try {
            // Insert role
            $query = "INSERT INTO roles (name, description) VALUES (:name, :description)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null
            ]);
            
            $roleId = $this->db->lastInsertId();

            // Assign permissions
            if (!empty($data['permissions'])) {
                $query = "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)";
                $stmt = $this->db->prepare($query);
                
                foreach ($data['permissions'] as $permissionId) {
                    $stmt->execute([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId
                    ]);
                }
            }

            $this->db->commit();
            return $roleId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $data)
    {
        $this->db->beginTransaction();

        try {
            // Update role
            $query = "UPDATE roles SET name = :name, description = :description WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null
            ]);

            // Update permissions
            $query = "DELETE FROM role_permissions WHERE role_id = :role_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['role_id' => $id]);

            if (!empty($data['permissions'])) {
                $query = "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)";
                $stmt = $this->db->prepare($query);
                
                foreach ($data['permissions'] as $permissionId) {
                    $stmt->execute([
                        'role_id' => $id,
                        'permission_id' => $permissionId
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        // Check if role is assigned to any users
        $query = "SELECT COUNT(*) FROM user_roles WHERE role_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new \Exception('Cannot delete role that is assigned to users');
        }

        $this->db->beginTransaction();

        try {
            // Delete role permissions
            $query = "DELETE FROM role_permissions WHERE role_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);

            // Delete role
            $query = "DELETE FROM roles WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getPermissions()
    {
        $query = "SELECT * FROM permissions ORDER BY name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 