<?php
require_once "../config/db.php";
require_once "../includes/auth.php";
require_admin_login();

$by_status = $conn->query("
    SELECT s.status_name, COUNT(c.complaint_id) AS total
    FROM complaint_status s LEFT JOIN complaints c ON s.status_id = c.status_id
    GROUP BY s.status_id ORDER BY s.status_id
");

$by_category = $conn->query("
    SELECT cat.category_name, COUNT(c.complaint_id) AS total
    FROM categories cat LEFT JOIN complaints c ON cat.category_id = c.category_id
    GROUP BY cat.category_id ORDER BY total DESC
");

$by_department = $conn->query("
    SELECT d.department_name, COUNT(c.complaint_id) AS total
    FROM departments d
    LEFT JOIN administrators a ON d.department_id = a.department_id
    LEFT JOIN complaints c ON a.admin_id = c.admin_id
    GROUP BY d.department_id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include "../includes/navbar_admin.php"; ?>

<div class="container">
    <h2 class="page-title">Complaint Reports</h2>

    <div class="cards" style="grid-template-columns: 1fr 1fr 1fr;">
        <div class="card" style="text-align:left;">
            <h4 style="margin-bottom:10px; color:#1f4e79;">By Status</h4>
            <table>
                <?php while ($r = $by_status->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($r["status_name"]); ?></td><td><?php echo $r["total"]; ?></td></tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="card" style="text-align:left;">
            <h4 style="margin-bottom:10px; color:#1f4e79;">By Category</h4>
            <table>
                <?php while ($r = $by_category->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($r["category_name"]); ?></td><td><?php echo $r["total"]; ?></td></tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="card" style="text-align:left;">
            <h4 style="margin-bottom:10px; color:#1f4e79;">By Department</h4>
            <table>
                <?php while ($r = $by_department->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($r["department_name"]); ?></td><td><?php echo $r["total"]; ?></td></tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

<footer>&copy; <?php echo date("Y"); ?> University of Port Harcourt — Complaint Management System</footer>
</body>
</html>
