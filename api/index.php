-- Create database
CREATE DATABASE IF NOT EXISTS font_system;

USE font_system;

-- Create fonts table
CREATE TABLE IF NOT EXISTS fonts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create font groups table
CREATE TABLE IF NOT EXISTS font_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create font group items table
CREATE TABLE IF NOT EXISTS font_group_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    font_id INT NOT NULL,
    FOREIGN KEY (group_id) REFERENCES font_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (font_id) REFERENCES fonts(id) ON DELETE CASCADE
);