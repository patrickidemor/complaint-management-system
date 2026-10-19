<?php
require_once "config/db.php";
$error = ""; $success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if (!$name || !$email || !$password) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hash);
            if ($stmt->execute()) {
                $success = "Registration successful. You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register — Complaint Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="form-wrapper">
    <h2>Create an Account</h2>
    <?php if ($error): ?><p class="notice error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <?php if ($success): ?><p class="notice success"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>
    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="full_name" required>
        <label>Email Address</label>
        <input type="email" name="email" required>
        <label>Phone Number</label>
        <input type="text" name="phone">
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">Register</button>
    </form>
    <p class="switch">Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
