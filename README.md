# BlogSystem

BlogSystem is a small PHP and MySQL blog application for publishing and browsing posts. It is also a learning project that demonstrates a class-based PHP structure, server-rendered pages, and basic blog workflows.

## Features

- Register and sign in to an account, manage a profile, and create or edit posts.
- Save posts as drafts or publish them with an optional featured image.
- Organize posts into categories and browse author profiles.
- Search published posts and browse paginated results.
- Submit comments for moderation; administrators can review comments and view dashboard statistics.
- Use CSRF tokens, password hashing, prepared database statements, and session-based access checks.

## Getting started

### Requirements

- PHP 7.4 or later with the `mysqli` extension
- MySQL 5.7 or later
- The PHP GD extension for image thumbnails
- Git (to clone the repository)

The project has no Composer dependencies. Bootstrap and TinyMCE are loaded from external CDNs by the pages.

### Install and run locally

The application currently uses `/BlogSystem/public` in its page and asset URLs. Clone it into a directory named `BlogSystem` and serve that directory from its parent so those URLs resolve:

```sh
git clone https://github.com/VoidLance/course-files-php-blogsystem.git BlogSystem
cd BlogSystem
mysql -u root -p < config/schema.sql
```

Edit the database connection settings in [`config/Database.php`](config/Database.php) so the host, database name, username, and password match your local MySQL setup. The schema creates the `blog_system` database and includes a demo administrator and starter categories.

From the directory containing `BlogSystem`, start PHP's development server:

```sh
cd ..
php -S localhost:8000
```

Open [http://localhost:8000/BlogSystem/public/index.php](http://localhost:8000/BlogSystem/public/index.php). Sign in to the seeded demo account with **admin@blogsystem.com** and **admin123**. This account is for local development only; change its password and remove demo credentials before any deployment.

New-account email verification is currently a mock implementation, so the seeded verified demo account is the simplest way to try the application. Do not use this project as-is for a public production site.

For a shorter setup walkthrough and more notes, see [`QUICKSTART.md`](QUICKSTART.md).

## Project layout

| Path | Purpose |
| --- | --- |
| `bootstrap.php` | Loads the application classes, connects to MySQL, and initializes shared objects |
| `config/Database.php` | MySQL connection settings |
| `config/schema.sql` | Database schema and starter data |
| `classes/` | User, post, comment, and category data operations |
| `helpers/Helper.php` | Shared input, upload, pagination, and formatting helpers |
| `middleware/AuthMiddleware.php` | Session authentication and authorization checks |
| `public/` | Public pages, administration pages, and styles |

## Help and documentation

- See [`QUICKSTART.md`](QUICKSTART.md) for the quick-start guide.
- For setup or usage questions and bug reports, [open an issue](https://github.com/VoidLance/course-files-php-blogsystem/issues).
- Consult the [PHP manual](https://www.php.net/manual/) and [MySQL documentation](https://dev.mysql.com/doc/) for language and database references.

## Maintainers and contributions

This repository is maintained by [@VoidLance](https://github.com/VoidLance). Contributions are welcome: open an issue to discuss a change, then submit a focused pull request with a clear description and the checks you performed. Keep database credentials and other secrets out of commits.
