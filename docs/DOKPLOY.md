# VDOT Deployment Guide for Dokploy

## Prerequisites
1. A **Dokploy** instance running.
2. This repository (`vdampro`) connected to your Dokploy.

## Step 1: Create the Database
In Dokploy, create a new **Database** service:
- **Type**: MariaDB (recommended) or MySQL.
- **Name**: `vdot-db` (or similar).
- **Note down these credentials** (you will need them for the App):
  - Host (usually the service name or internal IP)
  - Database Name
  - Username
  - Password
  - Port (usually 3306)

## Step 2: Deploy the Application
In Dokploy, create a new **Application**:
- **Source**: Git Repository
- **Repository**: `JRizzle1985/vdampro`
- **Branch**: `master`
- **Build Type**: `Dockerfile` (The repo includes a production-ready Dockerfile).

## Step 3: Environment Variables
Go to the **Environment** tab of your new Application and add these variables. 
**Crucial**: You must generate a unique `APP_KEY`. You can generate one locally by running `openssl rand -base64 32` or `php artisan key:generate --show`.

```env
# App Settings
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_URL=https://your-vdot-domain.com
APP_TIMEZONE=UTC
APP_LOCALE=en-US

# Database (Use values from Step 1)
DB_CONNECTION=mysql
DB_HOST=vdot-db
DB_PORT=3306
DB_DATABASE=vdot
DB_USERNAME=root
DB_PASSWORD=YOUR_DB_PASSWORD

# Email (Required for invites/resets)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_user
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDR=noreply@your-domain.com
MAIL_FROM_NAME=VDOT

# Image Library
IMAGE_LIB=gd
```

## Step 4: Deploy
Click **Deploy**. 
- The build process will use the `Dockerfile` in the root.
- It installs PHP 8.3, Apache, and Composer dependencies.
- It copies the pre-built VDOT assets (CSS/JS) from the repo.
- On startup, it runs migrations automatically.

## Troubleshooting
- **500 Error**: Check `APP_KEY` is set and 32 characters (base64). Check DB credentials.
- **Assets missing**: Ensure you didn't exclude `public/css` or `public/js` in `.gitignore` (VDOT repo includes them by default).
- **Permissions**: The Dockerfile handles permissions for `/var/www/html/storage`.
