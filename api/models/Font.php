<?php
// api/models/Font.php
namespace Models;

use Services\DatabaseService;

class Font {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseService::getInstance();
    }
    
    public function create($name, $filePath, $originalName) {
        $data = [
            'name' => $name,
            'file_path' => $filePath,
            'original_name' => $originalName
        ];
        
        $fontId = $this->db->insert('fonts', $data);
        return $this->getById($fontId);
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM fonts ORDER BY uploaded_at DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->query("SELECT * FROM fonts WHERE id = ?", [$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function delete($id) {
        // Get font info for file deletion
        $font = $this->getById($id);
        if (!$font) {
            return false;
        }
        
        // Delete from database
        $deleted = $this->db->delete('fonts', 'id = ?', [$id]);
        
        // Delete physical file
        if ($deleted) {
            $filePath = __DIR__ . '/../../fonts/' . $font['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        return $deleted;
    }
}
?>