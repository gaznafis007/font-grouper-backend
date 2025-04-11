<?php
// api/services/FileUploadService.php
namespace Services;

use Config\Config;

class FileUploadService {
    public function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Upload error: ' . $this->getUploadErrorMessage($file['error'])
            ];
        }
        
        // Check file size (10MB limit)
        if ($file['size'] > 10 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => 'File too large. Maximum size is 10MB.'
            ];
        }
        
        // Validate extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, Config::$ALLOWED_EXTENSIONS)) {
            return [
                'success' => false,
                'message' => 'Only TTF files are allowed.'
            ];
        }
        
        return ['success' => true];
    }
    
    public function uploadFile($file) {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueName = uniqid('font_') . '.' . $extension;
        $uploadPath = Config::$FONT_UPLOAD_DIR . $uniqueName;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => false,
                'message' => 'Failed to move uploaded file.'
            ];
        }
        
        return [
            'success' => true,
            'file_path' => $uniqueName,
            'original_name' => $file['name']
        ];
    }
    
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }
    
    public function extractFontName($filePath) {
        // Use a library like PHP-Font-Lib to extract font metadata
        // For simplicity, we'll just use the original filename as the font name
        $originalName = pathinfo($filePath, PATHINFO_FILENAME);
        return $originalName;
    }
}
?>