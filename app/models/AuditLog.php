<?php

namespace App\Models;

use PDO;

class AuditLog extends BaseModel
{
    public function log($userId, $action, $entityType = null, $entityId = null, $data = [])
    {
        $query = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
                 VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => isset($data['old']) ? json_encode($data['old']) : null,
            'new_values' => isset($data['new']) ? json_encode($data['new']) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    public function getRecent($limit = 10)
    {
        $query = "SELECT al.*, u.username 
                 FROM audit_logs al
                 LEFT JOIN users u ON al.user_id = u.id
                 ORDER BY al.created_at DESC
                 LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaginated($page = 1, $perPage = 20, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if (!empty($filters['user'])) {
            $where[] = "u.username LIKE :user";
            $params['user'] = "%{$filters['user']}%";
        }

        if (!empty($filters['action'])) {
            $where[] = "al.action LIKE :action";
            $params['action'] = "%{$filters['action']}%";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "al.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "al.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT al.*, u.username 
                 FROM audit_logs al
                 LEFT JOIN users u ON al.user_id = u.id
                 {$whereClause}
                 ORDER BY al.created_at DESC
                 LIMIT :offset, :per_page";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count($filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['user'])) {
            $where[] = "u.username LIKE :user";
            $params['user'] = "%{$filters['user']}%";
        }

        if (!empty($filters['action'])) {
            $where[] = "al.action LIKE :action";
            $params['action'] = "%{$filters['action']}%";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "al.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "al.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT COUNT(*) 
                 FROM audit_logs al
                 LEFT JOIN users u ON al.user_id = u.id
                 {$whereClause}";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getByEntity($entityType, $entityId)
    {
        $query = "SELECT al.*, u.username 
                 FROM audit_logs al
                 LEFT JOIN users u ON al.user_id = u.id
                 WHERE al.entity_type = :entity_type 
                 AND al.entity_id = :entity_id
                 ORDER BY al.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser($userId)
    {
        $query = "SELECT al.*, u.username 
                 FROM audit_logs al
                 LEFT JOIN users u ON al.user_id = u.id
                 WHERE al.user_id = :user_id
                 ORDER BY al.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 