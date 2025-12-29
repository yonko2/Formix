<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$submission_id = $_GET['id'] ?? null;
$error_message = '';

if (!$submission_id) {
    header('Location: my_answers.php');
    exit;
}

$submission = get_submission($submission_id);

if (!$submission || $submission['user_id'] != $_SESSION['user_id']) {
    $error_message = "You don't have access to this submission.";
}

?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <?php if ($error_message) : ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <p><?= $error_message ?></p>
            <div class="action-buttons">
                <a href="my_answers.php" class="btn btn-primary">Back to My Answers</a>
            </div>
        </div>
    <?php else : ?>
        <div class="submission-container">
            <div class="submission-header">
                <a href="my_answers.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to My Answers
                </a>
                <h2>Response to: <?= htmlspecialchars($submission['form_name']) ?></h2>
                <div class="submission-meta">
                    <div class="submission-time">
                        <i class="far fa-clock"></i> 
                        Submitted on <?= date('F j, Y \a\t g:i a', strtotime($submission['submission_time'])) ?>
                    </div>
                </div>
            </div>
            
            <div class="submission-content">
                <?php if (empty($submission['values'])) : ?>
                    <p class="no-values">This submission has no values.</p>
                <?php else : ?>
                    <div class="values-list">
                        <?php foreach ($submission['values'] as $value) : ?>
                            <div class="value-item value-type-<?= htmlspecialchars($value['field_type']) ?>">
                                <div class="value-label">
                                    <span class="field-icon">
                                        <?php if ($value['field_type'] === 'text') : ?>
                                            <i class="fas fa-font"></i>
                                        <?php elseif ($value['field_type'] === 'number') : ?>
                                            <i class="fas fa-hashtag"></i>
                                        <?php elseif ($value['field_type'] === 'textarea') : ?>
                                            <i class="fas fa-align-left"></i>
                                        <?php endif; ?>
                                    </span>
                                    <h3><?= htmlspecialchars($value['field_name']) ?></h3>
                                </div>
                                <div class="value-content">
                                    <?php if (empty($value['value'])) : ?>
                                        <p class="empty-value"><i>No answer provided</i></p>
                                    <?php elseif ($value['field_type'] === 'textarea') : ?>
                                        <p class="textarea-value"><?= nl2br(htmlspecialchars($value['value'])) ?></p>
                                    <?php elseif ($value['field_type'] === 'file') : ?>
                                        <a href="download_file.php?filename=<?= urlencode($value['value']) ?>" class="file-download-link" target="_blank">
                                            <i class="fas fa-download"></i>
                                            Download File
                                        </a>
                                        <p class="filename-display">(<?= htmlspecialchars($value['value']) ?>)</p>
                                    <?php else : ?>
                                        <p><?= htmlspecialchars($value['value']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?> 
