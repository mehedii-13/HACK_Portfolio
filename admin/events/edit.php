<?php


require_once __DIR__ . '/../../includes/db.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /hack/admin/events/');
    exit;
}

try {
    $pdo   = get_db();
    $fetch = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $fetch->execute([$id]);
    $event = $fetch->fetch();
} catch (PDOException $e) {
    $event = null;
}

if (!$event) {
    header('Location: /hack/admin/events/');
    exit;
}

$admin_page_title = 'Edit Event';
$admin_current    = 'events';
require_once __DIR__ . '/../../includes/admin_header.php';

$errors = [];

$event_types = [
    'meeting'          => 'Meeting',
    'class'            => 'Class',
    'workshop'         => 'Workshop',
    'project_showcase' => 'Project Showcase',
    'competition'      => 'Competition',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $type        = $_POST['type'] ?? 'meeting';
    $event_date  = $_POST['event_date'] ?? '';
    $event_time  = $_POST['event_time'] ?? '';
    $venue       = trim($_POST['venue'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_name  = $event['image']; // keep existing by default

    if (!$title)                               $errors[] = 'Event title is required.';
    if (!array_key_exists($type, $event_types)) $errors[] = 'Invalid event type.';
    if (!$event_date)                          $errors[] = 'Event date is required.';

    // Remove image if checkbox ticked
    if (!empty($_POST['remove_image']) && $image_name) {
        $path = UPLOAD_DIR . $image_name;
        if (file_exists($path)) unlink($path);
        $image_name = null;
    }

    // Upload new image if provided
    if (!empty($_FILES['image']['name'])) {
        $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $max_bytes = 3 * 1024 * 1024;

        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'Image must be JPG, PNG, WEBP or GIF.';
        } elseif ($_FILES['image']['size'] > $max_bytes) {
            $errors[] = 'Image must be under 3 MB.';
        } else {
            $new_name = uniqid('ev_', true) . '.' . $ext;
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $new_name)) {
                // Delete old image
                if ($image_name && file_exists(UPLOAD_DIR . $image_name)) {
                    unlink(UPLOAD_DIR . $image_name);
                }
                $image_name = $new_name;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo->prepare(
                'UPDATE events
                 SET title=?, type=?, event_date=?, event_time=?, venue=?, description=?, image=?
                 WHERE id=?'
            )->execute([
                $title, $type, $event_date,
                $event_time ?: null,
                $venue ?: null,
                $description ?: null,
                $image_name,
                $id,
            ]);
            header('Location: /hack/admin/events/?updated=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    // Update $event array for the form to show corrected values
    $event = array_merge($event, compact('title', 'type', 'event_date', 'event_time', 'venue', 'description'));
    $event['image'] = $image_name;
}
?>

<div class="page-header">
    <div>
        <h1 class="page-header__title">Edit Event</h1>
        <p class="page-header__sub">Editing: <?= htmlspecialchars($event['title']) ?></p>
    </div>
    <a href="/hack/admin/events/" class="btn btn--secondary">← Back to Events</a>
</div>

<?php if ($errors): ?>
    <div class="alert alert--error">
        <?php foreach ($errors as $err): ?>
            <div>• <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-form-card" id="edit-event-form">
    <div class="form-grid">

        <div class="form-group full">
            <label class="form-label" for="title">Event Title *</label>
            <input class="form-control" type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($event['title']) ?>" />
        </div>

        <div class="form-group">
            <label class="form-label" for="type">Event Type *</label>
            <select class="form-control" id="type" name="type" required>
                <?php foreach ($event_types as $val => $label): ?>
                    <option value="<?= $val ?>"
                        <?= $event['type'] === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="event_date">Date *</label>
            <input class="form-control" type="date" id="event_date" name="event_date" required
                   value="<?= htmlspecialchars($event['event_date']) ?>" />
        </div>

        <div class="form-group">
            <label class="form-label" for="event_time">Time</label>
            <input class="form-control" type="time" id="event_time" name="event_time"
                   value="<?= htmlspecialchars($event['event_time'] ?? '') ?>" />
        </div>

        <div class="form-group">
            <label class="form-label" for="venue">Venue</label>
            <input class="form-control" type="text" id="venue" name="venue"
                   value="<?= htmlspecialchars($event['venue'] ?? '') ?>" />
        </div>

        <div class="form-group full">
            <label class="form-label" for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group full">
            <label class="form-label">Event Image</label>

            <?php if (!empty($event['image'])): ?>
                <div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.75rem;">
                    <img src="<?= UPLOAD_URL . htmlspecialchars($event['image'], ENT_QUOTES) ?>"
                         alt="Current event image"
                         style="height:64px; border-radius:6px; border:1px solid var(--border); object-fit:cover;" />
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.82rem; color:var(--text-muted); cursor:pointer; font-family:var(--font-head);">
                        <input type="checkbox" name="remove_image" value="1"
                               style="accent-color:var(--accent); width:14px; height:14px;" />
                        Remove current image
                    </label>
                </div>
            <?php endif; ?>

            <input class="form-control" type="file" id="image" name="image" accept="image/*" />
            <span class="form-hint">Upload a new image to replace the existing one (JPG/PNG/WEBP · max 3 MB).</span>
        </div>

    </div><!-- /.form-grid -->

    <div class="form-actions">
        <button type="submit" class="btn btn--primary">Save Changes</button>
        <a href="/hack/admin/events/" class="btn btn--secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
