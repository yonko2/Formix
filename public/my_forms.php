<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$forms = get_user_forms($_SESSION['user_id']);
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <h2>My Forms</h2>
    
    <div class="my-forms-header">
        <a href="create_form.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Form
        </a>
    </div>
    
    <?php if (empty($forms)) : ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3>No Forms Yet</h3>
            <p>You haven't created any forms yet. Click the button above to get started.</p>
        </div>
    <?php else : ?>
        <div class="forms-list">
            <?php foreach ($forms as $form) : ?>
                <div class="form-card">
                    <div class="form-card-header">
                        <h3><?= htmlspecialchars($form['name']) ?></h3>
                        <div class="form-card-date">
                            Created: <?= date('M j, Y', strtotime($form['created_at'])) ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($form['description'])) : ?>
                        <div class="form-card-description">
                            <?= htmlspecialchars($form['description']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-card-actions">
                        <a href="edit_form.php?id=<?= $form['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="preview_form.php?id=<?= $form['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-eye"></i> Preview
                        </a>
                        <a href="view_responses.php?id=<?= $form['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> Responses
                        </a>
                        <a href="form_submissions_chart.php?id=<?= $form['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-chart-line"></i> Analytics
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?> 
