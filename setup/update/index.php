<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');
$env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');

try {
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}

$common->local_only();




//get the current database version
$queryText = "SELECT value FROM config WHERE setting = 'DB_VERSION'";
$result = $common->query_to_sd_array($queryText, null);
$current_version = $result['value'];

if ($current_version == $common->db_version) {
    die("Database is at the latest version");
}

echo "Updating database to version $common->db_version.  Current version is $current_version.";
