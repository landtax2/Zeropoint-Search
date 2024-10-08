<?php
//start the session
session_start();

//parse the .env file if it exists
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/.env')) {
    $env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');
} else {
    $env = [];
}

// Set maximum execution time to 5 minutes (300 seconds)
ini_set('max_execution_time', 300);

// Include necessary classes
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/common.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/chat.class.php';

//instantiate the common class
try {
    //env is passed to the common class to be loaded into the class
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}



//performs security to ensure this can only be called by a logged in user
$common->security_check();

//log access to the api
$access = [
    'IP' => $common->get_ip(),
    'User ID' => $_SESSION['user_id'],
    '$_POST' => $_POST
];
$common->write_to_log('access', $_SERVER['REQUEST_URI'], $access);

// Handle chat request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'chat') {
    // Sanitize input from errors
    $systemPrompt = $_POST['system_prompt'] ?? '';
    $userPrompt = $_POST['user_prompt'] ?? '';
    $userData = $_POST['user_data'];

    // Initialize chat
    $chat = new chat_ollama($common);
    $chat->contextWindow = (int) $_POST['context_window'] ?? 6000;

    // Prepare messages
    $prompt = $userPrompt . "\n" . $userData;

    // Send request and echo response
    try {
        $response = $chat->sendRequest($prompt);
        echo $response;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred while processing your request.', 'error_message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method or action.']);
}
