<?php


require_once __DIR__ . '/includes/db.php';

$allowed_types = ['all', 'meeting', 'class', 'workshop', 'project_showcase', 'competition'];
$filter        = $_GET['type'] ?? 'all';
if (!in_array($filter, $allowed_types, true)) $filter = 'all';

$events   = [];
$db_error = false;

try {
    $pdo = get_db();

    if ($filter === 'all') {
        $stmt = $pdo->prepare(
            'SELECT * FROM events WHERE event_date >= CURDATE()
             ORDER BY event_date ASC, event_time ASC'
        );
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare(
            'SELECT * FROM events WHERE event_date >= CURDATE() AND type = ?
             ORDER BY event_date ASC, event_time ASC'
        );
        $stmt->execute([$filter]);
    }
    $events = $stmt->fetchAll();

} catch (PDOException $e) {
    $db_error = true;
}

$type_icons = [
    'meeting'          => '🤝',
    'class'            => '📚',
    'workshop'         => '🔧',
    'project_showcase' => '🚀',
    'competition'      => '🏆',
];

$type_labels = [
    'all'              => 'All Events',
    'meeting'          => '🤝 Meetings',
    'class'            => '📚 Classes',
    'workshop'         => '🔧 Workshops',
    'project_showcase' => '🚀 Showcases',
    'competition'      => '🏆 Competitions',
];

$page_title = 'Events — HACK Club KUET';
require_once __DIR__ . '/includes/header.php';
?>

<main class="page">
    <section class="section">
        <h1 class="section__title">Events</h1>

        <!-- Filter bar -->
        <div class="filter-bar" role="group" aria-label="Filter events by type">
            <?php foreach ($type_labels as $key => $label): ?>
                <a href="/hack/events.php?type=<?= $key ?>"
                   class="filter-btn <?= $filter === $key ? 'active' : '' ?>"
                   aria-pressed="<?= $filter === $key ? 'true' : 'false' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($db_error): ?>
            <div class="alert alert--info">
                Database not configured yet.
                <a href="/hack/setup.php?key=setup2025" style="color:#60a5fa">Run setup →</a>
            </div>
        <?php elseif (empty($events)): ?>
            <div class="empty-state">
                <span class="empty-state__icon">📅</span>
                <p>No upcoming events in this category — check back later!</p>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <article class="event-card reveal" data-type="<?= htmlspecialchars($event['type']) ?>">
                        <?php if (!empty($event['image'])): ?>
                            <img class="event-card__img"
                                 src="<?= UPLOAD_URL . htmlspecialchars($event['image'], ENT_QUOTES) ?>"
                                 alt="<?= htmlspecialchars($event['title'], ENT_QUOTES) ?>"
                                 loading="lazy" />
                        <?php else: ?>
                            <div class="event-card__img-placeholder">
                                <?= $type_icons[$event['type']] ?? '🔧' ?>
                            </div>
                        <?php endif; ?>

                        <div class="event-card__body">
                            <div class="event-card__meta">
                                <span class="event-card__type event-card__type--<?= htmlspecialchars($event['type']) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $event['type'])) ?>
                                </span>
                                <span class="event-card__date">
                                    <?= date('d M Y', strtotime($event['event_date'])) ?>
                                </span>
                                <?php if (!empty($event['event_time'])): ?>
                                    <span class="event-card__time">
                                        <?= date('g:i A', strtotime($event['event_time'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h2 class="event-card__title">
                                <?= htmlspecialchars($event['title'], ENT_QUOTES) ?>
                            </h2>
                            <?php if (!empty($event['venue'])): ?>
                                <p class="event-card__venue">
                                    📍 <?= htmlspecialchars($event['venue'], ENT_QUOTES) ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($event['description'])): ?>
                                <p class="event-card__desc">
                                    <?= htmlspecialchars($event['description'], ENT_QUOTES) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
