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
    echo 'Database already setup.';
} else {
    echo 'Creating database structures.';
}
