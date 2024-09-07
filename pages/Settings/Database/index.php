<?PHP
$common->print_template_card('Database Settings', 'start');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Network Files Statistics</h5>
                </div>
                <div class="card-body">
                    <?php
                    $queryText = "SELECT COUNT(*) as total_records FROM network_file";
                    $result = $common->query_to_sd_array($queryText, []);
                    $totalRecords = $result['total_records'];
                    ?>
                    <h2 class="mb-0"><?php echo number_format($totalRecords); ?></h2>
                    <p class="text-muted">Total records in network_files</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Database Size</h5>
                </div>
                <div class="card-body">
                    <?php
                    $queryText = "SELECT pg_size_pretty(pg_database_size(current_database())) as db_size";
                    $result = $common->query_to_sd_array($queryText, []);
                    $dbSize = $result['db_size'];
                    ?>
                    <h2 class="mb-0"><?php echo $dbSize; ?></h2>
                    <p class="text-muted">Current size of the database</p>
                </div>
            </div>
        </div>
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Database Management</h5>
                </div>
                <div class="card-body">
                    <button id="emptyNetworkFilesBtn" class="btn btn-danger">Empty Network Files Table</button>
                </div>
                <div class="card-body">
                    <button id="resetDatabaseBtn" class="btn btn-danger">Reset Database</button>
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
                                    location.href = '/'; // Redirect to the home page
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