<?php
/**
 * Obenlo Database Schema - Hybrid Data Layer
 */
if (!defined('ABSPATH')) exit;

function obenlo_init_database() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // 1. MESSAGES TABLE (Booking-style efficiency)
    $table_messages = $wpdb->prefix . 'obenlo_messages';
    $sql_messages = "CREATE TABLE $table_messages (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        sender_id bigint(20) NOT NULL,
        receiver_id bigint(20) NOT NULL,
        ses_id bigint(20) DEFAULT 0,
        message_text text NOT NULL,
        sent_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        is_read tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_messages);
}
