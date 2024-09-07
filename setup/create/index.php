<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');
$env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');
//instantiate the common class

try {
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}

//Check to see if the config table exists
$queryText = "SELECT EXISTS (
   SELECT FROM information_schema.tables 
   WHERE  table_schema = 'public'
   AND    table_name   = 'config'
   ) as \"exist\";";

$result = $common->query_to_sd_array($queryText, null);

if ($result['exist'] == 1) {
    die('Database already setup.');
} else {
    echo 'Creating database structures.<br/>';
}

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
}
if (isset($env['CHAT_API_OLLAMA'])) {
    echo "Setting CHAT_API_OLLAMA to " . $env['CHAT_API_OLLAMA'] . "<br/>";
    $queryText = "UPDATE public.config SET value = '" . $env['CHAT_API_OLLAMA'] . "' WHERE setting = 'CHAT_API_OLLAMA';";
    $common->get_db_connection()->exec($queryText);
}
if (isset($env['LOGIN_PASSWORD'])) {
    echo "Setting LOGIN_PASSWORD to the one defined in the .env file. <br/>";
    $queryText = "UPDATE public.config SET value = '" . $env['LOGIN_PASSWORD'] . "' WHERE setting = 'LOGIN_PASSWORD';";
    $common->get_db_connection()->exec($queryText);
}
if (isset($env['DEFAULT_CLIENT_API_KEY'])) {
    echo "Setting DEFAULT_CLIENT_API_KEY to " . $env['DEFAULT_CLIENT_API_KEY'] . "<br/>";
    $queryText = "UPDATE public.client SET api_key = '" . $env['DEFAULT_CLIENT_API_KEY'] . "' WHERE id = 1;";
    $common->get_db_connection()->exec($queryText);
}

echo "Tables and default config values created successfully.<br/>";
echo "Setup complete.<br/>";
echo "Redirecting to login page in 5 seconds.<br/>";

?>
<meta http-equiv="refresh" content="5; url=/account/login/index.php" />