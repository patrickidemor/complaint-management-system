<?php
require_once "../config/db.php";
require_once "../includes/auth.php";
require_admin_login();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_user"])) {
    $uid = (int) $_POST["user_id"];
    $conn->query("DELETE FROM users WHERE user_id = $uid");
    $message = "User account removed.";
}

$users = $conn->query("
    SELECT u.user_id, u.full_name, u.email, u.phone, u.reg_date,
           COUNT(c.complaint_id) AS complaint_count
    FROM users u
    LEFT JOIN complaints c ON u.user_id = c.user_id
    GROUP BY u.user_id
    ORDER BY u.reg_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Users</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include "../includes/navbar_admin.php"; ?>

<div class="container">
    <h2 class="page-title">Registered Users</h2>
    <?php if ($message): ?><p class="notice success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>

    <table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Complaints Filed</th><th>Registered</th><th>Action</th></tr>
        <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $u["user_id"]; ?></td>
                <td><?php echo htmlspecialchars($u["full_name"]); ?></td>
                <td><?php echo htmlspecialchars($u["email"]); ?></td>
                <td><?php echo htmlspecialchars($u["phone"]); ?></td>
                <td><?php echo $u["complaint_count"]; ?></td>
                <td><?php echo date("d M Y", strtotime($u["reg_date"])); ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Remove this user account? This cannot be undone.');">
                        <input type="hidden" name="user_id" value="<?php echo $u["user_id"]; ?>">
                        <button type="submit" name="delete_user" class="btn btn-danger btn-small">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<footer>&copy; <?php echo date("Y"); ?> University of Port Harcourt — Complaint Management System</footer>
</body>
</html>
