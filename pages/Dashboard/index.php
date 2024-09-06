<?PHP
$display_ollama_warning = false;
$display_doctor_api_warning = false;
$display_db_version_warning = false;
$display_password_warning = false;

//code to see if the ollama config is set
$queryText = "SELECT * FROM config WHERE setting = 'CHAT_API_OLLAMA'";
$queryParams = null;
$ollama_config = $common->query_to_sd_array($queryText, $queryParams);
if ($ollama_config['value'] == '') {
    $display_ollama_warning = true;
}

//code to see if the doctor api is set
$queryText = "SELECT * FROM config WHERE setting = 'DOCTOR_API'";
$queryParams = null;
$doctor_api_config = $common->query_to_sd_array($queryText, $queryParams);
if ($doctor_api_config['value'] == '') {
    $display_doctor_api_warning = true;
}

//code to see if the database is up to date
$queryText = "SELECT * FROM config WHERE setting = 'DB_VERSION'";
$queryParams = null;
$db_version = $common->query_to_sd_array($queryText, $queryParams);
if ($db_version['value'] < $common->db_version) {
    $display_db_version_warning = true;
}

//code to see if the password was changed from notsecure
$queryText = "SELECT * FROM config WHERE setting = 'LOGIN_PASSWORD'";
$queryParams = null;
$admin_password = $common->query_to_sd_array($queryText, $queryParams);
if ($admin_password['value'] == 'notsecure') {
    $display_password_warning = true;
}

$queryText = "SELECT * FROM network_file WHERE ai_summary is not null LIMIT 50";
$queryParams = null;
$files = $common->query_to_md_array($queryText, $queryParams);

$common->print_template_card('Dashboard', 'start');

?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#table_1').DataTable({
            "paging": true,
            "ordering": true,
            "info": false,
            "searching": true,
            "responsive": true,
            "order": [
                [5, "desc"]
            ],
            "sScrollX": "100%",
        })
    });
</script>

<?PHP
if ($display_password_warning) {
    echo "<div class=\"alert alert-danger\" role=\"alert\">The default password for the admin user is still in use.  <a href=\"/?s1=Settings&s2=Configuration&s3=Detail&id=14\">Click here to update the password.</a></div>";
}
if ($display_ollama_warning) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">Ollama is not configured. <a href=\"?s1=Settings&s2=Configuration&s3=Detail&id=4\">Please configure Ollama for this application to function properly.</a></div>";
}
if ($display_doctor_api_warning) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">Doctor API is not configured. <a href=\"?s1=Settings&s2=Configuration&s3=Detail&id=11\">Please configure Doctor API for this application to function properly.</a> <a href=\"/?s1=Docs&s2=Doctor\">Click here for information on how to set up this API endpoint.</a></div>";
}
if ($display_db_version_warning) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">Database structure is out of date.  This application may not function properly. <a href=\"/setup/update/index.php\">Click here to update the database.</a></div>";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4">Welcome to Your Dashboard</h2>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Application Version</h5>
                                <p class="card-text h3">
                                    <?= $common->get_config_value('APP_VERSION') ?>
                                </p>
                                <p class="card-text text-muted">The version of the application</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Document Count</h5>
                                <p class="card-text h3">
                                    <?= number_format($common->query_to_sd_array("SELECT COUNT(*) as count FROM network_file WHERE ai_summary is not null", null)['count']) ?>
                                </p>
                                <p class="card-text text-muted">Total documents in database</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Additional dashboard widget can be added here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<h3 class="mb-4">Recently Analyzed Documents (50)</h3>

<table class="dataTable stripe w-100" id="table_1">
    <thead>
        <tr>
            <th>File Name</th>
            <th>AI Title</th>
            <th>File Path</th>
            <th>Last Found</th>
            <th>Date Created</th>
            <th>Date Modified</th>
            <th class="none">AI Summary</th>
            <th class="none">AI Tags</th>
        </tr>
    </thead>
    <tbody>
        <?PHP

        foreach ($files as $d) {
            $d['last_found'] = $common->sql2date($d['last_found']);
            $d['date_created'] = $common->sql2date($d['date_created']);
            $d['date_modified'] = $common->sql2date($d['date_modified']);
            $d['ai_summary'] = nl2br("\n\n" . $d['ai_summary']);
            echo "<tr>
                        <td>
                            <a target=\"_BLANK\" href=\"/?page=utilities&sub=network_file_detail&id=$d[id]\">$d[name]</a>
                        </td>
                        <td>$d[ai_title]</td>
                        <td>$d[path]</td>
                        
                        <td data-sort=\"" . strtotime($d['last_found']) . "\">$d[last_found]</td>
                        <td data-sort=\"" . strtotime($d['date_created']) . "\">$d[date_created]</td>
                        <td data-sort=\"" . strtotime($d['date_modified']) . "\">$d[date_modified]</td>
                        <td>$d[ai_summary]</td>
                        <td>$d[ai_tags]</td>
                    </tr>";
        }

        ?>
    </tbody>
</table>

<?PHP
$common->print_template_card('Dashboard', 'end');
?>