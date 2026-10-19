<?php
require_once "../config/db.php";
require_once "../includes/auth.php";
require_admin_login();

$complaint_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$admin_id = $_SESSION["admin_id"];
$message = "";

// Handle form actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["assign"])) {
        $stmt = $conn->prepare("UPDATE complaints SET admin_id = ?, status_id = (SELECT status_id FROM complaint_status WHERE status_name='Assigned') WHERE complaint_id = ?");
        $stmt->bind_param("ii", $admin_id, $complaint_id);
        $stmt->execute();
        $stmt->close();
        $message = "Complaint assigned to you.";
    }

    if (isset($_POST["update_status"])) {
        $new_status = (int) $_POST["status_id"];
        $stmt = $conn->prepare("UPDATE complaints SET status_id = ? WHERE complaint_id = ?");
        $stmt->bind_param("ii", $new_status, $complaint_id);
        $stmt->execute();
        $stmt->close();
        $message = "Complaint status updated.";
    }

    if (isset($_POST["respond"])) {
        $text = trim($_POST["response_text"]);
        if ($text) {
            $stmt = $conn->prepare("INSERT INTO responses (complaint_id, admin_id, response_text) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $complaint_id, $admin_id, $text);
            $stmt->execute();
            $stmt->close();
            $message = "Response added.";
        }
    }
}

$complaint = $conn->query("
    SELECT c.*, u.full_name AS user_name, u.email AS user_email,
           cat.category_name, s.status_name
    FROM complaints c
    JOIN users u ON c.user_id = u.user_id
    JOIN categories cat ON c.category_id = cat.category_id
    JOIN complaint_status s ON c.status_id = s.status_id
    WHERE c.complaint_id = $complaint_id
")->fetch_assoc();

if (!$complaint) {
    die("Complaint not found.");
}

$statuses = $conn->query("SELECT status_id, status_name FROM complaint_status ORDER BY status_id");
$responses = $conn->query("
    SELECT r.response_text, r.response_date, a.full_name
    FROM responses r JOIN administrators a ON r.admin_id = a.admin_id
    WHERE r.complaint_id = $complaint_id ORDER BY r.response_date ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Complaint #<?php echo $complaint_id; ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include "../includes/navbar_admin.php"; ?>

<div class="container">
    <p><a href="manage_complaints.php">&larr; Back to all complaints</a></p>
    <?php if ($message): ?><p class="notice success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>

    <div class="card" style="text-align:left;">
        <div style="display:flex; justify-content:space-between;">
            <h3>#<?php echo $complaint_id; ?> — <?php echo htmlspecialchars($complaint["title"]); ?></h3>
            <span class="status-badge <?php echo status_class($complaint["status_name"]); ?>"><?php echo $complaint["status_name"]; ?></span>
        </div>
        <p style="margin-top:8px; color:#5a6b7b; font-size:0.9rem;">
            Submitted by <?php echo htmlspecialchars($complaint["user_name"]); ?> (<?php echo htmlspecialchars($complaint["user_email"]); ?>)
            on <?php echo date("d M Y, g:i a", strtotime($complaint["date_submitted"])); ?>
            &nbsp;|&nbsp; Category: <?php echo htmlspecialchars($complaint["category_name"]); ?>
        </p>
        <p style="margin-top:12px;"><?php echo nl2br(htmlspecialchars($complaint["description"])); ?></p>
    </div>

    <div class="cards" style="grid-template-columns: 1fr 1fr; margin-top:20px;">
        <div class="card" style="text-align:left;">
            <h4 style="margin-bottom:10px;">Assign / Update Status</h4>
            <?php if (!$complaint["admin_id"]): ?>
                <form method="POST" style="margin-bottom:14px;">
                    <button type="submit" name="assign" class="btn btn-primary">Assign to Me</button>
                </form>
            <?php endif; ?>
            <form method="POST">
                <select name="status_id" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ccd6e0; margin-bottom:10px;">
                    <?php while ($s = $statuses->fetch_assoc()): ?>
                        <option value="<?php echo $s["status_id"]; ?>" <?php echo ($s["status_name"] === $complaint["status_name"]) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($s["status_name"]); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
            </form>
        </div>

        <div class="card" style="text-align:left;">
            <h4 style="margin-bottom:10px;">Add Response</h4>
            <form method="POST">
                <textarea name="response_text" placeholder="Write a response to the complainant..." style="width:100%; min-height:90px; padding:8px; border-radius:6px; border:1px solid #ccd6e0;" required></textarea>
                <button type="submit" name="respond" class="btn btn-primary" style="margin-top:10px;">Send Response</button>
            </form>
        </div>
    </div>

    <h4 class="page-title" style="margin-top:24px;">Response History</h4>
    <?php if ($responses->num_rows === 0): ?>
        <p>No responses yet.</p>
    <?php else: while ($r = $responses->fetch_assoc()): ?>
        <div class="notice" style="text-align:left;">
            <strong><?php echo htmlspecialchars($r["full_name"]); ?></strong> —
            <?php echo date("d M Y, g:i a", strtotime($r["response_date"])); ?><br>
            <?php echo nl2br(htmlspecialchars($r["response_text"])); ?>
        </div>
    <?php endwhile; endif; ?>
</div>

<footer>&copy; <?php echo date("Y"); ?> University of Port Harcourt — Complaint Management System</footer>
</body>
</html>
