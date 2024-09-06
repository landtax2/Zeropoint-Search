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

$common->security_check();
$common->local_only();


//get the current database version
$queryText = "SELECT value FROM config WHERE setting = 'DB_VERSION'";
$result = $common->query_to_sd_array($queryText, null);
$current_version = $result['value'];

if ($current_version == $common->db_version) {
    die("Database is at the latest version");
}

echo "Updating database to version $common->db_version.  Current version is $current_version. <br/>";
//for loop to run all the update scripts
for ($i = $current_version + 1; $i <= $common->db_version; $i++) {
    echo "Running update script for version $i.<br/>";

    // Read the contents of the database.sql file
    try {
        $sql = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/setup/update/sql/' . $i . '.sql');
    } catch (Exception $e) {
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
            }
        }
    }

    echo "Update script for version $i completed.<br/>";
}
echo "Database updated to version $common->db_version.  Redirecting to index in 5 seconds.<br/>";

?>
<meta http-equiv="refresh" content="5; url=/" />