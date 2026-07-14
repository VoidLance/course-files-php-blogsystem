# BlogSystem - Quick Start Guide

## 🚀 Installation in 5 Minutes

### Step 1: Import the Database Schema
Open your terminal and run:
```bash
mysql -u root -p < /path/to/BlogSystem/config/schema.sql
```

### Step 2: Update Database Credentials
Edit `config/Database.php` and update these lines:
```php
private $host = 'localhost';      // MySQL host
private $db_name = 'blog_system'; // Database name
private $db_user = 'root';        // MySQL user
private $db_pass = '';            // MySQL password (add yours)
```

### Step 3: Set File Permissions
```bash
chmod 755 BlogSystem/public/uploads/posts
chmod 755 BlogSystem/public/uploads/thumbnails
chmod 755 BlogSystem/logs
```

### Step 4: Start Your Server
```bash
cd BlogSystem
php -S localhost:8000
```

### Step 5: Access the Blog
- Homepage: http://localhost:8000/public/index.php
- Admin: http://localhost:8000/public/admin/dashboard.php
- Login with: admin@blogsystem.com / admin123

## 📖 File Structure Overview

```
BlogSystem/
├── bootstrap.php              👈 Include this in all files!
├── config/
│   ├── Database.php          Database connection
│   └── schema.sql            Database setup
├── classes/
│   ├── User.php             User management
│   ├── Post.php             Blog posts
│   ├── Comment.php          Comments
│   └── Category.php         Categories
├── helpers/
│   └── Helper.php           Utility functions
├── middleware/
│   └── AuthMiddleware.php   Security & auth
├── public/
│   ├── index.php            🏠 Homepage
│   ├── login.php            Login
│   ├── register.php         Sign up
│   ├── post.php             View post
│   ├── create-post.php      Write post
│   ├── profile.php          User profile
│   ├── category.php         Browse category
│   ├── author.php           Author profile
│   ├── search.php           Search posts
│   ├── admin/
│   │   ├── dashboard.php    📊 Admin panel
│   │   ├── comments.php     Moderate comments
│   │   └── posts.php        Manage posts (stub)
│   └── css/style.css        Styling
├── logs/
│   └── error_log.txt        Error tracking
└── README.md                Full documentation
```

## 🔑 Key Concepts to Understand

### 1. Bootstrap File
EVERY file should include this at the top:
```php
require_once dirname(__FILE__) . '/../bootstrap.php';
```
This initializes everything you need!

### 2. Authentication
Check if user is logged in:
```php
if (Helper::isLoggedIn()) {
    // User is logged in
}
```

Check admin privileges:
```php
AuthMiddleware::checkAdmin(); // Redirects if not admin
```

### 3. Database Queries
Always use prepared statements:
```php
$query = "SELECT * FROM posts WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
```

### 4. CSRF Protection
Include in every form:
```html
<input type="hidden" name="csrf_token" value="<?php echo Helper::generateCsrfToken(); ?>">
```

Verify on submission:
```php
if (!Helper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    die('CSRF validation failed');
}
```

### 5. Input Sanitization
Always sanitize user input:
```php
$username = Helper::sanitizeInput($_POST['username'] ?? '');
```

## 🎯 Common Tasks

### Creating a New Page
1. Create file in `public/` folder
2. Include bootstrap at top: `require_once dirname(__FILE__) . '/../bootstrap.php';`
3. Check authentication if needed: `AuthMiddleware::checkAuth();`
4. Use database classes: `$postObj->getPostById($id)`
5. Build HTML/Bootstrap layout
6. Include footer and close properly

### Adding a Form
1. Include CSRF token: `<input type="hidden" name="csrf_token" value="<?php echo Helper::generateCsrfToken(); ?>">`
2. Verify on POST: `if (!Helper::verifyCsrfToken($_POST['csrf_token'] ?? '')) { ... }`
3. Sanitize inputs: `Helper::sanitizeInput($_POST['field'])`
4. Validate data before database operations
5. Use prepared statements for queries

### Protecting Admin Pages
Add this at the top:
```php
AuthMiddleware::checkAdmin();
```

### Adding New Database Field
1. Modify schema in `config/schema.sql`
2. Run: `mysql -u root -p < config/schema.sql`
3. Update class methods to use new field

## 🐛 Common Issues & Fixes

| Issue | Fix |
|-------|-----|
| "Connection failed" | Check DB credentials in config/Database.php |
| "File not found" in uploads | Check folder permissions: `chmod 755 public/uploads/*` |
| "CSRF token validation failed" | Ensure form was submitted properly, not via AJAX |
| Can't login as admin | Run schema.sql again to reset admin user |
| Sessions not working | Check php.ini has `session.auto_start = 1` or session_start() is called |
| Typos in HTML not showing | Use TinyMCE properly: strip_tags() for display |

## 📚 Learning Resources in Code

Look for these patterns in the code:

- **Authentication**: See `User.php::login()` for session handling
- **Database**: See `Post.php::getPostById()` for prepared statements
- **Security**: See `Helper.php` for sanitization and validation
- **Pagination**: See `Helper.php::paginate()` for result splitting
- **File Upload**: See `Helper.php::uploadFile()` for secure uploads
- **Error Handling**: See `bootstrap.php` for error logging

## 💡 Pro Tips

1. **Use the error log**: Check `logs/error_log.txt` for debugging
2. **Read the comments**: Every section has student-friendly explanations
3. **Test as you code**: Use `var_dump()` to inspect data
4. **Check database**: Use MySQL CLI to verify data was saved
5. **Use browser DevTools**: Debug JavaScript issues in Console tab
6. **Start simple**: Don't try to add features until basics work

## 🎓 Next Steps to Extend

1. **Add categories admin page** (`admin/categories.php`)
2. **Add users admin page** (`admin/users.php`)  
3. **Add email notifications** when comments are approved
4. **Add post scheduling** to publish at specific times
5. **Add tags system** for finer post organization
6. **Add blog settings page** for customization
7. **Add API endpoints** for mobile apps
8. **Add theme system** for blog customization

## 🤔 Questions to Ask Yourself

- What data needs to be stored in the database?
- Who should have access to each page?
- What validations are needed?
- How do I prevent security issues?
- What if the user inputs bad data?

---

**Happy coding!** 🚀
