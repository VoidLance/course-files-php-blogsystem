<?php
// Login page - where users prove who they are
require_once dirname(__FILE__) . '/../bootstrap.php';

$errors = [];
$success = '';

// If already logged in, redirect to home
if (Helper::isLoggedIn()) {
    header("Location: /BlogSystem/public/index.php");
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token (always do this!)
    if (!Helper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize inputs (never trust user input!)
        $email = Helper::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (empty($email) || empty($password)) {
            $errors[] = 'Please fill in all fields';
        } else {
            // Attempt login
            $result = $userObj->login($email, $password);
            if ($result['success']) {
                // Log the activity
                Helper::logActivity($conn, $_SESSION['user_id'], 'LOGIN', 'User logged in');
                header("Location: /BlogSystem/public/index.php");
                exit();
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BlogSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="mb-0">🔐 Login</h2>
                    </div>
                    <div class="card-body">
                        <!-- Display error messages -->
                        <?php if (!empty($errors)): ?>
                            <?php foreach ($errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Login form - get those credentials! -->
                        <form method="POST" action="">
                            <!-- CSRF token (security first!) -->
                            <input type="hidden" name="csrf_token" value="<?php echo Helper::generateCsrfToken(); ?>">

                            <!-- Email field -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <!-- Password field -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <!-- Remember me checkbox (optional feature) -->
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>

                            <!-- Submit button -->
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>

                        <!-- Registration link - for new users -->
                        <hr>
                        <p class="text-center mb-0">
                            Don't have an account? 
                            <a href="/BlogSystem/public/register.php">Register here</a>
                        </p>
                    </div>
                </div>

                <!-- Demo credentials hint -->
                <div class="alert alert-info mt-3">
                    <strong>Demo Account:</strong><br>
                    Email: admin@blogsystem.com<br>
                    Password: admin123
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
