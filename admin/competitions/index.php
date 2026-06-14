<?php


require_once __DIR__ . '/../../includes/db.php';

$admin_page_title = 'Competitions';
$admin_current    = 'competitions';
require_once __DIR__ . '/../../includes/admin_header.php';

$flash = null;

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        get_db()->prepare('DELETE FROM competitions WHERE id = ?')->execute([(int) $_POST['delete_id']]);
        header('Location: /hack/admin/competitions/?deleted=1');
        exit;
    } catch (PDOException $e) {
        $flash = ['type' => 'error', 'msg' => 'Could not delete the competition.'];
    }
}

// Flash from redirects
if (isset($_GET['created'])) $flash = ['type' => 'success', 'msg' => 'Competition added.'];
if (isset($_GET['updated'])) $flash = ['type' => 'success', 'msg' => 'Competition updated.'];
if (isset($_GET['deleted'])) $flash = ['type' => 'success', 'msg' => 'Competition deleted.'];

$competitions = [];
try {
    $competitions = get_db()
        ->query('SELECT * FROM competitions ORDER BY deadline ASC, created_at DESC')
        ->fetchAll();
} catch (PDOException $e) {
    $flash = ['type' => 'error', 'msg' => 'Could not load competitions.'];
}
?>

<div class="page-header">
    <div>
        <h1 class="page-header__title">Competitions</h1>
        <p class="page-header__sub"><?= count($competitions) ?> listed</p>
    </div>
    <a href="/hack/admin/competitions/create.php" class="btn btn--primary">+ Add Competition</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert--<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<div class="table-card">
    <?php if (empty($competitions)): ?>
        <div class="empty-state">
            <span class="empty-state__icon">🏆</span>
            <p>
                No competitions listed yet.
                <a href="/hack/admin/competitions/create.php" style="color:var(--accent)">Add one →</a>
            </p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Organizer</th>
                    <th>Deadline</th>
                    <th>URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($competitions as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td style="color:var(--text-muted)">
                            <?= htmlspecialchars($c['organizer'] ?? '—') ?>
                        </td>
                        <td>
                            <?php if (!empty($c['deadline'])): ?>
                                <?= date('d M Y', strtotime($c['deadline'])) ?>
                            <?php else: ?>
                                <span style="color:var(--text-muted)">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($c['event_url'])): ?>
                                <a href="<?= htmlspecialchars($c['event_url'], ENT_QUOTES) ?>"
                                   target="_blank" rel="noopener"
                                   style="color:var(--accent); font-size:0.82rem;">
                                    Visit →
                                </a>
                            <?php else: ?>
                                <span style="color:var(--text-muted)">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="/hack/admin/competitions/edit.php?id=<?= $c['id'] ?>"
                                   class="btn btn--secondary btn--sm">Edit</a>
                                <form method="post" class="delete-form"
                                      onsubmit="return confirm('Delete: <?= addslashes(htmlspecialchars($c['title'])) ?>?')">
                                    <input type="hidden" name="delete_id" value="<?= $c['id'] ?>" />
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
