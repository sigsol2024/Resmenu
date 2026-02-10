# Restaurant Menu Platform

A PHP-based multi-restaurant menu platform with admin dashboards, database backend, and full customization capabilities.

## Features

- **Super Admin Dashboard**: Manage all restaurants, view statistics
- **Restaurant Manager Dashboard**: Manage categories, menu items, and customize menu appearance
- **Public Menu Page**: Dynamic menu display with customization support
- **Database-Driven**: MySQL database for all content
- **Customization**: Change colors, fonts, sizes for menu titles, prices, descriptions
- **Multi-Restaurant Support**: One platform, multiple restaurants

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Quick Start

1. **Clone or download the project** to your web server directory

2. **Create the database**:
   ```bash
   mysql -u root -p < database.sql
   ```
   Or using phpMyAdmin:
   - Open phpMyAdmin
   - Click "Import"
   - Select `database.sql`
   - Click "Go"

3. **Configure database connection**:
   Edit `config/config.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'restaurant_menu_platform');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Configure site URL**:
   Edit `config/config.php` and update:
   ```php
   define('SITE_URL', 'http://your-domain.com');
   ```

5. **Set permissions**:
   ```bash
   # Linux/Mac
   chmod -R 755 uploads/
   
   # Windows (PowerShell)
   icacls uploads /grant Users:F /T
   ```

6. **Access the platform**:
   - Admin Login: `http://your-domain.com/admin/login.php`
   - Username: `admin`
   - Password: `admin123`
   - **IMPORTANT**: Change this password immediately!

### Troubleshooting

**Database Connection Error**
- Check database credentials in `config/config.php`
- Ensure MySQL is running
- Verify database exists

**Image Upload Not Working**
- Check `uploads/` folder permissions (must be writable)
- Verify `MAX_FILE_SIZE` in `config/config.php`
- Check PHP `upload_max_filesize` in php.ini

**Page Not Found**
- Ensure `.htaccess` is present (for URL rewriting)
- Check web server configuration
- Verify file paths are correct

## Usage

### Super Admin

1. Login at `/admin/login.php`
2. Create restaurants in "Restaurant Management"
3. View all restaurants and statistics in dashboard

### Restaurant Manager

1. Login at `/admin/login.php` (managers use same login page)
2. Access manager dashboard
3. Manage categories, menu items, and customize menu appearance

### Public Menu

- Access menu at `/index.php?restaurant=restaurant-slug`
- Or set up URL rewriting to use `/restaurant-slug`

## File Structure

```
restaurant-menu-platform/
├── admin/              # Super admin pages
│   ├── login.php
│   ├── dashboard.php
│   └── restaurants.php
├── manager/            # Restaurant manager pages
│   ├── dashboard.php
│   ├── categories.php
│   ├── menu-items.php
│   └── customization.php
├── api/                # API endpoints
├── assets/             # CSS, images, etc.
├── config/             # Configuration files
│   ├── config.php
│   └── database.php
├── includes/           # Shared PHP files
│   ├── auth.php
│   └── functions.php
├── uploads/            # Uploaded files
│   ├── categories/
│   ├── menu-items/
│   └── logos/
├── index.php           # Public menu page
├── database.sql        # Database schema
└── README.md
```

## Database Schema

- **users**: Admin and manager accounts
- **restaurants**: Restaurant information
- **categories**: Menu categories
- **menu_items**: Individual menu items
- **customization_settings**: Menu appearance customization

## Customization

Managers can customize:
- Menu title color, size, font
- Price color, size, font
- Description color, size, font
- Category title color, size, font
- Background colors
- Primary and secondary colors

## Security Checklist

- [ ] Change default admin password
- [ ] Use strong database passwords
- [ ] Enable HTTPS in production
- [ ] Set proper file permissions
- [ ] Keep PHP and MySQL updated
- [ ] Regular backups

## Support

For issues or questions, please refer to the code comments or contact support.

## License

This project is provided as-is for restaurant menu management.

