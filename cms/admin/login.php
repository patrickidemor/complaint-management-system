<?php
require_once "../config/db.php";
require_once "../includes/auth.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT admin_id, full_name, password FROM administrators WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin["password"])) {
            $_SESSION["admin_id"] = $admin["admin_id"];
            $_SESSION["admin_name"] = $admin["full_name"];
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
<title>Administrator Login</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="form-wrapper">
    <h2>Administrator Login</h2>
    <?php if ($error): ?><p class="notice error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <form method="POST">
        <label>Email Address</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
    <p class="switch"><a href="../login.php">User login</a></p>
</div>
</body>
</html>
