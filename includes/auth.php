<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        header('Location: /hack/admin/login.php');
        exit;
    }
}

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}
