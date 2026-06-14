<?php

require_once __DIR__ . '/../../includes/db.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /hack/admin/competitions/');
    exit;
}

try {
    $pdo   = get_db();
    $fetch = $pdo->prepare('SELECT * FROM competitions WHERE id = ?');
    $fetch->execute([$id]);
    $comp  = $fetch->fetch();
} catch (PDOException $e) {
    $comp = null;
}

if (!$comp) {
    header('Location: /hack/admin/competitions/');
    exit;
}

$admin_page_title = 'Edit Competition';
$admin_current    = 'competitions';
require_once __DIR__ . '/../../includes/admin_header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $organizer   = trim($_POST['organizer'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline    = $_POST['deadline'] ?? '';
    $event_url   = trim($_POST['event_url'] ?? '');

    if (!$title) $errors[] = 'Competition title is required.';
    if ($event_url && !filter_var($event_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'The URL format is invalid. It must start with https:// or http://';
    }

    if (empty($errors)) {
        try {
            $pdo->prepare(
                'UPDATE competitions
                 SET title=?, organizer=?, description=?, deadline=?, event_url=?
                 WHERE id=?'
            )->execute([
                $title,
                $organizer   ?: null,
                $description ?: null,
                $deadline    ?: null,
                $event_url   ?: null,
                $id,
            ]);
            header('Location: /hack/admin/competitions/?updated=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    // Re-populate $comp with submitted values on error
    $comp = array_merge($comp, compact('title', 'organizer', 'description', 'deadline', 'event_url'));
}
?>

<div class="page-header">
    <div>
        <h1 class="page-header__title">Edit Competition</h1>
        <p class="page-header__sub"><?= htmlspecialchars($comp['title']) ?></p>
    </div>
    <a href="/hack/admin/competitions/" class="btn btn--secondary">← Back</a>
</div>

<?php if ($errors): ?>
    <div class="alert alert--error">
        <?php foreach ($errors as $err): ?>
            <div>• <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="admin-form-card" id="edit-competition-form">
    <div class="form-grid">

        <div class="form-group full">
            <label class="form-label" for="title">Competition Title *</label>
            <input class="form-control" type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($comp['title']) ?>" />
        </div>

        <div class="form-group">
            <label class="form-label" for="organizer">Organizer</label>
            <input class="form-control" type="text" id="organizer" name="organizer"
                   value="<?= htmlspecialchars($comp['organizer'] ?? '') ?>" />
        </div>

        <div class="form-group">
            <label class="form-label" for="deadline">Registration Deadline</label>
            <input class="form-control" type="date" id="deadline" name="deadline"
                   value="<?= htmlspecialchars($comp['deadline'] ?? '') ?>" />
        </div>

        <div class="form-group full">
            <label class="form-label" for="event_url">Competition URL</label>
            <input class="form-control" type="url" id="event_url" name="event_url"
                   value="<?= htmlspecialchars($comp['event_url'] ?? '') ?>"
                   placeholder="https://..." />
        </div>

        <div class="form-group full">
            <label class="form-label" for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($comp['description'] ?? '') ?></textarea>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn--primary">Save Changes</button>
        <a href="/hack/admin/competitions/" class="btn btn--secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
