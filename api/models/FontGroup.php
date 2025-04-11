<?php
// api/models/FontGroup.php
namespace Models;

use Services\DatabaseService;

class FontGroup {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseService::getInstance();
    }
    
    public function create($name, $fontIds) {
        // Begin transaction
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Insert group
            $groupId = $this->db->insert('font_groups', ['name' => $name]);
            
            // Insert group items
            foreach ($fontIds as $fontId) {
                $this->db->insert('font_group_items', [
                    'group_id' => $groupId,
                    'font_id' => $fontId
                ]);
            }
            
            $this->db->getConnection()->commit();
            return $this->getById($groupId);
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    public function getAll() {
        $groups = $this->db->query("SELECT * FROM font_groups ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get fonts for each group
        foreach ($groups as &$group) {
            $group['fonts'] = $this->getFontsForGroup($group['id']);
        }
        
        return $groups;
    }
    
    public function getById($id) {
        $stmt = $this->db->query("SELECT * FROM font_groups WHERE id = ?", [$id]);
        $group = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($group) {
            $group['fonts'] = $this->getFontsForGroup($id);
        }
        
        return $group;
    }
    
    public function update($id, $name, $fontIds) {
        // Begin transaction
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Update group name
            $this->db->update('font_groups', ['name' => $name], 'id = ?', [$id]);
            
            // Delete old items
            $this->db->delete('font_group_items', 'group_id = ?', [$id]);
            
            // Insert new items
            foreach ($fontIds as $fontId) {
                $this->db->insert('font_group_items', [
                    'group_id' => $id,
                    'font_id' => $fontId
                ]);
            }
            
            $this->db->getConnection()->commit();
            return $this->getById($id);
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    public function delete($id) {
        return $this->db->delete('font_groups', 'id = ?', [$id]);
    }
    
    private function getFontsForGroup($groupId) {
        $sql = "
            SELECT f.* 
            FROM fonts f
            JOIN font_group_items fgi ON f.id = fgi.font_id
            WHERE fgi.group_id = ?
        ";
        
        $stmt = $this->db->query($sql, [$groupId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>