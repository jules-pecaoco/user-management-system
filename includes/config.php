<?php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_management');
define('DB_PORT', 3307);

// Application settings
define('SITE_NAME', 'User Management System');
define('HOME_URL', 'http://localhost/user-management-system/');
define('ADMIN_URL', 'http://localhost/user-management-system/admin/');

// File uploads
define('ASSETS_URL', HOME_URL . 'assets/');
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/user-management-system/uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Session and security
define('SESSION_NAME', 'ums_session');
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('COOKIE_LIFETIME', 86400); // 1 day in seconds



// Session settings
