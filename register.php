<?php


require_once __DIR__ . '/includes/db.php';

$errors  = [];
$success = false;
$email   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $department = trim($_POST['department'] ?? '');
    $batch      = trim($_POST['batch']      ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $phone      = trim($_POST['phone']      ?? '');

    if (!$name)                                                   $errors[] = 'Full name is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = 'A valid email address is required.';

    if (empty($errors)) {
        try {
            $pdo = get_db();

            // Check for duplicate email
            $check = $pdo->prepare('SELECT id FROM members WHERE email = ?');
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = 'This email address is already registered.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO members (name, email, department, batch, student_id, phone)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $name,
                    $email,
                    $department ?: null,
                    $batch      ?: null,
                    $student_id ?: null,
                    $phone      ?: null,
                ]);
                $success = true;
            }
        } catch (PDOException $e) {
            $errors[] = 'A database error occurred. Please try again later.';
        }
    }
}

$departments = ['EEE','ECE','CSE','ME','CE','ChE','IPE','MSE','Arch','URP','TE','BME'];

$page_title = 'Join HACK Club — KUET';
require_once __DIR__ . '/includes/header.php';
?>

<main class="page">
    <section class="section">
        <h1 class="section__title">Join HACK Club</h1>
        <p style="color:var(--text-muted); margin-top:-1.75rem; margin-bottom:2.5rem; max-width:500px; font-size:0.95rem; line-height:1.75;">
            Become a member of the Hardware Acceleration Club of KUET.
            Your application will be reviewed and approved by the club admin.
        </p>

        <?php if ($success): ?>
            <!-- ── Success State ── -->
            <div class="form-card" style="max-width:480px; text-align:center; padding:3rem 2rem;">
                <div style="font-size:3rem; margin-bottom:1.25rem;">🎉</div>
                <h2 style="font-family:var(--font-head); font-size:1.35rem; font-weight:700; margin-bottom:0.75rem;">
                    Registration Received!
                </h2>
                <p style="color:var(--text-muted); line-height:1.75; margin-bottom:1.75rem;">
                    Thank you! Your membership request is pending admin review.
                    We'll follow up at
                    <strong style="color:var(--accent)"><?= htmlspecialchars($email) ?></strong>.
                </p>
                <a href="/hack/" class="btn btn--ghost">← Back to Home</a>
            </div>

        <?php else: ?>
            <!-- ── Errors ── -->
            <?php if ($errors): ?>
                <div class="alert alert--error">
                    <?php foreach ($errors as $err): ?>
                        <div>• <?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ── Registration Form ── -->
            <form method="post" class="form-card" novalidate id="register-form">

                <div class="form-group">
                    <label class="form-label" for="name">
                        Full Name <span class="req">*</span>
                    </label>
                    <input class="form-control" type="text" id="name" name="name" required
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="e.g. Rahim Uddin" />
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">
                        University Email <span class="req">*</span>
                    </label>
                    <input class="form-control" type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="student@student.kuet.ac.bd" />
                </div>

                <div class="form-group">
                    <label class="form-label" for="student_id">Student ID</label>
                    <input class="form-control" type="text" id="student_id" name="student_id"
                           value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>"
                           placeholder="e.g. 2001001" />
                </div>

                <div class="form-group">
                    <label class="form-label" for="department">Department</label>
                    <select class="form-control" id="department" name="department">
                        <option value="">— Select Department —</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept ?>" <?= (($_POST['department'] ?? '') === $dept) ? 'selected' : '' ?>>
                                <?= $dept ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="batch">Batch / Admission Year</label>
                    <input class="form-control" type="text" id="batch" name="batch"
                           value="<?= htmlspecialchars($_POST['batch'] ?? '') ?>"
                           placeholder="e.g. 2022" />
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input class="form-control" type="tel" id="phone" name="phone"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           placeholder="01XXXXXXXXX" />
                </div>

                <button type="submit" class="btn btn--primary"
                        style="width:100%; justify-content:center; margin-top:0.5rem;">
                    Submit Registration →
                </button>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
