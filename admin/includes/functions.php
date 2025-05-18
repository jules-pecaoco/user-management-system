<?php

/**
 * Admin-specific functions
 */

/**
 * Count total number of users in the system
 *
 * @param mysqli $mysqli Database connection
 * @return int Number of users
 */
function countTotalUsers($mysqli) {
  $sql = "SELECT COUNT(id) as total FROM users";
  $result = $mysqli->query($sql);

  if ($result) {
    $row = $result->fetch_assoc();
    return $row['total'];
  }

  return 0;
}

/**
 * Count number of admin users
 *
 * @param mysqli $mysqli Database connection
 * @return int Number of admin users
 */
function countAdminUsers($mysqli) {
  $sql = "SELECT COUNT(id) as total FROM users WHERE is_admin = 1";
  $result = $mysqli->query($sql);

  if ($result) {
    $row = $result->fetch_assoc();
    return $row['total'];
  }

  return 0;
}

/**
 * Count number of active users
 *
 * @param mysqli $mysqli Database connection
 * @return int Number of active users
 */
function countActiveUsers($mysqli) {
  $sql = "SELECT COUNT(id) as total FROM users WHERE is_active = 1";
  $result = $mysqli->query($sql);

  if ($result) {
    $row = $result->fetch_assoc();
    return $row['total'];
  }

  return 0;
}

/**
 * Get recently registered users
 *
 * @param mysqli $mysqli Database connection
 * @param int $limit Number of users to retrieve
 * @return array Array of user data
 */
function getRecentUsers($mysqli, $limit = 5) {
  $users = array();

  $sql = "SELECT id, username, email, first_name, last_name, date_joined, is_active, is_admin 
            FROM users 
            ORDER BY date_joined DESC 
            LIMIT ?";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $limit);

    if ($stmt->execute()) {
      $result = $stmt->get_result();

      while ($row = $result->fetch_assoc()) {
        $users[] = $row;
      }
    }

    $stmt->close();
  }

  return $users;
}

/**
 * Search users based on criteria
 *
 * @param mysqli $mysqli Database connection
 * @param string $searchTerm Search term
 * @param string $status User status (active, inactive, all)
 * @param string $role User role (admin, user, all)
 * @return array Array of user data matching criteria
 */
function searchUsers($mysqli, $searchTerm = "", $status = "all", $role = "all") {
  $users = array();
  $conditions = array();
  $params = array();
  $types = "";

  // Build search query
  $sql = "SELECT id, username, email, first_name, last_name, date_joined, is_active, is_admin FROM users";

  // Add search term condition if provided
  if (!empty($searchTerm)) {
    $conditions[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $searchParam = "%" . $searchTerm . "%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ssss";
  }

  // Add status condition if not "all"
  if ($status == "active") {
    $conditions[] = "is_active = 1";
  } elseif ($status == "inactive") {
    $conditions[] = "is_active = 0";
  }

  // Add role condition if not "all"
  if ($role == "admin") {
    $conditions[] = "is_admin = 1";
  } elseif ($role == "user") {
    $conditions[] = "is_admin = 0";
  }

  // Combine conditions if any
  if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
  }

  // Add order by
  $sql .= " ORDER BY date_joined DESC";

  if ($stmt = $mysqli->prepare($sql)) {
    // Bind parameters if any
    if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
    }

    if ($stmt->execute()) {
      $result = $stmt->get_result();

      while ($row = $result->fetch_assoc()) {
        $users[] = $row;
      }
    }

    $stmt->close();
  }

  return $users;
}

/**
 * Toggle user active status
 *
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @param bool $isActive New active status
 * @return bool True on success, false on failure
 */
function updateUserStatus($mysqli, $userId, $isActive) {
  $sql = "UPDATE users SET is_active = ? WHERE id = ?";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ii", $isActive, $userId);

    if ($stmt->execute()) {
      $stmt->close();
      return true;
    }

    $stmt->close();
  }

  return false;
}

/**
 * Toggle user admin status
 *
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @param bool $is_admin New admin status
 * @return bool True on success, false on failure
 */
function updateUserAdminStatus($mysqli, $userId, $is_admin) {
  $sql = "UPDATE users SET is_admin = ? WHERE id = ?";

  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ii", $is_admin, $userId);

    if ($stmt->execute()) {
      $stmt->close();
      return true;
    }

    $stmt->close();
  }

  return false;
}

/**
 * Get user details by ID
 *
 * @param mysqli $mysqli Database connection
 * @param int $userId User ID
 * @return array|bool User data or false if not found
 */
function getUserById($mysqli, $userId) {
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


