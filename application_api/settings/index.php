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
        $common->write_to_log('config', 'Configuration updated', 'Config ID: ' . $data['id'] . ' has been set to ' . $data['value']);
        echo json_encode(array('success' => true, 'message' => 'Configuration updated successfully'));
        break;
    case 'empty_network_files':
        $queryText = "TRUNCATE TABLE network_file";
        $common->query_to_sd_array($queryText, []);
        $queryText = "VACUUM network_file;";
        $common->query_to_sd_array($queryText, []);
        echo json_encode(array('success' => true, 'message' => 'Network files table emptied successfully'));
        break;
    case 'reset_database':
        try {
            // Get all table names in the zps database
            $queryText = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
            $tables = $common->query_to_md_array($queryText, []);

            // Drop each table
            foreach ($tables as $table) {
                $tableName = $table['table_name'];
                $dropQuery = "DROP TABLE IF EXISTS $tableName CASCADE";
                $common->query_to_sd_array($dropQuery, []);
            }

            // Vacuum the database to reclaim storage
            $common->query_to_sd_array("VACUUM FULL", []);

            $common->write_to_log('database', 'All tables dropped and database reset');
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'Error resetting database: ' . $e->getMessage()));
            die();
        }
        echo json_encode(array('success' => true, 'message' => 'Database reset successfully'));
        break;
    case 'empty_all_logs':
        $log_dir = $_SERVER['DOCUMENT_ROOT'] . '/logs/';
        $log_files = glob($log_dir . '*.log');
        foreach ($log_files as $file) {
            if (is_writable($file)) {
                file_put_contents($file, '');
            } else {
                die(json_encode(array('success' => false, 'message' => 'Unable to empty log file: ' . $file)));
            }
        }
        $common->write_to_log('log_clear', 'All logs emptied successfully');
        echo json_encode(array('success' => true, 'message' => 'All logs emptied successfully'));
        break;
    default:
        echo json_encode(array('success' => false, 'message' => 'Unknown action'));
        break;
}
