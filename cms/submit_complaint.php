<?php
require_once "config/db.php";
require_once "includes/auth.php";
require_user_login();

$user_id = $_SESSION["user_id"];
$error = ""; $success = "";
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_id = (int) $_POST["category_id"];
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);

    if (!$category_id || !$title || !$description) {
        $error = "Please fill in all fields.";
    } else {
        // status_id 1 = Submitted
        $stmt = $conn->prepare("INSERT INTO complaints (user_id, category_id, status_id, title, description) VALUES (?, ?, 1, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $category_id, $title, $description);
        if ($stmt->execute()) {
            log_activity($conn, $user_id, "Submitted complaint: " . $title);
            $success = "Your complaint has been submitted successfully. Reference number: #" . $stmt->insert_id;
        } else {
            $error = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Submit Complaint</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include "includes/navbar_user.php"; ?>

<div class="form-wrapper">
    <h2>Submit a Complaint</h2>
    <?php if ($error): ?><p class="notice error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <?php if ($success): ?><p class="notice success"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>
    <form method="POST">
        <label>Complaint Category</label>
        <select name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat["category_id"]; ?>"><?php echo htmlspecialchars($cat["category_name"]); ?></option>
            <?php endwhile; ?>
        </select>
        <label>Complaint Title</label>
        <input type="text" name="title" maxlength="150" required>
        <label>Complaint Details</label>
        <textarea name="description" required></textarea>
        <button type="submit">Submit Complaint</button>
    </form>
</div>

<footer>&copy; <?php echo date("Y"); ?> University of Port Harcourt — Complaint Management System</footer>
</body>
</html>
