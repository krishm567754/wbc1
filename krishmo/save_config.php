<?php
/**
 * save_config.php
 * Receives the full config.js content (as plain text) from the Admin panel
 * and writes it back to config.js in the same directory.
 *
 * Usage: POST plain-text body containing the JS source → saved as config.js
 */

// 1. Read incoming body
$config_content = file_get_contents('php://input');

if (empty($config_content)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No config data received.']);
    exit;
}

// 2. Basic sanity check – must contain our marker
if (strpos($config_content, 'DASHBOARD_CONFIG') === false) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid config content.']);
    exit;
}

// 3. Write
$config_file = 'config.js';
if (file_put_contents($config_file, $config_content) !== false) {
    echo json_encode(['status' => 'success', 'message' => 'config.js updated.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to write config.js. Check server permissions.']);
}
?>
