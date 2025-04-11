<?php
// api/controllers/FontController.php
namespace Controllers;

use Models\Font;
use Services\FileUploadService;

class FontController {
    private $fontModel;
    private $fileUploadService;
    
    public function __construct() {
        $this->fontModel = new Font();
        $this->fileUploadService = new FileUploadService();
    }
    
    public function upload() {
        // Check if file was uploaded
        if (!isset($_FILES['fontFile']) || $_FILES['fontFile']['error'] == UPLOAD_ERR_NO_FILE) {
            $this->sendResponse(400, ['success' => false, 'message' => 'No file uploaded']);
            return;
        }
        
        // Upload file
        $uploadResult = $this->fileUploadService->uploadFile($_FILES['fontFile']);
        
        if (!$uploadResult['success']) {
            $this->sendResponse(400, $uploadResult);
            return;
        }
        
        // Extract font name (could use a library to read font metadata)
        $fontName = pathinfo($_FILES['fontFile']['name'], PATHINFO_FILENAME);
        
        // Save to database
        $font = $this->fontModel->create(
            $fontName,
            $uploadResult['file_path'],
            $uploadResult['original_name']
        );
        
        // Add URL for preview
        $font['preview_url'] = '/fonts/' . $font['file_path'];
        
        $this->sendResponse(201, [
            'success' => true,
            'message' => 'Font uploaded successfully',
            'font' => $font
        ]);
    }
    
    public function getAll() {
        $fonts = $this->fontModel->getAll();
        
        // Add preview URL for each font
        foreach ($fonts as &$font) {
            $font['preview_url'] = '/fonts/' . $font['file_path'];
        }
        
        $this->sendResponse(200, [
            'success' => true,
            'fonts' => $fonts
        ]);
    }
    
    public function getById($id) {
        $font = $this->fontModel->getById($id);
        
        if (!$font) {
            $this->sendResponse(404, [
                'success' => false,
                'message' => 'Font not found'
            ]);
            return;
        }
        
        $font['preview_url'] = '/fonts/' . $font['file_path'];
        
        $this->sendResponse(200, [
            'success' => true,
            'font' => $font
        ]);
    }
    
    public function delete($id) {
        $deleted = $this->fontModel->delete($id);
        
        if (!$deleted) {
            $this->sendResponse(404, [
                'success' => false,
                'message' => 'Font not found or could not be deleted'
            ]);
            return;
        }
        
        $this->sendResponse(200, [
            'success' => true,
            'message' => 'Font deleted successfully'
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