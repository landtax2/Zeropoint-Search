<?php
$queryText = "SELECT * FROM config WHERE id = :id";
$queryParams = array(':id' => $_GET['id']);
$config = $common->query_to_sd_array($queryText, $queryParams);
$common->print_template_card('Configuration', 'start');


$override_value = '';
if (isset($_ENV[$config['setting']])) {
    $override_value = $_ENV[$config['setting']];
    $config['editable'] = 0;
} else if (isset($_SERVER[$config['setting']])) {
    $override_value = $_SERVER[$config['setting']];
    $config['editable'] = 0;
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
    <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">Warning!</h4>
        <p>Changing configuration values can significantly impact the functionality and behavior of the application. Please exercise caution when modifying these settings.</p>
        <hr>
        <p class="mb-0">Only make changes if you fully understand their implications. Incorrect modifications may lead to system instability or unexpected behavior. If you're unsure about a setting, consult the documentation or seek assistance from the system administrator before making any changes.</p>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-6">
                <form method="post" action="" class="mb-3">
                    <div class="mb-3 row">
                        <label for="setting" class="col-md-3 col-form-label">Setting Name</label>
                        <div class="col-md-9">
                            <input type="text" readonly class="form-control-plaintext" id="setting" value="<?php echo $config['setting']; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="value" class="col-md-3 col-form-label">Value</label>
                        <div class="col-md-9">
                            <textarea name="value" id="value" class="form-control" <?php echo $config['editable'] == 0 ? 'readonly' : ''; ?>><?php echo htmlspecialchars($config['value']); ?></textarea>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="override_value" class="col-md-3 col-form-label">Overridden Value</label>
                        <div class="col-md-9">
                            <?php echo $override_value; ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="editable" class="col-md-3 col-form-label">Editable</label>
                        <div class="col-md-9">
                            <input type="text" readonly class="form-control-plaintext" id="editable" value="<?php echo $config['editable'] == 1 ? 'Yes' : 'No'; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="description" class="col-md-3 col-form-label">Description</label>
                        <div class="col-md-9">
                            <p id="description" class="form-control-plaintext"><?php echo htmlspecialchars($config['description']); ?></p>
                        </div>
                    </div>
                    <?php if ($config['editable'] == 1): ?>
                        <div class="row">
                            <div class="col-md-9 offset-md-3">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
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