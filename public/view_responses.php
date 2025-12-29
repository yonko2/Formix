<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$form_id = $_GET['id'] ?? null;
$submission_id = $_GET['submission'] ?? null;
$error_message = '';

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

if ($submission_id) {
    $submission = get_submission($submission_id);
    if (!$submission || $submission['form_id'] != $form_id) {
        $error_message = "Submission not found";
        $submission = null;
    }
} else {
    $submissions = get_form_submissions($form_id);
}
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <div class="responses-header">
        <h2>
            <?php if (isset($submission)) : ?>
                Submission Details
            <?php else : ?>
                Responses: <?= htmlspecialchars($form['name']) ?>
            <?php endif; ?>
        </h2>
        
        <div class="back-link">
            <?php if (isset($submission)) : ?>
                <a href="view_responses.php?id=<?= $form_id ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to All Responses
                </a>
            <?php else : ?>
                <a href="my_forms.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to My Forms
                </a>
                <?php if (!empty($submissions)) : ?>
                <a href="download_responses.php?id=<?= $form_id ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Download as CSV
                </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($error_message) : ?>
        <p class="error-message"><?= $error_message ?></p>
    <?php elseif (isset($submission)) : ?>
        <div class="submission-detail">
            <div class="submission-meta">
                <div class="meta-item">
                    <span class="meta-label">Submitted:</span>
                    <span class="meta-value"><?= date('M j, Y, g:i a', strtotime($submission['submission_time'])) ?></span>
                </div>
                
                <?php if ($form['require_auth']) : ?>
                <div class="meta-item">
                    <span class="meta-label">Submitted by:</span>
                    <span class="meta-value">
                        <?php if ($submission['user_id']) : ?>
                            <i class="fas fa-user"></i> <?= htmlspecialchars($submission['user_email'] ?? 'Unknown user') ?>
                        <?php else : ?>
                            Anonymous
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="submission-values">
                <h3>Form Values</h3>
                
                <?php if (empty($submission['values'])) : ?>
                    <p class="no-data">No data submitted.</p>
                <?php else : ?>
                    <div class="values-table">
                        <?php foreach ($submission['values'] as $value) : ?>
                            <div class="value-row">
                                <div class="value-field"><?= htmlspecialchars($value['field_name']) ?>:</div>
                                <div class="value-content"><?= htmlspecialchars($value['value']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else : ?>
        <?php if (empty($submissions)) : ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3>No Responses Yet</h3>
                <p>You haven't received any responses to this form yet.</p>
                <div class="empty-actions">
                    <a href="publish_form.php?id=<?= $form_id ?>" class="btn btn-primary">
                        <i class="fas fa-share-alt"></i> Share Form
                    </a>
                </div>
            </div>
        <?php else : ?>
            <div class="submissions-summary">
                <p><strong><?= count($submissions) ?></strong> response(s) received</p>
            </div>
            
            <div class="submissions-table">
                <div class="table-header" style="grid-template-columns: <?= $form['require_auth'] ? '2fr 2fr 1fr' : '3fr 1fr' ?>;">
                    <div class="header-cell">Submission Date</div>
                    <?php if ($form['require_auth']) : ?>
                        <div class="header-cell">Submitted By</div>
                    <?php endif; ?>
                    <div class="header-cell">Actions</div>
                </div>
                
                <?php foreach ($submissions as $sub) : ?>
                    <div class="table-row" style="grid-template-columns: <?= $form['require_auth'] ? '2fr 2fr 1fr' : '3fr 1fr' ?>;">
                        <div class="table-cell">
                            <?= date('M j, Y, g:i a', strtotime($sub['submission_time'])) ?>
                        </div>
                        
                        <?php if ($form['require_auth']) : ?>
                            <div class="table-cell">
                                <?php if ($sub['user_id']) : ?>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($sub['user_email'] ?? 'Unknown user') ?>
                                <?php else : ?>
                                    Anonymous
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="table-cell">
                            <a href="view_responses.php?id=<?= $form_id ?>&submission=<?= $sub['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?> 
