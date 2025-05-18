# User Management System

## How to Run the Application

1. **Install XAMPP**  
  Download and install XAMPP from [Apache Friends](https://www.apachefriends.org/).

2. **Start Services**  
  Open the XAMPP Control Panel and start the **Apache** and **MySQL** services.

3. **Set Up the Database**  
  - Open phpMyAdmin at [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
  - Create a new database named `user_management`.
  - Import the provided SQL schema into this database.

4. **Deploy the Project**  
  - Download or clone the repository into your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\user-management-system`).

5. **Configure Database Connection**  
  - Open `includes/config.php` or `includes/db.php` and update the database credentials if needed.

6. **Access the Application**  
  - For the client interface, go to: [http://localhost/user-management-system/](http://localhost/user-management-system/)
  - For the admin interface, go to: [http://localhost/user-management-system/admin/](http://localhost/user-management-system/admin/)

7. **Login**  
  - Use the initial admin credentials set in the database to log in as an administrator.

> **Tip:** Make sure to replace the default admin password hash in the SQL schema with a secure bcrypt hash before using in production.
