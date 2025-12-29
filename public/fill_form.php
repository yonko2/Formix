<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

$form_id = $_GET['id'] ?? null;
$error_message = '';
$success_message = '';
$password_required = false;
$form_authenticated = false;

if (!$form_id) {
    $error_message = "Form not found";
} else {
    $form = get_form($form_id);

    if (!$form) {
        $error_message = "Form not found";
    } else {
        if ($form['require_auth'] && !isset($_SESSION['user_id'])) {
            header("Location: login.php?redirect=fill_form.php?id=$form_id");
            exit;
        }

        if (
            isset($_SESSION['user_id']) && !$form['allow_multiple_submissions'] &&
            has_user_submitted_form($form_id, $_SESSION['user_id'])
        ) {
            $error_message = "You have already submitted this form.";
        }

        if (!empty($form['password'])) {
            $password_required = true;

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_password'])) {
                if ($_POST['form_password'] === $form['password']) {
                    $form_authenticated = true;
                    $_SESSION['form_authenticated_' . $form_id] = true;
                } else {
                    $error_message = "Incorrect password.";
                }
            }

            if (isset($_SESSION['form_authenticated_' . $form_id]) && $_SESSION['form_authenticated_' . $form_id]) {
                $form_authenticated = true;
            }
        } else {
            $form_authenticated = true;
        }

        if ($form_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_form') {
            $field_values = [];
            $fields = get_form_fields($form_id);
            $validation_errors = [];

            $upload_dir = __DIR__ . '/../files';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($fields as $field) {
                $field_id = $field['id'];
                $field_name = "field_{$field_id}";

                if ($field['type'] === 'file') {
                    if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES[$field_name]['tmp_name'];
                        $original_name = basename($_FILES[$field_name]['name']);
                        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
                        $new_filename = uniqid('file_', true) . '.' . $file_extension;
                        $destination = $upload_dir . '/' . $new_filename;

                        if (move_uploaded_file($tmp_name, $destination)) {
                            $field_values[$field_id] = $new_filename;
                        } else {
                            $validation_errors[] = "Failed to upload file for '{$field['name']}'.";
                        }
                    } elseif ($field['is_required']) {
                        $validation_errors[] = "The file for '{$field['name']}' is required.";
                    }
                } else {
                    $value = $_POST[$field_name] ?? '';

                    if ($field['is_required'] && empty($value)) {
                        $validation_errors[] = "The field '{$field['name']}' is required.";
                    }

                    $field_values[$field_id] = $value;
                }
            }

            if (empty($validation_errors)) {
                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                $submission_id = submit_form($form_id, $field_values, $user_id);

                if ($submission_id) {
                    $success_message = "Form submitted successfully!";
                } else {
                    $error_message = "There was an error submitting the form. Please try again.";
                }
            } else {
                $error_message = implode("<br>", $validation_errors);
            }
        }
    }
}

$fields = [];
if (isset($form) && $form && $form_authenticated && empty($success_message)) {
    $fields = get_form_fields($form_id);
}
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <?php if ($error_message) : ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <p><?= $error_message ?></p>
        </div>
        <?php if (strpos($error_message, "already submitted") !== false) : ?>
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">Return to Home</a>
            </div>
        <?php endif; ?>
    <?php elseif ($success_message) : ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <h2>Thank You!</h2>
            <p><?= $success_message ?></p>
            <div class="action-buttons">
                <?php if ($form['allow_multiple_submissions']) : ?>
                    <a href="fill_form.php?id=<?= $form_id ?>" class="btn btn-secondary">Submit Another Response</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-primary">Return to Home</a>
            </div>
        </div>
    <?php elseif ($password_required && !$form_authenticated) : ?>
        <div class="form-password-container">
            <h2>This form is password protected</h2>
            <p>Please enter the password to access this form.</p>
            
            <form method="POST" class="password-form">
                <div class="form-group">
                    <label for="form_password">Password</label>
                    <input type="password" id="form_password" name="form_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Continue</button>
            </form>
        </div>
    <?php elseif (isset($form) && $form) : ?>
        <div class="form-container">
            <div class="form-header">
                <h2><?= htmlspecialchars($form['name']) ?></h2>
                <?php if (!empty($form['description'])) : ?>
                    <p class="form-description"><?= htmlspecialchars($form['description']) ?></p>
                <?php endif; ?>
                
                <?php if ($form['require_auth'] && isset($_SESSION['user_id'])) : ?>
                    <div class="auth-info">
                        <i class="fas fa-user"></i>
                        <span>You are submitting this form as <?= htmlspecialchars($_SESSION['email'] ?? 'an authenticated user') ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="public-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="submit_form">
                
                <?php foreach ($fields as $field) : ?>
                    <div class="form-group field-type-<?= $field['type'] ?>">
                        <div class="field-label">
                            <span class="field-icon">
                                <?php if ($field['type'] === 'text') : ?>
                                    <i class="fas fa-font"></i>
                                <?php elseif ($field['type'] === 'number') : ?>
                                    <i class="fas fa-hashtag"></i>
                                <?php elseif ($field['type'] === 'textarea') : ?>
                                    <i class="fas fa-align-left"></i>
                                <?php elseif ($field['type'] === 'file') : ?>
                                    <i class="fas fa-file-upload"></i>
                                <?php endif; ?>
                            </span>
                            <label for="field_<?= $field['id'] ?>">
                                <?= htmlspecialchars($field['name']) ?>
                                <?php if ($field['is_required']) : ?>
                                    <span class="field-required">*</span>
                                <?php endif; ?>
                            </label>
                        </div>
                        
                        <div class="input-wrapper">
                            <?php if ($field['type'] === 'textarea') : ?>
                                <textarea 
                                    id="field_<?= $field['id'] ?>" 
                                    name="field_<?= $field['id'] ?>" 
                                    rows="4"
                                    placeholder="Enter your response here..."
                                    <?= $field['is_required'] ? 'required' : '' ?>
                                ></textarea>
                                <span class="input-icon">
                                    <i class="fas fa-align-left"></i>
                                </span>
                            <?php elseif ($field['type'] === 'number') : ?>
                                <input 
                                    type="number" 
                                    id="field_<?= $field['id'] ?>" 
                                    name="field_<?= $field['id'] ?>"
                                    placeholder="Enter a number"
                                    <?= $field['is_required'] ? 'required' : '' ?>
                                >
                                <span class="input-icon">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                            <?php elseif ($field['type'] === 'file') : ?>
                                <div class="file-input-wrapper">
                                    <label for="field_<?= $field['id'] ?>" class="file-input-label btn">
                                        <i class="fas fa-upload"></i>
                                        <span>Choose a file...</span>
                                    </label>
                                    <input 
                                        type="file" 
                                        id="field_<?= $field['id'] ?>" 
                                        name="field_<?= $field['id'] ?>"
                                        class="file-input-native"
                                        <?= $field['is_required'] ? 'required' : '' ?>
                                    >
                                    <span class="file-input-filename"></span>
                                </div>
                            <?php else : ?>
                                <input 
                                    type="text" 
                                    id="field_<?= $field['id'] ?>" 
                                    name="field_<?= $field['id'] ?>"
                                    placeholder="Enter your response here..."
                                    <?= $field['is_required'] ? 'required' : '' ?>
                                >
                                <span class="input-icon">
                                    <i class="fas fa-font"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($field['type'] === 'number') : ?>
                            <div class="field-description">
                                Enter numerical values only
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('.file-input-native');
    
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const fileNameDisplay = e.target.closest('.file-input-wrapper').querySelector('.file-input-filename');
            if (e.target.files.length > 0) {
                fileNameDisplay.textContent = e.target.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        });
    });
});
</script>

<?php include '../templates/footer.php'; ?> 
