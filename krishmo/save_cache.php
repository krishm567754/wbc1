<?php
// This is the "chef" script.
// It receives the clean JSON data from the browser and saves it to a file.

// 1. Get the JSON data sent from the browser
$json_data = file_get_contents('php://input');

// 2. Check if data is valid
if (empty($json_data)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'No data received.']);
    exit;
}

// 3. Decode to make sure it's valid JSON
$data = json_decode($json_data);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data.']);
    exit;
}

// 4. Define the cache file name
$cache_file = 'dashboard_data.json';

// 5. Save the data to the cache file
// file_put_contents() overwrites the file, which is what we want.
if (file_put_contents($cache_file, $json_data) !== false) {
    // Success!
    echo json_encode(['status' => 'success', 'message' => 'Cache updated successfully.']);
} else {
    // Error!
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to write cache file. Check server permissions.']);
}
?>

