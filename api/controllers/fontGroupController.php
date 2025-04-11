<?php
// api/controllers/FontGroupController.php
namespace Controllers;

use Models\FontGroup;

class FontGroupController {
    private $fontGroupModel;
    
    public function __construct() {
        $this->fontGroupModel = new FontGroup();
    }
    
    public function create() {
        // Get JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate request
        if (!isset($data['name']) || !isset($data['fontIds']) || !is_array($data['fontIds'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Group name and at least two font IDs are required'
            ]);
            return;
        }
        
        // Check if at least two fonts are selected
        if (count($data['fontIds']) < 2) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'At least two fonts must be selected for a group'
            ]);
            return;
        }
        
        // Create font group
        try {
            $fontGroup = $this->fontGroupModel->create($data['name'], $data['fontIds']);
            
            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Font group created successfully',
                'fontGroup' => $fontGroup
            ]);
        } catch (\Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'message' => 'Failed to create font group: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getAll() {
        $fontGroups = $this->fontGroupModel->getAll();
        
        $this->sendResponse(200, [
            'success' => true,
            'fontGroups' => $fontGroups
        ]);
    }
    
    public function getById($id) {
        $fontGroup = $this->fontGroupModel->getById($id);
        
        if (!$fontGroup) {
            $this->sendResponse(404, [
                'success' => false,
                'message' => 'Font group not found'
            ]);
            return;
        }
        
        $this->sendResponse(200, [
            'success' => true,
            'fontGroup' => $fontGroup
        ]);
    }
    
    public function update($id) {
        // Get JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate request
        if (!isset($data['name']) || !isset($data['fontIds']) || !is_array($data['fontIds'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Group name and at least two font IDs are required'
            ]);
            return;
        }
        
        // Check if at least two fonts are selected
        if (count($data['fontIds']) < 2) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'At least two fonts must be selected for a group'
            ]);
            return;
        }
        
        // Check if group exists
        $fontGroup = $this->fontGroupModel->getById($id);
        if (!$fontGroup) {
            $this->sendResponse(404, [
                'success' => false,
                'message' => 'Font group not found'
            ]);
            return;
        }
        
        // Update font group
        try {
            $updatedFontGroup = $this->fontGroupModel->update($id, $data['name'], $data['fontIds']);
            
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Font group updated successfully',
                'fontGroup' => $updatedFontGroup
            ]);
        } catch (\Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'message' => 'Failed to update font group: ' . $e->getMessage()
            ]);
        }
    }
    
    public function delete($id) {
        // Check if group exists
        $fontGroup = $this->fontGroupModel->getById($id);
        if (!$fontGroup) {
            $this->sendResponse(404, [
                'success' => false,
                'message' => 'Font group not found'
            ]);
            return;
        }
        
        // Delete font group
        $deleted = $this->fontGroupModel->delete($id);
        
        if (!$deleted) {
            $this->sendResponse(500, [
                'success' => false,
                'message' => 'Failed to delete font group'
            ]);
            return;
        }
        
        $this->sendResponse(200, [
            'success' => true,
            'message' => 'Font group deleted successfully'
        ]);
    }
    
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>