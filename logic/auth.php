<?php

function get_db()
{

    static $db = null;
    if ($db === null) {
        $config = require __DIR__ . '/config.php';
        $dsn = "mysql:host={$config['DB_HOST']};port={$config['DB_PORT']};charset=utf8mb4";
        $username = $config['DB_USERNAME'];
        $password = $config['DB_PASSWORD'];
        $db_name = $config['DB_DATABASE'];
        try {
            $p_db = new PDO($dsn, $username, $password);
            $p_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $p_db->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
            $p_db->exec("USE `$db_name`");
            $db = $p_db;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $db;
}

function init_auth_db()
{

    $db = get_db();
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

function register_user($email, $password)
{

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email.";
    }
    if (strlen($password) < 6) {
        return "Password too short.";
    }

    $db = get_db();
    try {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return "Email already registered.";
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $hash]);
        return "Registration successful.";
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return "An error occurred during registration.";
    }
}

function login_user($email, $password)
{

    $db = get_db();
    try {
        $stmt = $db->prepare("SELECT id, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            return null;
        }
        return "Invalid credentials.";
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return "An error occurred during login.";
    }
}

function current_user_id()
{

    return $_SESSION['user_id'] ?? null;
}

function get_user_email($user_id)
{

    if (!$user_id) {
        return null;
    }

    $db = get_db();
    try {
        $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user['email'] : null;
    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

init_auth_db();