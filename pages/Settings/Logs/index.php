<?php
$common->print_template_card('Log Files', 'start');

$display_debug_warning = false;

//code to see if the debug config is set
if ($common->get_config_value('DEBUGGING') == '1') {
    $display_debug_warning = true;
}

$log_dir = $_SERVER['DOCUMENT_ROOT'] . '/logs/';
$log_files = glob($log_dir . '*.log');

?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#log_files_table').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "searching": true,
            "responsive": true,
            "order": [
                [2, "desc"]
            ]
        });
    });
</script>

<?PHP
if ($display_debug_warning) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">Debugging is enabled.  Log files will be created as long as the log directory has the correct permissions.  This will log sensitive information.</div>";
} else {
    echo "<div class=\"alert alert-warning\" role=\"alert\">Debugging is disable. Log files will not be created or updated.</div>";
}
?>

<table id="log_files_table" class="display" style="width:100%">
    <thead>
        <tr>
            <th>File Name</th>
            <th>Size</th>
            <th>Last Modified</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($log_files as $file): ?>
            <tr>
                <td><a href="/?s1=Settings&s2=Logs&s3=View&log=<?php echo basename($file); ?>"><?php echo basename($file); ?></a></td>
                <td><?php echo $common->humanFileSize(filesize($file)); ?></td>
                <td><?php echo $common->sql2date_military_time(date("Y-m-d H:i:s", filemtime($file))); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="mt-4">
    <button onclick="emptyAllLogs()" class="btn btn-danger">Empty All Logs</button>
</div>

<script>
    function emptyAllLogs() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to empty all log files? This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, empty them!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/application_api/settings/index.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'empty_all_logs'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Emptied!',
                                'All log files have been emptied successfully.',
                                'success'
                            ).then(() => {
                                location.reload(); // Reload the page to reflect the changes
                            });
                        } else {
                            Swal.fire(
                                'Error',
                                'Failed to empty log files: ' + data.message,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error',
                            'An error occurred while trying to empty log files.',
                            'error'
                        );
                    });
            }
        });
    }
</script>




<?php
$common->print_template_card('', 'end');
?>