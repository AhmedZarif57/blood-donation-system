<?php
include 'auth.php';
include '../includes/db_connect.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if ($full_name === '' || $username === '' || $password === '') {
        $error = 'Please complete all required fields.';
    } else {
        $check = $conn->prepare('SELECT admin_id FROM Admin WHERE username = ? LIMIT 1');
        $check->bind_param('s', $username);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            $error = 'That username is already in use.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO Admin (full_name, username, password_hash, phone) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $full_name, $username, $hash, $phone);
            if ($stmt->execute()) {
                $message = 'Administrator created successfully.';
            } else {
                $error = 'Could not create admin. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="page-shell">
    <div class="page-shell">
        <section class="page-hero">
            <span class="eyebrow">Admin create</span>
            <h1 class="page-title">Create Admin</h1>
            <p>Use the existing admin authentication system to create another admin with the same protected workflow.</p>
        </section>

        <section class="section-block grid grid-2">
            <div class="content-card">
                <p class="helper-text">Only an authenticated admin can add another admin. Do not expose this page to public users.</p>
            </div>
            <div class="content-card">
                <?php if ($message !== '') { ?><div class="message-box message-success" style="margin-bottom:12px;"><?= htmlspecialchars($message) ?></div><?php } ?>
                <?php if ($error !== '') { ?><div class="message-box message-error" style="margin-bottom:12px;"><?= htmlspecialchars($error) ?></div><?php } ?>
                <form method="POST">
                    <div>
                        <label>Full Name</label>
                        <input type="text" name="full_name" required placeholder="Enter full name">
                    </div>
                    <div>
                        <label>Username</label>
                        <input type="text" name="username" required placeholder="Enter username">
                    </div>
                    <div>
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="Enter password">
                    </div>
                    <div>
                        <label>Phone</label>
                        <input type="text" name="phone" placeholder="Optional contact number">
                    </div>
                    <div class="actions">
                        <button type="submit" class="button-primary">Create Admin</button>
                        <a class="button-outline" href="manage_admins.php">Back</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
