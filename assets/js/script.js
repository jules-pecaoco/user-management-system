/**
 * Main JavaScript file for User Management System
 */

document.addEventListener("DOMContentLoaded", function () {
  // Enable Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Enable Bootstrap popovers
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  // Profile image preview
  const profileImageInput = document.getElementById("profile_photo");
  const profileImagePreview = document.getElementById("profile_photo_preview");

  if (profileImageInput && profileImagePreview) {
    profileImageInput.addEventListener("change", function () {
      if (this.files && this.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
          profileImagePreview.src = e.target.result;
        };

        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // Password toggle visibility
  const togglePasswordBtns = document.querySelectorAll(".toggle-password");

  if (togglePasswordBtns) {
    togglePasswordBtns.forEach(function (btn) {
      btn.addEventListener("click", function () {
        const inputId = this.getAttribute("data-target");
        const passwordField = document.getElementById(inputId);

        if (passwordField.type === "password") {
          passwordField.type = "text";
          this.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
          passwordField.type = "password";
          this.innerHTML = '<i class="fas fa-eye"></i>';
        }
      });
    });
  }

  // User status toggle
  const userStatusToggles = document.querySelectorAll(".user-status-toggle");

  if (userStatusToggles) {
    userStatusToggles.forEach(function (toggle) {
      toggle.addEventListener("click", function (e) {
        e.preventDefault();

        const userId = this.getAttribute("data-user-id");
        const currentStatus = this.getAttribute("data-current-status");
        const newStatus = currentStatus === "1" ? "0" : "1";

        if (confirm("Are you sure you want to change this user's status?")) {
          // Create form data
          const formData = new FormData();
          formData.append("user_id", userId);
          formData.append("status", newStatus);
          formData.append("action", "toggle_status");

          // Send AJAX request
          fetch("users.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                // Update toggle button
                this.setAttribute("data-current-status", newStatus);

                // Update button text and class
                if (newStatus === "1") {
                  this.innerHTML = '<i class="fas fa-toggle-on"></i> Active';
                  this.classList.remove("btn-danger");
                  this.classList.add("btn-success");
                } else {
                  this.innerHTML = '<i class="fas fa-toggle-off"></i> Inactive';
                  this.classList.remove("btn-success");
                  this.classList.add("btn-danger");
                }

                // Update status badge
                const statusBadge = document.querySelector(`.user-status-badge[data-user-id="${userId}"]`);
                if (statusBadge) {
                  if (newStatus === "1") {
                    statusBadge.innerHTML = "Active";
                    statusBadge.classList.remove("bg-danger");
                    statusBadge.classList.add("bg-success");
                  } else {
                    statusBadge.innerHTML = "Inactive";
                    statusBadge.classList.remove("bg-success");
                    statusBadge.classList.add("bg-danger");
                  }
                }

                // Show success message
                showAlert("User status updated successfully!", "success");
              } else {
                showAlert("Failed to update user status: " + data.message, "danger");
              }
            })
            .catch((error) => {
              showAlert("An error occurred: " + error, "danger");
            });
        }
      });
    });
  }

  // Admin role toggle
  const adminRoleToggles = document.querySelectorAll(".admin-role-toggle");

  if (adminRoleToggles) {
    adminRoleToggles.forEach(function (toggle) {
      toggle.addEventListener("click", function (e) {
        e.preventDefault();

        const userId = this.getAttribute("data-user-id");
        const currentRole = this.getAttribute("data-current-role");
        const newRole = currentRole === "1" ? "0" : "1";

        if (confirm("Are you sure you want to change this user's role?")) {
          // Create form data
          const formData = new FormData();
          formData.append("user_id", userId);
          formData.append("role", newRole);
          formData.append("action", "toggle_role");

          // Send AJAX request
          fetch("users.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                // Update toggle button
                this.setAttribute("data-current-role", newRole);

                // Update button text and class
                if (newRole === "1") {
                  this.innerHTML = '<i class="fas fa-user-shield"></i> Admin';
                  this.classList.remove("btn-secondary");
                  this.classList.add("btn-primary");
                } else {
                  this.innerHTML = '<i class="fas fa-user"></i> User';
                  this.classList.remove("btn-primary");
                  this.classList.add("btn-secondary");
                }

                // Update role badge
                const roleBadge = document.querySelector(`.user-role-badge[data-user-id="${userId}"]`);
                if (roleBadge) {
                  if (newRole === "1") {
                    roleBadge.innerHTML = "Admin";
                    roleBadge.classList.remove("bg-secondary");
                    roleBadge.classList.add("bg-primary");
                  } else {
                    roleBadge.innerHTML = "User";
                    roleBadge.classList.remove("bg-primary");
                    roleBadge.classList.add("bg-secondary");
                  }
                }

                // Show success message
                showAlert("User role updated successfully!", "success");
              } else {
                showAlert("Failed to update user role: " + data.message, "danger");
              }
            })
            .catch((error) => {
              showAlert("An error occurred: " + error, "danger");
            });
        }
      });
    });
  }

  // Helper function to show alerts
  function showAlert(message, type = "info") {
    const alertContainer = document.getElementById("alert-container");

    if (alertContainer) {
      const alertDiv = document.createElement("div");
      alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
      alertDiv.role = "alert";
      alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

      alertContainer.appendChild(alertDiv);

      // Auto-remove alert after 5 seconds
      setTimeout(function () {
        alertDiv.classList.remove("show");
        setTimeout(function () {
          alertContainer.removeChild(alertDiv);
        }, 150);
      }, 5000);
    }
  }
});
