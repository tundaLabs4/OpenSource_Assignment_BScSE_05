<?php
include("connect.php"); // must define $conn (MySQLi)
$conn = getConnection();
$databasename = "open_source_project";

/**
 * =========================
 * CREATE DATABASE
 * =========================
 */
$conn->query("CREATE DATABASE IF NOT EXISTS `$databasename`");
$conn->select_db($databasename);

/**
 * =========================
 * CREATE TABLES
 * =========================
 */

$queries = [

    "CREATE TABLE IF NOT EXISTS category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cat VARCHAR(150) NOT NULL DEFAULT ''
)",

    "CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL DEFAULT '',
    date VARCHAR(40) NOT NULL DEFAULT '',
    des TEXT NOT NULL,
    cat TEXT NOT NULL,
    status VARCHAR(150) NOT NULL DEFAULT '',
    sort INT NOT NULL DEFAULT 0,
    private TINYINT(1) NOT NULL DEFAULT 0,
    last_changed VARCHAR(50) NOT NULL DEFAULT '',
    last_user VARCHAR(50) NOT NULL DEFAULT ''
)",

    "CREATE TABLE IF NOT EXISTS status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(200) NOT NULL DEFAULT ''
)",

    "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL DEFAULT '',
    password TEXT NOT NULL,
    date VARCHAR(20) NOT NULL DEFAULT '',
    ip VARCHAR(45) NOT NULL DEFAULT '',
    admin TINYINT(1) NOT NULL DEFAULT 0
)"

];

foreach ($queries as $sql) {
    if (!$conn->query($sql)) {
        die("Table creation failed: " . $conn->error);
    }
}

/**
 * =========================
 * CREATE DEFAULT ADMIN USER
 * =========================
 */

$defaultPassword = password_hash("a", PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO users (name, password, date, ip, admin)
    VALUES (?, ?, NOW(), '127.0.0.1', 1)
");

$username = "User123";
$stmt->bind_param("ss", $username, $defaultPassword);
$stmt->execute();

/**
 * =========================
 * FINISH MESSAGE
 * =========================
 */

die("
<div class='message message-success'>
    <strong>All tables have been successfully created.</strong>
</div>

<div class='card'>
    <div class='card-body'>

        <p style='margin-bottom:1rem;'>You can log in with:</p>

        <div style='background:#f8fafc; padding:1rem; border-radius:8px; font-family:monospace; margin-bottom:1rem;'>
            <strong>Username:</strong> User123<br>
            <strong>Password:</strong> a
        </div>

        <div class='security-notice'>
            IMPORTANT: Delete or rename this file immediately for security reasons.
        </div>
    </div>
</div>
");
?>