<?php
require_once "../config/db.php";
require_once "../includes/auth.php";
require_admin_login();

$total = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()["c"];
$unresolved = $conn->query("SELECT COUNT(*) AS c FROM complaints c JOIN complaint_status s ON c.status_id=s.status_id WHERE s.status_name NOT IN ('Resolved','Closed')")->fetch_assoc()["c"];
$resolved = $conn->query("SELECT COUNT(*) AS c FROM complaints c JOIN complaint_status s ON c.status_id=s.status_id WHERE s.status_name = 'Resolved'")->fetch_assoc()["c"];
$users_count = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()["c"];

$recent = $conn->query("
    SELECT c.complaint_id, c.title, u.full_name, c.date_submitted, s.status_name
    FROM complaints c
    JOIN users u ON c.user_id = u.user_id
    JOIN complaint_status s ON c.status_id = s.status_id
    ORDER BY c.date_submitted DESC LIMIT 8
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include "../includes/navbar_admin.php"; ?>

<div class="container">
    <h2 class="page-title">Welcome, <?php echo htmlspecialchars($_SESSION["admin_name"]); ?></h2>

    <div class="cards">
        <div class="card"><div class="num"><?php echo $total; ?></div><div class="label">Total Complaints</div></div>
        <div class="card"><div class="num"><?php echo $unresolved; ?></div><div class="label">Unresolved</div></div>
        <div class="card"><div class="num"><?php echo $resolved; ?></div><div class="label">Resolved</div></div>
        <div class="card"><div class="num"><?php echo $users_count; ?></div><div class="label">Registered Users</div></div>
    </div>

    <h3 class="page-title">Recent Complaints</h3>
    <table>
        <tr><th>ID</th><th>Title</th><th>Submitted By</th><th>Date</th><th>Status</th></tr>
        <?php while ($row = $recent->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row["complaint_id"]; ?></td>
                <td><?php echo htmlspecialchars($row["title"]); ?></td>
                <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
                <td><?php echo date("d M Y", strtotime($row["date_submitted"])); ?></td>
                <td><span class="status-badge <?php echo status_class($row["status_name"]); ?>"><?php echo $row["status_name"]; ?></span></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<footer>&copy; <?php echo date("Y"); ?> University of Port Harcourt — Complaint Management System</footer>
</body>
</html>
