<?php
// Register page - welcome to the blog! (but verify your email first)
require_once dirname(__FILE__) . '/../bootstrap.php';

$errors = [];
$success = '';

// If already logged in, redirect to home
if (Helper::isLoggedIn()) {
    header("Location: /BlogSystem/public/index.php");
    exit();
}

// Handle registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token (don't skip this!)
    if (!Helper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token';
    } else {
        // Get and sanitize inputs
        $username = Helper::sanitizeInput($_POST['username'] ?? '');
        $email = Helper::sanitizeInput($_POST['email'] ?? '');
        $first_name = Helper::sanitizeInput($_POST['first_name'] ?? '');
        $last_name = Helper::sanitizeInput($_POST['last_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validation - make sure everything is legit
        if (empty($username) || empty($email) || empty($password)) {
            $errors[] = 'Please fill in all required fields';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif ($userObj->usernameExists($username)) {
            $errors[] = 'Username already taken';
        } elseif (!Helper::validateEmail($email)) {
            $errors[] = 'Invalid email format';
        } elseif ($userObj->emailExists($email)) {
            $errors[] = 'Email already registered';
        } elseif (!Helper::validatePassword($password)) {
            $errors[] = 'Password must be at least 8 characters with uppercase, number, and special character';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Passwords do not match';
        } else {
            // All validation passed, try to register
            $result = $userObj->register($username, $email, $password, $first_name, $last_name);
            if ($result['success']) {
                $success = $result['message'];
                // Log the activity
                Helper::logActivity($conn, null, 'REGISTRATION', "New user registered: $username");
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
    <title>Register - BlogSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/BlogSystem/public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/BlogSystem/public/css/style.css">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white text-center">
                        <h2 class="mb-0">✨ Create Account</h2>
                    </div>
                    <div class="card-body">
                        <!-- Display success message -->
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Display errors -->
                        <?php if (!empty($errors)): ?>
                            <?php foreach ($errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Registration form - gather the info! -->
                        <form method="POST" action="">
                            <!-- CSRF token for security -->
                            <input type="hidden" name="csrf_token" value="<?php echo Helper::generateCsrfToken(); ?>">

                            <!-- Username field -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" required 
                                       value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                                <small class="text-muted">3+ characters, alphanumeric</small>
                            </div>

                            <!-- First name field -->
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
                            </div>

                            <!-- Last name field -->
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
                            </div>

                            <!-- Email field -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                            </div>

                            <!-- Password field -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">8+ chars, 1 uppercase, 1 number, 1 special char</small>
                            </div>

                            <!-- Confirm password field -->
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>

                            <!-- Submit button -->
                            <button type="submit" class="btn btn-success w-100">Create Account</button>
                        </form>

                        <!-- Login link -->
                        <hr>
                        <p class="text-center mb-0">
                            Already have an account? 
                            <a href="/BlogSystem/public/login.php">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
