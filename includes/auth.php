<?php
// includes/auth.php - loads the database + helpers and checks who is logged in.
// Every page starts with:  require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function is_logged_in() { return isset($_SESSION['user']); }
function user() { return $_SESSION['user'] ?? null; }
function has_role($roles) { $u = user(); return $u && in_array($u['role'], (array)$roles, true); }

function dashboard_url($role) {
    $map = ['admin' => 'admin/dashboard.php', 'farmer' => 'farmer/dashboard.php',
            'buyer' => 'buyer/dashboard.php', 'inspector' => 'quality.php#inspect'];
    return $map[$role] ?? 'login.php';
}
// Blocks the page unless the user is logged in with one of the allowed roles.
function require_role($roles) {
    if (!is_logged_in()) { flash('Please log in to continue.', 'error'); redirect('login.php'); }
    if (!has_role($roles)) { flash('You are not allowed to open that page.', 'error'); redirect(dashboard_url(user()['role'])); }
}
function current_farmer() {
    return db_one("SELECT f.*, u.full_name, u.email, u.phone FROM farmers f JOIN users u ON f.user_id=u.id WHERE f.user_id=?", 'i', [user()['id']]);
}
function current_buyer() {
    return db_one("SELECT b.*, u.full_name, u.email, u.phone FROM buyers b JOIN users u ON b.user_id=u.id WHERE b.user_id=?", 'i', [user()['id']]);
}
function unread_count() {
    return is_logged_in() ? db_count("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0", 'i', [user()['id']]) : 0;
}
