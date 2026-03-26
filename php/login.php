<?php
// ============================================
//  TypeForge — Login Handler
// ============================================

header("Content-Type: application/json");
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

$email    = trim($_POST['email']    ?? '');
$password =      $_POST['password'] ?? '';

// ---- Validation ----
if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email format"]);
    exit();
}

// ---- Fetch user by email (prepared statement) ----
$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "No account found with this email"]);
    $stmt->close();
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// ---- Verify password ----
if (!password_verify($password, $user['password'])) {
    echo json_encode(["success" => false, "message" => "Incorrect password"]);
    exit();
}

// ---- Start session ----
$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];

// ---- Set cookie (Module 5.3 — Sessions & Cookies) ----
// Stores last login time for 30 days
setcookie("last_login", date("Y-m-d H:i:s"), time() + (86400 * 30), "/");

echo json_encode([
    "success"  => true,
    "message"  => "Login successful",
    "redirect" => "dashboard.php"
]);

$conn->close();
?>