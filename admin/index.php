<?php


require_once __DIR__ . '/../includes/db.php';

$admin_page_title = 'Dashboard';
$admin_current    = 'dashboard';
require_once __DIR__ . '/../includes/admin_header.php';

$stats          = ['upcoming' => 0, 'total_events' => 0, 'members_pending' => 0, 'members_total' => 0, 'competitions' => 0];
$recent_events  = [];
$recent_members = [];
$db_error       = false;

try {
    $pdo = get_db();

    $stats['total_events']    = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
    $stats['upcoming']        = (int) $pdo->query('SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()')->fetchColumn();
    $stats['members_pending'] = (int) $pdo->query('SELECT COUNT(*) FROM members WHERE status = "pending"')->fetchColumn();
    $stats['members_total']   = (int) $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn();
    $stats['competitions']    = (int) $pdo->query('SELECT COUNT(*) FROM competitions')->fetchColumn();

    $recent_events  = $pdo->query('SELECT * FROM events ORDER BY created_at DESC LIMIT 5')->fetchAll();
    $recent_members = $pdo->query('SELECT * FROM members ORDER BY registered_at DESC LIMIT 5')->fetchAll();

} catch (PDOException $e) {
    $db_error = true;
}
?>

<!-- Page header -->
<div class="page-header">
    <div>
        <h1 class="page-header__title">Dashboard</h1>
        <p class="page-header__sub">Welcome back, admin! Here's a quick overview.</p>
    </div>
    <a href="/hack/admin/events/create.php" class="btn btn--primary">+ New Event</a>
</div>

<?php if ($db_error): ?>
    <div class="alert alert--error">
        Database connection failed. Make sure the DB is set up by running
        <a href="/hack/setup.php?key=setup2025" style="color:#60a5fa">/hack/setup.php?key=setup2025</a>.
    </div>
<?php else: ?>

<!-- Stat cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--teal">📅</div>
        <div>
            <span class="stat-card__num"><?= $stats['upcoming'] ?></span>
            <span class="stat-card__label">Upcoming Events</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--purple">👥</div>
        <div>
            <span class="stat-card__num"><?= $stats['members_total'] ?></span>
            <span class="stat-card__label">Total Members</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--amber">⏳</div>
        <div>
            <span class="stat-card__num"><?= $stats['members_pending'] ?></span>
            <span class="stat-card__label">Pending Approvals</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--blue">🏆</div>
        <div>
            <span class="stat-card__num"><?= $stats['competitions'] ?></span>
            <span class="stat-card__label">Competitions Listed</span>
        </div>
    </div>
</div>

<!-- Recent events table -->
<div class="table-card">
    <div class="table-card__header">
        <span class="table-card__title">Recent Events</span>
        <a href="/hack/admin/events/" class="btn btn--secondary btn--sm">View All</a>
    </div>
    <?php if (empty($recent_events)): ?>
        <div class="empty-state">
            <span class="empty-state__icon">📅</span>
            <p>No events yet. <a href="/hack/admin/events/create.php" style="color:var(--accent)">Create one →</a></p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_events as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['title']) ?></td>
                        <td>
                            <span class="badge badge--<?= $e['type'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $e['type'])) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($e['event_date'])) ?></td>
                        <td style="color:var(--text-muted)">
                            <?= htmlspecialchars($e['venue'] ?? '—') ?>
                        </td>
                        <td>
                            <a href="/hack/admin/events/edit.php?id=<?= $e['id'] ?>"
                               class="btn btn--secondary btn--sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Recent member registrations -->
<div class="table-card">
    <div class="table-card__header">
        <span class="table-card__title">Recent Registrations</span>
        <a href="/hack/admin/members/" class="btn btn--secondary btn--sm">View All</a>
    </div>
    <?php if (empty($recent_members)): ?>
        <div class="empty-state">
            <span class="empty-state__icon">👥</span>
            <p>No member registrations yet.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_members as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <td style="color:var(--text-muted); font-size:0.82rem;">
                            <?= htmlspecialchars($m['email']) ?>
                        </td>
                        <td><?= htmlspecialchars($m['department'] ?? '—') ?></td>
                        <td>
                            <span class="badge badge--<?= $m['status'] ?>">
                                <?= ucfirst($m['status']) ?>
                            </span>
                        </td>
                        <td style="color:var(--text-muted); font-size:0.82rem;">
                            <?= date('d M Y', strtotime($m['registered_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
