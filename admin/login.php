<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Already logged in → redirect to dashboard
if (is_admin_logged_in()) {
    header('Location: /hack/admin/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            $pdo  = get_db();
            $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin']    = ['id' => $admin['id'], 'username' => $admin['username']];
                session_regenerate_id(true); // prevent session fixation
                header('Location: /hack/admin/');
                exit;
            } else {
                $error = 'Incorrect username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error — make sure setup.php has been run.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login — HACK Club KUET</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/hack/assets/css/admin.css" />
</head>
<body>

<div class="login-wrap">
    <div class="login-card">
        <!-- Brand -->
        <div class="login-card__brand">
            <span class="sidebar__brand-mark">HACK</span>
            <span style="font-family:var(--font-head); font-size:0.9rem; color:var(--text-muted);">
                Admin Panel
            </span>
        </div>

        <h1 class="login-card__title">Welcome back</h1>
        <p class="login-card__sub">Sign in to manage the HACK Club portal.</p>

        <?php if ($error): ?>
            <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" id="login-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" type="text" id="username" name="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autocomplete="username" required placeholder="admin" />
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password"
                       autocomplete="current-password" required placeholder="••••••••" />
            </div>
            <button type="submit" class="btn btn--primary"
                    style="width:100%; justify-content:center; margin-top:1rem;">
                Sign In →
            </button>
        </form>

        <p style="margin-top:1.5rem; text-align:center; font-size:0.8rem; color:var(--text-muted);">
            <a href="/hack/" style="color:var(--text-muted); transition:color 0.2s;"
               onmouseover="this.style.color='var(--accent)'"
               onmouseout="this.style.color='var(--text-muted)'">
                ← Back to public site
            </a>
        </p>
    </div>
</div>

</body>
</html>
