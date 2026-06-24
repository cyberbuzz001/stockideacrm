#!/bin/bash

# Shreesvarn CRM - Production Deployment Script (v1.1)
# Optimized for: AlmaLinux 9.7 + CyberPanel (OpenLiteSpeed)

echo "🚀 Starting Shreesvarn CRM Deployment (AlmaLinux Mode)..."

# 1. Update & Install System Dependencies
dnf update -y
dnf install -y git zip unzip curl wget redis

# 2. Configure PHP 8.2 (CyberPanel/LSWS Path)
# Ensure required LSphp extensions are present
dnf install -y lsphp82-mysqlnd lsphp82-gd lsphp82-mbstring lsphp82-opcache lsphp82-xml lsphp82-process lsphp82-pdo

PHP_INI="/usr/local/lsws/lsphp82/etc/php/8.2/lsqapi/php.ini"
if [ -f "$PHP_INI" ]; then
    echo "⚙️ Configuring PHP 8.2 memory limits..."
    sed -i "s/memory_limit = .*/memory_limit = 512M/" $PHP_INI
    sed -i "s/upload_max_filesize = .*/upload_max_filesize = 100M/" $PHP_INI
    sed -i "s/post_max_size = .*/post_max_size = 100M/" $PHP_INI
    systemctl restart lscpd
fi

# 3. Setup Project Directory
# Detecting current directory
DEPLOY_DIR=$(pwd)
ROOT_DIR=$(dirname "$DEPLOY_DIR")

echo "📂 Navigating to CRM core: $ROOT_DIR/crm-core"
cd "$ROOT_DIR/crm-core"

# Install PHP Dependencies (using system or local composer)
if [ -f "composer.json" ]; then
    php artisan --version || /usr/local/lsws/lsphp82/bin/php artisan --version
    /usr/local/lsws/lsphp82/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader || composer install --no-dev --optimize-autoloader
else
    echo "❌ Error: composer.json not found in $(pwd)"
fi

# 4. Permissions & Security
echo "🔒 Setting file permissions for OpenLiteSpeed..."
cd "$ROOT_DIR"
# CyberPanel standard owner is usually 'lshttpd' or the website user
chown -R lshttpd:lshttpd .
chmod -R 775 crm-core/storage crm-core/bootstrap/cache

# Ensure KYC/Learning directories exist
mkdir -p crm-core/public/uploads/kyc crm-core/public/uploads/learning
chmod -R 775 crm-core/public/uploads

# 5. Real-Time Chat Server (Node.js/PM2)
echo "💬 Setting up Chat Server..."
if ! command -v node &> /dev/null; then
    curl -fsSL https://rpm.nodesource.com/setup_18.x | bash -
    dnf install -y nodejs
fi
npm install -g pm2

cd "$ROOT_DIR/chat-server"
npm install
pm2 start ecosystem.config.js
pm2 save

echo "✅ Deployment Complete! Please import mysql_migration.sql via CyberPanel Database manager."
