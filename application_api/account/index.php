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

($common->get_env_value('DEBUGGING') == '1') ? ini_set('display_errors', 1) : ini_set('log_errors', 0); //turns off error logging if not debugging
$common->local_only();
// Get JSON payload
$json_payload = file_get_contents('php://input');
$data = json_decode($json_payload, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(array('success' => false, 'message' => 'Invalid JSON payload'));
    exit;
}

switch ($data['action']) {
    case 'login':
        if ($common->get_config_value('LOGIN_PASSWORD') == $data['password']) {
            $_SESSION['user_id'] = '1';
            echo json_encode(array('success' => true, 'message' => 'Login Successful'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Login Failed. Bad Password.'));
        }
        break;
    default:
        echo json_encode(array('success' => false, 'message' => 'Unknown action'));
        break;
}
