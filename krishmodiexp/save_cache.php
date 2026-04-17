<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = file_get_contents('php://input');
if ($data) {
    $file = 'processed_cache.json';
    if (file_exists($file)) { 
        unlink($file); // Purani cache ko poori tarah delete karna
    }
    // Naya data fresh likhna
    $success = file_put_contents($file, $data, LOCK_EX);
    if ($success) {
        chmod($file, 0777);
        echo json_encode(["status" => "success", "updated_at" => date("h:i:s A")]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Write Permission Denied"]);
    }
}
?>