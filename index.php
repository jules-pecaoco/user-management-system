<?php
// Start session
session_start();

// Include configuration and database connection
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
$user = null;

if ($logged_in) {
  // Get user information
  $user_id = $_SESSION['user_id'];
  $query = "SELECT * FROM users WHERE id = ?";
  $stmt = get_db()->prepare($query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
  } else {
    // User not found, clear session
    session_unset();
    session_destroy();
    $logged_in = false;
  }
}

// Page title
$page_title = "Welcome to User Management System";

// Include header
include_once 'includes/header.php';
?>

<div class="container mt-5">
  <div class="row">
    <div class="col-lg-12 text-center">
      <h1>User Management System</h1>
      <p class="lead">A comprehensive solution for managing users and accounts</p>
    </div>
  </div>

  <div class="row mt-4">
    <?php if ($logged_in): ?>
      <!-- Content for logged-in users -->
      <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="card-title">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
            <p class="card-text">You are successfully logged in to the User Management System.</p>

            <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
              <a href="profile.php" class="btn btn-primary me-md-2">View Profile</a>
              <?php if ($user['is_admin']): ?>
                <a href="admin/dashboard.php" class="btn btn-success me-md-2">Admin Dashboard</a>
              <?php endif; ?>
              <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <!-- Content for non-logged-in users -->
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="card-title">User Features</h3>
            <ul class="list-unstyled">
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> Create and manage your account</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> Update your profile information</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> Secure authentication system</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> User-friendly interface</li>
            </ul>
            <div class="d-grid gap-2">
              <a href="login.php" class="btn btn-primary mb-2">Login to Your Account</a>
              <a href="register.php" class="btn btn-outline-primary">Create New Account</a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="card-title">Admin Features</h3>
            <ul class="list-unstyled">
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> Manage all user accounts</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> Add new administrators</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> Search and filter users</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> Activate/deactivate accounts</li>
            </ul>
            <div class="d-grid gap-2">
              <a href="admin/index.php" class="btn btn-success">Admin Login</a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- System Information Section -->
  <div class="row mt-5">
    <div class="col-lg-12">
      <div class="card bg-light">
        <div class="card-body">
          <h3 class="card-title text-center mb-4">About This System</h3>
          <div class="row">
            <div class="col-md-4 mb-3">
              <div class="text-center">
                <i class="bi bi-shield-lock fs-1 text-primary"></i>
                <h4 class="mt-3">Secure</h4>
                <p>Password hashing and secure session management keep your data safe.</p>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <div class="text-center">
                <i class="bi bi-speedometer2 fs-1 text-primary"></i>
                <h4 class="mt-3">Efficient</h4>
                <p>Optimized database queries and caching for fast performance.</p>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <div class="text-center">
                <i class="bi bi-gear fs-1 text-primary"></i>
                <h4 class="mt-3">Customizable</h4>
                <p>Easily extend or modify to fit your specific requirements.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>