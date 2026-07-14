<?php
// Access Denied Page - sorry, you can't do that!
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - BlogSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center text-white">
                <h1 class="display-1">🔐</h1>
                <h2 class="mb-3">Access Denied</h2>
                <p class="lead mb-4">Sorry, you don't have permission to access this page.</p>
                <p class="mb-4">This could be because:</p>
                <ul class="list-unstyled mb-4">
                    <li>✗ You're not logged in</li>
                    <li>✗ You don't own this resource</li>
                    <li>✗ You don't have admin privileges</li>
                </ul>
                <a href="/BlogSystem/public/index.php" class="btn btn-light btn-lg">← Back to Homepage</a>
                <br><br>
                <a href="/BlogSystem/public/login.php" class="btn btn-outline-light">Login to Your Account</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
