<?php


require_once __DIR__ . '/../../includes/db.php';

$admin_page_title = 'Create Event';
$admin_current    = 'events';
require_once __DIR__ . '/../../includes/admin_header.php';

$errors = [];
$post   = [];

$event_types = [
    'meeting'          => 'Meeting',
    'class'            => 'Class',
    'workshop'         => 'Workshop',
    'project_showcase' => 'Project Showcase',
    'competition'      => 'Competition',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post        = $_POST;
    $title       = trim($post['title'] ?? '');
    $type        = $post['type'] ?? 'meeting';
    $event_date  = $post['event_date'] ?? '';
    $event_time  = $post['event_time'] ?? '';
    $venue       = trim($post['venue'] ?? '');
    $description = trim($post['description'] ?? '');
    $image_name  = null;

    if (!$title)                           $errors[] = 'Event title is required.';
    if (!array_key_exists($type, $event_types)) $errors[] = 'Invalid event type.';
    if (!$event_date)                      $errors[] = 'Event date is required.';

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $ext        = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed    = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $max_bytes  = 3 * 1024 * 1024; // 3 MB

        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'Image must be JPG, PNG, WEBP or GIF.';
        } elseif ($_FILES['image']['size'] > $max_bytes) {
            $errors[] = 'Image file size must be under 3 MB.';
        } else {
            $image_name = uniqid('ev_', true) . '.' . $ext;
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            if (!move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $image_name)) {
                $errors[] = 'Failed to upload image. Check folder permissions.';
                $image_name = null;
            }
        }
    }

    if (empty($errors)) {
        try {
            get_db()->prepare(
                'INSERT INTO events (title, type, event_date, event_time, venue, description, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $title, $type, $event_date,
                $event_time ?: null,
                $venue ?: null,
                $description ?: null,
                $image_name,
            ]);
            header('Location: /hack/admin/events/?created=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-header__title">New Event</h1>
        <p class="page-header__sub">Fill in the details below to create an event.</p>
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

<form method="post" enctype="multipart/form-data" class="admin-form-card" id="create-event-form">
    <div class="form-grid">

        <!-- Title -->
        <div class="form-group full">
            <label class="form-label" for="title">Event Title *</label>
            <input class="form-control" type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                   placeholder="e.g. FPGA Workshop Session 3" />
        </div>

        <!-- Type -->
        <div class="form-group">
            <label class="form-label" for="type">Event Type *</label>
            <select class="form-control" id="type" name="type" required>
                <?php foreach ($event_types as $val => $label): ?>
                    <option value="<?= $val ?>"
                        <?= ($post['type'] ?? 'meeting') === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Date -->
        <div class="form-group">
            <label class="form-label" for="event_date">Date *</label>
            <input class="form-control" type="date" id="event_date" name="event_date" required
                   value="<?= htmlspecialchars($post['event_date'] ?? '') ?>" />
        </div>

        <!-- Time -->
        <div class="form-group">
            <label class="form-label" for="event_time">Time <span style="font-weight:400;color:var(--text-muted)">(optional)</span></label>
            <input class="form-control" type="time" id="event_time" name="event_time"
                   value="<?= htmlspecialchars($post['event_time'] ?? '') ?>" />
        </div>

        <!-- Venue -->
        <div class="form-group">
            <label class="form-label" for="venue">Venue <span style="font-weight:400;color:var(--text-muted)">(optional)</span></label>
            <input class="form-control" type="text" id="venue" name="venue"
                   value="<?= htmlspecialchars($post['venue'] ?? '') ?>"
                   placeholder="e.g. ECE Lab 3, KUET" />
        </div>

        <!-- Description -->
        <div class="form-group full">
            <label class="form-label" for="description">Description <span style="font-weight:400;color:var(--text-muted)">(optional)</span></label>
            <textarea class="form-control" id="description" name="description" rows="4"
                      placeholder="Brief description of what will happen at this event..."><?= htmlspecialchars($post['description'] ?? '') ?></textarea>
        </div>

        <!-- Image upload -->
        <div class="form-group full">
            <label class="form-label" for="image">
                Event Image
                <span style="font-weight:400;color:var(--text-muted)">(optional · JPG/PNG/WEBP · max 3 MB)</span>
            </label>
            <input class="form-control" type="file" id="image" name="image" accept="image/*" />
        </div>

    </div><!-- /.form-grid -->

    <div class="form-actions">
        <button type="submit" class="btn btn--primary">Create Event →</button>
        <a href="/hack/admin/events/" class="btn btn--secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
