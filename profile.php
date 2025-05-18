<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
  redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

if (!$user) {
  redirect('logout.php');
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $email = sanitize($_POST['email'] ?? '');
  $first_name = sanitize($_POST['first_name'] ?? '');
  $last_name = sanitize($_POST['last_name'] ?? '');
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  // Validate input
  if (empty($email)) {
    $errors[] = "Email is required";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
  }

  if (empty($first_name)) {
    $errors[] = "First name is required";
  }

  if (empty($last_name)) {
    $errors[] = "Last name is required";
  }

  // If changing password
  $change_password = false;
  if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
    // Verify all password fields are filled
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
      $errors[] = "All password fields are required to change password";
    } else {
      // Verify current password
      $conn = get_db();
      $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $user_data = $result->fetch_assoc();

      if (!password_verify($current_password, $user_data['password'])) {
        $errors[] = "Current password is incorrect";
      }

      // Validate new password
      if (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters";
      }

      if ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match";
      }

      $change_password = true;
    }
  }

  // If no errors, update profile
  if (empty($errors)) {
    if ($change_password) {
      $result = update_profile($user_id, $email, $first_name, $last_name, $new_password);
    } else {
      $result = update_profile($user_id, $email, $first_name, $last_name);
    }

    if ($result) {
      $success = true;
      // Update session data
      $_SESSION['email'] = $email;
      $_SESSION['first_name'] = $first_name;
      $_SESSION['last_name'] = $last_name;

      // Get updated user data
      $user = get_user($user_id);
    } else {
      $errors[] = "Failed to update profile";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile - <?php echo SITE_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo ASSETS_URL; ?>css/styles.css" rel="stylesheet">
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h2>Edit Profile</h2>
          </div>
          <div class="card-body">
            <?php if ($success): ?>
              <div class="alert alert-success" role="alert">
                Profile updated successfully!
              </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                  <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="POST" action="profile.php">
              <div class="row mb-3">
                <label for="username" class="col-sm-3 col-form-label">Username</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                  <div class="form-text">Username cannot be changed</div>
                </div>
              </div>

              <div class="row mb-3">
                <label for="email" class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-9">
                  <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
              </div>

              <div class="row mb-3">
                <label for="first_name" class="col-sm-3 col-form-label">First Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                </div>
              </div>

              <div class="row mb-3">
                <label for="last_name" class="col-sm-3 col-form-label">Last Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                </div>
              </div>

              <hr>
              <h4>Change Password</h4>
              <p class="text-muted">Leave blank if you don't want to change your password</p>

              <div class="row mb-3">
                <label for="current_password" class="col-sm-3 col-form-label">Current Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" id="current_password" name="current_password">
                </div>
              </div>

              <div class="row mb-3">
                <label for="new_password" class="col-sm-3 col-form-label">New Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" id="new_password" name="new_password">
                </div>
              </div>

              <div class="row mb-3">
                <label for="confirm_password" class="col-sm-3 col-form-label">Confirm New Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Update Profile</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>