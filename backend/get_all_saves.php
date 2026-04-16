<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

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

// Database connection
$host = "localhost";
$dbname = "Aco_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Database connection failed", "details" => $e->getMessage()]);
    exit();
}

// Fetch all saves for authenticated user
try {
    $userId = $_SESSION['user_id']; // Assuming authenticate.php sets this
    $stmt = $pdo->prepare("SELECT id, algorithm_name, input_size, runtime, complexity, created_at 
                           FROM saves WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $userId]);
    $saves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $saves
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch saves", "details" => $e->getMessage()]);
    exit();
}
?>
