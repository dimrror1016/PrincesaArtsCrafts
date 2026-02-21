<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================
// Session Security
// ==================
function secureSession() {
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}
secureSession();

// ==================
// Login Checks
// ==================
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// ==================
// Role Checks
// ==================
function isAdmin() {
    return isAdminLoggedIn()
        && isset($_SESSION['admin_role'])
        && $_SESSION['admin_role'] === 'admin';
}

function isSuperAdmin() {
    return isAdminLoggedIn()
        && isset($_SESSION['admin_role'])
        && $_SESSION['admin_role'] === 'super_admin';
}

// ==================
// Route Guards
// ==================
function requireUser() {
    if (!isUserLoggedIn()) {
        redirect("../user/login.php");
    }
}

/*
 | Admin pages:
 | Admin OR Super Admin
 */
function requireAdmin() {
    if (!isAdmin() && !isSuperAdmin()) {
        redirect("../admin/login.php");
    }
}

/*
 | Super Admin ONLY
 */
function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        redirect("../admin/login.php");
    }
}

// ==================
// Redirect Helper
// ==================
function redirect($url) {
    header("Location: $url");
    exit;
}
