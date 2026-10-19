<?php
// Session + access control helpers, included at the top of protected pages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_user_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: ../login.php");
        exit();
    }
}

function require_admin_login() {
    if (!isset($_SESSION["admin_id"])) {
        header("Location: login.php");
        exit();
    }
}

function log_activity($conn, $user_id, $action) {
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
    $stmt->close();
}

function status_class($status_name) {
    return "status-" . str_replace(" ", "-", $status_name);
}
