<?php
session_start();

/**
 * =========================
 * VALIDATE ID EXISTS
 * =========================
 */
function checkid($conn, $id)
{
    if (!is_numeric($id)) {
        die('<div class="message message-error">Invalid ID format.</div>');
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die('<div class="message message-error">No item exists with that ID.</div>');
    }
}

/**
 * =========================
 * CHECK OWNERSHIP (USER SECURITY)
 * =========================
 */
function ownedit($conn, $id)
{
    if (($_SESSION['admin'] ?? 0) != 1) {

        $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row || $row['name'] !== ($_SESSION['user'] ?? '')) {
            die('<div class="message message-error">You are only allowed to edit your own settings.</div>');
        }
    }
}

/**
 * =========================
 * INPUT CLEANING (BASIC SANITIZATION)
 * =========================
 */
function clean($string)
{
    if ($string === null) {
        return '';
    }

    return trim(htmlspecialchars($string, ENT_QUOTES, 'UTF-8'));
}

/**
 * =========================
 * ERROR HANDLING
 * =========================
 */
function err($field)
{
    die('<div class="message message-error">Invalid or missing input for: ' . $field . '</div>');
}

/**
 * =========================
 * ADMIN CHECK
 * =========================
 */
function admin()
{
    if (($_SESSION['admin'] ?? 0) != 1) {
        die('<div class="message message-error">Sorry, only admins can perform this action.</div>');
    }
}
?>
