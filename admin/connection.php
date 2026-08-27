<?php
// JuttaPasal admin database connection.
// Fill these four values for your MySQL server.
$host = 'localhost';
$username = 'suman';
$password = 'suman123';
$database = 'juttapasal';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$con = mysqli_connect($host, $username, $password, $database);
if (!$con) die('Database connection failed: ' . mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header('Location: ' . $url); exit; }
function admin_guard() {
    if (!isset($_SESSION['user_id'])) return;
    // If your existing login stores Role, block non-admin users.
    if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
        http_response_code(403); exit('Access forbidden.');
    }
}
function icon($name) {
    $icons = [
        'grid'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
        'orders'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12l2 4v14H4V7l2-4Z"/><path d="M4 7h16M9 11h6M9 15h6"/></svg>',
        'box'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v9l9 5 9-5V8M12 13v9"/></svg>',
        'users'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'plus'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>',
        'search'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>',
        'edit'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>',
        'trash'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/></svg>',
        'logout'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/></svg>',
        'shoe'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 15c2.5 0 4-1.2 5.4-4.4l.9-2.1c.4-.9 1.5-1.2 2.2-.5l2.4 2.4c1.4 1.4 3.4 2.3 5.5 2.6 1.2.2 2 1.2 2 2.4v1.1c0 1.4-1.1 2.5-2.5 2.5H4.5C3.1 19 2 17.9 2 16.5S2.9 15 4 15Z"/><path d="M10 11.5 14 15M13 9l3.5 3.5"/></svg>',
        'arrow'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'
    ];
    return $icons[$name] ?? '';
}
admin_guard();
