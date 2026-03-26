<?php
// ============================================
//  TypeForge — Fetch Random Text
// ============================================

header("Content-Type: application/json");
include 'config.php';

$type    = $_GET['type'] ?? 'sentence';
$allowed = ['sentence', 'paragraph'];

// ---- Sanitize type input ----
if (!in_array($type, $allowed)) {
    $type = 'sentence';
}

// ---- Fetch one random text of requested type ----
$stmt = $conn->prepare("SELECT content FROM texts WHERE type = ? ORDER BY RAND() LIMIT 1");
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$stmt->close();
$conn->close();

if ($row) {
    echo json_encode([
        "success" => true,
        "content" => $row['content']
    ]);
} else {
    // Fallback if DB has no texts seeded yet
    echo json_encode([
        "success" => true,
        "content" => "The quick brown fox jumps over the lazy dog near the old riverbank."
    ]);
}
?>