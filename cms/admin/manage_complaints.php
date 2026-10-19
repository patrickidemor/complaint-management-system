<?php
require_once "../config/db.php";
require_once "../includes/auth.php";
require_admin_login();

$status_filter = isset($_GET["status"]) ? $_GET["status"] : "";
$statuses = $conn->query("SELECT status_id, status_name FROM complaint_status ORDER BY status_id");

$sql = "
    SELECT c.complaint_id, c.title, u.full_name, cat.category_name, s.status_name,
           c.date_submitted, a.full_name AS admin_name
    FROM complaints c
    JOIN users u ON c.user_id = u.user_id
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN complaint_status s ON c.status_id = s.status_id
    LEFT JOIN administrators a ON c.admin_id = a.admin_id
";
if ($status_filter !== "") {
    $sql .= " WHERE s.status_name = '" . $conn->real_escape_string($status_filter) . "'";
}
$sql .= " ORDER BY c.date_submitted DESC";
$complaints = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Complaints</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include "../includes/navbar_admin.php"; ?>

<div class="container">
    <h2 class="page-title">Manage Complaints</h2>

    <form method="GET" style="margin-bottom:16px;">
        <label style="font-size:0.85rem;">Filter by status:</label>
        <select name="status" onchange="this.form.submit()" style="padding:6px 10px; border-radius:6px; border:1px solid #ccd6e0;">
            <option value="">All</option>
            <?php while ($s = $statuses->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($s["status_name"]); ?>" <?php echo ($status_filter === $s["status_name"]) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($s["status_name"]); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <table>
        <tr><th>ID</th><th>Title</th><th>Submitted By</th><th>Category</th><th>Assigned To</th><th>Status</th><th>Date</th><th>Action</th></tr>
        <?php if ($complaints->num_rows === 0): ?>
            <tr><td colspan="8">No complaints found.</td></tr>
        <?php else: while ($c = $complaints->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $c["complaint_id"]; ?></td>
                <td><?php echo htmlspecialchars($c["title"]); ?></td>
                <td><?php echo htmlspecialchars($c["full_name"]); ?></td>
                <td><?php echo htmlspecialchars($c["category_name"]); ?></td>
                <td><?php echo $c["admin_name"] ? htmlspecialchars($c["admin_name"]) : "— Unassigned —"; ?></td>
                <td><span class="status-badge <?php echo status_class($c["status_name"]); ?>"><?php echo $c["status_name"]; ?></span></td>
                <td><?php echo date("d M Y", strtotime($c["date_submitted"])); ?></td>
                <td><a class="btn btn-primary btn-small" href="complaint_detail.php?id=<?php echo $c["complaint_id"]; ?>">Manage</a></td>
            </tr>
        <?php endwhile; endif; ?>
    </table>
</div>

<footer>&copy; <?php echo date("Y"); ?> University of Port Harcourt — Complaint Management System</footer>
</body>
</html>
