<?PHP
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/common.php');
$env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');

try {
    $common = new common($env);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()));
    exit;
}

/*standard headers to prevent caching*/
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: -1"); //for the above - prevents browsers from caching dynamic page.

$common->security_check();
$common->local_only();

$common->write_to_log('setup', 'Update', 'Starting database update.');


//get the current database version
$queryText = "SELECT value FROM config WHERE setting = 'DB_VERSION'";
$result = $common->query_to_sd_array($queryText, null);
$current_version = $result['value'];
$common->write_to_log('setup', 'Update', 'Current database version: ' . $current_version);

if ($current_version == $common->db_version) {
    $common->write_to_log('setup', 'Update', 'Database is at the latest version.');
    echo '<meta http-equiv="refresh" content="5; url=/" />';
    die("Database is at the latest version.  Redirecting to the index in 5 seconds.");
}

echo "Updating database to version $common->db_version.  Current version is $current_version. <br/>";
//for loop to run all the update scripts
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
echo "Database updated to version $common->db_version.  Redirecting to index in 5 seconds.<br/>";
$common->write_to_log('setup', 'Update', 'Database updated to version ' . $common->db_version);


?>
<meta http-equiv="refresh" content="5; url=/" />