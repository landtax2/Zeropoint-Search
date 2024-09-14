<?php
$queryText = "SELECT *
  FROM client t1
  WHERE t1.id = :id";
$queryParams = array(':id' => $_GET['id']);
$data = $common->query_to_sd_array($queryText, $queryParams);
$client_inactive = $data['days_till_client_inactive']  * -1;

$common->print_template_card('Client Details', 'start');
?>

<div class="container-fluid">
    <div class="row justify-content-left">
        <div class="col-lg-8 col-md-10">

            <div class="card mb-4">
                <div class="card-header">
                    <h4>Client Information</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>Name:</strong>
                        </div>
                        <div class="col-sm-9">
                            <?PHP echo htmlspecialchars($data['client_name']); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <strong>API Key:</strong>
                        </div>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?PHP echo htmlspecialchars($data['api_key']); ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this)">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Scripts Information</h4>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        The following scripts are used to import and process document data into this application. They provide automated methods for classifying files and facilitating efficient data ingestion from various sources.
                    </p>
                    <p class="mb-3">
                        These scripts will automatically include the API Key shown above, ensuring secure and seamless integration this system.
                    </p>
                    <h5 class="mb-3">Available Scripts</h5>
                    <div class="list-group">
                        <a href="/?s1=Settings&s2=Clients&s3=Scripts&s4=Powershell_file_classification&id=<?PHP echo htmlspecialchars($_GET['id']); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Powershell File Classification</strong>
                                <p class="mb-0 text-muted">Automates file classification using Powershell</p>
                            </div>
                            <span class="badge bg-primary rounded-pill">1</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function copyToClipboard(button) {
        var input = button.previousElementSibling;
        input.select();
        document.execCommand("copy");
        button.textContent = "Copied!";
        setTimeout(function() {
            button.textContent = "Copy";
        }, 2000);
    }
</script>

<?PHP
$common->print_template_card(null, 'end');
?>