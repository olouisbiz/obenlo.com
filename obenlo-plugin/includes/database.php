<?php
/**
 * Obenlo Marketplace Database Schema
 */
if (!defined('ABSPATH')) exit;

function obenlo_init_marketplace_db() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Marketplace Communications Table
    $table_signals = $wpdb->prefix . 'obenlo_signals';
    $sql_signals = "CREATE TABLE $table_signals (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        buyer_id bigint(20) NOT NULL,
        seller_id bigint(20) NOT NULL,
        asset_id bigint(20) NOT NULL,
        message text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        is_read tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id),
        KEY buyer_id (buyer_id),
        KEY seller_id (seller_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_signals);
}
