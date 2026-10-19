<?php
// Run this file ONCE in your browser to create the first administrator
// account, then delete it (or move it out of htdocs) for security.
require_once "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($name && $email && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO administrators (department_id, full_name, email, password) VALUES (1, ?, ?, ?)"
        );
        $stmt->bind_param("sss", $name, $email, $hash);
        if ($stmt->execute()) {
            $message = "Administrator account created successfully. You can now delete setup_admin.php and log in from admin/login.php.";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Administrator Account</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="form-wrapper">
    <h2>One-Time Admin Setup</h2>
    <?php if ($message): ?><p class="notice"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="full_name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Create Admin Account</button>
    </form>
</div>
</body>
</html>
