<?php

require_once __DIR__ . '/../../includes/db.php';

$admin_page_title = 'Add Competition';
$admin_current    = 'competitions';
require_once __DIR__ . '/../../includes/admin_header.php';

$errors = [];
$post   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post        = $_POST;
    $title       = trim($post['title'] ?? '');
    $organizer   = trim($post['organizer'] ?? '');
    $description = trim($post['description'] ?? '');
    $deadline    = $post['deadline'] ?? '';
    $event_url   = trim($post['event_url'] ?? '');

    if (!$title) $errors[] = 'Competition title is required.';
    if ($event_url && !filter_var($event_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'The URL format is invalid. It must start with https:// or http://';
    }

    if (empty($errors)) {
        try {
            get_db()->prepare(
                'INSERT INTO competitions (title, organizer, description, deadline, event_url)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([
                $title,
                $organizer   ?: null,
                $description ?: null,
                $deadline    ?: null,
                $event_url   ?: null,
            ]);
            header('Location: /hack/admin/competitions/?created=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-header__title">Add Competition</h1>
        <p class="page-header__sub">List an upcoming external competition for club members.</p>
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

<form method="post" class="admin-form-card" id="create-competition-form">
    <div class="form-grid">

        <div class="form-group full">
            <label class="form-label" for="title">Competition Title *</label>
            <input class="form-control" type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                   placeholder="e.g. IEEE Region 10 Robotics Challenge 2025" />
        </div>

        <div class="form-group">
            <label class="form-label" for="organizer">Organizer</label>
            <input class="form-control" type="text" id="organizer" name="organizer"
                   value="<?= htmlspecialchars($post['organizer'] ?? '') ?>"
                   placeholder="e.g. IEEE Bangladesh Section" />
        </div>

        <div class="form-group">
            <label class="form-label" for="deadline">Registration Deadline</label>
            <input class="form-control" type="date" id="deadline" name="deadline"
                   value="<?= htmlspecialchars($post['deadline'] ?? '') ?>" />
        </div>

        <div class="form-group full">
            <label class="form-label" for="event_url">Competition URL</label>
            <input class="form-control" type="url" id="event_url" name="event_url"
                   value="<?= htmlspecialchars($post['event_url'] ?? '') ?>"
                   placeholder="https://competition-website.com" />
        </div>

        <div class="form-group full">
            <label class="form-label" for="description">
                Description
                <span style="font-weight:400; color:var(--text-muted)">(prizes, eligibility, theme…)</span>
            </label>
            <textarea class="form-control" id="description" name="description" rows="4"
                      placeholder="Brief description about the competition, prizes offered, and eligibility criteria..."><?= htmlspecialchars($post['description'] ?? '') ?></textarea>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn--primary">Add Competition →</button>
        <a href="/hack/admin/competitions/" class="btn btn--secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
