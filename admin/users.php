<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if not logged in or not admin
if (!is_logged_in() || !is_admin()) {
  redirect(ADMIN_URL); // Ensure ADMIN_URL is defined in config.php
}

$search_term = '';
$search_by = 'username'; // Default search by username
$users = [];
$message = '';

// Get database connection for functions that need it
$conn = get_db(); // Assuming get_db() returns the mysqli connection

// Handle user search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
  $search_term = sanitize($_GET['search_term'] ?? '');
  $search_by = sanitize($_GET['search_by'] ?? 'username');

  if (!empty($search_term)) {
    // Pass the connection to search_users if it's refactored to accept it
    $users = search_users($search_term, $search_by);
  }
} else {
  // Optionally: Display all active users by default if no search term is present
  // You would need a function like getAllUsers() that returns all users or a default search_users call.
  // For now, it will be empty if no search is performed, as per your original logic.
  // Example: $users = search_users('', 'username'); // This would return all users if search_users handles empty term
}


// Handle user deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_user'])) {
  // Validate CSRF token (highly recommended for POST requests)
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $message = display_error("Invalid request token. Please try again.");
  } else {
    $user_id = intval($_POST['user_id'] ?? 0);

    // Prevent admin from deactivating themselves or other super admins (optional, but good practice)
    if ($user_id > 0 && $_SESSION['user_id'] != $user_id) { // Ensure admin cannot deactivate self
      // You might also want to check if the target user is an admin before deactivating
      // $target_user_data = get_user($user_id); // Fetch target user data
      // if ($target_user_data && (bool)$target_user_data['is_admin']) {
      //     $message = display_error("Cannot deactivate another admin user directly.");
      // } else {
      if (deactivate_user($user_id)) { // Assuming this uses get_db() internally or you pass $conn
        $message = display_success("User deactivated successfully.");
      } else {
        $message = display_error("Failed to deactivate user.");
      }
      // }
    } else {
      $message = display_error("Invalid user ID or you cannot deactivate your own account.");
    }
  }

  // Re-fetch users after deactivation to reflect changes immediately
  if (!empty($search_term)) {
    $users = search_users($search_term, $search_by);
  } else {
    // If no search was active, you might want to show all active users again.
    // Assuming search_users with an empty term returns all active users.
    $users = search_users('', 'username');
  }
}

// Generate a new CSRF token for the form (important for POST requests)
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users - <?php echo SITE_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link href="<?php echo ASSETS_URL; ?>css/styles.css" rel="stylesheet">
</head>

<body>
  <?php include 'includes/header.php'; // Assuming header.php is in admin/includes/ 
  ?>

  <div class="container-fluid">
    <div class="row">
      <?php include 'includes/sidebar.php'; // Assuming sidebar.php is in admin/includes/ 
      ?>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">Manage Users</h1>
        </div>

        <?php echo $message; ?>

        <div class="card mb-4">
          <div class="card-header">
            <h5>Search Users</h5>
          </div>
          <div class="card-body">
            <form method="GET" action="users.php" class="row g-3">
              <div class="col-md-6">
                <input type="text" class="form-control" id="search_term" name="search_term" placeholder="Search term..." value="<?php echo htmlspecialchars($search_term); ?>">
              </div>

              <div class="col-md-4">
                <select class="form-select" id="search_by" name="search_by">
                  <option value="username" <?php echo ($search_by === 'username') ? 'selected' : ''; ?>>Username</option>
                  <option value="email" <?php echo ($search_by === 'email') ? 'selected' : ''; ?>>Email</option>
                  <option value="first_name" <?php echo ($search_by === 'first_name') ? 'selected' : ''; ?>>First Name</option>
                  <option value="last_name" <?php echo ($search_by === 'last_name') ? 'selected' : ''; ?>>Last Name</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="submit" name="search" class="btn btn-primary w-100">Search</button>
              </div>
            </form>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h5>User List</h5>
          </div>
          <div class="card-body">
            <?php if (empty($users)): ?>
              <div class="alert alert-info" role="alert">
                No users found based on your search criteria.
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Username</th>
                      <th>Email</th>
                      <th>First Name</th>
                      <th>Last Name</th>
                      <th>Date Joined</th>
                      <th>Active</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($users as $user): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                        <td><?php echo formatDate($user['date_joined']); ?></td>
                        <td>
                          <?php if ($user['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                          <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($user['is_active']): ?>
                            <form method="POST" action="users.php" class="d-inline deactivate-form">
                              <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                              <input type="hidden" name="deactivate_user" value="1">
                              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                              <button type="submit" class="btn btn-sm btn-warning" title="Deactivate User">
                                <i class="bi bi-person-x"></i> Deactivate
                              </button>
                            </form>
                          <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled>Inactive</button>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </main>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // JavaScript for deactivation confirmation
    document.querySelectorAll('.deactivate-form').forEach(form => {
      form.addEventListener('submit', function(event) {
        const username = this.closest('tr').querySelector('td:nth-child(2)').textContent;
        if (!confirm(`Are you sure you want to deactivate user "${username}"? This action can be undone by an admin, but the user will not be able to log in.`)) {
          event.preventDefault(); // Prevent form submission if user cancels
        }
      });
    });
  </script>
</body>

</html>