<?php

namespace App\Models;

use PDO;

class SystemSetting extends BaseModel
{
    public function getAll()
    {
        $query = "SELECT * FROM system_settings ORDER BY setting_key";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert to key-value array
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->formatValue($setting);
        }

        return $result;
    }

    public function get($key)
    {
        $query = "SELECT * FROM system_settings WHERE setting_key = :key";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['key' => $key]);
        $setting = $stmt->fetch(PDO::FETCH_ASSOC);

        return $setting ? $this->formatValue($setting) : null;
    }

    public function update($key, $value)
    {
        $query = "UPDATE system_settings SET setting_value = :value WHERE setting_key = :key";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'key' => $key,
            'value' => $value
        ]);
    }

    public function create($data)
    {
        $query = "INSERT INTO system_settings (setting_key, setting_value, setting_type, description) 
                 VALUES (:key, :value, :type, :description)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'key' => $data['key'],
            'value' => $data['value'],
            'type' => $data['type'] ?? 'string',
            'description' => $data['description'] ?? null
        ]);
    }

    public function delete($key)
    {
        $query = "DELETE FROM system_settings WHERE setting_key = :key";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['key' => $key]);
    }

    protected function formatValue($setting)
    {
        switch ($setting['setting_type']) {
            case 'boolean':
                return filter_var($setting['setting_value'], FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $setting['setting_value'];
            case 'json':
                return json_decode($setting['setting_value'], true);
            default:
                return $setting['setting_value'];
        }
    }

    public function getByType($type)
    {
        $query = "SELECT * FROM system_settings WHERE setting_type = :type ORDER BY setting_key";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['type' => $type]);
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert to key-value array
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->formatValue($setting);
        }

        return $result;
    }

    public function updateMultiple($settings)
    {
        $this->db->beginTransaction();

        try {
            foreach ($settings as $key => $value) {
                $this->update($key, $value);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
} 