<?PHP
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

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4">Welcome to Your Dashboard</h2>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Document Count</h5>
                                <p class="card-text h3">
                                    <?= number_format($common->query_to_sd_array("SELECT COUNT(*) as count FROM network_file", null)['count']) ?>
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