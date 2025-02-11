<?php
if (!class_exists('RFQ_Database')) {
    class RFQ_Database {
        public static function create_table() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rfqking_requests';
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                customer_id BIGINT UNSIGNED NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                quantity INT NOT NULL,
                category VARCHAR(255),
                price_range VARCHAR(100),
                deadline DATE NOT NULL,
                status ENUM('pending', 'in_review', 'completed') DEFAULT 'pending',
                priority ENUM('low', 'medium', 'high') DEFAULT 'low',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                notes TEXT,
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
        public static function create_units_table() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rfqking_units';
            $charset_collate = $wpdb->get_charset_collate();
        
            $sql = "CREATE TABLE $table_name (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (slug)
            ) $charset_collate;";
        
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
register_activation_hook(RFQKING_PATH . 'rfqking.php', array('RFQ_Database', 'create_table'));