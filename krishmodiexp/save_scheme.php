<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = file_get_contents('php://input');
if (!$data) {
    echo json_encode(["status" => "error", "msg" => "No data received"]);
    exit;
}

// Validate it is valid JSON
$decoded = json_decode($data, true);
if ($decoded === null) {
    echo json_encode(["status" => "error", "msg" => "Invalid JSON"]);
    exit;
}

$file = 'schemes_db.json';
$success = file_put_contents($file, $data, LOCK_EX);
if ($success !== false) {
    chmod($file, 0777);
    echo json_encode(["status" => "success", "saved_at" => date("h:i:s A"), "count" => count($decoded)]);
} else {
    echo json_encode(["status" => "error", "msg" => "Write permission denied on schemes_db.json"]);
}
?>
