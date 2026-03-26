<?php
// ============================================
//  TypeForge — Auth Status Check
// ============================================

header("Content-Type: application/json");
session_start();

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "loggedIn" => true,
        "username" => $_SESSION['username']
    ]);
} else {
    echo json_encode([
        "loggedIn" => false,
        "username" => null
    ]);
}
?>