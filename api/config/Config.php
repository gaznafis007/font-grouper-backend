<?php
// api/config/Config.php
namespace Config;

class Config {
    // Database configuration
    public static $DB_HOST = 'sql305.infinityfree.com';
    public static $DB_NAME = 'if0_38744480_font_db';
    public static $DB_USER = 'if0_38744480';
    public static $DB_PASS = 'Pj6Ouwd9Tvswou';
    
    // Paths
    public static $FONT_UPLOAD_DIR = __DIR__ . '../fonts/';
    public static $ALLOWED_EXTENSIONS = ['ttf'];
    
    // Create fonts directory if it doesn't exist
    public static function init() {
        if (!file_exists(self::$FONT_UPLOAD_DIR)) {
            mkdir(self::$FONT_UPLOAD_DIR, 0755, true);
        }
    }
}

// class Config {
//     public static $DB_HOST = "localhost";
//     public static $DB_NAME = "font_system";
//     public static $DB_USER = "root";
//     public static $DB_PASS = ""; // Usually empty password for local XAMPP
    
//     public static $FONT_UPLOAD_DIR = "../fonts/";
//     public static $ALLOWED_EXTENSIONS = ["ttf", "otf", "woff", "woff2"];
// }

// Initialize config
Config::init();
?>