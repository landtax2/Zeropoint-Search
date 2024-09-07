<?php
//set the max execution time ini to 10 minutes
ini_set('max_execution_time', 600);


$env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');

// Start session and include necessary files
//session_start();
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/chat.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/common.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/extract_document_text.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/pii_regex.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/ai_processing.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/data_functions.class.php';

//instantiate the common class
try {
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}


function respond_with_error($message)
{
    respond_with_json(['error' => $message], 400);
}

function respond_with_json($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function handle_check_file_id_action($common, $clientId)
{
    $dataFunctions = new DataFunctions($common);
    $result = $dataFunctions->check_and_create_network_file($clientId, $_POST);
    respond_with_json($result);
}

function sanitize_input($input, $fields)
{
    $sanitized = [];
    foreach ($fields as $field) {
        $sanitized[$field] = $input[$field] ?? '';
    }
    return $sanitized;
}

function handle_extract_action($common, $client_id)
{
    //Extracts text from the file
    if (isset($_FILES['file'])) {
        $extractor = new extract_document_text($common, false);
        try {
            $extracted_text = $extractor->extract($_FILES['file']);

            if (strlen($extracted_text) < $common->get_config_value('OCR_THRESHOLD')) {
                $extractor = new extract_document_text($common, true);
                $extracted_text = $extractor->extract($_FILES['file']);
            }

            if (strlen($extracted_text) == 0) {
                die(json_encode(['error' => 'Extraction failed or returned no text']));
            }
        } catch (Exception $e) {
            die(json_encode(['error' => $e->getMessage()]));
        }
    } else {
        die(json_encode(['error' => 'No file uploaded']));
    }

    $extracted_text_total_length = strlen($extracted_text);
    $extracted_text = substr($extracted_text, 0, $common->get_config_value('AI_PROCESSING_CHAT_MAX_LENGTH'));
    $extracted_text_total_length_after = strlen($extracted_text);

    //Remove the temporary file
    unlink($_FILES['file']['tmp_name']);

    //print_r($_FILES);
    //print_r($_POST);
    //die($extracted_text . 'fin');

    $ssn_hard = PIIRegex::contains_ssn_hard($extracted_text) ? '1' : '0';
    $ssn_soft = PIIRegex::contains_ssn_soft($extracted_text) ? '1' : '0';
    $phone_number = PIIRegex::contains_phone_number($extracted_text) ? '1' : '0';
    $email = PIIRegex::contains_email($extracted_text) ? '1' : '0';
    $password = PIIRegex::contains_password($extracted_text) ? '1' : '0';

    $ai_processing = new ai_processing($common);
    $piiAnalysis = $ai_processing->analyzePII($extracted_text);

    $pii = array(
        'contains_social_security_number' => 0,
        'contains_phone_number' => 0,
        'contains_street_address' => 0,
        'contains_first_and_last_name' => 0,
        'contains_medical_information' => 0,
        'contains_email_address' => 0,
        'contains_credit_card' => 0
    );

    if (isset($piiAnalysis['pii_analysis']) && is_array($piiAnalysis['pii_analysis'])) {
        foreach ($pii as $key => &$value) {
            if (isset($piiAnalysis['pii_analysis'][$key])) {
                $value = strtolower($piiAnalysis['pii_analysis'][$key]);
                $value = ($value === 'yes' || $value === 'true') ? 1 : 0;
            }
        }
    }

    $severity = isset($piiAnalysis['pii_analysis']['severity_of_pii']) ? $piiAnalysis['pii_analysis']['severity_of_pii'] : '0';

    //AI processing
    $time = time();
    $execution_times = array(
        'summary' => 0,
        'title' => 0,
        'tags' => 0,
        'contact_information' => 0
    );

    //AI processing for summary
    $summary = $ai_processing->summarizeText($extracted_text);
    $execution_times['summary'] = time() - $time;

    //AI processing for title
    $title = $ai_processing->titleText($summary);
    $execution_times['title'] = time() - $time;

    //AI processing for tags
    if ($common->get_config_value('FILE_CLASSIFICATION_PROCESS_TAGS') == '1') {
        $tags = $ai_processing->ai_tags($summary);
        $execution_times['tags'] = time() - $time;
    } else {
        $tags = '';
    }

    //AI processing for contact information
    if ($common->get_config_value('FILE_CLASSIFICATION_PROCESS_CONTACT_INFORMATION') == '1') {
        $contact_information = $ai_processing->contact_information($extracted_text);
        $execution_times['contact_information'] = time() - $time;
    } else {
        $contact_information = '';
    }



    // Sanitize and assign variables
    $fields = [
        'name',
        'extension',
        'path',
        'hash',
        'date_created',
        'date_modified',
        'folder',
        'cert_signer',
        'cert_date',
        'cert_issued_by',
        'cert_valid',
        'cert_thumb_print',
        'size',
        'internal_name',
        'product_version',
        'file_version',
        'file_id'
    ];

    $sanitized = [];
    foreach ($fields as $field) {
        $sanitized[$field] = isset($_POST[$field]) ? $_POST[$field] : '';
    }

    $last_found = date('m/d/Y H:i:s');

    $dataFunctions = new DataFunctions($common);
    $updateData = [
        'sanitized' => $sanitized,
        'client_id' => $client_id,
        'last_found' => $last_found,
        'title' => $title,
        'summary' => $summary,
        'tags' => $tags,
        'contact_information' => $contact_information,
        'pii' => $pii,
        'severity' => $severity,
        'ssn_hard' => $ssn_hard,
        'ssn_soft' => $ssn_soft,
        'phone_number' => $phone_number,
        'email' => $email,
        'password' => $password,
    ];

    $dataFunctions->updateNetworkFile($updateData);
    respond_with_json([
        'title' => $title,
        'name' => $_POST['name'],
        'execution_times' => $execution_times,
        'extracted_text_total_length' => $extracted_text_total_length,
        'extracted_text_total_length_after' => $extracted_text_total_length_after,
        'context_window' => $common->get_config_value('AI_PROCESSING_CONTEXT_WINDOW'),
        'summary_length' => $common->get_config_value('AI_PROCESSING_SUMMARY_LENGTH')
    ]);
}

//validates api key is sent
if (!isset($_POST['api_key'])) {
    respond_with_error('No API key provided');
}

//validates api key is valid
if (!$common->validate_api_key($_POST['api_key'])) {
    respond_with_error('Invalid API key');
}

//gets client id from api key
$client_id = $common->api_key_to_client_id($_POST['api_key']);

// Handle different actions
$action = $_GET['action'] ?? '';
switch ($action) {
    case 'extract':
        //die('extract disabled for testing');
        handle_extract_action($common, $client_id);
        break;
    case 'check_file_id':
        handle_check_file_id_action($common, $client_id);
        break;
    default:
        respond_with_error('Invalid action');
}
