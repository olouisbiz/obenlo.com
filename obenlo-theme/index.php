<?php
/**
 * Obenlo Marketplace - Safety Fallback
 * Redirects unauthorized or lost traffic to the primary Marketplace Explorer.
 */
wp_redirect(home_url('/listings'));
exit;
