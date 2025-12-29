<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$submissions = get_user_submissions($_SESSION['user_id']);
?>
<?php include '../templates/header.php'; ?>

<div class="container answers-container">
    <div class="page-header">
        <h2>My Form Responses</h2>
        <p class="page-description">View all the forms you have submitted</p>
        
        <div class="page-actions">
            <a href="user_submissions_chart.php" class="btn btn-primary">
                <i class="fas fa-chart-bar"></i> View Submission Trends
            </a>
        </div>
    </div>
    
    <?php if (empty($submissions)) : ?>
        <div class="empty-answers">
            <i class="far fa-clipboard"></i>
            <h3>No responses yet</h3>
            <p>You haven't submitted any forms yet. Once you do, they will appear here.</p>
            <a href="index.php" class="btn btn-primary">Browse Forms</a>
        </div>
    <?php else : ?>
        <div class="submissions-list">
            <?php foreach ($submissions as $submission) : ?>
                <div class="submission-card">
                    <div class="submission-header">
                        <h3 class="submission-title"><?= htmlspecialchars($submission['form_name']) ?></h3>
                        <div class="submission-meta">
                            <div class="submission-time">
                                <i class="far fa-clock"></i> 
                                <?= date('F j, Y \a\t g:i a', strtotime($submission['submission_time'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="submission-content">
                        <ul class="preview-values">
                            <?php if (empty($submission['preview_values'])) : ?>
                                <li class="preview-value">
                                    <div class="field-value empty">No response data available</div>
                                </li>
                            <?php else : ?>
                                <?php foreach ($submission['preview_values'] as $value) : ?>
                                    <li class="preview-value">
                                        <div class="field-label"><?= htmlspecialchars($value['field_name']) ?></div>
                                        <div class="field-value">
                                            <?php if (empty($value['value'])) : ?>
                                                <span class="empty">No answer provided</span>
                                            <?php else : ?>
                                                <?= htmlspecialchars(substr($value['value'], 0, 100)) ?><?= strlen($value['value']) > 100 ? '...' : '' ?>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="submission-footer">
                        <a href="view_submission.php?id=<?= $submission['id'] ?>" class="view-details">
                            View Full Response <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?> 
