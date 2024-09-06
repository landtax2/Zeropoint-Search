<?PHP
//Gets the common class
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');

session_start();
$env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');
//instantiate the common class
try {
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}

//security check - prevents access from non-logged in users
$common->security_check();

($common->get_env_value('DEBUGGING') == '1') ? ini_set('display_errors', 1) : ini_set('log_errors', 0); //turns off error logging if not debugging

// Get JSON payload
$json_payload = file_get_contents('php://input');
$data = json_decode($json_payload, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(array('success' => false, 'message' => 'Invalid JSON payload'));
    exit;
}

switch ($data['action']) {
    case 'update_config':
        $queryText = "UPDATE config SET value = :value WHERE id = :id";
        $queryParams = array(':value' => $data['value'], ':id' => $data['id']);
        $common->query_to_sd_array($queryText, $queryParams);
        echo json_encode(array('success' => true, 'message' => 'Configuration updated successfully'));
        break;
    default:
        echo json_encode(array('success' => false, 'message' => 'Unknown action'));
        break;
}
