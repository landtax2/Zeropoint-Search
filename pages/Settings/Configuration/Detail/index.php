<?php
$queryText = "SELECT * FROM config WHERE id = :id";
$queryParams = array(':id' => $_GET['id']);
$config = $common->query_to_sd_array($queryText, $queryParams);
$common->print_template_card('Configuration', 'start');


$override_value = '';
$override_type = '';

if (isset($common->env[$config['setting']])) {
    $override_value = $common->env[$config['setting']];
    $config['editable'] = 0;
    $override_type = 'ENV File';
} else if (isset($_ENV[$config['setting']])) {
    $override_value = $_ENV[$config['setting']];
    $config['editable'] = 0;
    $override_type = 'Container Environment Variable';
} else if (isset($_SERVER[$config['setting']])) {
    $override_value = $_SERVER[$config['setting']];
    $config['editable'] = 0;
    $override_type = 'Container Environment Variable';
}

?>

<?php
if (!empty($config)) {

?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const id = <?php echo json_encode($_GET['id']); ?>;
                const value = document.getElementById('value').value;
                const setting = document.getElementById('setting').value;

                fetch('/application_api/settings/index.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: id,
                            value: value,
                            action: 'update_config',
                            setting: setting
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Configuration updated successfully',
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update configuration',
                            });
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred',
                        });
                    });
            });
        });
    </script>
    <div class="container-fluid">
        <div class="row justify-content-left">
            <div class="col-lg-8 col-md-10">
                <div class="alert alert-warning" role="alert">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <h4 class="alert-heading mb-0">Warning!</h4>
                    </div>
                    <p class="mb-3">Changing configuration values can significantly impact the functionality and behavior of the application. Please exercise caution when modifying these settings.</p>
                    <hr>
                    <div class="d-flex align-items-start mt-3">
                        <i class="fas fa-info-circle mt-1 me-3"></i>
                        <p class="mb-0">Only make changes if you fully understand their implications. Incorrect modifications may lead to system instability or unexpected behavior. If you're unsure about a setting, consult the documentation or seek assistance from the system administrator before making any changes.</p>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row justify-content-left">
                    <div class="col-lg-8 col-md-10">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Configuration Details</h5>
                            </div>
                            <div class="card-body">
                                <form method="post" action="" class="mb-3">
                                    <div class="mb-3 row">
                                        <label for="setting" class="col-sm-4 col-form-label">Setting Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control-plaintext" id="setting" value="<?php echo htmlspecialchars($config['setting']); ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="value" class="col-sm-4 col-form-label">Database Value</label>
                                        <div class="col-sm-8">
                                            <textarea name="value" id="value" class="form-control" rows="3" <?php echo $config['editable'] == 0 ? 'readonly' : ''; ?>><?php echo htmlspecialchars($config['value']); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="override_value" class="col-sm-4 col-form-label">Overridden Value</label>
                                        <div class="col-sm-8">
                                            <p class="form-control-plaintext"><?php echo htmlspecialchars($override_value); ?></p>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="override_type" class="col-sm-4 col-form-label">Overridden Type</label>
                                        <div class="col-sm-8">
                                            <p class="form-control-plaintext"><?php echo htmlspecialchars($override_type); ?></p>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="editable" class="col-sm-4 col-form-label">Editable</label>
                                        <div class="col-sm-8">
                                            <input type="text" readonly class="form-control-plaintext" id="editable" value="<?php echo $config['editable'] == 1 ? 'Yes' : 'No'; ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="description" class="col-sm-4 col-form-label">Description</label>
                                        <div class="col-sm-8">
                                            <p id="description" class="form-control-plaintext"><?php echo htmlspecialchars($config['description']); ?></p>
                                        </div>
                                    </div>
                                    <?php if ($config['editable'] == 1): ?>
                                        <div class="row">
                                            <div class="col-sm-8 offset-sm-4">
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
    echo "<p>Configuration not found.</p>";
}
?>


<?PHP
$common->print_template_card(null, 'end');
?>