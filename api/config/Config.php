<?php
// api/config/Config.php
namespace Config;

class Config {
    // Database configuration
    public static $DB_HOST = 'localhost';
    public static $DB_NAME = 'font_system';
    public static $DB_USER = 'root';
    public static $DB_PASS = '';
    
    // Paths
    public static $FONT_UPLOAD_DIR = __DIR__ . '/../../fonts/';
    public static $ALLOWED_EXTENSIONS = ['ttf'];
    
    // Create fonts directory if it doesn't exist
    public static function init() {
        if (!file_exists(self::$FONT_UPLOAD_DIR)) {
            mkdir(self::$FONT_UPLOAD_DIR, 0755, true);
        }
    }
}

// Initialize config
Config::init();
?>