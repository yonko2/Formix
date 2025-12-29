<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$form_id = $_GET['id'] ?? null;

if (!$form_id) {
    header('Location: my_forms.php');
    exit;
}

$form = get_form($form_id);
if (!$form || $form['user_id'] != $_SESSION['user_id']) {
    header('Location: my_forms.php');
    exit;
}

$fields = get_form_fields($form_id);
?>
<?php
$page_specific_styles = '';
if (!empty($form['custom_css'])) {
    $page_specific_styles = '<style>' . $form['custom_css'] . '</style>';
}
include '../templates/header.php';
?>

<div class="container">
    <div class="preview-header">
        <h2>Form Preview: <?= htmlspecialchars($form['name']) ?></h2>
        <p class="preview-note">This is a preview of how your form will appear to users.</p>
    </div>
    
    <div class="form-preview">
        <?php if (empty($fields)) : ?>
            <div class="empty-fields">
                <p>This form doesn't have any fields yet. <a href="edit_form.php?id=<?= $form_id ?>">Add some fields</a> to see a preview.</p>
            </div>
        <?php else : ?>
            <div class="form-container">
                <div class="form-header">
                    <h2><?= htmlspecialchars($form['name']) ?></h2>
                    <?php if (!empty($form['description'])) : ?>
                        <p class="form-description"><?= htmlspecialchars($form['description']) ?></p>
                    <?php endif; ?>
                    
                    <?php if ($form['require_auth']) : ?>
                        <div class="auth-info">
                            <i class="fas fa-user"></i>
                            <span>Users will need to be logged in to submit this form</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form class="public-form">
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
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="preview-actions">
        <a href="edit_form.php?id=<?= $form_id ?>" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Back to Editor
        </a>
        <a href="publish_form.php?id=<?= $form_id ?>" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Publish Form
        </a>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 
