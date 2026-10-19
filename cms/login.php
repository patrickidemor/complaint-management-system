<?php
require_once "config/db.php";
require_once "includes/auth.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, full_name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_name"] = $user["full_name"];
            log_activity($conn, $user["user_id"], "Logged in");
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect email or password.";
        }
    } else {
        $error = "Incorrect email or password.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — Complaint Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="form-wrapper">
    <h2>User Login</h2>
    <?php if ($error): ?><p class="notice error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <form method="POST">
        <label>Email Address</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
    <p class="switch">Don't have an account? <a href="register.php">Register here</a></p>
    <p class="switch"><a href="admin/login.php">Administrator login</a></p>
</div>
</body>
</html>
