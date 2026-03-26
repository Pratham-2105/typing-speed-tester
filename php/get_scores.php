<?php
// ============================================
//  TypeForge — Get Leaderboard + User History
// ============================================

header("Content-Type: application/json");
session_start();
include 'config.php';

$response = [];

// ---- Global Leaderboard (top 10 by best WPM) ----
$stmt = $conn->prepare("
    SELECT
        u.username,
        MAX(s.wpm)                 AS best_wpm,
        ROUND(AVG(s.accuracy), 1)  AS avg_accuracy,
        COUNT(*)                   AS total_tests
    FROM scores s
    JOIN users u ON s.user_id = u.id
    GROUP BY s.user_id
    ORDER BY best_wpm DESC
    LIMIT 10
");
$stmt->execute();
$result             = $stmt->get_result();
$response['leaderboard'] = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ---- User specific data (only if logged in) ----
if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];

    // Recent 10 tests
    $stmt = $conn->prepare("
        SELECT wpm, accuracy, mode, duration, created_at
        FROM scores
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result           = $stmt->get_result();
    $response['history'] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Aggregate stats
    $stmt = $conn->prepare("
        SELECT
            MAX(wpm)                   AS best_wpm,
            ROUND(AVG(wpm), 0)         AS avg_wpm,
            ROUND(AVG(accuracy), 1)    AS avg_accuracy,
            COUNT(*)                   AS total_tests
        FROM scores
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result         = $stmt->get_result();
    $response['stats'] = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>