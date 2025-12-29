<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formica - Form Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/main.css">
    
    <?php
    $current_page = basename($_SERVER['PHP_SELF'], '.php');

    switch ($current_page) {
        case 'index':
            echo '<link rel="stylesheet" href="assets/css/pages/home.css">';
            break;
        case 'login':
            echo '<link rel="stylesheet" href="assets/css/pages/forms.css">';
            break;
        case 'register':
            echo '<link rel="stylesheet" href="assets/css/pages/forms.css">';
            echo '<link rel="stylesheet" href="assets/css/pages/register.css">';
            break;
        case 'my_forms':
            echo '<link rel="stylesheet" href="assets/css/pages/forms.css">';
            echo '<link rel="stylesheet" href="assets/css/pages/my-forms.css">';
            break;
        case 'my_answers':
            echo '<link rel="stylesheet" href="assets/css/pages/my-answers.css">';
            break;
        case 'view_submission':
            echo '<link rel="stylesheet" href="assets/css/pages/view-submission.css">';
            break;
        case 'view_responses':
            echo '<link rel="stylesheet" href="assets/css/pages/view-responses.css">';
            break;
        case 'create_form':
        case 'edit_form':
            echo '<link rel="stylesheet" href="assets/css/pages/forms.css">';
            echo '<link rel="stylesheet" href="assets/css/pages/form-builder.css">';
            break;
        case 'fill_form':
            echo '<link rel="stylesheet" href="assets/css/pages/form-fill.css">';
            break;
        case 'preview_form':
            echo '<link rel="stylesheet" href="assets/css/pages/form-fill.css">';
            echo '<link rel="stylesheet" href="assets/css/pages/preview.css">';
            break;
        case 'publish_form':
            echo '<link rel="stylesheet" href="assets/css/pages/publish-form.css">';
            break;
        case 'user_submissions_chart':
            echo '<link rel="stylesheet" href="assets/css/pages/user-submissions-chart.css">';
            break;
        case 'form_submissions_chart':
            echo '<link rel="stylesheet" href="assets/css/pages/form-submissions-chart.css">';
            break;
        default:
            break;
    }
    ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <?php if (isset($page_specific_styles)) {
        echo $page_specific_styles;
    } ?>
</head>

<body>
    <nav>
        <div class="nav-brand">
            <a href="index.php" class="logo">Formica</a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])) : ?>
                <a href="create_form.php">Create Form</a>
                <a href="my_forms.php">My Forms</a>
                <a href="my_answers.php">My Answers</a>
                <a href="logout.php">Logout</a>
            <?php else : ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <hr>
