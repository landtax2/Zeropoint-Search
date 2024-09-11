<?PHP
$common->print_template_card('Database Settings', 'start');
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Network Files Statistics</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <?php
                    $queryText = "SELECT COUNT(*) as total_records FROM network_file";
                    $result = $common->query_to_sd_array($queryText, []);
                    $totalRecords = $result['total_records'];
                    ?>
                    <h2 class="mb-0"><?php echo number_format($totalRecords); ?></h2>
                    <p class="text-muted mb-0">Total records in network_files</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Database Size</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <?php
                    $queryText = "SELECT pg_size_pretty(pg_database_size(current_database())) as db_size";
                    $result = $common->query_to_sd_array($queryText, []);
                    $dbSize = $result['db_size'];
                    ?>
                    <h2 class="mb-0"><?php echo $dbSize; ?></h2>
                    <p class="text-muted mb-0">Current size of the database</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Database Version</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <h2 class="mb-0"><?php echo $common->get_config_value('DB_VERSION'); ?></h2>
                    <p class="text-muted mb-0">Current database version</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Database Time</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <h2 class="mb-0">
                        <?php
                        $queryText = "SELECT TO_CHAR(CURRENT_TIMESTAMP, 'YYYY-MM-DD HH24:MI:SS') AS current_time";
                        $result = $common->query_to_sd_array($queryText, []);
                        echo $result['current_time'];
                        ?>
                    </h2>
                    <p class="text-muted mb-0">Current database time</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">

        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Time</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <h2 class="mb-0">
                        <?php
                        echo date('Y-m-d H:i:s');
                        ?>
                    </h2>
                    <p class="text-muted mb-0">Current database time</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Database Management</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Warning!</h4>
                        <p>The actions below are irreversible and will permanently delete data. Please proceed with caution.</p>
                        <hr>
                        <p class="mb-0">Make sure you have a backup of your database before performing any of these operations.</p>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button id="emptyNetworkFilesBtn" class="btn btn-danger btn-lg">
                                    <i class="cil-trash mr-2"></i>
                                    Empty Network Files Table
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button id="resetDatabaseBtn" class="btn btn-danger btn-lg">
                                    <i class="cil-reload mr-2"></i>
                                    Reset Database
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emptyNetworkFilesBtn = document.getElementById('emptyNetworkFilesBtn');

        emptyNetworkFilesBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete all records from the network_file table.  There will be no way to recover them unless you have a backup.  This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete all records!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/application_api/settings/index.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'empty_network_files'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'All records have been deleted from the network_file table.',
                                    'success'
                                ).then(() => {
                                    location.reload(); // Reload the page to update statistics
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'Failed to delete records: ' + data.message,
                                    'error'
                                );
                            }
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            Swal.fire(
                                'Error!',
                                'An unexpected error occurred',
                                'error'
                            );
                        });
                }
            });
        });

        // Reset Database Button
        const resetDatabaseBtn = document.getElementById('resetDatabaseBtn');
        resetDatabaseBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will reset the entire database to its initial state. All data will be lost. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reset the database!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/application_api/settings/index.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'reset_database'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Reset Complete!',
                                    'The database has been reset to its initial state.',
                                    'success'
                                ).then(() => {
                                    location.href = '/setup/create/index.php'; // Redirect to setup page
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'Failed to reset database: ' + data.message,
                                    'error'
                                );
                            }
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            Swal.fire(
                                'Error!',
                                'An unexpected error occurred while resetting the database',
                                'error'
                            );
                        });
                }
            });
        });

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // You can add any JavaScript functionality here if needed
        // For example, you could add a refresh button or auto-update feature
    });
</script>



<?php
$common->print_template_card(null, 'end');
?>