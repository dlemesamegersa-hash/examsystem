# Temporary Deployment Guide (Windows + IIS)

This guide provides steps to deploy the University Class Schedule Management System on a Windows Server or Desktop using **Internet Information Services (IIS)** as a temporary hosting solution.

## Phase 1: Prepare the Windows Environment

### 1. Enable IIS and CGI
1. Open **Control Panel** > **Programs** > **Turn Windows features on or off**.
2. Expand **Internet Information Services** > **World Wide Web Services** > **Application Development Features**.
3. Check **CGI** (This is required for FastCGI/PHP).
4. Click **OK** and wait for installation to finish.

### 2. Install PHP for IIS
1. Download the latest **Non-Thread Safe (NTS) x64** version of PHP from [windows.php.net](https://windows.php.net/download/).
2. Extract the files to `C:\php`.
3. Rename `php.ini-production` to `php.ini`.
4. Edit `php.ini` and ensure the following extensions are enabled (remove the `;`):
   ```ini
   extension=curl
   extension=gd
   extension=mbstring
   extension=mysqli
   extension=openssl
   extension=pdo_mysql
   ```
5. Set the timezone: `date.timezone = UTC` (or your local timezone).
6. Set `cgi.force_redirect = 0` and `fastcgi.impersonate = 1`.

### 3. Configure FastCGI in IIS
1. Open **IIS Manager**.
2. Select your server in the left pane.
3. Double-click **Handler Mappings**.
4. Click **Add Module Mapping** in the right pane:
   - **Request path:** `*.php`
   - **Module:** `FastCgiModule`
   - **Executable:** `C:\php\php-cgi.exe`
   - **Name:** `PHP_via_FastCGI`
5. Click **OK**.

---

## Phase 2: Database Setup

### 1. Install MySQL/MariaDB
- If you don't have it installed, you can use **MySQL Community Server** or continue using the MySQL instance from your XAMPP installation if it's already running as a service.

### 2. Import Schema
- Use a tool like **HeidiSQL** or **phpMyAdmin** to import `schema.sql` into a new database named `university_schedule`.

---

## Phase 3: Project Deployment

### 1. Copy Project Files
- Move the contents of the `obuclass` folder to `C:\inetpub\wwwroot\obuclass`.

### 2. Configure Permissions
1. Right-click the `obuclass` folder > **Properties** > **Security**.
2. Click **Edit** > **Add**.
3. Type `IUSR` and click OK. Give it **Read & Execute** permissions.
4. Add `IIS_IUSRS` and give it **Read & Execute**.
5. **Important**: For the `cache/` and `images/` folders, give both `IUSR` and `IIS_IUSRS` **Modify** permissions so the system can write files.

### 3. Update Configuration
- Open `C:\inetpub\wwwroot\obuclass\includes\config.php`.
- Update the database credentials to match your production environment.
- Update `BASE_URL` if necessary (e.g., `http://your-ip-or-domain/obuclass/`).

---

## Phase 4: IIS Website Setup

1. In **IIS Manager**, right-click **Sites** > **Add Website**.
2. **Site name**: `OBU_Schedule`
3. **Physical path**: `C:\inetpub\wwwroot\obuclass`
4. **Binding**: Set your desired port (e.g., 80 or 8080).
5. Ensure **Default Document** in IIS has `index.php` at the top of the list.

## Troubleshooting
- **500 Internal Server Error**: Check the PHP error logs (configure `error_log` in `php.ini`).
- **403 Forbidden**: Ensure the directory permissions for `IUSR` are correctly set.
- **Database Connection Refused**: Ensure the MySQL service is running and the credentials in `config.php` are correct.

---
**Note:** This IIS setup is intended as a temporary bridge until you migrate to Ubuntu.
