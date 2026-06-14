<?php


define('SETUP_KEY', 'setup2025');

$provided = $_GET['key'] ?? '';
if ($provided !== SETUP_KEY) {
    http_response_code(403);
    die('<!DOCTYPE html><html><body style="font-family:monospace;background:#05050d;color:#f87171;padding:2rem"><h2>403 — Access Denied</h2><p>Visit with <code>?key=setup2025</code></p></body></html>');
}

$host = 'localhost';
$user = 'root';
$pass = '';      // XAMPP default

$log = [];

try {
    // ── Connect without selecting a DB
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $log[] = ' Connected to MySQL.';

    // ── Run schema (split on semicolons)
    $sql = file_get_contents(__DIR__ . '/hackclub_db.sql');
    // Strip ALL -- comment lines first so they don't contaminate the next statement
    $sql = preg_replace('/^[ \t]*--.*$/m', '', $sql);
    // Split on semicolons, trim whitespace, skip blanks
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        $pdo->exec($stmt);
    }
    $log[] = ' Database <strong>hackclub_db</strong> and all tables created.';

    // ── Re-connect to hackclub_db 
    $pdo = new PDO("mysql:host=$host;dbname=hackclub_db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // ── Seed admin account 
    $adminUser = 'admin';
    $adminPass = 'hack@kuet2025';
    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
    $pdo->prepare('INSERT IGNORE INTO admins (username, password_hash) VALUES (?, ?)')->execute([$adminUser, $hash]);
    $log[] = ' Admin account seeded: <strong>' . $adminUser . '</strong> / <strong>' . $adminPass . '</strong>';

    $status = 'success';
} catch (PDOException $e) {
    $log[] = ' Error: ' . htmlspecialchars($e->getMessage());
    $status = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>HACK Club Setup</title>
<style>
  body { font-family: 'Courier New', monospace; background: #05050d; color: #eeeef5; padding: 2rem; }
  h2 { color: #00f5c4; margin-bottom: 1.5rem; }
  .log { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 1.5rem; line-height: 2; }
  .log div { border-bottom: 1px solid rgba(255,255,255,0.05); padding: 0.4rem 0; }
  .warning { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 8px; padding: 1rem 1.5rem; color: #f87171; margin-top: 1.5rem; }
  a { color: #00f5c4; }
  strong { color: #00f5c4; }
</style>
</head>
<body>
<h2> HACK Club — Setup</h2>
<div class="log">
  <?php foreach ($log as $line): ?>
    <div><?= $line ?></div>
  <?php endforeach; ?>
</div>
<?php if ($status === 'success'): ?>
<div class="warning">
   <strong>Security:</strong> Delete <code>setup.php</code> from your server now!<br>
  <br>→ <a href="/hack/admin/login.php">Go to Admin Login</a>
</div>
<?php endif; ?>
</body>
</html>
