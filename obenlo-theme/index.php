<?php
// Fallback to front-page or explore if index is hit
wp_redirect(home_url('/explore'));
exit;
