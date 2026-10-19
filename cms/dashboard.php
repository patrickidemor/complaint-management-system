<?php
require_once "config/db.php";
require_once "includes/auth.php";
require_user_login();

$user_id = $_SESSION["user_id"];

$total = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE user_id = $user_id")->fetch_assoc()["c"];
$resolved = $conn->query("SELECT COUNT(*) AS c FROM complaints c JOIN complaint_status s ON c.status_id = s.status_id WHERE c.user_id = $user_id AND s.status_name = 'Resolved'")->fetch_assoc()["c"];
$pending = $conn->query("SELECT COUNT(*) AS c FROM complaints c JOIN complaint_status s ON c.status_id = s.status_id WHERE c.user_id = $user_id AND s.status_name NOT IN ('Resolved','Closed')")->fetch_assoc()["c"];

$recent = $conn->query("
    SELECT c.complaint_id, c.title, c.date_submitted, s.status_name
    FROM complaints c JOIN complaint_status s ON c.status_id = s.status_id
    WHERE c.user_id = $user_id
    ORDER BY c.date_submitted DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard — Complaint Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include "includes/navbar_user.php"; ?>

<div class="container">
    <h2 class="page-title">Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?></h2>

    <div class="cards">
        <div class="card"><div class="num"><?php echo $total; ?></div><div class="label">Total Complaints</div></div>
        <div class="card"><div class="num"><?php echo $pending; ?></div><div class="label">Pending / In Progress</div></div>
        <div class="card"><div class="num"><?php echo $resolved; ?></div><div class="label">Resolved</div></div>
    </div>

    <h3 class="page-title">Recent Complaints</h3>
    <table>
        <tr><th>ID</th><th>Title</th><th>Date Submitted</th><th>Status</th></tr>
        <?php if ($recent->num_rows === 0): ?>
            <tr><td colspan="4">You have not submitted any complaints yet.</td></tr>
        <?php else: while ($row = $recent->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row["complaint_id"]; ?></td>
                <td><?php echo htmlspecialchars($row["title"]); ?></td>
                <td><?php echo date("d M Y, g:i a", strtotime($row["date_submitted"])); ?></td>
                <td><span class="status-badge <?php echo status_class($row["status_name"]); ?>"><?php echo $row["status_name"]; ?></span></td>
            </tr>
        <?php endwhile; endif; ?>
    </table>
</div>

<footer>&copy; <?php echo date("Y"); ?> University of Port Harcourt — Complaint Management System</footer>
</body>
</html>
