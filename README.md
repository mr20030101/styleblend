# StyleBlend POS

Professional Point of Sale and Inventory Management System for retail clothing stores.

## Overview

StyleBlend is a comprehensive web-based POS system designed specifically for clothing retailers. It provides real-time inventory management, sales tracking, customer management, and detailed reporting capabilities.

## System Requirements

- **PHP**: 8.1 or higher
- **Database**: MySQL 8.0 or higher
- **Web Server**: Apache or Nginx
- **Node.js**: 18+ (for asset compilation)
- **Memory**: Minimum 512MB RAM
- **Storage**: Minimum 1GB available space

## Quick Start

For detailed production deployment instructions, see [DEPLOYMENT.md](DEPLOYMENT.md).

### Development Setup

```bash
# 1. Clone and install dependencies
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Setup database
php artisan migrate --seed

# 4. Build assets and start
npm run build
php artisan serve
```

## Features

### 🛒 Point of Sale
- Fast barcode scanning and product search
- Real-time inventory updates
- Multiple payment methods
- Receipt printing (thermal printer optimized)
- Transaction history and voiding

### 📦 Inventory Management
- Product variants (size, color, style)
- Stock level tracking and alerts
- Purchase order management
- Supplier management
- Inventory adjustments and transfers

### 👥 Customer Management
- Customer profiles and purchase history
- Loyalty program integration
- Raffle entry system
- Customer analytics

### 📊 Reporting & Analytics
- Sales reports (daily, weekly, monthly, custom)
- Inventory reports and stock analysis
- Customer analytics
- Export capabilities (CSV, PDF)
- Real-time dashboard

### 🔐 User Management
- Role-based access control (Admin, Cashier)
- User activity logging
- Secure authentication

## Default Login

After installation, you can use any of these accounts:

| Name | Email | Password | Role |
|------|-------|----------|------|
| Admin | admin@styleblend.com | password | Admin |
| Shean Louise Margallo | sheanlouisemargallo@gmail.com | password | Admin |
| Jayannet | jayannet4@gmail.com | password | Admin |
| Cashier | cashier@styleblend.com | password | Cashier |

**⚠️ Change all default passwords immediately after first login!**

## Keyboard Shortcuts

| Key | Action |
|-----|--------|
| `F2` | Focus product search |
| `F12` | Open checkout |
| `Esc` | Close modals |
| `Ctrl+N` | New transaction |

## Support

For technical support or deployment assistance, refer to the [DEPLOYMENT.md](DEPLOYMENT.md) guide or check the application logs in `storage/logs/`.

## License

This software is proprietary. All rights reserved.

---

**StyleBlend POS** - Streamlining retail operations with modern technology.
