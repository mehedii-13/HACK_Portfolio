<?php


require_once __DIR__ . '/../../includes/db.php';

$admin_page_title = 'Events';
$admin_current    = 'events';
require_once __DIR__ . '/../../includes/admin_header.php';

$flash = null;

// Flash messages from redirects
if (isset($_GET['created'])) $flash = ['type' => 'success', 'msg' => 'Event created successfully.'];
if (isset($_GET['updated'])) $flash = ['type' => 'success', 'msg' => 'Event updated successfully.'];

// Handle delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $pdo  = get_db();
        $find = $pdo->prepare('SELECT image FROM events WHERE id = ?');
        $find->execute([(int) $_POST['delete_id']]);
        $row = $find->fetch();

        // Remove image file if it exists
        if ($row && !empty($row['image'])) {
            $path = UPLOAD_DIR . $row['image'];
            if (file_exists($path)) unlink($path);
        }

        $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([(int) $_POST['delete_id']]);
        header('Location: /hack/admin/events/?deleted=1');
        exit;
    } catch (PDOException $e) {
        $flash = ['type' => 'error', 'msg' => 'Could not delete the event.'];
    }
}

if (isset($_GET['deleted'])) $flash = ['type' => 'success', 'msg' => 'Event deleted.'];

$events = [];
try {
    $events = get_db()->query('SELECT * FROM events ORDER BY event_date DESC, created_at DESC')->fetchAll();
} catch (PDOException $e) {
    $flash = ['type' => 'error', 'msg' => 'Could not load events.'];
}
?>

<div class="page-header">
    <div>
        <h1 class="page-header__title">Events</h1>
        <p class="page-header__sub"><?= count($events) ?> total event<?= count($events) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="/hack/admin/events/create.php" class="btn btn--primary">+ New Event</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert--<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<div class="table-card">
    <?php if (empty($events)): ?>
        <div class="empty-state">
            <span class="empty-state__icon">📅</span>
            <p>
                No events yet.
                <a href="/hack/admin/events/create.php" style="color:var(--accent)">Create your first event →</a>
            </p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $e): ?>
                    <tr>
                        <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?= htmlspecialchars($e['title']) ?>
                        </td>
                        <td>
                            <span class="badge badge--<?= $e['type'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $e['type'])) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($e['event_date'])) ?></td>
                        <td style="color:var(--text-muted)">
                            <?= !empty($e['event_time']) ? date('g:i A', strtotime($e['event_time'])) : '—' ?>
                        </td>
                        <td style="color:var(--text-muted); max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?= htmlspecialchars($e['venue'] ?? '—') ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="/hack/admin/events/edit.php?id=<?= $e['id'] ?>"
                                   class="btn btn--secondary btn--sm">Edit</a>
                                <form method="post" class="delete-form"
                                      onsubmit="return confirm('Delete event: <?= addslashes(htmlspecialchars($e['title'])) ?>?')">
                                    <input type="hidden" name="delete_id" value="<?= $e['id'] ?>" />
                                    <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
