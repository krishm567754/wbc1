<?php
// save_kpis.php
// Receives the updated KPI definitions array from the Admin panel and saves it.

// 1. Get the JSON data sent from the browser
$json_data = file_get_contents('php://input');

// 2. Check if data is valid
if (empty($json_data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No data received.']);
    exit;
}

// 3. Decode to make sure it's valid JSON and it's an array
$data = json_decode($json_data);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data.']);
    exit;
}

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data must be a JSON array of KPI definitions.']);
    exit;
}

// 4. Define the target file
$kpi_file = 'kpi_definitions.json';

// 5. Pretty-print the JSON for readability
$pretty_json = json_encode($data, JSON_PRETTY_PRINT);

// 6. Save
if (file_put_contents($kpi_file, $pretty_json) !== false) {
    echo json_encode(['status' => 'success', 'message' => 'KPI definitions saved successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to write kpi_definitions.json. Check server permissions.']);
}
?>
