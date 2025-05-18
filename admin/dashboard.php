```php
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if not logged in or not admin
if (!is_logged_in() || !is_admin()) {
  redirect(ADMIN_URL);
}

// Get user count
$conn = get_db();
$result = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0");
$row = $result->fetch_assoc();
$total_users = $row['total_users'];

// Get admin count
$result = $conn->query("SELECT COUNT(*) as total_admins FROM users WHERE is_admin = 1");
$row = $result->fetch_assoc();
$total_admins = $row['total_admins'];

// Get active user count
$result = $conn->query("SELECT COUNT(*) as active_users FROM users WHERE is_active = 1 AND is_admin = 0");
$row = $result->fetch_assoc();
$active_users = $row['active_users'];

// Get inactive user count
$result = $conn->query("SELECT COUNT(*) as inactive_users FROM users WHERE is_active = 0 AND is_admin = 0");
$row = $result->fetch_assoc();
$inactive_users = $row['inactive_users'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link href="<?php echo ASSETS_URL; ?>css/styles.css" rel="stylesheet">
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="container-fluid">
    <div class="row">
      <?php include 'includes/sidebar.php'; ?>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">Dashboard</h1>
        </div>

        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary">
              <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h1 class="display-4"><?php echo $total_users; ?></h1>
                <p class="card-text">Registered client accounts</p>
              </div>
            </div>
          </div>

          <div class="col-md-3 mb-4">
            <div class="card text-white bg-success">
              <div class="card-body">
                <h5 class="card-title">Active Users</h5>
                <h1 class="display-4"><?php echo $active_users; ?></h1>
                <p class="card-text">Active client accounts</p>
              </div>
            </div>
          </div>

          <div class="col-md-3 mb-4">
            <div class="card text-white bg-danger">
              <div class="card-body">
                <h5 class="card-title">Inactive Users</h5>
                <h1 class="display-4"><?php echo $inactive_users; ?></h1>
                <p class="card-text">Deactivated accounts</p>
              </div>
            </div>
          </div>

          <div class="col-md-3 mb-4">
            <div class="card text-white bg-secondary">
              <div class="card-body">
                <h5 class="card-title">Administrators</h5>
                <h1 class="display-4"><?php echo $total_admins; ?></h1>
                <p class="card-text">Admin accounts</p>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h5>Quick Links</h5>
              </div>
              <div class="card-body">
                <div class="d-grid gap-2">
                  <a href="users.php" class="btn btn-outline-primary">Manage Users</a>
                  <a href="add-user.php" class="btn btn-outline-success">Add New Admin</a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h5>Current Admin</h5>
              </div>
              <div class="card-body">
                <p><strong>Username:</strong> <?php echo $_SESSION['username']; ?></p>
                <p><strong>Name:</strong> <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
                <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
```