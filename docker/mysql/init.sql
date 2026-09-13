-- MySQL Initialization Script for ERP Database

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `erp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user and grant privileges
CREATE USER IF NOT EXISTS 'erp_user'@'%' IDENTIFIED BY 'secret_password';
CREATE USER IF NOT EXISTS 'erp_user'@'localhost' IDENTIFIED BY 'secret_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON `erp`.* TO 'erp_user'@'%';
GRANT ALL PRIVILEGES ON `erp`.* TO 'erp_user'@'localhost';

-- Create test database for testing
CREATE DATABASE IF NOT EXISTS `erp_testing` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `erp_testing`.* TO 'erp_user'@'%';
GRANT ALL PRIVILEGES ON `erp_testing`.* TO 'erp_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Set database variables
SET GLOBAL innodb_file_per_table = 1;
SET GLOBAL innodb_buffer_pool_size = 256M;
SET GLOBAL max_connections = 100;
