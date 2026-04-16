<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$dbname = "aco_db";
$username = "root";
$password = "";

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only POST requests are allowed"]);
    exit();
}

session_start();

// Include authentication script
require_once 'authenticate.php';
if (!isAuthenticated()) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "User not authenticated"]);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Database connection failed", "details" => $e->getMessage()]);
    exit();
}

// Fetch all results for authenticated user
try {
    $userId = $_SESSION['user_id']; // Assuming authenticate.php sets this
    $stmt = $pdo->prepare("SELECT id, input_size, execution_time, algorithm, spaced_used, created_at 
                           FROM results 
                           WHERE user_id = :user_id AND deleted_at IS NULL
                           ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $userId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $results
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch results", "details" => $e->getMessage()]);
    exit();
}
?>
