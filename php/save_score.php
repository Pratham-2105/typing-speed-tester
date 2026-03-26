<?php
// ============================================
//  TypeForge — Save Score
// ============================================

header("Content-Type: application/json");
session_start();
include 'config.php';

// ---- Must be logged in ----
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

// ---- Sanitize inputs ----
$user_id  = (int)   $_SESSION['user_id'];
$wpm      = (int)   ($_POST['wpm']      ?? 0);
$accuracy = (float) ($_POST['accuracy'] ?? 0);
$mode     =         ($_POST['mode']     ?? 'words');
$duration = (int)   ($_POST['duration'] ?? 60);

// ---- Validate ----
$allowed_modes = ['words', 'sentence', 'paragraph', 'custom'];

if (!in_array($mode, $allowed_modes)) {
    $mode = 'words';
}

if ($wpm <= 0 || $accuracy < 0 || $accuracy > 100) {
    echo json_encode(["success" => false, "message" => "Invalid score data"]);
    exit();
}

// ---- Insert score (prepared statement) ----
$stmt = $conn->prepare("INSERT INTO scores (user_id, wpm, accuracy, mode, duration) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iidsi", $user_id, $wpm, $accuracy, $mode, $duration);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Score saved"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save score"]);
}

$stmt->close();
$conn->close();
?>