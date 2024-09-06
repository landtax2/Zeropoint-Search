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


echo "Tables and default config values created successfully.<br/>";
echo "Setup complete.<br/>";
echo "Redirecting to login page in 5 seconds.<br/>";

?>
<meta http-equiv="refresh" content="5; url=/account/login/index.php" />