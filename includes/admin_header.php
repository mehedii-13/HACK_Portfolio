<?php


require_once __DIR__ . '/auth.php';
require_admin_login();

$admin_page_title ??= 'Admin';
$admin_current    ??= '';

function sidebar_link(string $href, string $icon, string $label, string $key, string $current): void
{
    $active = ($current === $key || str_starts_with($current, $key)) ? 'active' : '';
    echo "<a href=\"{$href}\" class=\"sidebar__link {$active}\"><span class=\"icon\">{$icon}</span> {$label}</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($admin_page_title) ?> — HACK Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/hack/assets/css/admin.css" />
</head>
<body>

<div class="admin-layout">

    <!-- ── Sidebar ── -->
    <aside class="sidebar" id="admin-sidebar">
        <div class="sidebar__brand">
            <span class="sidebar__brand-mark">HACK</span>
            <div class="sidebar__brand-text">
                <span class="sidebar__brand-name">Admin Panel</span>
                <span class="sidebar__brand-sub">KUET Club</span>
            </div>
        </div>

        <nav class="sidebar__nav" aria-label="Admin navigation">
            <div class="sidebar__section-label">Overview</div>
            <?php sidebar_link('/hack/admin/', '⚡', 'Dashboard', 'dashboard', $admin_current); ?>

            <div class="sidebar__section-label">Manage</div>
            <?php sidebar_link('/hack/admin/events/', '📅', 'Events', 'events', $admin_current); ?>
            <?php sidebar_link('/hack/admin/competitions/', '🏆', 'Competitions', 'competitions', $admin_current); ?>
            <?php sidebar_link('/hack/admin/members/', '👥', 'Members', 'members', $admin_current); ?>

            <div class="sidebar__section-label">Site</div>
            <a href="/hack/" target="_blank" class="sidebar__link">
                <span class="icon">🌐</span> View Public Site
            </a>
        </nav>

        <div class="sidebar__footer">
            <form action="/hack/admin/logout.php" method="post">
                <button type="submit" class="sidebar__logout">
                    <span>🚪</span> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- ── Main ── -->
    <main class="admin-main">
        <div class="admin-topbar">
            <span class="admin-topbar__title"><?= htmlspecialchars($admin_page_title) ?></span>
            <div class="admin-topbar__user">
                <div class="admin-topbar__avatar">A</div>
                admin
            </div>
        </div>

        <div class="admin-content">
<!-- ↑ Pages inject their content here; admin_footer.php closes these divs -->
