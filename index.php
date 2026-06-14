<?php
/**
 * index.php — HACK Club KUET Public Homepage
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$events       = [];
$competitions = [];
$stats        = ['events' => 0, 'members' => 0, 'competitions' => 0];
$db_error     = false;

try {
    $pdo = get_db();

    // Upcoming events (show 6 on homepage)
    $stmt = $pdo->prepare(
        'SELECT * FROM events
         WHERE event_date >= CURDATE()
         ORDER BY event_date ASC, event_time ASC
         LIMIT 6'
    );
    $stmt->execute();
    $events = $stmt->fetchAll();

    // Upcoming competitions (deadline not passed)
    $stmt = $pdo->prepare(
        'SELECT * FROM competitions
         WHERE deadline >= CURDATE() OR deadline IS NULL
         ORDER BY deadline ASC
         LIMIT 4'
    );
    $stmt->execute();
    $competitions = $stmt->fetchAll();

    // Stats
    $stats['events']       = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
    $stats['members']      = (int) $pdo->query('SELECT COUNT(*) FROM members WHERE status = "approved"')->fetchColumn();
    $stats['competitions'] = (int) $pdo->query('SELECT COUNT(*) FROM competitions')->fetchColumn();

} catch (PDOException $e) {
    $db_error = true;
}

$page_title = 'HACK Club KUET — Hardware Acceleration Club';
require_once __DIR__ . '/includes/header.php';

// Event type icons map
$type_icons = [
    'meeting'          => '🤝',
    'class'            => '📚',
    'workshop'         => '🔧',
    'project_showcase' => '🚀',
    'competition'      => '🏆',
];
?>

<main class="page">

    <!-- ══ Hero ═════════════════════════════════════════════════ -->
    <section id="home" class="hero section">
        <span class="hero__tag">Hardware Acceleration Club · KUET</span>
        <h1>Build.<br>Accelerate.<br>Innovate.</h1>
        <p>
            The Hardware Acceleration Club of KUET — where engineers learn,
            experiment, and compete in FPGAs, embedded systems, and cutting-edge hardware.
        </p>
        <div class="hero__actions">
            <a class="btn btn--primary" href="/hack/register.php">Join the Club →</a>
            <a class="btn btn--ghost" href="/hack/events.php">View Events</a>
        </div>
    </section>

    <!-- ══ Stats ════════════════════════════════════════════════ -->
    <?php if (!$db_error): ?>
    <section class="section" style="padding-top: 0; padding-bottom: 3rem;">
        <div class="stats-row">
            <div class="stat-card reveal">
                <span class="stat-card__num" data-count="<?= $stats['events'] ?>"><?= $stats['events'] ?></span>
                <span class="stat-card__label">Events Held</span>
            </div>
            <div class="stat-card reveal">
                <span class="stat-card__num" data-count="<?= $stats['members'] ?>"><?= $stats['members'] ?></span>
                <span class="stat-card__label">Active Members</span>
            </div>
            <div class="stat-card reveal">
                <span class="stat-card__num" data-count="<?= $stats['competitions'] ?>"><?= $stats['competitions'] ?></span>
                <span class="stat-card__label">Competitions Listed</span>
            </div>
            <div class="stat-card reveal">
                <span class="stat-card__num" data-count="3">3</span>
                <span class="stat-card__label">Awards Won</span>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ══ Upcoming Events ══════════════════════════════════════ -->
    <section id="events" class="section section--alt">
        <h2 class="section__title reveal">Upcoming Events</h2>

        <?php if ($db_error): ?>
            <div class="alert alert--info">
                ℹ️ Database not set up yet. Visit
                <strong><a href="/hack/setup.php?key=setup2025" style="color:#60a5fa">/hack/setup.php?key=setup2025</a></strong>
                to initialize the database.
            </div>
        <?php elseif (empty($events)): ?>
            <div class="empty-state">
                <span class="empty-state__icon">📅</span>
                <p>No upcoming events right now — check back soon!</p>
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
                            <h3 class="event-card__title">
                                <?= htmlspecialchars($event['title'], ENT_QUOTES) ?>
                            </h3>
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

            <div style="text-align:center; margin-top:2.5rem;">
                <a class="btn btn--ghost" href="/hack/events.php">See All Events →</a>
            </div>
        <?php endif; ?>
    </section>

    <!-- ══ Achievements ═════════════════════════════════════════ -->
    <section id="achievements" class="section">
        <h2 class="section__title reveal">Achievements</h2>
        <div class="card-grid">
            <article class="card reveal">
                <div style="font-size:1.75rem; margin-bottom:0.75rem;">🥇</div>
                <h3>National Hardware Hackathon</h3>
                <p>1st place for FPGA-based vision acceleration pipeline, 2025.</p>
            </article>
            <article class="card reveal">
                <div style="font-size:1.75rem; margin-bottom:0.75rem;">🏅</div>
                <h3>Smart Device Expo</h3>
                <p>Best prototype award for low-power sensor cluster design.</p>
            </article>
            <article class="card reveal">
                <div style="font-size:1.75rem; margin-bottom:0.75rem;">🥈</div>
                <h3>Inter-University Robotics Meet</h3>
                <p>Runner-up in the autonomous systems league category.</p>
            </article>
        </div>
    </section>

    <!-- ══ Upcoming Competitions ════════════════════════════════ -->
    <?php if (!$db_error && !empty($competitions)): ?>
    <section id="competitions" class="section section--alt">
        <h2 class="section__title reveal">Upcoming Competitions</h2>
        <p style="color:var(--text-muted); margin-top:-1.5rem; margin-bottom:2rem; font-size:0.93rem;">
            External competitions open to HACK Club members.
        </p>
        <div class="card-grid">
            <?php foreach ($competitions as $comp): ?>
                <article class="competition-card reveal">
                    <?php if (!empty($comp['organizer'])): ?>
                        <span class="competition-card__org">
                            <?= htmlspecialchars($comp['organizer'], ENT_QUOTES) ?>
                        </span>
                    <?php endif; ?>
                    <h3 class="competition-card__title">
                        <?= htmlspecialchars($comp['title'], ENT_QUOTES) ?>
                    </h3>
                    <?php if (!empty($comp['description'])): ?>
                        <p class="competition-card__desc">
                            <?= htmlspecialchars($comp['description'], ENT_QUOTES) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($comp['deadline'])): ?>
                        <p class="competition-card__deadline">
                            Deadline: <strong><?= date('d M Y', strtotime($comp['deadline'])) ?></strong>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($comp['event_url'])): ?>
                        <a class="competition-card__link"
                           href="<?= htmlspecialchars($comp['event_url'], ENT_QUOTES) ?>"
                           target="_blank" rel="noopener noreferrer">
                            Learn more →
                        </a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ══ Best Teams ═══════════════════════════════════════════ -->
    <section id="teams" class="section">
        <h2 class="section__title reveal">Best Teams</h2>
        <div class="card-grid">
            <article class="card reveal">
                <div style="font-size:1.75rem; margin-bottom:0.75rem;">⚡</div>
                <h3>PulseForge</h3>
                <p>Specialists in real-time DSP pipelines and edge AI acceleration.</p>
            </article>
            <article class="card reveal">
                <div style="font-size:1.75rem; margin-bottom:0.75rem;">🔐</div>
                <h3>Quantum Loom</h3>
                <p>Masters of hardware security and accelerated cryptography.</p>
            </article>
            <article class="card reveal">
                <div style="font-size:1.75rem; margin-bottom:0.75rem;">🤖</div>
                <h3>Ion Crew</h3>
                <p>Known for robotics control stacks and advanced sensor fusion.</p>
            </article>
        </div>
    </section>

    <!-- ══ Leadership Quotes ═════════════════════════════════════ -->
    <section id="words" class="section section--alt">
        <h2 class="section__title reveal">Words from the Leadership</h2>
        <div class="quote-grid">
            <blockquote class="quote reveal">
                <p>"We are building a culture of hands-on curiosity where everyone learns by shipping real hardware."</p>
                <cite>President, HACK Club KUET</cite>
            </blockquote>
            <blockquote class="quote reveal">
                <p>"Our teams win because we iterate together, share knowledge freely, and celebrate every prototype."</p>
                <cite>Secretary, HACK Club KUET</cite>
            </blockquote>
        </div>
    </section>

    <!-- ══ Contact ══════════════════════════════════════════════ -->
    <section id="contact" class="section">
        <h2 class="section__title reveal">Contact Us</h2>
        <div class="contact__details">
            <div class="contact__item reveal">
                <p class="contact__label">Email</p>
                <p class="contact__value">
                    <a href="mailto:hackclub@kuet.edu" style="color:var(--accent)">
                        hackclub@kuet.edu
                    </a>
                </p>
            </div>
            <div class="contact__item reveal">
                <p class="contact__label">Location</p>
                <p class="contact__value">Academic Building (B Block), KUET Campus, Khulna</p>
            </div>
            <div class="contact__item reveal">
                <p class="contact__label">Social</p>
                <p class="contact__value">@hackclub_kuet</p>
            </div>
            <div class="contact__item reveal">
                <p class="contact__label">Become a Member</p>
                <a href="/hack/register.php" class="btn btn--primary"
                   style="display:inline-flex; margin-top:0.25rem; font-size:0.88rem; padding:0.55rem 1.2rem;">
                    Register Now →
                </a>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
