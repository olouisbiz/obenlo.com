<?php
/**
 * Obenlo Plugin Activation Diagnostic Script
 * 
 * Upload this file to your wp-content/plugins/obenlo-booking/ folder
 * and visit it in your browser: https://obenlo.com/wp-content/plugins/obenlo-booking/diag.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Obenlo Diagnostic Tool</h1>";

$files = [
    'includes/class-post-types.php',
    'includes/class-roles.php',
    'includes/class-frontend-dashboard.php',
    'includes/class-payments.php',
    'includes/class-reviews.php',
    'includes/class-frontend-experience.php',
    'includes/class-admin-dashboard.php',
    'includes/class-notifications.php',
    'includes/class-communication.php',
    'includes/class-host-verification.php',
    'includes/class-payout-manager.php',
    'includes/class-badges.php',
    'includes/class-wishlist.php',
    'includes/class-i18n.php'
];

// Mock ABSPATH to prevent exit
if (!defined('ABSPATH')) {
    define('ABSPATH', true);
}

foreach ($files as $file) {
    echo "Checking: <code>$file</code> ... ";
    try {
        if (file_exists($file)) {
            include_once($file);
            echo "<span style='color:green'>OK</span><br>";
        } else {
            echo "<span style='color:red'>FILE MISSING</span><br>";
        }
    } catch (Throwable $e) {
        echo "<span style='color:red'>FATAL ERROR: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "</span><br>";
    }
}

echo "<h2>Check Class Instantiations</h2>";

$classes = [
    'Obenlo_Booking_Post_Types',
    'Obenlo_Booking_Roles',
    'Obenlo_Booking_Frontend_Dashboard',
    'Obenlo_Booking_Payments',
    'Obenlo_Booking_Reviews',
    'Obenlo_Booking_Frontend_Experience',
    'Obenlo_Booking_Admin_Dashboard',
    'Obenlo_Booking_Notifications',
    'Obenlo_Booking_Communication',
    'Obenlo_Booking_Host_Verification',
    'Obenlo_Booking_Payout_Manager',
    'Obenlo_Booking_Badges',
    'Obenlo_Booking_Wishlist',
    'Obenlo_Booking_i18n'
];

foreach ($classes as $class) {
    echo "Instantiating: <code>$class</code> ... ";
    try {
        if (class_exists($class)) {
            $obj = new $class();
            echo "<span style='color:green'>SUCCESS</span><br>";
        } else {
            echo "<span style='color:red'>CLASS NOT DEFINED</span><br>";
        }
    } catch (Throwable $e) {
        echo "<span style='color:red'>FATAL ERROR: " . $e->getMessage() . "</span><br>";
    }
}

echo "<br><p>Diagnostic Finished.</p>";
