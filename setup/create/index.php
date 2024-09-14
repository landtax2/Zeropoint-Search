<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');
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

/*standard headers to prevent caching*/
$common->anti_cache_headers();

//Check to see if the config table exists
$queryText = "SELECT EXISTS (
   SELECT FROM information_schema.tables 
   WHERE  table_schema = 'public'
   AND    table_name   = 'config'
   ) as \"exist\";";

$result = $common->query_to_sd_array($queryText, null);

if ($result['exist'] == 1) {
    die('Database already setup. Redirecting to login page in 5 seconds. <meta http-equiv="refresh" content="5; url=/account/login/index.php" />');
} else {
    echo 'Creating database structures.<br/>';
}
$common->write_to_log('setup', 'Create', 'Creating database structures.');
//Runs the database.sql file
// Read the contents of the database.sql file
$sql = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/setup/create/database.sql');

// Split the SQL file into individual queries
$queries = explode(';', $sql);

// Execute each query
foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        try {
            $common->get_db_connection()->exec($query);
        } catch (PDOException $e) {
            $common->write_to_log('setup', 'Create', 'Error executing query: ' . $e->getMessage() . ' for query: ' . $query);
            echo "Error executing query: " . $e->getMessage() . "<br/>";
            echo "Query: " . $query . "<br/>";
        }
    }
}

//Setting values from the .env file
if (isset($env['DOCTOR_API'])) {
    echo "Setting DOCTOR_API to " . $env['DOCTOR_API'] . "<br/>";
    $queryText = "UPDATE public.config SET value = '" . $env['DOCTOR_API'] . "' WHERE setting = 'DOCTOR_API';";
    $common->get_db_connection()->exec($queryText);
    $common->write_to_log('setup', 'Create', 'Setting DOCTOR_API to ' . $env['DOCTOR_API']);
}
if (isset($env['CHAT_API_OLLAMA'])) {
    echo "Setting CHAT_API_OLLAMA to " . $env['CHAT_API_OLLAMA'] . "<br/>";
    $queryText = "UPDATE public.config SET value = '" . $env['CHAT_API_OLLAMA'] . "' WHERE setting = 'CHAT_API_OLLAMA';";
    $common->get_db_connection()->exec($queryText);
    $common->write_to_log('setup', 'Create', 'Setting CHAT_API_OLLAMA to ' . $env['CHAT_API_OLLAMA']);
}
if (isset($env['LOGIN_PASSWORD'])) {
    echo "Setting LOGIN_PASSWORD to the one defined in the .env file. <br/>";
    $queryText = "UPDATE public.config SET value = '" . $env['LOGIN_PASSWORD'] . "' WHERE setting = 'LOGIN_PASSWORD';";
    $common->get_db_connection()->exec($queryText);
    $common->write_to_log('setup', 'Create', 'Setting LOGIN_PASSWORD');
}
if (isset($env['DEFAULT_CLIENT_API_KEY'])) {
    echo "Setting DEFAULT_CLIENT_API_KEY to " . $env['DEFAULT_CLIENT_API_KEY'] . "<br/>";
    $queryText = "UPDATE public.client SET api_key = '" . $env['DEFAULT_CLIENT_API_KEY'] . "' WHERE id = 1;";
    $common->get_db_connection()->exec($queryText);
    $common->write_to_log('setup', 'Create', 'Setting DEFAULT_CLIENT_API_KEY');
}


$common->write_to_log('setup', 'Create', 'Tables and default config values created successfully.');
echo "Tables and default config values created successfully.<br/>";
echo "Running updates<br/>";


//for loop to run all the update scripts
$current_version = 100;
for ($i = $current_version + 1; $i <= $common->db_version; $i++) {
    echo "Running update script for version $i.<br/>";
    $common->write_to_log('setup', 'Update', 'Running update script for version ' . $i);

    // Read the contents of the database.sql file
    try {
        $sql = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/setup/update/sql/' . $i . '.sql');
    } catch (Exception $e) {
        $common->write_to_log('setup', 'Update', 'Error reading update script for version ' . $i . ': ' . $e->getMessage());
        die("Error reading update script for version $i: " . $e->getMessage());
    }

    // Split the SQL file into individual queries
    $queries = explode(';', $sql);

    // Execute each query
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $common->get_db_connection()->exec($query);
            } catch (PDOException $e) {
                echo "Error executing query: " . $e->getMessage() . "<br/>";
                echo "Query: " . $query . "<br/>";
                $common->write_to_log('setup', 'Update', 'Error executing query: ' . $e->getMessage() . ' for query: ' . $query);
            }
        }
    }

    echo "Update script for version $i completed.<br/>";
    $common->write_to_log('setup', 'Update', 'Update script for version ' . $i . ' completed.');
}


echo "Setup complete.<br/>";
echo "Redirecting to login page in 5 seconds.<br/>";

?>
<meta http-equiv="refresh" content="5; url=/account/login/index.php" />