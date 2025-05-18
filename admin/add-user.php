<?php
// Initialize the session if not already started
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in and is an admin, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["is_admin"] !== true) {
  header("location: index.php");
  exit;
}

// Include config file
require_once "../includes/config.php";
require_once "../includes/db.php";
require_once "includes/functions.php";

// Define variables and initialize with empty values
$username = $email = $password = $confirm_password = $first_name = $last_name = "";
$username_err = $email_err = $password_err = $confirm_password_err = $first_name_err = $last_name_err = "";
$is_admin = false;
$success_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Validate username
  if (empty(trim($_POST["username"]))) {
    $username_err = "Please enter a username.";
  } else {
    // Prepare a select statement
    $sql = "SELECT id FROM users WHERE username = ?";

    if ($stmt = get_db()->prepare($sql)) {
      // Bind variables to the prepared statement as parameters
      $stmt->bind_param("s", $param_username);

      // Set parameters
      $param_username = trim($_POST["username"]);

      // Attempt to execute the prepared statement
      if ($stmt->execute()) {
        // Store result
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
          $username_err = "This username is already taken.";
        } else {
          $username = trim($_POST["username"]);
        }
      } else {
        echo "Oops! Something went wrong. Please try again later.";
      }

      // Close statement
      $stmt->close();
    }
  }

  // Validate email
  if (empty(trim($_POST["email"]))) {
    $email_err = "Please enter an email.";
  } else {
    // Check if email is valid
    if (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
      $email_err = "Please enter a valid email address.";
    } else {
      // Prepare a select statement
      $sql = "SELECT id FROM users WHERE email = ?";

      if ($stmt = get_db()->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("s", $param_email);

        // Set parameters
        $param_email = trim($_POST["email"]);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
          // Store result
          $stmt->store_result();

          if ($stmt->num_rows == 1) {
            $email_err = "This email is already taken.";
          } else {
            $email = trim($_POST["email"]);
          }
        } else {
          echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
      }
    }
  }

  // Validate password
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter a password.";
  } elseif (strlen(trim($_POST["password"])) < 6) {
    $password_err = "Password must have at least 6 characters.";
  } else {
    $password = trim($_POST["password"]);
  }

  // Validate confirm password
  if (empty(trim($_POST["confirm_password"]))) {
    $confirm_password_err = "Please confirm password.";
  } else {
    $confirm_password = trim($_POST["confirm_password"]);
    if (empty($password_err) && ($password != $confirm_password)) {
      $confirm_password_err = "Password did not match.";
    }
  }

  // Validate first name
  if (empty(trim($_POST["first_name"]))) {
    $first_name_err = "Please enter a first name.";
  } else {
    $first_name = trim($_POST["first_name"]);
  }

  // Validate last name
  if (empty(trim($_POST["last_name"]))) {
    $last_name_err = "Please enter a last name.";
  } else {
    $last_name = trim($_POST["last_name"]);
  }

  // Check if user should be an admin
  if (isset($_POST["is_admin"]) && $_POST["is_admin"] == "1") {
    $is_admin = true;
  }

  // Check input errors before inserting in database
  if (
    empty($username_err) && empty($email_err) && empty($password_err) &&
    empty($confirm_password_err) && empty($first_name_err) && empty($last_name_err)
  ) {

    // Prepare an insert statement
    $sql = "INSERT INTO users (username, email, password, first_name, last_name, is_admin) VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = get_db()->prepare($sql)) {
      // Bind variables to the prepared statement as parameters
      $stmt->bind_param("sssssi", $param_username, $param_email, $param_password, $param_first_name, $param_last_name, $param_is_admin);

      // Set parameters
      $param_username = $username;
      $param_email = $email;
      $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
      $param_first_name = $first_name;
      $param_last_name = $last_name;
      $param_is_admin = $is_admin;

      // Attempt to execute the prepared statement
      if ($stmt->execute()) {
        // User added successfully
        $success_message = "User was created successfully.";

        // Clear form values
        $username = $email = $password = $confirm_password = $first_name = $last_name = "";
        $is_admin = false;
      } else {
        echo "Oops! Something went wrong. Please try again later.";
      }

      // Close statement
      $stmt->close();
    }
  }
}

// Include header
include "includes/header.php";
?>

<div class="container mt-5">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4>Add New User</h4>
        </div>
        <div class="card-body">
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
              <?php echo $success_message; ?>
            </div>
          <?php endif; ?>

          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group row mb-3">
              <label for="username" class="col-sm-3 col-form-label">Username</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                  id="username" name="username" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
              </div>
            </div>

            <div class="form-group row mb-3">
              <label for="email" class="col-sm-3 col-form-label">Email</label>
              <div class="col-sm-9">
                <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                  id="email" name="email" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
              </div>
            </div>

            <div class="form-group row mb-3">
              <label for="password" class="col-sm-3 col-form-label">Password</label>
              <div class="col-sm-9">
                <input type="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                  id="password" name="password" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
              </div>
            </div>

            <div class="form-group row mb-3">
              <label for="confirm_password" class="col-sm-3 col-form-label">Confirm Password</label>
              <div class="col-sm-9">
                <input type="password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>"
                  id="confirm_password" name="confirm_password" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
              </div>
            </div>

            <div class="form-group row mb-3">
              <label for="first_name" class="col-sm-3 col-form-label">First Name</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>"
                  id="first_name" name="first_name" value="<?php echo $first_name; ?>">
                <span class="invalid-feedback"><?php echo $first_name_err; ?></span>
              </div>
            </div>

            <div class="form-group row mb-3">
              <label for="last_name" class="col-sm-3 col-form-label">Last Name</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>"
                  id="last_name" name="last_name" value="<?php echo $last_name; ?>">
                <span class="invalid-feedback"><?php echo $last_name_err; ?></span>
              </div>
            </div>

            <div class="form-group row mb-3">
              <div class="col-sm-3">Administrator</div>
              <div class="col-sm-9">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1"
                    <?php echo $is_admin ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="is_admin">
                    Grant admin privileges
                  </label>
                </div>
              </div>
            </div>

            <div class="form-group row">
              <div class="col-sm-9 offset-sm-3">
                <button type="submit" class="btn btn-primary">Add User</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>