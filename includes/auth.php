<?php
/**
 * Session + role helpers shared by the payment, order, and admin pages.
 * Kicks' existing login (server/controller.php -> login()) sets
 * $_SESSION['user_id'] and, after the patch below, $_SESSION['role'].
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_role(): ?string {
    return $_SESSION['role'] ?? null;
}

/** $prefix = how many directories deep the calling file is, e.g. 'pages/x.php' passes '../' */
function require_login(string $prefix = ''): void {
    if (!is_logged_in()) {
        header('Location: ' . $prefix . 'pages/login.php');
        exit;
    }
}

function require_role(string $role, string $prefix = ''): void {
    require_login($prefix);
    if (current_role() !== $role) {
        header('Location: ' . $prefix . 'index.php');
        exit;
    }
}

function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function money($amount): string {
    return 'AED ' . number_format((float) $amount, 2);
}
