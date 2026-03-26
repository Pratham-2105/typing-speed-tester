<?php
// ============================================
//  TypeForge — Database Connection
// ============================================

$host = "localhost";
$user = "root";
$pass = "";
$db   = "typing_app";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8");
?>