<?php
session_start();
?>
<?php include '../templates/header.php'; ?>

<div class="hero">
    <div class="hero-content">
        <h1>Collect Data Effortlesslllllly</h1>
        <p>Create beautiful forms, surveys, and questionnaires with Formica. Gather insights, analyze responses, and make data-driven decisions.</p>
    </div>
</div>

<div class="container home-content">
    <div class="features">
        <div class="feature-item">
            <div class="feature-icon">📊</div>
            <h3>Smart Forms</h3>
            <p>Create forms with various question types including multiple choice, short answer, and file uploads.</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">📱</div>
            <h3>Mobile Friendly</h3>
            <p>Forms work seamlessly on all devices so you can collect responses anywhere, anytime.</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">📈</div>
            <h3>Real-time Analytics</h3>
            <p>View response data in real-time with beautiful charts and easy-to-understand metrics.</p>
        </div>
    </div>

    <?php if (!isset($_SESSION['user_id'])) : ?>
    <div class="cta-container">
        <div class="cta-content">
            <h2>Get Started Today</h2>
            <p>Join thousands of researchers, marketers, and educators who use Formica to collect and analyze data.</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary">Create an Account</a>
                <a href="login.php" class="btn btn-secondary">Sign In</a>
            </div>
        </div>
        <div class="cta-image">
            <img src="assets/form-illustration.svg" alt="Form illustration">
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?> 
