<?php

require_once __DIR__ . '/auth.php';

function get_forms_db()
{

    return get_db();
}

function init_forms_db()
{

    $db = get_forms_db();
    $db->exec("CREATE TABLE IF NOT EXISTS forms (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        password VARCHAR(255),
        allow_multiple_submissions BOOLEAN DEFAULT 0,
        require_auth BOOLEAN DEFAULT 0,
        custom_css TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $config = require __DIR__ . '/config.php';
    $db_name = $config['DB_DATABASE'];
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = 'forms' AND column_name = 'require_auth'");
    $stmt->execute([$db_name]);
    if (!$stmt->fetchColumn()) {
        $db->exec("ALTER TABLE forms ADD COLUMN require_auth BOOLEAN DEFAULT 0");
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = 'forms' AND column_name = 'custom_css'");
    $stmt->execute([$db_name]);
    if (!$stmt->fetchColumn()) {
        $db->exec("ALTER TABLE forms ADD COLUMN custom_css TEXT");
    }

    $db->exec("CREATE TABLE IF NOT EXISTS form_fields (
        id INT PRIMARY KEY AUTO_INCREMENT,
        form_id INT NOT NULL,
        type VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        field_order INT NOT NULL,
        is_required BOOLEAN DEFAULT 0,
        FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS form_submissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        form_id INT NOT NULL,
        user_id INT,
        submission_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS form_field_values (
        id INT PRIMARY KEY AUTO_INCREMENT,
        submission_id INT NOT NULL,
        field_id INT NOT NULL,
        value TEXT,
        FOREIGN KEY (submission_id) REFERENCES form_submissions(id) ON DELETE CASCADE,
        FOREIGN KEY (field_id) REFERENCES form_fields(id) ON DELETE CASCADE
    )");
}

function create_form($user_id, $name, $description, $password, $allow_multiple_submissions, $require_auth = 0)
{

    $db = get_forms_db();
    $stmt = $db->prepare("INSERT INTO forms (user_id, name, description, password, allow_multiple_submissions, require_auth) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $name, $description, $password, $allow_multiple_submissions ? 1 : 0, $require_auth ? 1 : 0]);
    return $db->lastInsertId();
}

function add_form_field($form_id, $type, $name, $field_order, $is_required)
{

    $db = get_forms_db();
    $stmt = $db->prepare("INSERT INTO form_fields (form_id, type, name, field_order, is_required) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$form_id, $type, $name, $field_order, $is_required ? 1 : 0]);
    return $db->lastInsertId();
}

function get_form($form_id)
{

    $db = get_forms_db();
    $stmt = $db->prepare("SELECT * FROM forms WHERE id = ?");
    $stmt->execute([$form_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_forms($user_id)
{

    $db = get_forms_db();
    $stmt = $db->prepare("SELECT * FROM forms WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_form_fields($form_id)
{

    $db = get_forms_db();
    $stmt = $db->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order ASC");
    $stmt->execute([$form_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function delete_form_field($field_id)
{

    $db = get_forms_db();
    $stmt = $db->prepare("DELETE FROM form_fields WHERE id = ?");
    $stmt->execute([$field_id]);
    return $stmt->rowCount() > 0;
}

function update_form_css($form_id, $custom_css)
{

    $db = get_forms_db();
    $stmt = $db->prepare("UPDATE forms SET custom_css = ? WHERE id = ?");
    $stmt->execute([$custom_css, $form_id]);
    return $stmt->rowCount() > 0;
}

function update_field_order($field_id, $new_order)
{

    $db = get_forms_db();
    $stmt = $db->prepare("UPDATE form_fields SET field_order = ? WHERE id = ?");
    $stmt->execute([$new_order, $field_id]);
    return $stmt->rowCount() > 0;
}

function submit_form($form_id, $field_values, $user_id = null)
{

    $db = get_forms_db();
    try {
        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO form_submissions (form_id, user_id) VALUES (?, ?)");
        $stmt->execute([$form_id, $user_id]);
        $submission_id = $db->lastInsertId();
        $stmt = $db->prepare("INSERT INTO form_field_values (submission_id, field_id, value) VALUES (?, ?, ?)");
        foreach ($field_values as $field_id => $value) {
            $stmt->execute([$submission_id, $field_id, $value]);
        }

        $db->commit();
        return $submission_id;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

function has_user_submitted_form($form_id, $user_id)
{

    $db = get_forms_db();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM form_submissions WHERE form_id = ? AND user_id = ?");
    $stmt->execute([$form_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}

function get_form_submissions($form_id)
{

    $db = get_forms_db();
    $stmt = $db->prepare("SELECT s.*, u.email as user_email 
                         FROM form_submissions s 
                         LEFT JOIN users u ON s.user_id = u.id
                         WHERE s.form_id = ? 
                         ORDER BY s.submission_time DESC");
    $stmt->execute([$form_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_submission_values($submission_id)
{

    $db = get_forms_db();
    $stmt = $db->prepare("SELECT v.*, f.name as field_name, f.type as field_type
                         FROM form_field_values v
                         JOIN form_fields f ON v.field_id = f.id
                         WHERE v.submission_id = ?");
    $stmt->execute([$submission_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_submission($submission_id)
{

    $db = get_forms_db();
    $stmt = $db->prepare("SELECT s.*, f.name as form_name, f.user_id as form_owner_id, u.email as user_email
                         FROM form_submissions s
                         JOIN forms f ON s.form_id = f.id
                         LEFT JOIN users u ON s.user_id = u.id
                         WHERE s.id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$submission) {
        return null;
    }

    $values = get_submission_values($submission_id);
    $submission['values'] = $values;
    return $submission;
}

function get_user_submissions($user_id)
{

    $db = get_forms_db();
    $stmt = $db->prepare("SELECT s.*, f.name as form_name
                         FROM form_submissions s
                         JOIN forms f ON s.form_id = f.id
                         WHERE s.user_id = ?
                         ORDER BY s.submission_time DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_submission_preview_values($submission_id, $limit = 3)
{

    $db = get_forms_db();
    $stmt = $db->prepare("SELECT v.*, f.name as field_name, f.type as field_type
                         FROM form_field_values v
                         JOIN form_fields f ON v.field_id = f.id
                         WHERE v.submission_id = ?
                         LIMIT ?");
    $stmt->execute([$submission_id, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

init_forms_db();
