<?php
// ============================================
//  TypeForge — Logout Handler
// ============================================

session_start();

// ---- Destroy session ----
session_unset();
session_destroy();

// ---- Clear the login cookie ----
// Set expiry to past time to delete it
setcookie("last_login", "", time() - 3600, "/");

// ---- Redirect to login ----
header("Location: ../login.html");
exit();
?>