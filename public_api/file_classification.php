<?php
//set the max execution time ini to 10 minutes
ini_set('max_execution_time', 600);

//set the content type to json
header('Content-Type: application/json');

//include the necessary files
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/chat.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/common.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/file_classification_main.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/extract_document_text.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/pii_regex.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/ai_processing.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/data_functions.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/convert_to_pdf.class.php';

//parse the .env file if it exists
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/.env')) {
    $env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');
} else {
    $env = [];
}


//instantiate the common class
try {
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}



//validates api key is sent
if (!isset($_POST['api_key'])) {
    $common->respond_with_error('No API key provided');
}

//validates api key is valid
if (!$common->validate_api_key($_POST['api_key'])) {
    $log = [
        'POST' => $_POST,
        'IP' => $common->get_ip(),
        'User Agent' => $_SERVER['HTTP_USER_AGENT'],
        'GET' => $_GET
    ];
    $common->write_to_log('security', 'Invalid API key', $log);
    $common->respond_with_error('Invalid API key');
    exit;
}

//log access to the api
$log = [
    'POST' => $_POST,
    'IP' => $common->get_ip(),
    'User Agent' => $_SERVER['HTTP_USER_AGENT'],
    'GET' => $_GET
];
$common->write_to_log('access', $_SERVER['REQUEST_URI'], $log);

//gets client id from api key
$client_id = $common->api_key_to_client_id($_POST['api_key']);

// Handle different actions
$action = $_GET['action'] ?? '';
switch ($action) {
    case 'extract':
        $log = [
            'GET' => $_GET,
            'POST' => $_POST
        ];
        $common->write_to_log('file_classification', 'Extract action called', $log);
        $file_classification_main = new file_classification_main($common, $client_id, $_POST);
        $file_classification_main->handle_extract_action();
        break;
    case 'check_file_id':
        $log = [
            'GET' => $_GET,
            'POST' => $_POST
        ];
        $common->write_to_log('file_classification', 'Check file id action called', $log);
        $file_classification_main = new file_classification_main($common, $client_id, $_POST);
        $file_classification_main->handle_check_file_id_action();
        break;
    default:
        $common->respond_with_error('Invalid action');
}
