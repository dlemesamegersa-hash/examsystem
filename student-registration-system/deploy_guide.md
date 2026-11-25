# Server Deployment & Production Guide (Ubuntu & Windows Server)
### Student Registration System (SRS)
**Target Static IP:** `10.140.1.9`  
**Remote Management:** SSH (`ssh <user>@10.140.1.9`) / RDP (Windows)  
**GitHub Repository:** `https://github.com/hashimsiraj06-commits/stud_reg_system.git`

---

## Table of Contents

### PART 1: Ubuntu Server Deployment
1. [Overview & Prerequisites](#1-overview--prerequisites)
2. [Step 1: Ubuntu Server Installation](#step-1-ubuntu-server-installation)
3. [Step 2: Assign Static IP (10.140.1.9) with Netplan](#step-2-assign-static-ip-1014019-with-netplan)
4. [Step 3: SSH Configuration & Remote Access](#step-3-ssh-configuration--remote-access)
5. [Step 4: Install LAMP Stack (Apache, MySQL, PHP 8.x)](#step-4-install-lamp-stack-apache-mysql-php-8x)
6. [Step 5: Move/Clone Project to Ubuntu Server](#step-5-moveclone-project-to-ubuntu-server)
   - [Method A: Clone directly from GitHub (Recommended)](#method-a-clone-directly-from-github-recommended)
   - [Method B: Transfer from Local Windows PC (SCP / SFTP)](#method-b-transfer-from-local-windows-pc-scp--sftp)
7. [Step 6: MySQL Database Configuration & Migration](#step-6-mysql-database-configuration--migration)
8. [Step 7: Application Configuration & Permissions](#step-7-application-configuration--permissions)
9. [Step 8: Apache VirtualHost Configuration](#step-8-apache-virtualhost-configuration)
10. [Step 9: Firewall Configuration (UFW)](#step-9-firewall-configuration-ufw)
11. [Step 10: Automated Daily Data Backup (Database + Uploads)](#step-10-automated-daily-data-backup-database--uploads)
12. [Step 11: Disaster Recovery & Restore Guide](#step-11-disaster-recovery--restore-guide)
13. [Maintenance & Troubleshooting Commands (Ubuntu)](#maintenance--troubleshooting-commands)

### PART 2: Windows Server Deployment (2019 / 2022 / 2025)
14. [14. Windows Server Architecture & Prerequisites](#14-windows-server-architecture--prerequisites)
15. [Step W1: Assign Static IP (10.140.1.9) on Windows Server](#step-w1-assign-static-ip-1014019-on-windows-server)
16. [Step W2: Enable OpenSSH Server on Windows Server for Remote Access](#step-w2-enable-openssh-server-on-windows-server-for-remote-access)
17. [Step W3: Move/Clone Project to Windows Server (Git & SCP)](#step-w3-moveclone-project-to-windows-server-git--scp)
18. [Step W4: Install & Configure MySQL Server on Windows Server](#step-w4-install--configure-mysql-server-on-windows-server)
19. [Step W5: Web Server Deployment Options on Windows Server](#step-w5-web-server-deployment-options-on-windows-server)
    - [Option A: Native IIS + PHP (FastCGI) Deployment](#option-a-native-iis--php-fastcgi-deployment-enterprise-standard)
    - [Option B: Production Apache & MySQL as Windows Services](#option-b-production-apache--mysql-as-windows-services-via-xamppapache-lounge)
20. [Step W6: NTFS Permissions for Uploads & Web Security](#step-w6-ntfs-permissions-for-uploads--web-security)
21. [Step W7: Windows Defender Firewall Configuration](#step-w7-windows-defender-firewall-configuration)
22. [Step W8: Automated Daily Backups with PowerShell & Task Scheduler](#step-w8-automated-daily-backups-with-powershell--task-scheduler)
23. [Step W9: Windows Server Disaster Recovery & Restore Guide](#step-w9-windows-server-disaster-recovery--restore-guide)
24. [Windows Server Maintenance & Health Check Commands](#windows-server-maintenance--health-check-commands)

---

## 1. Overview & Prerequisites

This document provides complete, step-by-step instructions to deploy the **Student Registration System** onto an **Ubuntu Server** (22.04 LTS or 24.04 LTS) connected to your local network.

### Network Architecture:
- **Server Operating System:** Ubuntu Server 22.04 LTS / 24.04 LTS (64-bit)
- **Assigned Static IP:** `10.140.1.9`
- **Subnet Mask:** `255.255.255.0` (`/24`)
- **Default Gateway:** `10.140.1.1` *(adjust to match your local router if different)*
- **DNS Nameservers:** `10.140.1.1`, `8.8.8.8`, `1.1.1.1`
- **Web Access URL:** `http://10.140.1.9/`
- **Remote Access:** `ssh <username>@10.140.1.9`

---

## Step 1: Ubuntu Server Installation

1. **Download Ubuntu Server ISO:**
   Download the latest Ubuntu Server 22.04 LTS or 24.04 LTS from [ubuntu.com/download/server](https://ubuntu.com/download/server).
2. **Create Bootable USB:**
   Use **Rufus** (Windows) or **BalenaEtcher** to flash the ISO to a USB flash drive.
3. **Boot & Install:**
   - Plug the USB into your server machine and boot from it.
   - Choose language and keyboard layout.
   - For installation type, choose **Ubuntu Server (standard)**.
   - **Network Connections:** You can accept DHCP initially or set static IP now (we will configure static IP permanently in Step 2).
   - **Storage Configuration:** Use an entire disk (LVM recommended).
   - **Profile Setup:**
     - Your name: `Administrator`
     - Server's name: `srs-server`
     - Username: `srsadmin` *(or your preferred username)*
     - Password: Choose a strong password.
   - **SSH Setup:** Check **[X] Install OpenSSH server**. (Critical for remote access).
   - **Featured Server Snaps:** Skip (press Done).
   - Allow installation to complete and reboot. Remove the USB when prompted.

---

## Step 2: Assign Static IP (`10.140.1.9`) with Netplan

Ubuntu Server uses **Netplan** to manage networking.

### 1. Identify Network Interface Name
Log in to the server console and run:
```bash
ip link
```
*Look for your Ethernet interface name. Common names are `enp3s0`, `eth0`, `eno1`, or `ens33`.*

### 2. Edit Netplan Configuration File
Inspect the files in `/etc/netplan/`:
```bash
ls /etc/netplan/
```
Typically the file is named `00-installer-config.yaml` or `01-netcfg.yaml`. Open it using `nano`:
```bash
sudo nano /etc/netplan/00-installer-config.yaml
```

### 3. Replace Contents with Static IP Settings
Replace the content with the following configuration (replace `enp3s0` with your actual interface name found in step 1):

```yaml
network:
  version: 2
  renderer: networkd
  ethernets:
    enp3s0:
      dhcp4: no
      addresses:
        - 10.140.1.9/24
      routes:
        - to: default
          via: 10.140.1.1
      nameservers:
        addresses:
          - 10.140.1.1
          - 8.8.8.8
          - 1.1.1.1
```

> **Note on YAML formatting:** YAML is strictly space-sensitive. Use spaces only (never tabs).

Save and exit in nano: press `CTRL + O`, then `ENTER`, then `CTRL + X`.

### 4. Apply Netplan Settings
```bash
sudo netplan generate
sudo netplan apply
```

### 5. Verify Static IP & Network Connectivity
```bash
# Verify IP is assigned to 10.140.1.9
ip addr show enp3s0

# Test gateway ping
ping -c 3 10.140.1.1

# Test external DNS/Internet connectivity
ping -c 3 8.8.8.8
```

---

## Step 3: SSH Configuration & Remote Access

Now that the server has static IP `10.140.1.9`, you can manage it remotely from your personal computer over SSH.

### 1. Verify SSH Service on Ubuntu Server
```bash
sudo apt update
sudo apt install openssh-server -y
sudo systemctl enable ssh
sudo systemctl start ssh
sudo systemctl status ssh
```

### 2. Connect from Local PC (Windows PowerShell, CMD, or Terminal)
Open **PowerShell** or **Command Prompt** on your Windows laptop/desktop (must be connected to the same `10.140.1.x` network):

```powershell
ssh srsadmin@10.140.1.9
```
- Type `yes` to accept the server fingerprint when connecting for the first time.
- Enter your password for `srsadmin`.
- You are now connected directly to the Ubuntu Server shell!

### 3. (Optional) Setup SSH Key-Based Authentication (Passwordless & Faster)
On your Windows machine in PowerShell:
```powershell
# Generate key if you don't have one
ssh-keygen -t ed25519

# Copy key to server
type $env:USERPROFILE\.ssh\id_ed25519.pub | ssh srsadmin@10.140.1.9 "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && chmod 700 ~/.ssh"
```
Now connecting with `ssh srsadmin@10.140.1.9` logs you in instantly without prompting for a password.

---

## Step 4: Install LAMP Stack (Apache, MySQL, PHP 8.x)

Run the following commands inside your SSH session on the server:

### 1. Update Package Repositories
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Apache Web Server
```bash
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2
sudo a2enmod rewrite
```

### 3. Install MySQL Database Server
```bash
sudo apt install mysql-server -y
sudo systemctl enable mysql
sudo systemctl start mysql
```

Secure the MySQL installation:
```bash
sudo mysql_secure_installation
```
*(Answer `Y` to enforce password validation, set your root password or press ENTER if using auth_socket, remove anonymous users, disallow root remote login, and remove test database).*

### 4. Install PHP and Required Modules
The Student Registration System uses PDO, file uploads, GD/finfo, and string utilities. Install PHP and its extensions:

```bash
sudo apt install php libapache2-mod-php php-mysql php-mbstring php-xml php-curl php-gd php-zip -y
```

### 5. Verify Installations
```bash
apache2 -v
mysql --version
php -v
```

---

## Step 5: Move/Clone Project to Ubuntu Server

Choose either **Method A** (via GitHub) or **Method B** (direct file transfer from Windows PC):

### Method A: Clone directly from GitHub (Recommended)

1. **Install Git:**
   ```bash
   sudo apt install git -y
   ```

2. **Clone the Repository directly into the web root:**
   ```bash
   cd /var/www/html
   sudo git clone https://github.com/hashimsiraj06-commits/stud_reg_system.git stud_reg_system
   ```

3. **To update in the future when changes are pushed:**
   ```bash
   cd /var/www/html/stud_reg_system
   sudo git pull origin main
   ```

---

### Method B: Transfer from Local Windows PC (SCP / SFTP)

If your local codebase contains files not yet pushed to GitHub, transfer them directly from your Windows machine using `scp` in **PowerShell**:

1. On your **Windows PC** (run from `C:\xampp\htdocs\`):
   ```powershell
   scp -r C:\xampp\htdocs\stud_reg_system srsadmin@10.140.1.9:/tmp/
   ```

2. On the **Ubuntu Server** (via SSH):
   ```bash
   sudo rm -rf /var/www/html/stud_reg_system
   sudo mv /tmp/stud_reg_system /var/www/html/stud_reg_system
   ```

*(Alternatively, you can use **WinSCP** or **FileZilla** to connect using SFTP to `10.140.1.9` on port `22` with your `srsadmin` credentials and drag-and-drop files).*

---

## Step 6: MySQL Database Configuration & Migration

### 1. Log into MySQL CLI
```bash
sudo mysql
```

### 2. Create Database & Dedicated Database User
Run the following SQL commands:
```sql
CREATE DATABASE IF NOT EXISTS `stud_reg_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated database user
CREATE USER 'srs_user'@'localhost' IDENTIFIED BY 'SrsSecurePass2026!';

-- Grant privileges
GRANT ALL PRIVILEGES ON `stud_reg_system`.* TO 'srs_user'@'localhost';
FLUSH PRIVILEGES;

-- Verify database
SHOW DATABASES;

EXIT;
```

### 3. Import SQL Schema and Seed Data
Import the project's SQL file (`database/stud_reg_system.sql`):
```bash
mysql -u srs_user -p'SrsSecurePass2026!' stud_reg_system < /var/www/html/stud_reg_system/database/stud_reg_system.sql
```

### 4. Verify Database Tables
```bash
mysql -u srs_user -p'SrsSecurePass2026!' -e "USE stud_reg_system; SHOW TABLES; SELECT id, full_name, role FROM users;"
```
You should see:
- `users` (with pre-seeded `admin` and `staff` accounts)
- `students` (with 5 sample student records)
- `contact_messages` (with 3 sample inquiries)

---

## Step 7: Application Configuration & Permissions

### 1. Update Database Credentials in `config/db.php`
Edit the configuration file on the server:
```bash
sudo nano /var/www/html/stud_reg_system/config/db.php
```

Ensure the credentials match your MySQL setup:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'stud_reg_system');
define('DB_USER', 'srs_user');
define('DB_PASS', 'SrsSecurePass2026!');
define('DB_PORT', '3306');
```
Save and exit (`CTRL + O`, `ENTER`, `CTRL + X`).

### 2. Configure File & Directory Permissions
Set Apache's web user (`www-data`) as the owner and grant write permissions for student photos:

```bash
# Set web server user ownership
sudo chown -R www-data:www-data /var/www/html/stud_reg_system

# Standard directory and file permissions
sudo find /var/www/html/stud_reg_system -type d -exec chmod 755 {} \;
sudo find /var/www/html/stud_reg_system -type f -exec chmod 644 {} \;

# Writable uploads folder for student photo uploads
sudo chmod -R 775 /var/www/html/stud_reg_system/uploads
sudo chmod -R 775 /var/www/html/stud_reg_system/uploads/students
```

---

## Step 8: Apache VirtualHost Configuration

Configure Apache to serve the project smoothly on `http://10.140.1.9`.

### 1. Create a New VirtualHost File
```bash
sudo nano /etc/apache2/sites-available/stud_reg_system.conf
```

Paste the following configuration:
```apache
<VirtualHost *:80>
    ServerAdmin admin@studreg.edu
    ServerName 10.140.1.9
    DocumentRoot /var/www/html/stud_reg_system

    <Directory /var/www/html/stud_reg_system>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/stud_reg_system_error.log
    CustomLog ${APACHE_LOG_DIR}/stud_reg_system_access.log combined
</VirtualHost>
```
Save and exit (`CTRL + O`, `ENTER`, `CTRL + X`).

### 2. Enable the Site and Rewrite Engine
```bash
# Enable Apache mod_rewrite
sudo a2enmod rewrite

# Disable default Apache welcome page
sudo a2dissite 000-default.conf

# Enable Student Registration System site
sudo a2ensite stud_reg_system.conf

# Test Apache syntax
sudo apache2ctl configtest
```
*(Ensure it responds with `Syntax OK`)*

### 3. Restart Apache
```bash
sudo systemctl restart apache2
```

---

## Step 9: Firewall Configuration (UFW)

Protect your server by enabling Ubuntu's Uncomplicated Firewall (UFW) while keeping SSH and Web ports open:

```bash
# Allow SSH so you don't get locked out
sudo ufw allow 22/tcp comment 'OpenSSH Remote Access'

# Allow HTTP Web Traffic
sudo ufw allow 80/tcp comment 'HTTP Web Server'

# Enable firewall
sudo ufw --force enable

# Check status
sudo ufw status verbose
```

---

## Step 10: Automated Daily Data Backup (Database + Uploads)

To safeguard your institution's records against hardware failure or corruption, configure automated daily backups of:
1. **The MySQL Database** (`stud_reg_system`)
2. **Uploaded Student Photos** (`/uploads/students/`)

### 1. Create Backup Storage Directory
```bash
sudo mkdir -p /var/backups/stud_reg_system
sudo chown -R srsadmin:srsadmin /var/backups/stud_reg_system
sudo chmod 700 /var/backups/stud_reg_system
```

### 2. Create the Backup Script
```bash
sudo nano /usr/local/bin/backup_srs.sh
```

Paste the following automated script:
```bash
#!/bin/bash
# ==============================================================================
# Automated Backup Script for Student Registration System
# Backs up MySQL database and uploaded student photos
# ==============================================================================

BACKUP_DIR="/var/backups/stud_reg_system"
DATE=$(date +%Y-%m-%d_%H%M%S)
DB_NAME="stud_reg_system"
DB_USER="srs_user"
DB_PASS="SrsSecurePass2026!"
WEB_UPLOADS="/var/www/html/stud_reg_system/uploads"

# Create destination if missing
mkdir -p "$BACKUP_DIR"

echo "[$DATE] Starting Student Registration System Backup..."

# 1. Database Backup with gzip compression
DB_FILE="$BACKUP_DIR/db_${DB_NAME}_${DATE}.sql.gz"
mysqldump -u "$DB_USER" -p"$DB_PASS" --single-transaction --quick "$DB_NAME" | gzip > "$DB_FILE"

if [ $? -eq 0 ]; then
    echo "[$DATE] Database backup successful: $DB_FILE"
else
    echo "[$DATE] ERROR: Database backup failed!" >&2
fi

# 2. Uploaded Photos & Attachments Backup
FILES_FILE="$BACKUP_DIR/files_uploads_${DATE}.tar.gz"
if [ -d "$WEB_UPLOADS" ]; then
    tar -czf "$FILES_FILE" -C "/var/www/html/stud_reg_system" uploads
    echo "[$DATE] Uploads backup successful: $FILES_FILE"
fi

# 3. Retention Policy: Automatically delete backups older than 30 days
find "$BACKUP_DIR" -type f -name "*.gz" -mtime +30 -exec rm -f {} \;
echo "[$DATE] Backup rotation completed. Cleaned archives older than 30 days."

echo "[$DATE] Backup routine complete."
```

### 3. Make the Script Executable
```bash
sudo chmod +x /usr/local/bin/backup_srs.sh
```

### 4. Test the Backup Script Manually
```bash
sudo /usr/local/bin/backup_srs.sh
ls -lh /var/backups/stud_reg_system/
```
*(You will see `.sql.gz` and `.tar.gz` files created successfully).*

### 5. Schedule Automated Daily Execution via Cron
Open root crontab:
```bash
sudo crontab -e
```
Add the following line at the very bottom to run the backup **every midnight at 02:00 AM**:
```cron
0 2 * * * /usr/local/bin/backup_srs.sh >> /var/log/srs_backup.log 2>&1
```
Save and exit. Check active cron schedule:
```bash
sudo crontab -l
```

---

## Step 11: Disaster Recovery & Restore Guide

If you ever need to restore your system from a backup:

### 1. Restore Database from a Backup File:
```bash
# Locate your backup file (e.g. db_stud_reg_system_2026-11-15_020000.sql.gz)
gunzip -c /var/backups/stud_reg_system/db_stud_reg_system_*.sql.gz | mysql -u srs_user -p'SrsSecurePass2026!' stud_reg_system
```

### 2. Restore Uploaded Student Photos:
```bash
# Extract uploads backup back into project directory
sudo tar -xzf /var/backups/stud_reg_system/files_uploads_*.tar.gz -C /var/www/html/stud_reg_system/
sudo chown -R www-data:www-data /var/www/html/stud_reg_system/uploads
sudo chmod -R 775 /var/www/html/stud_reg_system/uploads
```

---

## Maintenance & Troubleshooting Commands

### Test System Access from Any Device in Local Network
Open any browser on any PC, tablet, or phone on the `10.140.1.x` network:
- **Public Home:** `http://10.140.1.9/`
- **Portal Login:** `http://10.140.1.9/login.php`
- **Admin Dashboard:** `http://10.140.1.9/dashboard.php`

### Default Login Accounts:
| Role | Username | Password |
|---|---|---|
| **System Administrator** | `admin` | `admin123` |
| **Staff Member** | `staff` | `staff123` |

### Key Service Management Commands:
```bash
# Apache Status & Restart
sudo systemctl status apache2
sudo systemctl restart apache2

# MySQL Status & Restart
sudo systemctl status mysql
sudo systemctl restart mysql

# View Apache Error Logs in Real-Time
sudo tail -f /var/log/apache2/stud_reg_system_error.log

# View Backup Log History
sudo cat /var/log/srs_backup.log

# Check Server Resource Usage
htop
# or
free -h && df -h
```


---
---

# PART 2: Windows Server Deployment Guide
### Windows Server 2019 / 2022 / 2025
**Target Static IP:** `10.140.1.9`  
**Remote Management:** SSH (`ssh Administrator@10.140.1.9`) or RDP (`mstsc`)  
**GitHub Repository:** `https://github.com/hashimsiraj06-commits/stud_reg_system.git`

---

## 14. Windows Server Architecture & Prerequisites

If your organization utilizes a **Windows Server** environment (Windows Server 2019, 2022, or 2025 Datacenter/Standard), you can host the **Student Registration System** with high performance and reliability.

### Target Network Architecture:
- **Server OS:** Windows Server 2019, 2022, or 2025 (64-bit)
- **Static IPv4 Address:** `10.140.1.9`
- **Subnet Mask:** `255.255.255.0` (`/24`)
- **Default Gateway:** `10.140.1.1`
- **DNS Servers:** `10.140.1.1`, `8.8.8.8`, `1.1.1.1`
- **Web Address:** `http://10.140.1.9/`
- **Remote Administration:** SSH (`ssh Administrator@10.140.1.9`) and Remote Desktop (`10.140.1.9:3389`)

---

## Step W1: Assign Static IP (`10.140.1.9`) on Windows Server

You can configure the static IP address using PowerShell (recommended for automation) or through the Graphical User Interface.

### Method 1: Using PowerShell (Fastest)
Open **PowerShell as Administrator** on your Windows Server:

```powershell
# 1. Identify Network Adapter Name (e.g., "Ethernet" or "Ethernet0")
Get-NetAdapter | Select-Object Name, InterfaceDescription, Status, MacAddress

# Assume adapter name is "Ethernet" (adjust if different):
$AdapterName = "Ethernet"

# 2. Assign Static IP, Subnet Mask (/24), and Gateway
New-NetIPAddress -InterfaceAlias $AdapterName `
                 -IPAddress "10.140.1.9" `
                 -PrefixLength 24 `
                 -DefaultGateway "10.140.1.1"

# 3. Configure DNS Servers
Set-DnsClientServerAddress -InterfaceAlias $AdapterName `
                           -ServerAddresses ("10.140.1.1", "8.8.8.8", "1.1.1.1")

# 4. Verify Active IP Configuration
Get-NetIPConfiguration -InterfaceAlias $AdapterName
```

### Method 2: Using Windows GUI
1. Press `Win + R`, type `ncpa.cpl`, and press **Enter**.
2. Right-click your network adapter (e.g. `Ethernet`) and select **Properties**.
3. Double-click **Internet Protocol Version 4 (TCP/IPv4)**.
4. Select **Use the following IP address**:
   - **IP address:** `10.140.1.9`
   - **Subnet mask:** `255.255.255.0`
   - **Default gateway:** `10.140.1.1`
5. Select **Use the following DNS server addresses**:
   - **Preferred DNS server:** `10.140.1.1`
   - **Alternate DNS server:** `8.8.8.8`
6. Click **OK** twice to apply.

---

## Step W2: Enable OpenSSH Server on Windows Server for Remote Access

Windows Server 2019/2022/2025 includes a native, production-grade **OpenSSH Server** feature.

### 1. Install OpenSSH Server via PowerShell
Run in **PowerShell (Run as Administrator)** on the server:

```powershell
# Check if OpenSSH Server capability is available
Get-WindowsCapability -Online | Where-Object Name -like 'OpenSSH.Server*'

# Install OpenSSH Server
Add-WindowsCapability -Online -Name OpenSSH.Server~~~~0.0.1.0

# Start OpenSSH Service and set it to start automatically on boot
Start-Service sshd
Set-Service -Name sshd -StartupType 'Automatic'

# Verify service is running
Get-Service sshd
```

### 2. Configure Firewall for SSH (Port 22)
```powershell
# Confirm or create the inbound firewall rule for SSH
if (!(Get-NetFirewallRule -Name "OpenSSH-Server-In-TCP" -ErrorAction SilentlyContinue)) {
    New-NetFirewallRule -Name "OpenSSH-Server-In-TCP" `
                        -DisplayName "OpenSSH Server (sshd)" `
                        -Enabled True `
                        -Direction Inbound `
                        -Protocol TCP `
                        -Action Allow `
                        -LocalPort 22
}
```

### 3. Connect to Windows Server via SSH from Your Local Computer
From your laptop or workstation (PowerShell, CMD, or Linux/macOS terminal):

```powershell
ssh Administrator@10.140.1.9
```
*Enter the Windows Server administrator password when prompted. You will get a full Windows PowerShell remote session.*

### 4. (Optional) Enable Remote Desktop (RDP)
If you also want graphical remote desktop access:
```powershell
# Enable RDP in Windows Registry
Set-ItemProperty -Path 'HKLM:\System\CurrentControlSet\Control\Terminal Server' -Name "fDenyTSConnections" -Value 0

# Open RDP in Windows Firewall
Enable-NetFirewallRule -DisplayGroup "Remote Desktop"
```
Connect using Remote Desktop Connection (`mstsc.exe`): computer `10.140.1.9`.

---

## Step W3: Move/Clone Project to Windows Server (Git & SCP)

### Option A: Clone directly using Git on Windows Server
1. **Install Git on Windows Server:**
   ```powershell
   # Using Windows Package Manager (winget)
   winget install --id Git.Git -e --source winget --accept-source-agreements --accept-package-agreements
   ```
   *(Or download the installer from [git-scm.com/download/win](https://git-scm.com/download/win)).*
   Restart your PowerShell session after installation so `git` is in your `$env:PATH`.

2. **Clone the Project Repository:**
   ```powershell
   # For IIS deployment:
   git clone https://github.com/hashimsiraj06-commits/stud_reg_system.git C:\inetpub\wwwroot\stud_reg_system

   # Or for XAMPP deployment:
   git clone https://github.com/hashimsiraj06-commits/stud_reg_system.git C:\xampp\htdocs\stud_reg_system
   ```

### Option B: Transfer from Local Computer via SCP
From your local Windows computer where the project currently resides:
```powershell
scp -r C:\xampp\htdocs\stud_reg_system Administrator@10.140.1.9:C:\inetpub\wwwroot\
```

---

## Step W4: Install & Configure MySQL Server on Windows Server

### 1. Install MySQL Server Community
- Download the **MySQL Community Server MSI Installer** from [dev.mysql.com/downloads/installer/](https://dev.mysql.com/downloads/installer/).
- Or install via PowerShell:
  ```powershell
  winget install Oracle.MySQL -e --accept-source-agreements --accept-package-agreements
  ```
- During installation:
  - Choose **Server only** or **Developer Default**.
  - Set MySQL to run as a Windows Service named `MySQL80` (starts automatically).
  - Set a strong MySQL root password (e.g. `RootMasterPassword2026!`).

### 2. Configure Database and User for Student Registration System
Open PowerShell and connect to the MySQL CLI:
```powershell
& "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u root -p
```
Enter your MySQL root password, then execute:

```sql
-- 1. Create the database
CREATE DATABASE IF NOT EXISTS `stud_reg_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Create the dedicated web user
CREATE USER 'srs_user'@'localhost' IDENTIFIED BY 'SrsSecurePass2026!';

-- 3. Grant full privileges on the database
GRANT ALL PRIVILEGES ON `stud_reg_system`.* TO 'srs_user'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

### 3. Import SQL Schema and Seed Data
Run the import command in PowerShell:
```powershell
& "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u srs_user -pSrsSecurePass2026! stud_reg_system < "C:\inetpub\wwwroot\stud_reg_system\database\stud_reg_system.sql"
```
*(If deployed to `C:\xampp\htdocs\stud_reg_system\`, adjust the path accordingly).*

Verify the tables and seed data were imported:
```powershell
& "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u srs_user -pSrsSecurePass2026! -e "USE stud_reg_system; SHOW TABLES; SELECT id, full_name, role FROM users;"
```

---

## Step W5: Web Server Deployment Options on Windows Server

Choose one of the two supported production approaches on Windows Server:
- **Option A (Enterprise Native):** Microsoft IIS + PHP FastCGI (Recommended for corporate Windows Server environments)
- **Option B (All-in-One Service):** Production Apache + MySQL as Windows Services via XAMPP

---

### Option A: Native IIS + PHP (FastCGI) Deployment (Enterprise Standard)

#### 1. Install IIS and CGI Features on Windows Server
Run in PowerShell as Administrator:
```powershell
Install-WindowsFeature -Name Web-Server, Web-WebServer, Web-Common-Http, Web-Static-Content, Web-Default-Doc, Web-Http-Errors, Web-CGI, Web-Http-Redirect, Web-Filtering -IncludeManagementTools
```

#### 2. Install PHP 8.x (Non-Thread-Safe for IIS FastCGI)
1. Download **PHP 8.2 / 8.3 / 8.4 Non-Thread-Safe (NTS) x64 Zip** from [windows.php.net/download/](https://windows.php.net/download/).
2. Extract to `C:\PHP`:
   ```powershell
   New-Item -ItemType Directory -Path "C:\PHP" -Force
   Expand-Archive -Path "$env:USERPROFILE\Downloads\php-8.*-nts-Win32-vs16-x64.zip" -DestinationPath "C:\PHP"
   ```
3. Install the **Microsoft Visual C++ Redistributable 2015-2022 (x64)** (required by PHP for Windows):
   ```powershell
   winget install Microsoft.VCRedist.2015+.x64 -e
   ```

#### 3. Configure `php.ini`
In `C:\PHP`, copy `php.ini-production` to `php.ini`:
```powershell
Copy-Item "C:\PHP\php.ini-production" "C:\PHP\php.ini"
```
Edit `C:\PHP\php.ini` (e.g. `notepad C:\PHP\php.ini`) and ensure the following lines are uncommented (remove the leading `;`):
```ini
extension_dir = "ext"

; Enable required extensions
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql

; Upload and execution parameters
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 300
date.timezone = "UTC"

; CGI security for IIS
cgi.force_redirect = 0
cgi.fix_pathinfo = 0
fastcgi.impersonate = 1
```

#### 4. Add PHP to System Environment Path
```powershell
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\PHP", [EnvironmentVariableTarget]::Machine)
$env:Path += ";C:\PHP"
php -v
```

#### 5. Configure IIS FastCGI Handler Mapping for PHP
Run in PowerShell:
```powershell
# Register PHP FastCGI executable with IIS
& "$env:SystemRoot\system32\inetsrv\appcmd.exe" set config /section:system.webServer/fastCGI /+[fullPath='C:\PHP\php-cgi.exe']

# Add PHP Handler Mapping
& "$env:SystemRoot\system32\inetsrv\appcmd.exe" set config /section:system.webServer/handlers /+[name='PHP-FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='C:\PHP\php-cgi.exe',resourceType='Either']

# Set index.php as a default document
& "$env:SystemRoot\system32\inetsrv\appcmd.exe" set config /section:defaultDocument /+files.[value='index.php']
```

#### 6. Configure IIS Website for `10.140.1.9`
Point the Default Web Site to your project directory:
```powershell
Import-Module WebAdministration

# Update root directory to project folder
Set-ItemProperty 'IIS:\Sites\Default Web Site' -Name physicalPath -Value "C:\inetpub\wwwroot\stud_reg_system"

# Ensure binding is set to port 80 on all or 10.140.1.9
Get-WebBinding -Name "Default Web Site"

# Restart IIS
iisreset
```

---

### Option B: Production Apache & MySQL as Windows Services (via XAMPP/Apache Lounge)

If you prefer using Apache on Windows Server, configure Apache and MySQL as persistent Windows Services so they start automatically upon system boot without requiring user logon.

#### 1. Install XAMPP to `C:\xampp`
Download the Windows installer from [apachefriends.org](https://www.apachefriends.org/) and install it to `C:\xampp`.

#### 2. Install Apache & MySQL as Automatic Windows Background Services
Open PowerShell as Administrator:
```powershell
# Install Apache service
& "C:\xampp\apache\bin\httpd.exe" -k install

# Install MySQL service
& "C:\xampp\mysql\bin\mysqld.exe" --install MySQL

# Set both services to Automatic startup
Set-Service -Name "Apache2.4" -StartupType Automatic
Set-Service -Name "MySQL" -StartupType Automatic

# Start services
Start-Service -Name "Apache2.4"
Start-Service -Name "MySQL"
```

#### 3. Configure Apache VirtualHost for `10.140.1.9`
Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    ServerAdmin admin@studreg.edu
    DocumentRoot "C:/xampp/htdocs/stud_reg_system"
    ServerName 10.140.1.9

    <Directory "C:/xampp/htdocs/stud_reg_system">
        Options Indexes FollowSymLinks Includes ExecCGI
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/stud_reg_system-error.log"
    CustomLog "logs/stud_reg_system-access.log" combined
</VirtualHost>
```
Ensure Virtual Hosts are enabled in `C:\xampp\apache\conf\httpd.conf`:
```apache
Include conf/extra/httpd-vhosts.conf
```
Restart Apache:
```powershell
Restart-Service -Name "Apache2.4"
```

---

## Step W6: NTFS Permissions for Uploads & Web Security

Web applications running on Windows require appropriate NTFS permissions so that the web worker identity can write student photograph uploads to `uploads\students\`.

### For IIS Deployments (`C:\inetpub\wwwroot\stud_reg_system`)
Run in PowerShell (Admin):
```powershell
$ProjectPath = "C:\inetpub\wwwroot\stud_reg_system"
$UploadsPath = "C:\inetpub\wwwroot\stud_reg_system\uploads"

# Grant Read & Execute to IIS worker identity
icacls $ProjectPath /grant "IIS_IUSRS:(OI)(CI)RX" /T
icacls $ProjectPath /grant "IUSR:(OI)(CI)RX" /T

# Grant Modify & Write permissions to the uploads folder
icacls $UploadsPath /grant "IIS_IUSRS:(OI)(CI)M" /T
icacls $UploadsPath /grant "IUSR:(OI)(CI)M" /T
```

### For Apache/XAMPP Deployments (`C:\xampp\htdocs\stud_reg_system`)
```powershell
$UploadsPath = "C:\xampp\htdocs\stud_reg_system\uploads"
icacls $UploadsPath /grant "SYSTEM:(OI)(CI)F" /T
icacls $UploadsPath /grant "Administrators:(OI)(CI)F" /T
icacls $UploadsPath /grant "Users:(OI)(CI)M" /T
```

### Update `config/db.php` on Windows Server
Verify `config/db.php` points to the MySQL credentials created in Step W4:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'stud_reg_system');
define('DB_USER', 'srs_user');
define('DB_PASS', 'SrsSecurePass2026!');
define('DB_PORT', '3306');
```

---

## Step W7: Windows Defender Firewall Configuration

Ensure Windows Firewall permits incoming traffic on HTTP (80), HTTPS (443), and SSH (22).

Run in PowerShell as Administrator:
```powershell
# Open Port 80 (HTTP)
New-NetFirewallRule -DisplayName "SRS Web Server (HTTP Port 80)" `
                    -Direction Inbound `
                    -LocalPort 80 `
                    -Protocol TCP `
                    -Action Allow

# Open Port 443 (HTTPS)
New-NetFirewallRule -DisplayName "SRS Web Server (HTTPS Port 443)" `
                    -Direction Inbound `
                    -LocalPort 443 `
                    -Protocol TCP `
                    -Action Allow

# Open Port 22 (SSH)
New-NetFirewallRule -DisplayName "SRS Remote SSH (Port 22)" `
                    -Direction Inbound `
                    -LocalPort 22 `
                    -Protocol TCP `
                    -Action Allow

# Verify Rules
Get-NetFirewallRule -DisplayName "*SRS*" | Select-Object DisplayName, Enabled, Direction, Action
```

---

## Step W8: Automated Daily Backups with PowerShell & Task Scheduler

Configure automated daily backups of both the MySQL database and uploaded student photos using a native PowerShell script and **Windows Task Scheduler**.

### 1. Create the Backup Script Directory
```powershell
New-Item -ItemType Directory -Path "C:\scripts" -Force
New-Item -ItemType Directory -Path "C:\backups\stud_reg_system" -Force
```

### 2. Create the PowerShell Backup Script (`C:\scripts\backup_srs.ps1`)
Save the following script to `C:\scripts\backup_srs.ps1`:

```powershell
<#
================================================================================
Automated Backup Script for Student Registration System (Windows Server)
Backs up MySQL database dump (.sql) and compresses uploaded student photos (.zip)
================================================================================
#>

$BackupDir   = "C:\backups\stud_reg_system"
$TimeStamp   = Get-Date -Format "yyyy-MM-dd_HHmmss"
$LogFile     = "C:\backups\stud_reg_system\backup_log.txt"
$DbName      = "stud_reg_system"
$DbUser      = "srs_user"
$DbPass      = "SrsSecurePass2026!"

# Set path to mysqldump.exe (adjust path if using XAMPP: C:\xampp\mysql\bin\mysqldump.exe)
$MysqlDumpPath = "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe"
if (!(Test-Path $MysqlDumpPath)) {
    $MysqlDumpPath = "C:\xampp\mysql\bin\mysqldump.exe"
}

# Set path to web project uploads folder
$UploadsDir = "C:\inetpub\wwwroot\stud_reg_system\uploads"
if (!(Test-Path $UploadsDir)) {
    $UploadsDir = "C:\xampp\htdocs\stud_reg_system\uploads"
}

Function Write-Log {
    Param([string]$Message)
    $Entry = "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] $Message"
    Add-Content -Path $LogFile -Value $Entry
    Write-Output $Entry
}

Write-Log "Starting automated backup for $DbName..."

# 1. Database Backup via mysqldump
$SqlOutputFile = "$BackupDir\db_${DbName}_${TimeStamp}.sql"
$ZipDbFile     = "$BackupDir\db_${DbName}_${TimeStamp}.zip"

& $MysqlDumpPath --user=$DbUser --password=$DbPass --single-transaction --quick $DbName > $SqlOutputFile

if ($LASTEXITCODE -eq 0 -and (Test-Path $SqlOutputFile)) {
    # Compress SQL dump to save disk space
    Compress-Archive -Path $SqlOutputFile -DestinationPath $ZipDbFile -CompressionLevel Optimal
    Remove-Item $SqlOutputFile -Force
    Write-Log "Database backup completed successfully: $ZipDbFile"
} else {
    Write-Log "ERROR: Database dump failed with exit code $LASTEXITCODE"
}

# 2. Uploaded Photos & Files Backup
if (Test-Path $UploadsDir) {
    $ZipFilesFile = "$BackupDir\files_uploads_${TimeStamp}.zip"
    Compress-Archive -Path "$UploadsDir\*" -DestinationPath $ZipFilesFile -CompressionLevel Optimal
    Write-Log "Uploads folder backup completed: $ZipFilesFile"
}

# 3. 30-Day Retention Policy: Delete backups older than 30 days
$CutoffDate = (Get-Date).AddDays(-30)
Get-ChildItem -Path $BackupDir -Filter "*.zip" | Where-Object { $_.LastWriteTime -lt $CutoffDate } | ForEach-Object {
    Write-Log "Removing expired backup archive: $($_.FullName)"
    Remove-Item $_.FullName -Force
}

Write-Log "Backup process finished."
```

### 3. Test Run the Backup Script
Execute in PowerShell:
```powershell
& "C:\scripts\backup_srs.ps1"

# Check backup contents
Get-ChildItem "C:\backups\stud_reg_system"
Get-Content "C:\backups\stud_reg_system\backup_log.txt" -Tail 10
```

### 4. Create Scheduled Task in Windows Task Scheduler
Schedule the script to run **daily at 2:00 AM** under the `SYSTEM` account:

```powershell
$Action = New-ScheduledTaskAction -Execute "PowerShell.exe" `
    -Argument "-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File C:\scripts\backup_srs.ps1"

$Trigger = New-ScheduledTaskTrigger -Daily -At "02:00AM"

$Principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

Register-ScheduledTask -TaskName "SRS_Daily_Backup" `
                       -Action $Action `
                       -Trigger $Trigger `
                       -Principal $Principal `
                       -Description "Daily automated backup for Student Registration System database and uploads."

# Verify scheduled task is registered and ready
Get-ScheduledTask -TaskName "SRS_Daily_Backup"
```

---

## Step W9: Windows Server Disaster Recovery & Restore Guide

### 1. Restore Database from Compressed Backup:
Run in PowerShell:
```powershell
# 1. Unzip the target backup archive
Expand-Archive -Path "C:\backups\stud_reg_system\db_stud_reg_system_2026-11-15_020000.zip" -DestinationPath "C:\backups\stud_reg_system\temp_restore" -Force

# 2. Import into MySQL
& "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u srs_user -pSrsSecurePass2026! stud_reg_system < "C:\backups\stud_reg_system\temp_restore\db_stud_reg_system_2026-11-15_020000.sql"

# 3. Clean up extracted SQL file
Remove-Item "C:\backups\stud_reg_system\temp_restore" -Recurse -Force
Write-Host "Database restored successfully."
```

### 2. Restore Uploaded Student Photos:
```powershell
# Extract photos archive directly back to uploads folder
Expand-Archive -Path "C:\backups\stud_reg_system\files_uploads_2026-11-15_020000.zip" `
               -DestinationPath "C:\inetpub\wwwroot\stud_reg_system\uploads" `
               -Force
Write-Host "Uploads restored successfully."
```

---

## Windows Server Maintenance & Health Check Commands

Run these routine health check commands in PowerShell on your Windows Server:

```powershell
# 1. Check Web Server and MySQL Services
Get-Service -Name W3SVC, sshd, MySQL80  # For IIS + MySQL
# or
Get-Service -Name Apache2.4, MySQL, sshd # For XAMPP Service

# 2. Check Static IP and Network Status
Get-NetIPAddress -InterfaceAlias "Ethernet" -AddressFamily IPv4

# 3. View Live Web Server Error Logs (IIS)
Get-Content -Path "C:\inetpub\logs\LogFiles\W3SVC1\*.log" -Tail 20 -Wait

# 4. View Scheduled Backup Log
Get-Content -Path "C:\backups\stud_reg_system\backup_log.txt" -Tail 20

# 5. Check Disk Free Space
Get-PSDrive C | Select-Object Name, @{Name="FreeGB";Expression={[math]::Round($_.Free/1GB,2)}}, @{Name="TotalGB";Expression={[math]::Round(($_.Used+$_.Free)/1GB,2)}}

# 6. Test Web Connectivity from Server Console
Invoke-WebRequest -Uri "http://10.140.1.9" -UseBasicParsing
```
