<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_title ??= 'HACK Club KUET — Hardware Acceleration Club';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="HACK — Hardware Acceleration Club of KUET. Join workshops, attend events, and participate in competitions." />
    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- Preconnect for fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="/hack/assets/css/style.css" />
</head>
<body>

<header class="site-header">
    <div class="header__inner">
        <a href="/hack/" class="brand" aria-label="HACK Club KUET home">
            <span class="brand__mark">HACK</span>
            <span class="brand__name">KUET Club</span>
        </a>

        <nav class="nav" aria-label="Primary navigation">
            <!-- Mobile toggle -->
            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>

            <ul class="nav__list" id="nav-menu" role="list">
                <li><a class="nav__link" href="/hack/#home">Home</a></li>
                <li><a class="nav__link" href="/hack/events.php">Events</a></li>
                <li><a class="nav__link" href="/hack/#achievements">Achievements</a></li>
                <li><a class="nav__link" href="/hack/#teams">Teams</a></li>
                <li><a class="nav__link" href="/hack/#words">Leadership</a></li>
                <li><a class="nav__link" href="/hack/#contact">Contact</a></li>
            </ul>

            <a class="nav__cta" href="/hack/register.php">Join Club</a>
        </nav>
    </div>
</header>
