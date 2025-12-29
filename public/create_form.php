<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$form_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_form') {
    $name = $_POST['form_name'] ?? '';
    $description = $_POST['form_description'] ?? '';
    $password = $_POST['form_password'] ?? '';
    $allow_multiple = isset($_POST['allow_multiple']) ? true : false;
    $require_auth = isset($_POST['require_auth']) ? true : false;

    if (empty($name)) {
        $message = "Form name is required";
    } else {
        $form_id = create_form($_SESSION['user_id'], $name, $description, $password, $allow_multiple, $require_auth);
        if ($form_id) {
            header("Location: edit_form.php?id={$form_id}");
            exit;
        } else {
            $message = "Failed to create the form";
        }
    }
}
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <h2>Create a New Form</h2>
    
    <?php if ($message) : ?>
        <p class="error-message"><?= $message ?></p>
    <?php endif; ?>
    
    <div class="form-builder">
        <div class="form-builder-header">
            <p>Start by providing the basic information about your form. Once created, you'll be able to add fields.</p>
        </div>
        
        <form method="POST" action="create_form.php">
            <input type="hidden" name="action" value="create_form">
            
            <div class="form-section">
                <div class="section-title">Form Information</div>
                
                <div class="form-group">
                    <label for="form_name">Form Name</label>
                    <input type="text" id="form_name" name="form_name" required>
                    <p class="hint-text">This will be displayed as the title of your form. Choose something descriptive that your respondents will recognize.</p>
                </div>
                
                <div class="form-group">
                    <label for="form_description">Description (Optional)</label>
                    <textarea id="form_description" name="form_description" rows="3"></textarea>
                    <p class="hint-text">Provide instructions or context for your form. A good description helps people understand why they're filling out your form and what you'll do with their responses.</p>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-title">Form Settings</div>
                
                <div class="form-group">
                    <label for="form_password">Password Protection (Optional)</label>
                    <input type="password" id="form_password" name="form_password">
                    <p class="hint-text">If set, users will need this password to access your form. This adds a layer of security to restrict who can view and submit your form.</p>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="allow_multiple" id="allow_multiple">
                        <label for="allow_multiple">Allow multiple submissions from the same user</label>
                    </div>
                    <p class="hint-text">If checked, users can submit the form multiple times. Otherwise, they can only submit once.</p>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="require_auth" id="require_auth">
                        <label for="require_auth">Require authentication to submit this form</label>
                    </div>
                    <p class="hint-text">If checked, users must be logged in to submit this form.</p>
                </div>
                
                <div class="auth-settings" style="display: none; padding-left: 25px; margin-top: 10px;">
                    <p class="hint-text"><i class="fas fa-info-circle"></i> When authentication is required:</p>
                    <ul class="hint-list">
                        <li>Only logged-in users can submit the form</li>
                        <li>Submissions will be linked to the user's account</li>
                        <li>You'll be able to see who submitted each response</li>
                    </ul>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Form</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const requireAuthCheckbox = document.getElementById('require_auth');
    const authSettings = document.querySelector('.auth-settings');
    
    requireAuthCheckbox.addEventListener('change', function() {
        if (this.checked) {
            authSettings.style.display = 'block';
        } else {
            authSettings.style.display = 'none';
        }
    });
});
</script>

<?php include '../templates/footer.php'; ?> 
