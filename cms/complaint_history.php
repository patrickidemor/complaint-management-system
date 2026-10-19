<?php
require_once "config/db.php";
require_once "includes/auth.php";
require_user_login();

$user_id = $_SESSION["user_id"];

$complaints = $conn->query("
    SELECT c.complaint_id, c.title, c.description, c.date_submitted,
           cat.category_name, s.status_name,
           a.full_name AS admin_name
    FROM complaints c
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN complaint_status s ON c.status_id = s.status_id
    LEFT JOIN administrators a ON c.admin_id = a.admin_id
    WHERE c.user_id = $user_id
    ORDER BY c.date_submitted DESC
");

// Fetch responses for each complaint
function get_responses($conn, $complaint_id) {
    $res = $conn->query("
        SELECT r.response_text, r.response_date, a.full_name
        FROM responses r JOIN administrators a ON r.admin_id = a.admin_id
        WHERE r.complaint_id = $complaint_id ORDER BY r.response_date ASC
    ");
    return $res;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Complaints</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include "includes/navbar_user.php"; ?>

<div class="container">
    <h2 class="page-title">My Complaint History</h2>

    <?php if ($complaints->num_rows === 0): ?>
        <p>You have not submitted any complaints yet. <a href="submit_complaint.php">Submit one here.</a></p>
    <?php else: while ($c = $complaints->fetch_assoc()): ?>
        <div class="card" style="text-align:left; margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong>#<?php echo $c["complaint_id"]; ?> — <?php echo htmlspecialchars($c["title"]); ?></strong>
                <span class="status-badge <?php echo status_class($c["status_name"]); ?>"><?php echo $c["status_name"]; ?></span>
            </div>
            <p style="margin-top:8px; color:#5a6b7b; font-size:0.9rem;">
                Category: <?php echo htmlspecialchars($c["category_name"]); ?> &nbsp;|&nbsp;
                Submitted: <?php echo date("d M Y, g:i a", strtotime($c["date_submitted"])); ?>
                <?php if ($c["admin_name"]): ?> &nbsp;|&nbsp; Assigned to: <?php echo htmlspecialchars($c["admin_name"]); ?><?php endif; ?>
            </p>
            <p style="margin-top:10px;"><?php echo nl2br(htmlspecialchars($c["description"])); ?></p>

            <?php $responses = get_responses($conn, $c["complaint_id"]); ?>
            <?php if ($responses->num_rows > 0): ?>
                <div style="margin-top:14px; border-top:1px solid #eef1f5; padding-top:10px;">
                    <strong style="font-size:0.85rem;">Responses:</strong>
                    <?php while ($r = $responses->fetch_assoc()): ?>
                        <div style="margin-top:8px; font-size:0.88rem; background:#f7fafd; padding:8px 10px; border-radius:6px;">
                            <em><?php echo htmlspecialchars($r["full_name"]); ?></em> —
                            <?php echo date("d M Y, g:i a", strtotime($r["response_date"])); ?><br>
                            <?php echo nl2br(htmlspecialchars($r["response_text"])); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; endif; ?>
</div>

<footer>&copy; <?php echo date("Y"); ?> University of Port Harcourt — Complaint Management System</footer>
</body>
</html>
