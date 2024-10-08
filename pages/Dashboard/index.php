<?PHP
$display_ollama_warning = false;
$display_doctor_api_warning = false;
$display_db_version_warning = false;
$display_password_warning = false;
$display_debug_warning = false;

//code to see if the debug config is set
if ($common->get_config_value('DEBUGGING') == '1') {
    $display_debug_warning = true;
}
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
if ($common->get_config_value('LOGIN_PASSWORD') == 'notsecure') {
    $display_password_warning = true;
}

//Code to get the version
try {
    $app_version = $common->get_config_value('APP_VERSION');
} catch (Exception $e) {
    $app_version = '1.0.0';
}

$document_count = $common->query_to_sd_array("SELECT COUNT(*) as count FROM network_file WHERE ai_summary is not null", null)['count'];
$document_count_24 = $common->query_to_sd_array("SELECT COUNT(*) AS count FROM network_file WHERE record_created > NOW() - INTERVAL '24 hours' AND ai_summary IS NOT NULL;", null)['count'];
if ($document_count_24 > 0) {
    $documents_per_hour = $document_count_24 / 24;
} else {
    $documents_per_hour = 0;
}

//get the date of the most recently processed file
$most_recently_processed_file_date = $common->query_to_sd_array("SELECT last_found FROM network_file WHERE ai_summary is not null ORDER BY last_found DESC LIMIT 1", null)['last_found'] ?? null;


//get the number of files containing ssn
$ssn_files_count = $common->query_to_sd_array("SELECT COUNT(*) as count FROM network_file WHERE ssn_hard = '1'", null)['count'];
if (empty($ssn_files_count)) {
    $ssn_files_count = 0;
}

//get the number of files credentials
$ai_credentials_count = $common->query_to_sd_array("SELECT COUNT(*) as count FROM network_file WHERE ai_credentials = '1'", null)['count'];
if (empty($ai_credentials_count)) {
    $ai_credentials = 0;
}

//get the number of files containing personal information
$ai_personal_information_count = $common->query_to_sd_array("SELECT COUNT(*) as count FROM network_file WHERE ai_severity > '6'", null)['count'];
if (empty($ai_personal_information_count)) {
    $ai_personal_information_count = 0;
}

$queryText = "SELECT * FROM network_file WHERE ai_summary is not null ORDER BY id DESC LIMIT 50";
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
                [2, "desc"]
            ],
            "sScrollX": "100%",
        })
    });
</script>

<?PHP

if ($document_count == 0) {
    echo "<div class=\"alert alert-danger\" role=\"alert\">Please read the <a href=\"/?s1=Docs\">documents</a> on how to setup the Doctor and Ollama API endpoints.  This application wont be of any use without this being properly configured. This message will go away after the first document has been successfully analyzed.</div>";
}
if ($display_password_warning) {
    echo "<div class=\"alert alert-danger\" role=\"alert\">The default password for the admin user is still in use.  <a href=\"/?s1=Settings&s2=Configuration&s3=Detail&id=14\">Click here to update the password.</a></div>";
}
if ($display_ollama_warning) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">Ollama API endpoint is not defined. <a href=\"?s1=Settings&s2=Configuration&s3=Detail&id=4\">Please configure Ollama for this application to function properly.</a> <a href=\"/?s1=Docs&s2=Ollama\">Click here for information on how to set up this API endpoint.</a></div>";
}
if ($display_doctor_api_warning) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">Doctor API endpoint is not defined. <a href=\"?s1=Settings&s2=Configuration&s3=Detail&id=11\">Please configure Doctor API for this application to function properly.</a> <a href=\"/?s1=Docs&s2=Doctor\">Click here for information on how to set up this API endpoint.</a></div>";
}
if ($display_db_version_warning) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">Database structure is out of date.  Automatically updating in 5 seconds.</div>";
    echo '<meta http-equiv="refresh" content="5; url=/setup/update/index.php" />';
}
if ($display_debug_warning) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">Debugging is enabled. This may log sensitive information and cause the application to behave unpredictably. Disable debugging in the .env file to remove this warning.</div>";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4">Welcome to Your Dashboard</h2>
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Application Version</h5>
                                <p class="card-text h3 mb-2"><?= $app_version ?></p>
                                <p class="card-text text-muted mt-auto"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Document Count</h5>
                                <p class="card-text h3 mb-2"><?= number_format($document_count) ?></p>
                                <p class="card-text text-muted mt-auto">Total documents in database</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Documents Processed (24h)</h5>
                                <p class="card-text h3 mb-2"><?= number_format($document_count_24) ?> @ <?= number_format($documents_per_hour) ?> per hour</p>
                                <p class="card-text text-muted mt-auto">Documents processed in the last 24 hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Most Recently Processed File</h5>
                                <p class="card-text h3 mb-2"><?= $common->sql2date_military_time($most_recently_processed_file_date) ?? 'N/A' ?></p>
                                <p class="card-text text-muted mt-auto"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><a href="/?s1=Reports&s2=Pii&s3=Rgx_SSN">SSN Files</a></h5>
                                <p class="card-text h3 mb-2"><?= number_format($ssn_files_count) ?></p>
                                <p class="card-text text-muted mt-auto">SSN (Regex Match)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><a href="/?s1=Reports&s2=Pii&s3=Ai_credentials">Credentials Files</a></h5>
                                <p class="card-text h3 mb-2"><?= number_format($ai_credentials_count) ?></p>
                                <p class="card-text text-muted mt-auto">Credentials (AI Match)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><a href="/?s1=Reports&s2=Pii&s3=Ai_severity">Personal Information Files</a></h5>
                                <p class="card-text h3 mb-2"><?= number_format($ai_personal_information_count) ?></p>
                                <p class="card-text text-muted mt-auto">Personal Information (AI Match)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <!-- Space for additional dashboard widgets -->
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
            <th>Record Created</th>
            <th class="none">File Path</th>
            <th class="none">Last Found</th>
            <th class="none">Date Created</th>
            <th class="none">Date Modified</th>
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
            $d['record_created'] = $common->sql2date_military_time($d['record_created']);
            $d['ai_summary'] = nl2br("\n\n" . $d['ai_summary']);
            echo "<tr>
                        <td>
                            <a target=\"_BLANK\" href=\"/?s1=File&s2=Detail&id=$d[id]\">$d[name]</a>
                        </td>
                        <td>$d[ai_title]</td>
                        <td data-sort=\"" . strtotime($d['record_created']) . "\">$d[record_created]</td>
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