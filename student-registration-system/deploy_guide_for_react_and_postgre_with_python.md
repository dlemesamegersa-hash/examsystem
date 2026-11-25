# Student Registration System — Local Network Deployment Guide

**Project:** `student-reg-system`  
**Repository:** https://github.com/hashimsiraj06-commits/stud_reg_system.git  
**Database:** PostgreSQL  
**Database name:** `stud_reg`  
**Backend language:** Python  
**Frontend:** React  
**Server IP:** `10.140.3.3`  
**Operating system:** Ubuntu Server LTS (24.04 recommended)  
**Application URL after deployment:** `http://10.140.3.3`

This guide deploys the React frontend, Python backend, and PostgreSQL database on one Ubuntu server for use on a local network.

## 1. Before installation

### 1.1 Requirements

- Ubuntu Server 22.04 LTS or 24.04 LTS installed on the target machine.
- A user account with `sudo` privileges.
- The server must have the static IP address `10.140.3.3`.
- Network access to the GitHub repository, or a local copy of the project.
- At least 20 GB free disk space recommended.
- Python 3.10 or newer.
- Node.js 18 or newer for building the React application.
- PostgreSQL 14 or newer.

### 1.2 Network information

Use these values during Ubuntu installation and network configuration:

| Item | Value |
|---|---|
| Server IP address | `10.140.3.3` |
| Subnet mask | Use your organization's subnet mask, commonly `255.255.255.0` |
| Gateway | Use your organization's gateway, for example `10.140.3.1` |
| DNS servers | Use your organization's DNS servers, for example `10.140.3.1` and `8.8.8.8` |
| Application port | `80` through Nginx |
| Backend port | `8000` |
| PostgreSQL port | `5432` |

> Replace the gateway and DNS values with the actual values used in your local network.

## 2. Install Ubuntu Server

### 2.1 Create installation media

1. Download an Ubuntu Server LTS ISO from the official Ubuntu releases page.
2. Create a bootable USB drive using Rufus, balenaEtcher, or Ubuntu Startup Disk Creator.
3. Insert the USB drive into the server.
4. Restart the server and open its boot menu.
5. Select the USB drive as the boot device.

Ubuntu's installer supports installing from a USB drive; during installation, choose the server install image and follow the on-screen instructions. [web:5][web:10]

### 2.2 Ubuntu installation choices

During installation:

1. Select your language and keyboard layout.
2. Choose **Install Ubuntu Server**.
3. Configure the network interface manually.
4. Set the IPv4 address to `10.140.3.3`.
5. Enter the correct subnet mask, gateway, and DNS servers for your network.
6. Leave proxy settings empty unless your organization requires a proxy.
7. Use the entire disk for installation unless you have a specific partitioning requirement.
8. Create an administrator user, for example `deploy`.
9. Set a strong password.
10. Enable **Install OpenSSH server** so you can administer the server remotely.
11. Complete the installation and reboot.
12. Remove the installation media after reboot.

Ubuntu's installation flow includes selecting language, keyboard, network settings, storage, user credentials, and optionally enabling SSH. [web:7][web:12]

### 2.3 Log in

At the server console, log in with the user created during installation:

```bash
deploy@10.140.3.3
```

If installing from another computer, connect using SSH:

```bash
ssh deploy@10.140.3.3
```

## 3. Configure static IP address

Ubuntu Server commonly uses Netplan for network configuration.

### 3.1 Identify the network interface

```bash
ip a
```

Find the active interface name. It may be `enp0s3`, `ens33`, `ens160`, `eth0`, or similar.

### 3.2 Edit Netplan configuration

```bash
sudo nano /etc/netplan/00-installer-config.yaml
```

Use a configuration similar to the following. Replace `enp0s3` with your actual interface name and replace the gateway with your real network gateway.

```yaml
network:
  version: 2
  ethernets:
    enp0s3:
      dhcp4: false
      addresses:
        - 10.140.3.3/24
      routes:
        - to: default
          via: 10.140.3.1
      nameservers:
        addresses:
          - 10.140.3.1
          - 8.8.8.8
```

Save with `CTRL + O`, press `ENTER`, then exit with `CTRL + X`.

### 3.3 Apply the configuration

```bash
sudo netplan try
```

If the configuration is correct, press `ENTER` to keep it.

Then verify:

```bash
ip a
ip route
ping -c 4 10.140.3.1
```

## 4. Update and secure the server

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y curl git build-essential ufw
```

### 4.1 Configure firewall

Allow SSH, HTTP, and HTTPS:

```bash
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

Do **not** expose PostgreSQL port `5432` to the local network unless another server must connect directly to the database. For a single-server deployment, keep PostgreSQL accessible only from `localhost`.

## 5. Install required software

### 5.1 Install Python and PostgreSQL

```bash
sudo apt install -y python3 python3-pip python3-venv postgresql postgresql-contrib libpq-dev
```

Ubuntu supports installing PostgreSQL through `apt`; the official PostgreSQL Ubuntu repository also supports Ubuntu 24.04 LTS. [web:1][web:8]

Verify versions:

```bash
python3 --version
pip3 --version
psql --version
```

### 5.2 Install Node.js and npm

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

### 5.3 Install Nginx

```bash
sudo apt update
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
sudo systemctl status nginx
```

Nginx is used as a reverse proxy in front of the React frontend and Python backend. [web:2][web:3][web:4]

## 6. Configure PostgreSQL

### 6.1 Create database and database user

```bash
sudo -u postgres psql
```

Inside the PostgreSQL prompt, run:

```sql
CREATE DATABASE stud_reg;
CREATE USER stud_reg_user WITH PASSWORD 'CHANGE_THIS_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON DATABASE stud_reg TO stud_reg_user;
\c stud_reg
GRANT ALL ON SCHEMA public TO stud_reg_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO stud_reg_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO stud_reg_user;
\q
```

> Replace `CHANGE_THIS_STRONG_PASSWORD` with a long, unique password. Store it securely in your password manager or server secrets file.

### 6.2 Verify database connection

```bash
sudo -u postgres psql -d stud_reg -c "\l"
```

You should see `stud_reg` in the database list.

### 6.3 PostgreSQL security configuration

By default, PostgreSQL listens only on localhost, which is appropriate for this deployment.

Check the setting:

```bash
sudo grep -n "^listen_addresses" /etc/postgresql/*/main/postgresql.conf
```

Expected value:

```text
listen_addresses = 'localhost'
```

If needed, edit it:

```bash
sudo nano /etc/postgresql/*/main/postgresql.conf
```

Set:

```text
listen_addresses = 'localhost'
```

Restart PostgreSQL:

```bash
sudo systemctl restart postgresql
sudo systemctl enable postgresql
```

## 7. Deploy the project

### 7.1 Create application directories

```bash
sudo mkdir -p /opt/student-reg-system
sudo chown -R $USER:$USER /opt/student-reg-system
cd /opt/student-reg-system
```

### 7.2 Clone the repository

```bash
git clone https://github.com/hashimsiraj06-commits/stud_reg_system.git
cd stud_reg_system
```

If GitHub requires authentication for a private repository, use a personal access token or deploy key:

```bash
git clone https://<GITHUB_USERNAME>@github.com/hashimsiraj06-commits/stud_reg_system.git
```

### 7.3 Inspect the project structure

```bash
ls -la
```

Typical structure:

```text
stud_reg_system/
├── backend/          # Python backend
├── frontend/         # React frontend
├── README.md
└── requirements.txt
```

Your actual folder names may differ. Use the names present in the repository.

## 8. Configure the Python backend

### 8.1 Create a virtual environment

From the repository root:

```bash
cd backend
python3 -m venv venv
source venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
```

If your backend requirements file is in the repository root, use:

```bash
cd /opt/student-reg-system/stud_reg_system
python3 -m venv venv
source venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
```

### 8.2 Create environment variables

Create a protected environment file:

```bash
sudo nano /opt/student-reg-system/.env
```

Example for a Django backend:

```env
DEBUG=False
SECRET_KEY=generate-a-long-random-secret-key
ALLOWED_HOSTS=10.140.3.3,localhost,127.0.0.1
DATABASE_NAME=stud_reg
DATABASE_USER=stud_reg_user
DATABASE_PASSWORD=CHANGE_THIS_STRONG_PASSWORD
DATABASE_HOST=127.0.0.1
DATABASE_PORT=5432
CORS_ALLOWED_ORIGINS=http://10.140.3.3
```

Example for a FastAPI backend:

```env
DATABASE_URL=postgresql://stud_reg_user:CHANGE_THIS_STRONG_PASSWORD@127.0.0.1:5432/stud_reg
CORS_ORIGINS=http://10.140.3.3
```

Protect the file:

```bash
sudo chmod 600 /opt/student-reg-system/.env
```

### 8.3 Run database migrations

For Django:

```bash
python manage.py migrate
python manage.py createsuperuser
python manage.py collectstatic --noinput
```

For FastAPI with Alembic:

```bash
alembic upgrade head
```

For Flask with Flask-Migrate:

```bash
flask db upgrade
```

Use the migration command that matches your backend framework.

### 8.4 Test the backend manually

Django example:

```bash
python manage.py runserver 0.0.0.0:8000
```

FastAPI example:

```bash
uvicorn main:app --host 0.0.0.0 --port 8000
```

Flask example:

```bash
flask run --host=0.0.0.0 --port=8000
```

Open this address from another computer on the same network:

```text
http://10.140.3.3:8000
```

After confirming that the backend works, stop it with `CTRL + C`.

## 9. Build and deploy the React frontend

### 9.1 Install frontend dependencies

```bash
cd /opt/student-reg-system/stud_reg_system/frontend
npm install
```

### 9.2 Configure API address

Create a production environment file if your React project uses one:

```bash
nano .env.production
```

For an application using Vite:

```env
VITE_API_BASE_URL=http://10.140.3.3/api
```

For an application using Create React App:

```env
REACT_APP_API_BASE_URL=http://10.140.3.3/api
```

Use the environment-variable prefix and API path required by your project.

### 9.3 Build the frontend

```bash
npm run build
```

The production files are usually created in:

```text
frontend/build
```

for Create React App, or:

```text
frontend/dist
```

for Vite.

### 9.4 Copy build output to Nginx

For Create React App:

```bash
sudo mkdir -p /var/www/student-reg-system
sudo cp -r build/* /var/www/student-reg-system/
```

For Vite:

```bash
sudo mkdir -p /var/www/student-reg-system
sudo cp -r dist/* /var/www/student-reg-system/
```

Set ownership:

```bash
sudo chown -R www-data:www-data /var/www/student-reg-system
```

## 10. Configure Nginx

### 10.1 Create Nginx site configuration

```bash
sudo nano /etc/nginx/sites-available/student-reg-system
```

Paste the following configuration:

```nginx
server {
    listen 80;
    server_name 10.140.3.3;

    client_max_body_size 20M;

    # React frontend
    root /var/www/student-reg-system;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Python backend API
    location /api/ {
        proxy_pass http://127.0.0.1:8000/;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 120s;
    }

    # Static and media files, if used by the backend
    location /static/ {
        alias /opt/student-reg-system/stud_reg_system/backend/static/;
    }

    location /media/ {
        alias /opt/student-reg-system/stud_reg_system/backend/media/;
    }
}
```

If your backend does not use `/api/`, remove or adjust the `location /api/` block to match the backend routes.

### 10.2 Enable the site

```bash
sudo ln -s /etc/nginx/sites-available/student-reg-system /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

### 10.3 Test from the local network

From another computer connected to the same network, open:

```text
http://10.140.3.3
```

You should see the React application. API requests should be sent to:

```text
http://10.140.3.3/api
```

## 11. Run the backend as a service

Use Gunicorn for Django or Flask, or Uvicorn for FastAPI.

### 11.1 Django or Flask with Gunicorn

Install Gunicorn:

```bash
source /opt/student-reg-system/stud_reg_system/venv/bin/activate
pip install gunicorn
```

Create the service:

```bash
sudo nano /etc/systemd/system/student-reg-system.service
```

Paste:

```ini
[Unit]
Description=Student Registration System Backend
After=network.target postgresql.service

[Service]
Type=simple
User=deploy
Group=www-data
WorkingDirectory=/opt/student-reg-system/stud_reg_system
EnvironmentFile=/opt/student-reg-system/.env
ExecStart=/opt/student-reg-system/stud_reg_system/venv/bin/gunicorn --workers 3 --bind 127.0.0.1:8000 wsgi:application
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

For FastAPI, replace the `ExecStart` line with:

```ini
ExecStart=/opt/student-reg-system/stud_reg_system/venv/bin/uvicorn main:app --host 127.0.0.1 --port 8000 --workers 2
```

Replace `wsgi:application` or `main:app` with the actual module and application variable used by your project.

### 11.2 Start and enable the service

```bash
sudo systemctl daemon-reload
sudo systemctl enable student-reg-system
sudo systemctl start student-reg-system
sudo systemctl status student-reg-system
```

Check logs:

```bash
sudo journalctl -u student-reg-system -f
```

## 12. Final checks

Run these commands on the server:

```bash
sudo systemctl status nginx
sudo systemctl status postgresql
sudo systemctl status student-reg-system
sudo ufw status
curl -I http://127.0.0.1
curl -I http://10.140.3.3
```

From another computer on the same local network:

```text
http://10.140.3.3
```

Test login, student registration, data saving, and any file-upload features.

## 13. Database backup configuration

### 13.1 Create a backup directory

```bash
sudo mkdir -p /var/backups/stud_reg
sudo chown postgres:postgres /var/backups/stud_reg
sudo chmod 700 /var/backups/stud_reg
```

### 13.2 Create a daily backup script

```bash
sudo nano /usr/local/bin/backup-stud-reg.sh
```

Paste:

```bash
#!/bin/bash

set -euo pipefail

BACKUP_DIR="/var/backups/stud_reg"
DATABASE_NAME="stud_reg"
DATE="$(date +%F_%H-%M-%S)"
BACKUP_FILE="${BACKUP_DIR}/${DATABASE_NAME}_${DATE}.sql"

mkdir -p "${BACKUP_DIR}"

sudo -u postgres pg_dump "${DATABASE_NAME}" > "${BACKUP_FILE}"

gzip "${BACKUP_FILE}"

# Keep the latest 30 backups
find "${BACKUP_DIR}" -type f -name "${DATABASE_NAME}_*.sql.gz" -mtime +30 -delete

echo "Backup completed: ${BACKUP_FILE}.gz"
```

Make it executable:

```bash
sudo chmod 700 /usr/local/bin/backup-stud-reg.sh
```

### 13.3 Test the backup script

```bash
sudo /usr/local/bin/backup-stud-reg.sh
ls -lh /var/backups/stud_reg
```

### 13.4 Schedule daily backups

Open root's crontab:

```bash
sudo crontab -e
```

Add this line to run the backup every day at 2:00 AM:

```cron
0 2 * * * /usr/local/bin/backup-stud-reg.sh >> /var/log/stud_reg_backup.log 2>&1
```

Save and exit.

### 13.5 Restore a backup

To restore from a backup:

```bash
sudo -u postgres psql -d stud_reg -c "DROP SCHEMA public CASCADE; CREATE SCHEMA public;"
gunzip -c /var/backups/stud_reg/stud_reg_YYYY-MM-DD_HH-MM-SS.sql.gz | sudo -u postgres psql -d stud_reg
```

Replace the backup filename with the actual file you want to restore.

### 13.6 Off-server backup

A backup stored only on the same server is not sufficient. Copy backups to another computer, external drive, or network storage.

Example using `scp` from another computer:

```bash
scp deploy@10.140.3.3:/var/backups/stud_reg/*.sql.gz /path/to/local/backup/folder/
```

Example using `rsync` from the server to another local machine:

```bash
rsync -avz /var/backups/stud_reg/ backupuser@BACKUP_SERVER_IP:/path/to/backup/folder/
```

## 14. Application updates

To deploy a new version:

```bash
cd /opt/student-reg-system/stud_reg_system
sudo systemctl stop student-reg-system
git pull origin main
source venv/bin/activate
pip install -r requirements.txt
python manage.py migrate
python manage.py collectstatic --noinput
cd frontend
npm install
npm run build
sudo cp -r build/* /var/www/student-reg-system/
sudo chown -R www-data:www-data /var/www/student-reg-system
sudo systemctl start student-reg-system
sudo systemctl restart nginx
```

For a Vite project, replace `build` with `dist`.

## 15. Troubleshooting

| Problem | Command or action |
|---|---|
| Cannot access `http://10.140.3.3` | Check `sudo systemctl status nginx`, `sudo ufw status`, and the server IP with `ip a` |
| Backend is down | Check `sudo systemctl status student-reg-system` and `sudo journalctl -u student-reg-system -f` |
| Database connection error | Verify database name, user, password, host `127.0.0.1`, and port `5432` |
| React page loads but API fails | Confirm the frontend API URL and Nginx `/api/` proxy configuration |
| Blank React page | Confirm that `index.html`, JavaScript, and CSS files were copied from `build/` or `dist/` |
| Permission denied | Check ownership of `/var/www/student-reg-system` and backend folders |
| Backup fails | Check `/var/log/stud_reg_backup.log` and PostgreSQL service status |

## 16. Security recommendations

- Use strong, unique passwords for the Ubuntu user, PostgreSQL user, and application administrator account.
- Keep `.env` files outside version control and restrict permissions with `chmod 600`.
- Do not enable `DEBUG=True` in production.
- Keep PostgreSQL listening only on `localhost`.
- Restrict SSH access to trusted administrative computers if possible.
- Apply Ubuntu security updates regularly.
- Store database backups on a separate device or server.
- Test restoration of a backup before relying on it.

## 17. Deployment summary

After completing this guide, the system will be available at:

```text
http://10.140.3.3
```

The main components will be:

| Component | Location or port |
|---|---|
| React frontend | `/var/www/student-reg-system` |
| Python backend | `/opt/student-reg-system/stud_reg_system` |
| Backend service | `student-reg-system.service` |
| Backend port | `127.0.0.1:8000` |
| PostgreSQL database | `stud_reg` |
| Database port | `127.0.0.1:5432` |
| Nginx public port | `80` |
| Backup location | `/var/backups/stud_reg` |
| Daily backup time | `02:00` |
