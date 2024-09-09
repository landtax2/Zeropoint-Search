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


//log access to the api
$access = [
    'IP' => $common->get_ip(),
    'User ID' => $_SESSION['user_id'],
    'Data' => $data
];
$common->write_to_log('access', $_SERVER['REQUEST_URI'], $access);

switch ($data['action']) {
    case 'integration_doctor_test':
        // Include the extract_document_text class
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/extract_document_text.class.php');

        try {
            // Instantiate the ExtractDocumentText class
            $extractor = new extract_document_text($common);

            // Define the path to the test file
            $testFilePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/misc/test_file.pdf';

            // Prepare the file array
            $testFile = [
                'tmp_name' => $testFilePath,
                'type' => 'application/pdf',
                'name' => 'test_file.pdf',
                'error' => UPLOAD_ERR_OK
            ];

            // Extract text from the test file
            $extractedText = $extractor->extract($testFile);

            // Return the result
            echo json_encode([
                'success' => true,
                'message' => 'Doctor integration test successful',
                'data' => [
                    'extracted_text' => $extractedText
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Doctor integration test failed: ' . $e->getMessage()
            ]);
        }
        break;
    case 'update_config':
        $data['value'] = trim($data['value']);
        //this needs to update the database timezone
        if ($data['setting'] == 'TIME_ZONE') {
            //update the database timezone
            //this fails when parameters are used
            $time_zone = str_replace("'", "", $data['value']); //prevents SQL injection
            $queryText = "ALTER database zps SET timezone = '$time_zone'";
            $common->query_to_sd_array($queryText);
            $common->write_to_log('setup', 'Config Update', 'Setting timezone to ' . $data['value']);
        }
        //update the databse
        $queryText = "UPDATE config SET value = :value WHERE id = :id";
        $queryParams = array(':value' => $data['value'], ':id' => $data['id']);
        $common->query_to_sd_array($queryText, $queryParams);
        $common->write_to_log('config', 'Configuration updated', $data);
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
