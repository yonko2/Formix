<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';


    if ($action === 'add_field') {
        $field_type = $_POST['field_type'] ?? '';
        $field_name = $_POST['field_name'] ?? '';
        $field_order = count($fields) + 1;
        $is_required = isset($_POST['is_required']) ? 1 : 0;

        if (empty($field_name) || empty($field_type)) {
            $message = "Field name and type are required";
        } else {
            $field_id = add_form_field($form_id, $field_type, $field_name, $field_order, $is_required);
            if ($field_id) {
                $fields = get_form_fields($form_id);
                $message = "Field added successfully";
            } else {
                $message = "Failed to add field";
            }
        }
    }


    if ($action === 'delete_field' && isset($_POST['field_id'])) {
        $field_id = $_POST['field_id'];
        if (delete_form_field($field_id)) {
            $fields = get_form_fields($form_id);
            $message = "Field deleted successfully";
        } else {
            $message = "Failed to delete field";
        }
    }

    if ($action === 'update_css') {
        $custom_css = $_POST['custom_css'] ?? '';
        if (update_form_css($form_id, $custom_css)) {
            $form['custom_css'] = $custom_css;
            $message = "Custom styles saved successfully";
        } else {
            $message = "Failed to save custom styles";
        }
    }
}


function get_field_type_label($type)
{
    switch ($type) {
        case 'text':
            return 'Text Input';
        case 'number':
            return 'Number Input';
        case 'textarea':
            return 'Text Area';
        case 'file':
            return 'File Upload';
        default:
            return ucfirst($type);
    }
}
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <h2>Edit Form: <?= htmlspecialchars($form['name']) ?></h2>
    
    <?php if ($message) : ?>
        <p class="<?= strpos($message, 'success') !== false ? 'success-message' : 'error-message' ?>"><?= $message ?></p>
    <?php endif; ?>
    
    <div class="form-builder">
        <div class="form-builder-header">
            <p>Add fields to your form below. You can rearrange them by dragging and dropping.</p>
        </div>
        
        
        <div class="form-section">
            <div class="section-title">Form Fields</div>
            
            <?php if (empty($fields)) : ?>
                <p>Your form has no fields yet. Add some fields below.</p>
            <?php else : ?>
                <div class="fields-list" id="fieldsContainer">
                    <?php foreach ($fields as $field) : ?>
                        <div class="field-item" data-field-id="<?= $field['id'] ?>">
                            <div class="field-header">
                                <div>
                                    <span class="drag-handle"><i class="fas fa-grip-lines"></i></span>
                                    <span class="field-type"><?= get_field_type_label($field['type']) ?></span>
                                </div>
                                <div class="field-controls">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_field">
                                        <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                        <button type="submit" class="field-control-btn field-delete" onclick="return confirm('Are you sure you want to delete this field?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="field-body">
                                <strong><?= htmlspecialchars($field['name']) ?></strong>
                                <?php if ($field['is_required']) : ?>
                                    <span class="field-required">*Required</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        
        <div class="form-section">
            <div class="section-title">Add a New Field</div>
            
            <form method="POST" class="field-editor">
                <input type="hidden" name="action" value="add_field">
                
                <div class="editor-row">
                    <div class="editor-field">
                        <label for="field_name">Field Name</label>
                        <input type="text" id="field_name" name="field_name" required>
                        <p class="hint-text">This will be shown as the field label to your form respondents</p>
                    </div>
                    
                    <div class="editor-field">
                        <label>Field Type</label>
                        <div class="field-type-selector">
                            <div class="field-type-option">
                                <input type="radio" id="type_text" name="field_type" value="text" checked>
                                <label for="type_text">
                                    <i class="fas fa-font"></i>
                                    Text Input
                                </label>
                            </div>
                            <div class="field-type-option">
                                <input type="radio" id="type_number" name="field_type" value="number">
                                <label for="type_number">
                                    <i class="fas fa-hashtag"></i>
                                    Number
                                </label>
                            </div>
                            <div class="field-type-option">
                                <input type="radio" id="type_textarea" name="field_type" value="textarea">
                                <label for="type_textarea">
                                    <i class="fas fa-align-left"></i>
                                    Text Area
                                </label>
                            </div>
                            <div class="field-type-option">
                                <input type="radio" id="type_file" name="field_type" value="file">
                                <label for="type_file">
                                    <i class="fas fa-file-upload"></i>
                                    File Upload
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="editor-row">
                    <div class="editor-field required-field-row">
                        <div class="required-field-container">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="is_required" id="is_required">
                                <label for="is_required">Required Field</label>
                            </div>
                            <p class="hint-text">If checked, users must fill out this field to submit the form</p>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Field</button>
                </div>
            </form>
        </div>
        
        
        <div class="form-actions">
            <div class="action-left">
                <a href="my_forms.php" class="btn btn-secondary">Back to My Forms</a>
                <a href="preview_form.php?id=<?= $form_id ?>" class="btn btn-secondary">Preview Form</a>
            </div>
            <a href="publish_form.php?id=<?= $form_id ?>" class="btn btn-primary">Publish Form</a>
        </div>
    </div>

    <div class="form-section">
        <div class="section-title">Custom Styles</div>
        <p>Add your own CSS to customize the appearance of your form. These styles will be applied to the public form page.</p>
        
        <div class="css-editor-container">
            <form method="POST" class="css-editor-form">
                <input type="hidden" name="action" value="update_css">
                <div class="form-group">
                    <label for="custom_css">Custom CSS</label>
                    <textarea id="custom_css" name="custom_css" rows="15" class="css-editor"><?= htmlspecialchars($form['custom_css'] ?? '') ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Styles</button>
                </div>
            </form>

            <div class="css-guidelines">
                <div class="guidelines-header">
                    <i class="fas fa-info-circle"></i>
                    Styling Guide
                </div>
                <p>Use these selectors to style your form. Your styles will override the defaults.</p>
                <ul>
                    <li><code>.form-container</code>: The main form wrapper.</li>
                    <li><code>.form-header h2</code>: The form's main title.</li>
                    <li><code>.form-description</code>: The description text.</li>
                    <li><code>.form-group</code>: The wrapper for a single field.</li>
                    <li><code>.field-type-text</code>: Wrapper for a text input field.</li>
                    <li><code>.field-type-number</code>: Wrapper for a number input field.</li>
                    <li><code>.field-type-textarea</code>: Wrapper for a text area field.</li>
                    <li><code>.public-form label</code>: The text label for a field.</li>
                    <li><code>.field-required</code>: The required field indicator (*).</li>
                    <li><code>.field-icon</code>: The icon next to the field label.</li>
                    <li><code>.input-icon</code>: The icon inside the input field.</li>
                    <li><code>.public-form input, .public-form textarea</code>: All input fields.</li>
                    <li><code>.public-form input:focus</code>: A focused input field.</li>
                    <li><code>.public-form button[type="submit"]</code>: The submit button.</li>
                    <li><code>.public-form button[type="submit"]:hover</code>: The submit button on hover.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 
