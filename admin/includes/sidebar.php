<!-- Admin Sidebar -->
<div class="sidebar bg-dark text-white" id="sidebar">
  <div class="sidebar-header p-3 border-bottom border-secondary">
    <h3 class="fs-5 text-center">Admin Dashboard</h3>
    <div class="d-flex justify-content-center mt-2">
      <span class="badge bg-success">Administrator</span>
    </div>
  </div>

  <!-- Admin Info -->
  <div class="admin-info p-3 border-bottom border-secondary">
    <?php
    // Get admin info from session
    $admin_name = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
    $admin_username = htmlspecialchars($_SESSION['username']);
    ?>
    <div class="text-center mb-3">
      <div class="admin-avatar mb-2">
        <i class="bi bi-person-circle fs-1"></i>
      </div>
      <div class="admin-name"><?php echo $admin_name; ?></div>
      <small class="text-muted">@<?php echo $admin_username; ?></small>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active bg-primary' : ''; ?>" href="dashboard.php">
          <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active bg-primary' : ''; ?>" href="users.php">
          <i class="bi bi-people me-2"></i> Manage Users
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'add-user.php') ? 'active bg-primary' : ''; ?>" href="add-user.php">
          <i class="bi bi-person-plus me-2"></i> Add New User
        </a>
      </li>
    </ul>
  </nav>

  <!-- Footer Links -->
  <div class="sidebar-footer p-3 mt-auto border-top border-secondary">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link text-white" href="../index.php">
          <i class="bi bi-house me-2"></i> Main Site
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="../profile.php">
          <i class="bi bi-person me-2"></i> My Profile
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-danger" href="../logout.php">
          <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</div>

<!-- CSS for the sidebar -->
<style>
  .sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    width: 250px;
    z-index: 100;
    padding: 0;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow-y: auto;
    transition: all 0.3s;
  }

  .sidebar-nav .nav-link {
    padding: 0.8rem 1rem;
    border-radius: 0;
    transition: all 0.2s;
  }

  .sidebar-nav .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }

  .sidebar-nav .nav-link.active {
    font-weight: bold;
  }

  .main-content {
    margin-left: 250px;
    padding: 20px;
    transition: all 0.3s;
  }

  /* For mobile view */
  @media (max-width: 768px) {
    .sidebar {
      margin-left: -250px;
    }

    .sidebar.active {
      margin-left: 0;
    }

    .main-content {
      margin-left: 0;
    }

    .main-content.active {
      margin-left: 250px;
    }

    #sidebarCollapse {
      display: block !important;
    }
  }

  .admin-avatar {
    color: #fff;
  }
</style>

<!-- Optional: Toggle button for mobile view -->
<button type="button" id="sidebarCollapse" class="btn btn-dark d-md-none position-fixed top-0 start-0 mt-2 ms-2" style="z-index: 101; display: none;">
  <i class="bi bi-list"></i>
</button>

<!-- Script for toggling sidebar on mobile -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var sidebarCollapse = document.getElementById('sidebarCollapse');
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.querySelector('.main-content');

    if (sidebarCollapse) {
      sidebarCollapse.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('active');
      });
    }
  });
</script>