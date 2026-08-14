<?php
include '../includes/db_connect.php';
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare('SELECT admin_id, password_hash FROM Admin WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['admin_id'];
            header('Location: dashboard.php'); exit;
        }
    }
    $error = 'Invalid credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="page-shell" style="display:grid; place-items:center; min-height:100vh;">
    <div class="auth-card" style="width:min(440px, 100%);">
        <span class="eyebrow">Secure access</span>
        <h1 class="page-title" style="margin-bottom:8px;">Admin Login</h1>
        <p>Sign in to manage donors, hospitals, requests, banks, and donation operations.</p>
        <?php if ($error) { ?>
            <div class="message-box message-error" style="margin-top:8px;"><?= htmlspecialchars($error) ?></div>
        <?php } ?>
        <form method="POST" style="margin-top:12px;">
            <div>
                <label>Username</label>
                <input name="username" required autocomplete="username" placeholder="Enter admin username">
            </div>
            <div>
                <label>Password</label>
                <input name="password" type="password" required autocomplete="current-password" placeholder="Enter password">
            </div>
            <button type="submit" class="button-primary">Login</button>
        </form>
    </div>
</main>
</body>
</html>
