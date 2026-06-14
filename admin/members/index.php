<?php


require_once __DIR__ . '/../../includes/db.php';

$admin_page_title = 'Members';
$admin_current    = 'members';
require_once __DIR__ . '/../../includes/admin_header.php';

$flash = null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = get_db();

        if (!empty($_POST['approve_id'])) {
            $pdo->prepare('UPDATE members SET status = "approved" WHERE id = ?')->execute([(int) $_POST['approve_id']]);
            header('Location: /hack/admin/members/?approved=1');
            exit;
        }

        if (!empty($_POST['reject_id'])) {
            $pdo->prepare('UPDATE members SET status = "rejected" WHERE id = ?')->execute([(int) $_POST['reject_id']]);
            header('Location: /hack/admin/members/?rejected=1');
            exit;
        }

        if (!empty($_POST['delete_id'])) {
            $pdo->prepare('DELETE FROM members WHERE id = ?')->execute([(int) $_POST['delete_id']]);
            header('Location: /hack/admin/members/?deleted=1');
            exit;
        }
    } catch (PDOException $e) {
        $flash = ['type' => 'error', 'msg' => 'Database error: ' . $e->getMessage()];
    }
}

// Flash from query params
if (isset($_GET['approved'])) $flash = ['type' => 'success', 'msg' => 'Member approved.'];
if (isset($_GET['rejected'])) $flash = ['type' => 'success', 'msg' => 'Member rejected.'];
if (isset($_GET['deleted']))  $flash = ['type' => 'success', 'msg' => 'Member deleted.'];

// Filter
$filter        = $_GET['status'] ?? 'all';
$valid_filters = ['all', 'pending', 'approved', 'rejected'];
if (!in_array($filter, $valid_filters, true)) $filter = 'all';

$members = [];
$counts  = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

try {
    $pdo = get_db();

    if ($filter === 'all') {
        $members = $pdo->query('SELECT * FROM members ORDER BY registered_at DESC')->fetchAll();
    } else {
        $stmt = $pdo->prepare('SELECT * FROM members WHERE status = ? ORDER BY registered_at DESC');
        $stmt->execute([$filter]);
        $members = $stmt->fetchAll();
    }

    $counts['all']      = (int) $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn();
    $counts['pending']  = (int) $pdo->query('SELECT COUNT(*) FROM members WHERE status = "pending"')->fetchColumn();
    $counts['approved'] = (int) $pdo->query('SELECT COUNT(*) FROM members WHERE status = "approved"')->fetchColumn();
    $counts['rejected'] = (int) $pdo->query('SELECT COUNT(*) FROM members WHERE status = "rejected"')->fetchColumn();

} catch (PDOException $e) {
    $flash = ['type' => 'error', 'msg' => 'Could not load members.'];
}
?>

<div class="page-header">
    <div>
        <h1 class="page-header__title">Members</h1>
        <p class="page-header__sub">
            <?= $counts['all'] ?> registered ·
            <?= $counts['pending'] ?> pending approval
        </p>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert--<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<!-- Status filter tabs -->
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <?php foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label): ?>
        <a href="?status=<?= $key ?>"
           style="display:inline-block; font-family:var(--font-head); font-size:0.8rem; font-weight:600;
                  padding:0.4rem 1rem; border-radius:999px; text-decoration:none;
                  border:1px solid <?= $filter === $key ? 'var(--border-glow)' : 'var(--border)' ?>;
                  background:<?= $filter === $key ? 'var(--accent-dim)' : 'transparent' ?>;
                  color:<?= $filter === $key ? 'var(--accent)' : 'var(--text-muted)' ?>;
                  transition: all 0.2s;">
            <?= $label ?> (<?= $counts[$key] ?>)
        </a>
    <?php endforeach; ?>
</div>

<div class="table-card">
    <?php if (empty($members)): ?>
        <div class="empty-state">
            <span class="empty-state__icon">👥</span>
            <p>No members found for this filter.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Dept.</th>
                    <th>Batch</th>
                    <th>Student ID</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <td style="color:var(--text-muted); font-size:0.8rem;">
                            <?= htmlspecialchars($m['email']) ?>
                        </td>
                        <td><?= htmlspecialchars($m['department'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($m['batch'] ?? '—') ?></td>
                        <td style="color:var(--text-muted)">
                            <?= htmlspecialchars($m['student_id'] ?? '—') ?>
                        </td>
                        <td>
                            <span class="badge badge--<?= $m['status'] ?>">
                                <?= ucfirst($m['status']) ?>
                            </span>
                        </td>
                        <td style="color:var(--text-muted); font-size:0.8rem;">
                            <?= date('d M Y', strtotime($m['registered_at'])) ?>
                        </td>
                        <td>
                            <div class="actions">
                                <!-- Approve -->
                                <?php if (in_array($m['status'], ['pending', 'rejected'], true)): ?>
                                    <form method="post" class="delete-form">
                                        <input type="hidden" name="approve_id" value="<?= $m['id'] ?>" />
                                        <button type="submit" class="btn btn--sm"
                                                style="background:rgba(16,185,129,0.12);color:#34d399;border:1px solid rgba(16,185,129,0.28);">
                                            ✓ Approve
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Reject -->
                                <?php if (in_array($m['status'], ['pending', 'approved'], true)): ?>
                                    <form method="post" class="delete-form">
                                        <input type="hidden" name="reject_id" value="<?= $m['id'] ?>" />
                                        <button type="submit" class="btn btn--danger btn--sm">✗ Reject</button>
                                    </form>
                                <?php endif; ?>

                                <!-- Delete -->
                                <form method="post" class="delete-form"
                                      onsubmit="return confirm('Permanently delete <?= addslashes(htmlspecialchars($m['name'])) ?>?')">
                                    <input type="hidden" name="delete_id" value="<?= $m['id'] ?>" />
                                    <button type="submit" class="btn btn--danger btn--sm">🗑</button>
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
