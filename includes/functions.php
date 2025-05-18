<?php
require_once 'db.php';

// Sanitize input
function sanitize(string $data): string {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


// Display error message
function display_error($message) {
  return "<div class='alert alert-danger' role='alert'>$message</div>";
}

// Display success message
function display_success($message) {
  return "<div class='alert alert-success' role='alert'>$message</div>";
}

// Register new user
function register_user($username, $email, $password, $first_name, $last_name) {
  $conn = get_db();

  // Hash password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $username, $email, $hashed_password, $first_name, $last_name);

  if ($stmt->execute()) {
    return $conn->insert_id;
  } else {
    return false;
  }
}

// Login user
function login_user($username, $password) {
  $conn = get_db();

  $stmt = $conn->prepare("SELECT id, username, email, password, first_name, last_name, is_active, is_admin FROM users WHERE username = ? AND is_active = 1");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
      // Set session variables
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['email'] = $user['email'];
      $_SESSION['first_name'] = $user['first_name'];
      $_SESSION['last_name'] = $user['last_name'];
      $_SESSION['is_admin'] = (bool)$user['is_admin']; // Cast to boolean for consistency
      $_SESSION['loggedin'] = true; // Add this line
      return true;
    }
  }

  return false;
}

// Get user by ID
function get_user($user_id) {
  $conn = get_db();

  $stmt = $conn->prepare("SELECT id, username, email, first_name, last_name, profile_photo, date_joined, is_active FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    return $result->fetch_assoc();
  }

  return false;
}

// Update user profile
function update_profile($user_id, $email, $first_name, $last_name, $password = null) {
  $conn = get_db();

  if ($password) {
    // Update with password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $email, $first_name, $last_name, $hashed_password, $user_id);
  } else {
    // Update without password
    $stmt = $conn->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ? WHERE id = ?");
    $stmt->bind_param("sssi", $email, $first_name, $last_name, $user_id);
  }

  return $stmt->execute();
}

// Search users
function search_users($search_term, $search_by = 'username') {
  $conn = get_db();

  $search_term = "%$search_term%";

  if ($search_by === 'email') {
    $stmt = $conn->prepare("SELECT id, username, email, first_name, last_name, date_joined, is_active FROM users WHERE email LIKE ?");
  } elseif ($search_by === 'first_name') {
    $stmt = $conn->prepare("SELECT id, username, email, first_name, last_name, date_joined, is_active FROM users WHERE first_name LIKE ?");
  } elseif ($search_by === 'last_name') {
    $stmt = $conn->prepare("SELECT id, username, email, first_name, last_name, date_joined, is_active FROM users WHERE last_name LIKE ?");
  } else {
    $stmt = $conn->prepare("SELECT id, username, email, first_name, last_name, date_joined, is_active FROM users WHERE username LIKE ?");
  }

  $stmt->bind_param("s", $search_term);
  $stmt->execute();
  $result = $stmt->get_result();

  $users = [];
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }

  return $users;
}

// Deactivate user
function deactivate_user($user_id) {
  $conn = get_db();

  $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
  $stmt->bind_param("i", $user_id);

  return $stmt->execute();
}

// Add admin user
function add_admin($username, $email, $password, $first_name, $last_name) {
  $conn = get_db();

  // Hash password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, is_admin) VALUES (?, ?, ?, ?, ?, 1)");
  $stmt->bind_param("sssss", $username, $email, $hashed_password, $first_name, $last_name);

  return $stmt->execute();
}

// Update admin password
function update_admin_password($user_id, $password) {
  $conn = get_db();

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND is_admin = 1");
  $stmt->bind_param("si", $hashed_password, $user_id);

  return $stmt->execute();
}

/**
 * Common functions for User Management System
 */

/**
 * Check if user is logged in
 *
 * @return bool True if logged in, false otherwise
 */
function is_logged_in() {
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }

  return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

/**
 * Check if user is admin
 *
 * @return bool True if logged in and is admin, false otherwise
 */
function is_admin() {
  if (!is_logged_in()) {
    return false;
  }

  return isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
}

/**
 * Redirect to a specific page
 *
 * @param string $page URL to redirect to
 * @return void
 */
function redirect($page) {
  header("location: $page");
  exit;
}

/**
 * Display error message with Bootstrap alert
 *
 * @param string $message Error message
 * @return string HTML for error message
 */
function displayError($message) {
  return '<div class="alert alert-danger" role="alert">' . $message . '</div>';
}

/**
 * Display success message with Bootstrap alert
 *
 * @param string $message Success message
 * @return string HTML for success message
 */
function displaySuccess($message) {
  return '<div class="alert alert-success" role="alert">' . $message . '</div>';
}

/**
 * Format date in user-friendly format
 *
 * @param string $date Date to format
 * @param string $format PHP date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y g:i A') {
  $timestamp = strtotime($date);
  return date($format, $timestamp);
}

/**
 * Get user data by ID
 *
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @return array|bool User data or false if not found
 */
function getUserData($mysqli, $userId) {
  $sql = "SELECT id, username, email, first_name, last_name, profile_photo, date_joined, is_active, is_admin 
            FROM users WHERE id = ?";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
      $result = $stmt->get_result();

      if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
      }
    }

    $stmt->close();
  }

  return false;
}

/**
 * Update user profile
 *
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @param array $data Profile data to update
 * @return bool True on success, false on failure
 */
function updateUserProfile($mysqli, $userId, $data) {
  $sql = "UPDATE users SET first_name = ?, last_name = ?";
  $params = array($data['first_name'], $data['last_name']);
  $types = "ss";

  // Add profile photo if provided
  if (!empty($data['profile_photo'])) {
    $sql .= ", profile_photo = ?";
    $params[] = $data['profile_photo'];
    $types .= "s";
  }

  // Add email if provided and not empty
  if (isset($data['email']) && !empty($data['email'])) {
    $sql .= ", email = ?";
    $params[] = $data['email'];
    $types .= "s";
  }

  // Finalize query
  $sql .= " WHERE id = ?";
  $params[] = $userId;
  $types .= "i";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
      $stmt->close();
      return true;
    }

    $stmt->close();
  }

  return false;
}

/**
 * Change user password
 *
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @param string $newPassword New password (not hashed)
 * @return bool True on success, false on failure
 */
function changeUserPassword($mysqli, $userId, $newPassword) {
  $sql = "UPDATE users SET password = ? WHERE id = ?";
  $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("si", $hashedPassword, $userId);

    if ($stmt->execute()) {
      $stmt->close();
      return true;
    }

    $stmt->close();
  }

  return false;
}

/**
 * Upload profile photo
 *
 * @param array $file $_FILES array element
 * @return string|bool Filename on success, false on failure
 */
function uploadProfilePhoto($file) {
  // Check if file was uploaded without errors
  if ($file['error'] !== UPLOAD_ERR_OK) {
    return false;
  }

  // Check file size
  if ($file['size'] > MAX_UPLOAD_SIZE) {
    return false;
  }

  // Get file extension
  $fileInfo = pathinfo($file['name']);
  $extension = strtolower($fileInfo['extension']);

  // Check if extension is allowed
  if (!in_array($extension, ALLOWED_EXTENSIONS)) {
    return false;
  }

  // Create upload directory if it doesn't exist
  if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
  }

  // Generate unique filename
  $filename = uniqid() . '.' . $extension;
  $destination = UPLOAD_DIR . $filename;

  // Move uploaded file
  if (move_uploaded_file($file['tmp_name'], $destination)) {
    return $filename;
  }

  return false;
}

/**
 * Verify user's current password
 *
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @param string $password Password to verify
 * @return bool True if password matches, false otherwise
 */
function verifyCurrentPassword($mysqli, $userId, $password) {
  $sql = "SELECT password FROM users WHERE id = ?";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
      $result = $stmt->get_result();

      if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];
        $stmt->close();

        return password_verify($password, $hashedPassword);
      }
    }

    $stmt->close();
  }

  return false;
}

/**
 * Generate a random token
 *
 * @param int $length Token length
 * @return string Random token
 */
function generateToken($length = 32) {
  return bin2hex(random_bytes($length / 2));
}

/**
 * Clean input data to prevent XSS
 *
 * @param string $data Input data
 * @return string Cleaned data
 */
function cleanInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


// In includes/functions.php

/**
 * Get all users from the database with optional pagination, sorting, and filtering
 *
 * @param mysqli $mysqli Database connection
 * @param array $options Optional parameters for controlling the query:
 * - page: Current page number (default: 1)
 * - limit: Records per page (default: 10)
 * - sort_by: Column to sort by (default: 'id')
 * - sort_dir: Sort direction, 'ASC' or 'DESC' (default: 'ASC')
 * - filter: Array of filter conditions, e.g. ['is_active' => 1]
 * @return array Associative array containing:
 * - users: Array of user records
 * - total: Total number of records matching the criteria
 * - pages: Total number of pages
 * - current_page: Current page number
 */
function getAllUsers(\mysqli $mysqli, array $options = []): array {
  // Default options
  $defaults = [
    'page' => 1,
    'limit' => 10,
    'sort_by' => 'id',
    'sort_dir' => 'ASC',
    'filter' => []
  ];

  // Merge provided options with defaults
  $options = array_merge($defaults, $options);

  // Validate and sanitize options
  $page = max(1, (int)$options['page']);
  $limit = max(1, (int)$options['limit']);
  $offset = ($page - 1) * $limit;

  // Allowed columns for sorting and filtering to prevent SQL injection
  $allowed_columns = ['id', 'username', 'email', 'first_name', 'last_name', 'date_joined', 'is_active', 'is_admin'];
  $sort_by = in_array($options['sort_by'], $allowed_columns) ? $options['sort_by'] : 'id';

  // Validate sort direction
  $sort_dir = strtoupper($options['sort_dir']) === 'DESC' ? 'DESC' : 'ASC';

  // Start building the query
  $query = "SELECT id, username, email, first_name, last_name, profile_photo, date_joined, is_active, is_admin FROM users";
  $count_query = "SELECT COUNT(*) as total FROM users";

  // Handle filters
  $where_conditions = [];
  $where_params = [];
  $param_types = "";

  if (!empty($options['filter'])) {
    foreach ($options['filter'] as $column => $value) {
      // Validate column names to prevent SQL injection
      if (in_array($column, $allowed_columns)) {
        $where_conditions[] = "$column = ?";
        $where_params[] = $value;

        // Determine parameter type
        if (is_int($value)) {
          $param_types .= "i";
        } elseif (is_float($value)) {
          $param_types .= "d";
        } else {
          $param_types .= "s";
        }
      }
    }
  }

  // Add WHERE clause if we have filters
  if (!empty($where_conditions)) {
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
    $query .= $where_clause;
    $count_query .= $where_clause;
  }

  // Add ORDER BY and LIMIT clauses to the main query
  $query .= " ORDER BY $sort_by $sort_dir LIMIT ?, ?";

  // Add limit parameters for the main query
  $query_params = array_merge($where_params, [$offset, $limit]);
  $query_param_types = $param_types . "ii";

  // Get total count of records
  $total_records = 0;
  $total_pages = 0;

  if ($count_stmt = $mysqli->prepare($count_query)) {
    // Bind parameters if we have any filters for the count query
    if (!empty($where_params) && !empty($param_types)) {
      // Only bind if we have params to bind
      if (!empty($where_params)) {
        $count_stmt->bind_param($param_types, ...$where_params);
      }
    }

    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_records = $count_row['total'];
    $total_pages = ceil($total_records / $limit);
    $count_stmt->close();
  }

  // Get users data
  $users = [];

  if ($stmt = $mysqli->prepare($query)) {
    // Bind parameters for the main query
    if (!empty($query_params) && !empty($query_param_types)) {
      $stmt->bind_param($query_param_types, ...$query_params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
      $users[] = $row;
    }

    $stmt->close();
  }

  // Return results and pagination data
  return [
    'users' => $users,
    'total' => $total_records,
    'pages' => $total_pages,
    'current_page' => $page
  ];
}

/**
 * Generate pagination HTML for use with getAllUsers function
 *
 * @param int $current_page Current page number
 * @param int $total_pages Total number of pages
 * @param string $url_pattern URL pattern for pagination links, use {page} placeholder for page number
 * @param int $links_count Maximum number of page links to show (default: 5)
 * @return string HTML for pagination controls
 */
function generatePagination(int $current_page, int $total_pages, string $url_pattern, int $links_count = 5): string {
  if ($total_pages <= 1) {
    return '';
  }

  $html = '<nav aria-label="Page navigation"><ul class="pagination">';

  // Previous button
  if ($current_page > 1) {
    $prev_url = str_replace('{page}', $current_page - 1, $url_pattern);
    $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($prev_url) . '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
  } else {
    $html .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
  }

  // Calculate start and end page numbers
  $half_links = floor($links_count / 2);
  $start_page = max(1, $current_page - $half_links);
  $end_page = min($total_pages, $start_page + $links_count - 1);

  // Adjust start page if we're at the end
  if ($end_page - $start_page + 1 < $links_count) {
    $start_page = max(1, $end_page - $links_count + 1);
  }

  // First page link if not included in the page links
  if ($start_page > 1) {
    $first_url = str_replace('{page}', 1, $url_pattern);
    $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($first_url) . '">1</a></li>';

    if ($start_page > 2) {
      $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
    }
  }

  // Page links
  for ($i = $start_page; $i <= $end_page; $i++) {
    if ($i == $current_page) {
      $html .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
    } else {
      $page_url = str_replace('{page}', $i, $url_pattern);
      $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($page_url) . '">' . $i . '</a></li>';
    }
  }

  // Last page link if not included in the page links
  if ($end_page < $total_pages) {
    if ($end_page < $total_pages - 1) {
      $html .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
    }

    $last_url = str_replace('{page}', $total_pages, $url_pattern);
    $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($last_url) . '">' . $total_pages . '</a></li>';
  }

  // Next button
  if ($current_page < $total_pages) {
    $next_url = str_replace('{page}', $current_page + 1, $url_pattern);
    $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($next_url) . '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
  } else {
    $html .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
  }

  $html .= '</ul></nav>';

  return $html;
}
