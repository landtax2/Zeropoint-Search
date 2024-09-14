<?PHP
//Gets the common class
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');

session_start();

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

//security check - prevents access from non-logged in users
$common->security_check();

//debugging check
($common->get_config_value('DEBUGGING') == '1') ? ini_set('display_errors', 1) : ini_set('log_errors', 0); //turns off error logging if not debugging

// Get JSON payload
$json_payload = file_get_contents('php://input');
$data = json_decode($json_payload, true);


//deal with decodiing issues
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(array('success' => false, 'message' => 'Invalid JSON payload'));
    exit;
}


//log access to the api
$access = [
    'IP' => $common->get_ip(),
    'User ID' => $_SESSION['user_id'],
    'Data' => $data
];
$common->write_to_log('access', $_SERVER['REQUEST_URI'], $access);

switch ($data['action']) {
    case 'update_remediation':
        $queryText = "UPDATE network_file SET remediated = :value WHERE id = :id";
        $queryParams = array(':value' => $data['value'], ':id' => $data['id']);
        $common->query_to_sd_array($queryText, $queryParams);
        echo json_encode(array('success' => true, 'message' => 'File remediation updated'));
        $log = [
            'user_id' => $_SESSION['user_id'],
            'data' => $data
        ];
        $common->write_to_log('network_file', 'File remedation status', $log);
        break;
    case 'update_comment':
        $queryText = "UPDATE network_file SET comment = :value WHERE id = :id";
        $queryParams = array(':value' => $data['value'], ':id' => $data['id']);
        $common->query_to_sd_array($queryText, $queryParams);
        echo json_encode(array('success' => true, 'message' => 'File comment updated'));
        $log = [
            'user_id' => $_SESSION['user_id'],
            'data' => $data
        ];
        $common->write_to_log('network_file', 'File comment updated', $log);
        break;
    case 'delete_file':
        $queryText = "DELETE FROM network_file WHERE id = :id";
        $queryParams = array(':id' => $data['id']);
        $common->query_to_sd_array($queryText, $queryParams);
        echo json_encode(array('success' => true, 'message' => 'File deleted'));
        $log = [
            'user_id' => $_SESSION['user_id'],
            'data' => $data
        ];
        $common->write_to_log('network_file', 'File deleted', $log);
        break;
    default:
        echo json_encode(array('success' => false, 'message' => 'Unknown action'));
        break;
}
