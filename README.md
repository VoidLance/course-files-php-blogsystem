# 📝 BlogSystem - A Complete PHP Blog Application

Welcome to **BlogSystem**, a fully-featured blog application built with PHP, MySQL, and Bootstrap 5! This project demonstrates professional PHP development practices including OOP, MVC architecture, security best practices, and responsive design.

## 🎯 Features

### User Management
- ✅ User registration with email verification (mock)
- ✅ Secure login/logout with password hashing (bcrypt)
- ✅ User profile management
- ✅ Role-based access control (Admin/User)

### Blog Post Management
- ✅ Create, read, update, delete (CRUD) blog posts
- ✅ Rich text editor support (TinyMCE)
- ✅ Featured image uploads with thumbnail generation
- ✅ Draft/Publish status management
- ✅ Post view counter
- ✅ URL-friendly slugs

### Post Categories & Tagging
- ✅ Create and manage categories
- ✅ Assign multiple categories to posts
- ✅ Filter posts by category
- ✅ Display category post counts

### Comments System
- ✅ Registered users can comment on posts
- ✅ Comment moderation (approve/reject/spam)
- ✅ Edit/delete own comments
- ✅ Threaded comment replies (parent_comment_id)

### Search Functionality
- ✅ Full-text search for posts
- ✅ Search by title, content, or author
- ✅ Paginated search results
- ✅ Search history tracking

### Admin Panel
- ✅ Dashboard with statistics
- ✅ Manage all blog posts
- ✅ Manage categories
- ✅ Comment moderation interface
- ✅ User management
- ✅ Activity logging

### Security
- ✅ CSRF protection on all forms
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (HTML escaping)
- ✅ Password hashing with bcrypt
- ✅ Session security with regeneration
- ✅ Secure HTTP headers
- ✅ Input sanitization and validation

### Frontend
- ✅ Responsive design with Bootstrap 5
- ✅ Mobile-friendly interface
- ✅ Pagination for large result sets
- ✅ Clean, modern UI

## 📁 Project Structure

```
BlogSystem/
├── bootstrap.php              # Application initialization (include this first!)
├── config/
│   ├── Database.php           # Database connection class
│   └── schema.sql             # Database schema (run this to set up DB!)
├── classes/
│   ├── User.php               # User model for authentication/profiles
│   ├── Post.php               # Post model for CRUD operations
│   ├── Comment.php            # Comment model for moderation
│   └── Category.php           # Category model for organization
├── helpers/
│   └── Helper.php             # Utility functions (sanitize, validate, etc)
├── middleware/
│   └── AuthMiddleware.php     # Authentication & authorization checks
├── public/
│   ├── index.php              # Homepage with recent posts
│   ├── login.php              # User login page
│   ├── register.php           # User registration page
│   ├── logout.php             # Logout handler
│   ├── post.php               # Single post view with comments
│   ├── create-post.php        # Create new post form
│   ├── search.php             # Search results page
│   ├── profile.php            # User profile page (create this!)
│   ├── category.php           # Category posts view (create this!)
│   ├── author.php             # Author profile page (create this!)
│   ├── css/
│   │   └── style.css          # Custom styling for the blog
│   ├── js/
│   │   └── main.js            # JavaScript for client-side features (optional)
│   ├── uploads/
│   │   ├── posts/             # Uploaded post images go here
│   │   └── thumbnails/        # Generated thumbnails
│   └── admin/
│       ├── dashboard.php      # Admin dashboard (stats, overview)
│       ├── posts.php          # Manage all posts (create this!)
│       ├── comments.php       # Moderate comments
│       ├── categories.php     # Manage categories (create this!)
│       ├── users.php          # Manage users (create this!)
│       └── settings.php       # Blog settings (create this!)
├── logs/
│   └── error_log.txt          # Application error log
└── README.md                  # This file!
```

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled (optional, for pretty URLs)
- Composer (optional, for dependencies)

### Installation

1. **Clone or extract the project** into your web root:
   ```bash
   cp -r BlogSystem /var/www/html/
   ```

2. **Create the database** by running the schema:
   ```bash
   mysql -u root -p < config/schema.sql
   ```

3. **Update database credentials** in `config/Database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'blog_system';
   private $db_user = 'root';
   private $db_pass = ''; // Add your password
   ```

4. **Set proper permissions**:
   ```bash
   chmod 755 public/uploads/posts
   chmod 755 public/uploads/thumbnails
   chmod 755 logs
   ```

5. **Access the application**:
   - Homepage: http://localhost:8000/public/index.php
   - Admin panel: http://localhost:8000/public/admin/dashboard.php

6. **Demo credentials** (after running schema.sql):
   - Email: admin@blogsystem.com
   - Password: admin123

## 📚 How to Use

### For Users
1. **Register** - Create an account (verify email via log file)
2. **Create Posts** - Write blog posts with rich text editor
3. **Comment** - Leave comments on posts (admin approval required)
4. **Search** - Find posts by title or content
5. **Profile** - Update your user information

### For Admins
1. **Dashboard** - View statistics and recent activity
2. **Manage Posts** - Edit, delete, or publish posts
3. **Moderate Comments** - Approve, reject, or mark comments as spam
4. **Manage Categories** - Create and organize post categories
5. **Manage Users** - View, edit, or delete user accounts
6. **Settings** - Configure blog-wide settings

## 🔐 Security Features Explained

### CSRF Protection
All forms include a CSRF token checked on submission. This prevents attackers from tricking users into actions.

```php
// In forms:
<input type="hidden" name="csrf_token" value="<?php echo Helper::generateCsrfToken(); ?>">

// On submission:
if (!Helper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    die('CSRF validation failed');
}
```

### SQL Injection Prevention
All database queries use prepared statements to safely handle user input:

```php
$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
```

### XSS Protection
User input is escaped before display:

```php
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

### Password Security
Passwords are hashed using bcrypt, not stored in plain text:

```php
$hashed = password_hash($password, PASSWORD_BCRYPT);
password_verify($user_input, $hashed); // Verify on login
```

## 🎓 Learning Points

This project teaches:

1. **Object-Oriented Programming** - Classes for User, Post, Comment, Category
2. **MVC Architecture** - Separation of concerns (Models, Views, Controllers via bootstrap)
3. **Database Design** - Proper schema with relationships and indexing
4. **Session Management** - User authentication and role-based access
5. **Form Handling** - Server-side validation and sanitization
6. **File Uploads** - Secure image upload with validation
7. **Pagination** - Handling large datasets efficiently
8. **Error Handling** - Try-catch blocks and error logging
9. **Security Best Practices** - CSRF, XSS, SQL injection prevention
10. **Responsive Design** - Bootstrap 5 for mobile-first approach

## 🔧 Advanced Features to Add

1. **Social Login** - Google/Facebook integration
2. **Email Notifications** - Notify users of new comments
3. **Tags System** - More granular post organization
4. **Advanced Analytics** - Post performance metrics
5. **REST API** - RESTful endpoints for mobile apps
6. **Caching** - Redis/Memcached for performance
7. **Scheduled Posts** - Publish at specific times
8. **User Roles** - Editor, Contributor, Author roles
9. **Post Revisions** - Track changes over time
10. **Theme System** - Multiple blog themes

## 🐛 Troubleshooting

### "Connection failed" error
- Check MySQL is running
- Verify credentials in `config/Database.php`
- Ensure database name matches

### "Permission denied" on uploads
- Run `chmod 755 public/uploads/posts`
- Check web server user has write permissions
- Verify disk space availability

### "CSRF token validation failed"
- Make sure you're submitting from the actual form
- Check sessions are enabled
- Verify no headers sent before session_start()

### Can't login as admin
- Run `config/schema.sql` to create default admin
- Check default credentials above
- Clear browser cookies and try again

## 📝 Code Comments

Throughout the code, you'll find helpful one-line comments explaining what each section does. These serve as both documentation and teaching notes! For example:

```php
// Generate CSRF token - protect forms from cross-site attacks
// Hash the password because storing plain text is for villains
// Check if slug already exists (append random numbers if it does)
```

These comments are intentionally concise and sometimes playful to make learning more engaging! 😄

## 📖 Resources

- [PHP Documentation](https://www.php.net/manual/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.1/)
- [OWASP Security Guidelines](https://owasp.org/)
- [TinyMCE Editor](https://www.tiny.cloud/)

## 📄 License

This project is open-source and available for educational purposes.

## ✨ Tips for Students

1. **Read the code carefully** - Comments explain the "why" behind each decision
2. **Experiment** - Modify the code and see what happens (in a test environment!)
3. **Debug** - Use `var_dump()` and `error_log()` to understand data flow
4. **Extend** - Add new features to practice your skills
5. **Test** - Always test edge cases and invalid inputs
6. **Secure** - Never trust user input, always validate and sanitize

## 🎉 Happy Blogging!

This BlogSystem is designed to teach real-world PHP development while creating a functional application. Use it to learn, experiment, and build your understanding of full-stack web development!

---

**Remember**: Code is read 10 times more than it's written. Write clean, well-commented code! 🚀
