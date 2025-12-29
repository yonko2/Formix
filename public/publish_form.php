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

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$form_url = "{$protocol}://{$host}{$path}/fill_form.php?id={$form_id}";
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <h2>Publish Form: <?= htmlspecialchars($form['name']) ?></h2>
    
    <?php if (empty($fields)) : ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <p>This form doesn't have any fields yet. <a href="edit_form.php?id=<?= $form_id ?>">Add some fields</a> before publishing.</p>
        </div>
    <?php else : ?>
        <div class="publish-container">
            <div class="publish-section">
                <h3><i class="fas fa-link"></i> Share Your Form</h3>
                <p>Your form is ready to be shared. Use the link below to share it with others:</p>
                
                <div class="form-url-container">
                    <input type="text" id="formUrl" value="<?= $form_url ?>" readonly class="form-url">
                    <button class="btn btn-primary" onclick="copyFormUrl()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                
                <div id="copyMessage" class="copy-message" style="display: none;">
                    <i class="fas fa-check"></i> Link copied to clipboard!
                </div>
            </div>
            
            <div class="publish-section">
                <h3><i class="fas fa-cog"></i> Form Settings</h3>
                
                <div class="settings-list">
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-name">Password Protection</div>
                            <div class="setting-value">
                                <?php if (!empty($form['password'])) : ?>
                                    <span class="status-enabled"><i class="fas fa-lock"></i> Enabled</span>
                                <?php else : ?>
                                    <span class="status-disabled"><i class="fas fa-unlock"></i> Disabled</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="setting-description">
                            <?php if (!empty($form['password'])) : ?>
                                Users will need to enter a password to access this form.
                            <?php else : ?>
                                Anyone with the link can access this form without a password.
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-name">Multiple Submissions</div>
                            <div class="setting-value">
                                <?php if ($form['allow_multiple_submissions']) : ?>
                                    <span class="status-enabled"><i class="fas fa-check-circle"></i> Allowed</span>
                                <?php else : ?>
                                    <span class="status-disabled"><i class="fas fa-times-circle"></i> Not Allowed</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="setting-description">
                            <?php if ($form['allow_multiple_submissions']) : ?>
                                Users can submit this form multiple times.
                            <?php else : ?>
                                Users can only submit this form once.
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-name">Authentication Required</div>
                            <div class="setting-value">
                                <?php if ($form['require_auth']) : ?>
                                    <span class="status-enabled"><i class="fas fa-user-lock"></i> Required</span>
                                <?php else : ?>
                                    <span class="status-disabled"><i class="fas fa-user-check"></i> Not Required</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="setting-description">
                            <?php if ($form['require_auth']) : ?>
                                Users must be logged in to submit this form. Submissions will be linked to user accounts.
                            <?php else : ?>
                                Anyone can submit this form without logging in. Submissions will be anonymous.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="settings-actions">
                    <a href="edit_form.php?id=<?= $form_id ?>" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Edit Form
                    </a>
                    <a href="preview_form.php?id=<?= $form_id ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Preview Form
                    </a>
                </div>
            </div>
            
            <div class="publish-section">
                <h3><i class="fas fa-chart-bar"></i> Responses</h3>
                <p>Monitor form submissions and view response data:</p>
                
                <div class="responses-actions">
                    <a href="view_responses.php?id=<?= $form_id ?>" class="btn btn-primary">
                        <i class="fas fa-table"></i> View Responses
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function copyFormUrl() {
    var copyText = document.getElementById("formUrl");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    
    var copyMessage = document.getElementById("copyMessage");
    copyMessage.style.display = "block";
    
    setTimeout(function(){
        copyMessage.style.display = "none";
    }, 3000);
}
</script>

<?php include '../templates/footer.php'; ?> 
