<?php
/**
 * Plugin Name: Obenlo Core
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) exit;
define('OBENLO_PATH', plugin_dir_path(__FILE__));

require_once OBENLO_PATH . 'includes/post-types.php';
require_once OBENLO_PATH . 'includes/admin-settings.php';
require_once OBENLO_PATH . 'includes/auth-handlers.php';
require_once OBENLO_PATH . 'includes/auth-gate-logic.php';
require_once OBENLO_PATH . 'includes/routing.php';
require_once OBENLO_PATH . 'includes/stripe-engine.php';
require_once OBENLO_PATH . 'includes/database.php';
require_once OBENLO_PATH . 'includes/traveler-helpers.php';
require_once OBENLO_PATH . 'includes/ajax-handlers.php';
