<?php
require_once '../logic/auth.php';
require_once '../logic/forms.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $message = register_user($email, $password);

    if ($message === "Registration successful.") {
        $success = true;
    }
}
?>
<?php include '../templates/header.php'; ?>
<div class="container">
    <h2>Create Your Account</h2>
    
    <?php if ($success) : ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <p>Your account has been created successfully!</p>
            <p>You can now <a href="login.php">log in</a> to your account.</p>
        </div>
    <?php else : ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <p class="hint-text">Password must be at least 6 characters long</p>
            </div>
            
            <button type="submit">Register</button>
            
            <?php if ($message && $message !== "Registration successful.") : ?>
                <p class="error-message"><?= $message ?></p>
            <?php endif; ?>
            
            <p class="form-footer">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?>
